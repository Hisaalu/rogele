<?php
// File: /models/User.php
require_once __DIR__ . '/../config/database.php';

class User {
    private $db;
    private $conn;
    
    public function __construct() {
        $this->db = Database::getInstance();
        $this->conn = $this->db->getConnection();
    }
    
    /**
     * Generate registration number for learners
     */
    private function generateRegistrationNumber($class) {
        $prefix = 'ROG';
        $classMap = [
            'p1' => 'P1', 'primary 1' => 'P1', '1' => 'P1',
            'p2' => 'P2', 'primary 2' => 'P2', '2' => 'P2',
            'p3' => 'P3', 'primary 3' => 'P3', '3' => 'P3',
            'p4' => 'P4', 'primary 4' => 'P4', '4' => 'P4',
            'p5' => 'P5', 'primary 5' => 'P5', '5' => 'P5',
            'p6' => 'P6', 'primary 6' => 'P6', '6' => 'P6',
            'p7' => 'P7', 'primary 7' => 'P7', '7' => 'P7',
        ];
        
        $classCode = $classMap[strtolower(trim($class))] ?? 'P1';
        $unique = str_pad(mt_rand(1, 999), 3, '0', STR_PAD_LEFT);
        
        return $prefix . '-' . $classCode . '-' . $unique;
    }
    
    /**
     * Register new user
     */
    public function register($data) {
        try {
            // Check if email already exists
            $checkQuery = "SELECT id FROM users WHERE email = :email";
            $checkStmt = $this->conn->prepare($checkQuery);
            $checkStmt->execute([':email' => $data['email']]);
            
            if ($checkStmt->fetch()) {
                return ['success' => false, 'error' => 'Email already registered'];
            }
            
            // Hash password
            $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);
            
            // Generate registration number for learners
            $registrationNumber = null;
            if (isset($data['role']) && $data['role'] === 'learner') {
                $registrationNumber = $this->generateRegistrationNumber($data['class'] ?? 'P1');
            }
            
            // Get class_id if class is provided
            $classId = null;
            if (isset($data['class']) && !empty($data['class'])) {
                $classQuery = "SELECT id FROM classes WHERE level = :level OR name LIKE :name";
                $classStmt = $this->conn->prepare($classQuery);
                $classStmt->execute([
                    ':level' => strtoupper($data['class']),
                    ':name' => '%' . $data['class'] . '%'
                ]);
                $class = $classStmt->fetch();
                if ($class) {
                    $classId = $class['id'];
                }
            }
            
            // Insert user (auto-verify for now)
            $query = "INSERT INTO users (
                registration_number, email, password, first_name, last_name, 
                phone, role, class_id, email_verified, is_active, created_at
            ) VALUES (
                :registration_number, :email, :password, :first_name, :last_name,
                :phone, :role, :class_id, 1, 1, NOW()
            )";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute([
                ':registration_number' => $registrationNumber,
                ':email' => $data['email'],
                ':password' => $hashedPassword,
                ':first_name' => $data['first_name'] ?? null,
                ':last_name' => $data['last_name'] ?? null,
                ':phone' => $data['phone'] ?? null,
                ':role' => $data['role'] ?? 'external',
                ':class_id' => $classId
            ]);
            
            $userId = $this->conn->lastInsertId();
            
            // Start free trial for external users
            if (($data['role'] ?? 'external') === 'external') {
                $this->startFreeTrial($userId);
            }
            
            // Log activity
            $this->logActivity($userId, 'REGISTRATION', 'User registered successfully');
            
            // Return user data for auto-login
            $userData = $this->getById($userId);
            unset($userData['password']);
            
            return [
                'success' => true, 
                'user_id' => $userId, 
                'user' => $userData,
                'message' => 'Registration successful!'
            ];
            
        } catch (PDOException $e) {
            error_log("Registration error: " . $e->getMessage());
            return ['success' => false, 'error' => 'Registration failed. Please try again.'];
        }
    }
    
    /**
     * Login user
     */
    public function login($username, $password) {
        try {
            error_log("Login attempt for username: " . $username);
            
            $query = "SELECT u.*, c.name as class_name 
                    FROM users u 
                    LEFT JOIN classes c ON u.class_id = c.id 
                    WHERE u.email = :email OR u.registration_number = :reg_no";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindValue(':email', $username);
            $stmt->bindValue(':reg_no', $username);
            $stmt->execute();
            
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$user) {
                error_log("User not found: " . $username);
                return ['success' => false, 'error' => 'User not found'];
            }
            
            if (!password_verify($password, $user['password'])) {
                error_log("Password verification failed for user: " . $username);
                return ['success' => false, 'error' => 'Invalid password'];
            }
            
            if (!$user['is_active']) {
                return ['success' => false, 'error' => 'Your account is not active. Please contact support.'];
            }
            
            if ($user['is_suspended']) {
                return ['success' => false, 'error' => 'Your account has been suspended. Please contact support.'];
            }
            
            // Update last login
            $updateQuery = "UPDATE users SET last_login = NOW() WHERE id = :id";
            $updateStmt = $this->conn->prepare($updateQuery);
            $updateStmt->execute([':id' => $user['id']]);
            
            // Log activity
            $this->logActivity($user['id'], 'LOGIN', 'User logged in successfully');
            
            // Remove password from result
            unset($user['password']);
            
            return ['success' => true, 'user' => $user];
            
        } catch (PDOException $e) {
            error_log("Login error: " . $e->getMessage());
            return ['success' => false, 'error' => 'Database error: ' . $e->getMessage()];
        }
    }
    
    /**
     * Get user by ID
     */
    public function getById($id) {
        try {
            $query = "SELECT u.*, c.name as class_name 
                    FROM users u 
                    LEFT JOIN classes c ON u.class_id = c.id 
                    WHERE u.id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([':id' => $id]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log("Get user error: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Get user by email
     */
    public function getByEmail($email) {
        try {
            $query = "SELECT * FROM users WHERE email = :email";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([':email' => $email]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log("Get user by email error: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Get user profile with all details
     */
    public function getProfile($userId) {
        try {
            $query = "SELECT u.*, 
                    ft.start_date as trial_start, 
                    ft.end_date as trial_end,
                    s.plan_type, s.status as subscription_status, 
                    s.end_date as subscription_end
                    FROM users u
                    LEFT JOIN free_trials ft ON u.id = ft.user_id
                    LEFT JOIN subscriptions s ON u.id = s.user_id AND s.status = 'active'
                    WHERE u.id = :id";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute([':id' => $userId]);
            
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user) {
                unset($user['password']);
            }
            
            return $user;
        } catch (PDOException $e) {
            error_log("Get profile error: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Update profile
     */
    public function updateProfile($userId, $data) {
        try {
            // Check if email is being changed and if it's already taken
            $currentUser = $this->getById($userId);
            if ($currentUser && $currentUser['email'] !== $data['email']) {
                $checkQuery = "SELECT id FROM users WHERE email = :email AND id != :id";
                $checkStmt = $this->conn->prepare($checkQuery);
                $checkStmt->execute([
                    ':email' => $data['email'],
                    ':id' => $userId
                ]);
                if ($checkStmt->fetch()) {
                    return ['success' => false, 'error' => 'Email already taken by another user'];
                }
            }
            
            $query = "UPDATE users SET 
                    first_name = :first_name,
                    last_name = :last_name,
                    email = :email,
                    phone = :phone,
                    updated_at = NOW()
                    WHERE id = :id";
            
            $stmt = $this->conn->prepare($query);
            $result = $stmt->execute([
                ':first_name' => $data['first_name'],
                ':last_name' => $data['last_name'],
                ':email' => $data['email'],
                ':phone' => $data['phone'],
                ':id' => $userId
            ]);
            
            if ($result) {
                // REMOVED: Session update code - this should NOT be here
                // Session updates should be handled in the controller only for the logged-in user
                
                $this->logActivity($userId, 'PROFILE_UPDATE', 'User updated profile');
                return ['success' => true, 'message' => 'Profile updated successfully'];
            }
            
            return ['success' => false, 'error' => 'Failed to update profile'];
            
        } catch (PDOException $e) {
            error_log("Profile update error: " . $e->getMessage());
            return ['success' => false, 'error' => 'Database error occurred'];
        }
    }

    /**
     * Update user as admin (without affecting session)
     */
    public function updateUserAsAdmin($userId, $data) {
        try {
            // Check if email is being changed and if it's already taken
            $currentUser = $this->getById($userId);
            if ($currentUser && $currentUser['email'] !== $data['email']) {
                $checkQuery = "SELECT id FROM users WHERE email = :email AND id != :id";
                $checkStmt = $this->conn->prepare($checkQuery);
                $checkStmt->execute([
                    ':email' => $data['email'],
                    ':id' => $userId
                ]);
                if ($checkStmt->fetch()) {
                    return ['success' => false, 'error' => 'Email already taken by another user'];
                }
            }
            
            $query = "UPDATE users SET 
                    first_name = :first_name,
                    last_name = :last_name,
                    email = :email,
                    phone = :phone,
                    role = :role,
                    updated_at = NOW()
                    WHERE id = :id";
            
            $stmt = $this->conn->prepare($query);
            $result = $stmt->execute([
                ':first_name' => $data['first_name'],
                ':last_name' => $data['last_name'],
                ':email' => $data['email'],
                ':phone' => $data['phone'],
                ':role' => $data['role'],
                ':id' => $userId
            ]);
            
            if ($result) {
                // DO NOT update session - this is for admin editing another user
                $this->logActivity($userId, 'ADMIN_UPDATE', 'User updated by admin');
                return ['success' => true, 'message' => 'User updated successfully'];
            }
            
            return ['success' => false, 'error' => 'Failed to update user'];
            
        } catch (PDOException $e) {
            error_log("Admin update user error: " . $e->getMessage());
            return ['success' => false, 'error' => 'Database error occurred'];
        }
    }
    
    /**
     * Change password
     */
    public function changePassword($userId, $currentPassword, $newPassword) {
        try {
            // Verify current password
            $query = "SELECT password FROM users WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([':id' => $userId]);
            $user = $stmt->fetch();
            
            if (!$user) {
                return ['success' => false, 'error' => 'User not found'];
            }
            
            if (!password_verify($currentPassword, $user['password'])) {
                return ['success' => false, 'error' => 'Current password is incorrect'];
            }
            
            // Update password
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            $updateQuery = "UPDATE users SET password = :password, updated_at = NOW() WHERE id = :id";
            $updateStmt = $this->conn->prepare($updateQuery);
            $result = $updateStmt->execute([
                ':password' => $hashedPassword,
                ':id' => $userId
            ]);
            
            if ($result) {
                $this->logActivity($userId, 'PASSWORD_CHANGE', 'User changed password');
                return ['success' => true, 'message' => 'Password changed successfully'];
            }
            
            return ['success' => false, 'error' => 'Failed to change password'];
            
        } catch (PDOException $e) {
            error_log("Password change error: " . $e->getMessage());
            return ['success' => false, 'error' => 'Database error occurred'];
        }
    }
    
    /**
     * Delete account (for users deleting their own account)
     */
    public function deleteAccount($userId, $password) {
        try {
            // First, get the user to verify they exist
            $user = $this->getById($userId);
            if (!$user) {
                return ['success' => false, 'error' => 'User not found'];
            }
            
            // Verify password
            if (!password_verify($password, $user['password'])) {
                return ['success' => false, 'error' => 'Current password is incorrect'];
            }
            
            // Start transaction
            $this->conn->beginTransaction();
            
            // Delete related records
            $tables = ['subscriptions', 'free_trials', 'payments', 'activity_logs', 'bookmarks', 'quiz_attempts'];
            
            foreach ($tables as $table) {
                try {
                    // Check if table exists
                    $checkTable = $this->conn->query("SHOW TABLES LIKE '$table'");
                    if ($checkTable->rowCount() > 0) {
                        $deleteQuery = "DELETE FROM $table WHERE user_id = :user_id";
                        $deleteStmt = $this->conn->prepare($deleteQuery);
                        $deleteStmt->execute([':user_id' => $userId]);
                    }
                } catch (PDOException $e) {
                    // Table might not exist, continue
                    error_log("Warning: Could not delete from $table: " . $e->getMessage());
                }
            }
            
            // Finally delete the user
            $deleteUser = "DELETE FROM users WHERE id = :id";
            $deleteUserStmt = $this->conn->prepare($deleteUser);
            $deleteUserStmt->execute([':id' => $userId]);
            
            // Check if user was actually deleted
            if ($deleteUserStmt->rowCount() === 0) {
                throw new Exception('Failed to delete user record');
            }
            
            // Commit transaction
            $this->conn->commit();
            
            return ['success' => true, 'message' => 'Account deleted successfully'];
            
        } catch (PDOException $e) {
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }
            error_log("Delete account error: " . $e->getMessage());
            return ['success' => false, 'error' => 'Database error occurred. Please try again later.'];
        } catch (Exception $e) {
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }
            error_log("Delete account error: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Upload profile photo
     */
    public function uploadProfilePhoto($userId, $file) {
        try {
            $targetDir = __DIR__ . '/../public/uploads/profiles/';
            
            if (!file_exists($targetDir)) {
                mkdir($targetDir, 0777, true);
            }
            
            $fileName = time() . '_' . basename($file['name']);
            $targetFile = $targetDir . $fileName;
            
            // Validate image
            $check = getimagesize($file['tmp_name']);
            if ($check === false) {
                return ['success' => false, 'error' => 'File is not an image'];
            }
            
            // Check file size (max 2MB)
            if ($file['size'] > 2097152) {
                return ['success' => false, 'error' => 'File size must be less than 2MB'];
            }
            
            // Allow certain file formats
            $imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));
            $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];
            
            if (!in_array($imageFileType, $allowedTypes)) {
                return ['success' => false, 'error' => 'Only JPG, JPEG, PNG & GIF files are allowed'];
            }
            
            if (move_uploaded_file($file['tmp_name'], $targetFile)) {
                $photoPath = 'uploads/profiles/' . $fileName;
                $query = "UPDATE users SET profile_photo = :photo WHERE id = :id";
                $stmt = $this->conn->prepare($query);
                $stmt->execute([
                    ':photo' => $photoPath,
                    ':id' => $userId
                ]);
                
                return ['success' => true, 'photo' => $photoPath];
            }
            
            return ['success' => false, 'error' => 'Failed to upload photo'];
            
        } catch (PDOException $e) {
            error_log("Photo upload error: " . $e->getMessage());
            return ['success' => false, 'error' => 'Failed to upload photo'];
        }
    }
    
    /**
     * Start free trial for external users
     */
    private function startFreeTrial($userId) {
        try {
            $startDate = date('Y-m-d H:i:s');
            $endDate = date('Y-m-d H:i:s', strtotime('+' . FREE_TRIAL_DAYS . ' days'));
            
            $query = "INSERT INTO free_trials (user_id, start_date, end_date) 
                    VALUES (:user_id, :start_date, :end_date)";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([
                ':user_id' => $userId,
                ':start_date' => $startDate,
                ':end_date' => $endDate
            ]);
            
            return true;
        } catch (PDOException $e) {
            error_log("Free trial error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Check if user has active access
     */
    public function hasAccess($userId) {
        try {
            $userQuery = "SELECT role FROM users WHERE id = :id";
            $userStmt = $this->conn->prepare($userQuery);
            $userStmt->execute([':id' => $userId]);
            $user = $userStmt->fetch();
            
            if (!$user) {
                return false;
            }
            
            // Learners, teachers, and admins always have access
            if (in_array($user['role'], ['learner', 'teacher', 'admin'])) {
                return true;
            }
            
            // Check free trial
            $trialQuery = "SELECT * FROM free_trials WHERE user_id = :user_id AND end_date > NOW()";
            $trialStmt = $this->conn->prepare($trialQuery);
            $trialStmt->execute([':user_id' => $userId]);
            if ($trialStmt->fetch()) {
                return true;
            }
            
            // Check active subscription
            $subQuery = "SELECT * FROM subscriptions WHERE user_id = :user_id AND status = 'active' AND end_date > NOW()";
            $subStmt = $this->conn->prepare($subQuery);
            $subStmt->execute([':user_id' => $userId]);
            
            return $subStmt->fetch() ? true : false;
            
        } catch (PDOException $e) {
            error_log("Access check error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Verify email
     */
    public function verifyEmail($token) {
        try {
            $query = "UPDATE users SET email_verified = 1, verification_token = NULL 
                    WHERE verification_token = :token";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([':token' => $token]);
            
            if ($stmt->rowCount() > 0) {
                return ['success' => true, 'message' => 'Email verified successfully'];
            }
            
            return ['success' => false, 'error' => 'Invalid or expired verification token'];
            
        } catch (PDOException $e) {
            error_log("Email verification error: " . $e->getMessage());
            return ['success' => false, 'error' => 'Verification failed'];
        }
    }
    
    /**
     * Request password reset
     */
    public function requestPasswordReset($email) {
        try {
            $user = $this->getByEmail($email);
            
            if (!$user) {
                return ['success' => false, 'error' => 'Email not found'];
            }
            
            $resetToken = bin2hex(random_bytes(32));
            $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
            
            $query = "UPDATE users SET reset_token = :token, reset_expires = :expires WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([
                ':token' => $resetToken,
                ':expires' => $expires,
                ':id' => $user['id']
            ]);
            
            $this->sendResetEmail($email, $resetToken, $user['first_name']);
            
            return ['success' => true, 'message' => 'Password reset instructions sent to your email'];
            
        } catch (PDOException $e) {
            error_log("Password reset request error: " . $e->getMessage());
            return ['success' => false, 'error' => 'Failed to process request'];
        }
    }
    
    /**
     * Reset password
     */
    public function resetPassword($token, $newPassword) {
        try {
            $query = "SELECT id FROM users WHERE reset_token = :token AND reset_expires > NOW()";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([':token' => $token]);
            $user = $stmt->fetch();
            
            if (!$user) {
                return ['success' => false, 'error' => 'Invalid or expired reset token'];
            }
            
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            $updateQuery = "UPDATE users SET password = :password, reset_token = NULL, reset_expires = NULL WHERE id = :id";
            $updateStmt = $this->conn->prepare($updateQuery);
            $updateStmt->execute([
                ':password' => $hashedPassword,
                ':id' => $user['id']
            ]);
            
            return ['success' => true, 'message' => 'Password reset successful'];
            
        } catch (PDOException $e) {
            error_log("Password reset error: " . $e->getMessage());
            return ['success' => false, 'error' => 'Failed to reset password'];
        }
    }
    
    /**
     * Save reset token
     */
    public function saveResetToken($userId, $token, $expires) {
        try {
            $query = "UPDATE users SET 
                      reset_token = :token, 
                      reset_expires = :expires 
                      WHERE id = :id";
            
            $stmt = $this->conn->prepare($query);
            return $stmt->execute([
                ':token' => $token,
                ':expires' => $expires,
                ':id' => $userId
            ]);
        } catch (PDOException $e) {
            error_log("Save reset token error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get user by reset token
     */
    public function getUserByResetToken($token) {
        try {
            $query = "SELECT * FROM users 
                      WHERE reset_token = :token 
                      AND reset_expires > NOW() 
                      LIMIT 1";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute([':token' => $token]);
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Get user by token error: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Clear reset token
     */
    public function clearResetToken($userId) {
        try {
            $query = "UPDATE users SET 
                      reset_token = NULL, 
                      reset_expires = NULL 
                      WHERE id = :id";
            
            $stmt = $this->conn->prepare($query);
            return $stmt->execute([':id' => $userId]);
        } catch (PDOException $e) {
            error_log("Clear reset token error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Update password (without current password verification)
     */
    public function updatePassword($userId, $newPassword) {
        try {
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            
            $query = "UPDATE users SET 
                      password = :password,
                      updated_at = NOW()
                      WHERE id = :id";
            
            $stmt = $this->conn->prepare($query);
            $result = $stmt->execute([
                ':password' => $hashedPassword,
                ':id' => $userId
            ]);
            
            if ($result) {
                $this->logActivity($userId, 'PASSWORD_RESET', 'User reset password via email');
                return ['success' => true, 'message' => 'Password updated successfully'];
            }
            
            return ['success' => false, 'error' => 'Failed to update password'];
            
        } catch (PDOException $e) {
            error_log("Update password error: " . $e->getMessage());
            return ['success' => false, 'error' => 'Database error occurred'];
        }
    }
    
    /**
     * Log activity
     */
    private function logActivity($userId, $action, $description) {
        try {
            $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
            $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
            
            $query = "INSERT INTO activity_logs (user_id, action, description, ip_address, user_agent) 
                    VALUES (:user_id, :action, :description, :ip, :ua)";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([
                ':user_id' => $userId,
                ':action' => $action,
                ':description' => $description,
                ':ip' => $ip,
                ':ua' => $userAgent
            ]);
        } catch (PDOException $e) {
            error_log("Activity log error: " . $e->getMessage());
        }
    }
    
    /**
     * Send verification email
     */
    private function sendVerificationEmail($email, $token, $name) {
        $verificationLink = BASE_URL . "/verify-email?token=" . $token;
        error_log("Verification email would be sent to: $email with link: $verificationLink");
    }
    
    /**
     * Send reset email
     */
    private function sendResetEmail($email, $token, $name) {
        $resetLink = BASE_URL . "/reset-password?token=" . $token;
        error_log("Reset email would be sent to: $email with link: $resetLink");
    }
    
    /**
     * =====================================================
     * ADMIN METHODS
     * =====================================================
     */
    
    /**
     * Get all users (for admin) - SINGLE VERSION
     */
    public function getAllUsers($role = null, $limit = 20, $offset = 0) {
        try {
            $query = "SELECT u.*, c.name as class_name 
                      FROM users u 
                      LEFT JOIN classes c ON u.class_id = c.id";
            
            $params = [];
            
            if ($role) {
                $query .= " WHERE u.role = :role";
                $params[':role'] = $role;
            }
            
            $query .= " ORDER BY u.created_at DESC";
            
            if ($limit > 0) {
                $query .= " LIMIT :limit OFFSET :offset";
            }
            
            $stmt = $this->conn->prepare($query);
            
            if ($role) {
                $stmt->bindValue(':role', $role);
            }
            
            if ($limit > 0) {
                $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
                $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            }
            
            $stmt->execute();
            return $stmt->fetchAll();
            
        } catch (PDOException $e) {
            error_log("Get all users error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Search users by name or email
     */
    public function searchUsers($keyword) {
        try {
            $query = "SELECT u.*, c.name as class_name 
                      FROM users u 
                      LEFT JOIN classes c ON u.class_id = c.id 
                      WHERE u.first_name LIKE :keyword 
                         OR u.last_name LIKE :keyword 
                         OR u.email LIKE :keyword
                         OR CONCAT(u.first_name, ' ', u.last_name) LIKE :keyword
                      ORDER BY u.created_at DESC
                      LIMIT 50";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute([':keyword' => '%' . $keyword . '%']);
            
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Search users error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Suspend user
     */
    public function suspendUser($userId) {
        try {
            $query = "UPDATE users SET is_suspended = 1, updated_at = NOW() WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([':id' => $userId]);
            
            if ($stmt->rowCount() > 0) {
                $this->logActivity($userId, 'SUSPEND', 'User account suspended');
                return ['success' => true, 'message' => 'User suspended successfully'];
            }
            
            return ['success' => false, 'error' => 'User not found'];
            
        } catch (PDOException $e) {
            error_log("Suspend user error: " . $e->getMessage());
            return ['success' => false, 'error' => 'Failed to suspend user'];
        }
    }
    
    /**
     * Activate user
     */
    public function activateUser($userId) {
        try {
            $query = "UPDATE users SET is_suspended = 0, updated_at = NOW() WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([':id' => $userId]);
            
            if ($stmt->rowCount() > 0) {
                $this->logActivity($userId, 'ACTIVATE', 'User account activated');
                return ['success' => true, 'message' => 'User activated successfully'];
            }
            
            return ['success' => false, 'error' => 'User not found'];
            
        } catch (PDOException $e) {
            error_log("Activate user error: " . $e->getMessage());
            return ['success' => false, 'error' => 'Failed to activate user'];
        }
    }
    
    /**
     * Delete user (admin version)
     */
    public function deleteUser($userId) {
        try {
            // Check if user exists
            $user = $this->getById($userId);
            if (!$user) {
                return ['success' => false, 'error' => 'User not found'];
            }
            
            // Start transaction
            $this->conn->beginTransaction();
            
            // Delete related records
            $tables = ['subscriptions', 'free_trials', 'payments', 'activity_logs', 'bookmarks', 'quiz_attempts'];
            
            foreach ($tables as $table) {
                $deleteQuery = "DELETE FROM $table WHERE user_id = :user_id";
                $deleteStmt = $this->conn->prepare($deleteQuery);
                $deleteStmt->execute([':user_id' => $userId]);
            }
            
            // Finally delete user
            $deleteUser = "DELETE FROM users WHERE id = :id";
            $deleteUserStmt = $this->conn->prepare($deleteUser);
            $deleteUserStmt->execute([':id' => $userId]);
            
            $this->conn->commit();
            
            $this->logActivity($userId, 'DELETE', 'User account deleted by admin');
            
            return ['success' => true, 'message' => 'User deleted successfully'];
            
        } catch (PDOException $e) {
            $this->conn->rollBack();
            error_log("Delete user error: " . $e->getMessage());
            return ['success' => false, 'error' => 'Failed to delete user'];
        }
    }

    /**
     * Get count of active users today
     */
    public function getActiveToday() {
        try {
            $query = "SELECT COUNT(DISTINCT user_id) as count 
                    FROM activity_logs 
                    WHERE DATE(created_at) = CURDATE()";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $result = $stmt->fetch();
            return $result['count'] ?? 0;
        } catch (PDOException $e) {
            error_log("Get active today error: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Get count of new users today
     */
    public function getNewUsersToday() {
        try {
            $query = "SELECT COUNT(*) as count 
                    FROM users 
                    WHERE DATE(created_at) = CURDATE()";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $result = $stmt->fetch();
            return $result['count'] ?? 0;
        } catch (PDOException $e) {
            error_log("Get new users today error: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Get user registration statistics for a date range
     */
    public function getUserRegistrationStats($start_date, $end_date) {
        try {
            $query = "SELECT 
                        DATE(created_at) as date,
                        COUNT(*) as total,
                        SUM(CASE WHEN role = 'admin' THEN 1 ELSE 0 END) as admins,
                        SUM(CASE WHEN role = 'teacher' THEN 1 ELSE 0 END) as teachers,
                        SUM(CASE WHEN role = 'learner' THEN 1 ELSE 0 END) as learners,
                        SUM(CASE WHEN role = 'external' THEN 1 ELSE 0 END) as external
                    FROM users
                    WHERE DATE(created_at) BETWEEN :start_date AND :end_date
                    GROUP BY DATE(created_at)
                    ORDER BY date DESC";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute([
                ':start_date' => $start_date,
                ':end_date' => $end_date
            ]);
            
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Get user registration stats error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get students by teacher - SIMPLIFIED VERSION
     */
    public function getStudentsByTeacher($teacherId, $classId = null, $search = null) {
        try {
            $query = "SELECT u.*, c.name as class_name 
                    FROM users u
                    LEFT JOIN classes c ON u.class_id = c.id
                    WHERE (u.role = 'learner' OR u.role = 'external')
                    AND u.is_active = 1";
            
            $params = [];
            
            if ($classId) {
                $query .= " AND u.class_id = :class_id";
                $params[':class_id'] = $classId;
            }
            
            if ($search && !empty($search)) {
                $query .= " AND (
                            u.first_name LIKE :search 
                            OR u.last_name LIKE :search 
                            OR u.email LIKE :search
                            OR CONCAT(u.first_name, ' ', u.last_name) LIKE :search
                        )";
                $params[':search'] = '%' . $search . '%';
            }
            
            $query .= " ORDER BY u.role, u.first_name, u.last_name LIMIT 100";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute($params);
            
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Get students by teacher error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get students count by teacher with filters
     */
    public function countStudentsByTeacher($teacherId, $classId = null, $search = null) {
        try {
            $query = "SELECT COUNT(DISTINCT u.id) as total
                    FROM users u
                    LEFT JOIN classes c ON u.class_id = c.id
                    LEFT JOIN subjects s ON c.id = s.class_id
                    WHERE (u.role = 'learner' OR u.role = 'external')
                    AND u.is_active = 1";
            
            $params = [];
            
            if ($teacherId) {
                $query .= " AND (s.teacher_id = :teacher_id";
                
                if (!$classId) {
                    $query .= " OR u.class_id IS NULL";
                }
                $query .= ")";
                
                $params[':teacher_id'] = $teacherId;
            }
            
            if ($classId) {
                $query .= " AND u.class_id = :class_id";
                $params[':class_id'] = $classId;
            }
            
            if ($search && !empty($search)) {
                $query .= " AND (
                            u.first_name LIKE :search 
                            OR u.last_name LIKE :search 
                            OR u.email LIKE :search
                            OR CONCAT(u.first_name, ' ', u.last_name) LIKE :search
                        )";
                $params[':search'] = '%' . $search . '%';
            }
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute($params);
            $result = $stmt->fetch();
            
            return $result['total'] ?? 0;
        } catch (PDOException $e) {
            error_log("Count students by teacher error: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Get all students (for debugging)
     */
    public function getAllStudents() {
        try {
            $query = "SELECT u.*, c.name as class_name 
                    FROM users u
                    LEFT JOIN classes c ON u.class_id = c.id
                    WHERE (u.role = 'learner' OR u.role = 'external')
                    AND u.is_active = 1
                    ORDER BY u.role, u.first_name";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Get all students error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get remaining trial days for a user
     * 
     * @param int $userId The user ID
     * @param int $trialDays Total trial days (default 60)
     * @return int Remaining trial days (0 if expired or subscribed)
     */
    public function getRemainingTrialDays($userId, $trialDays = 60) {
        try {
            // Check if user has an active subscription
            $subscriptionModel = new Subscription();
            $activeSubscription = $subscriptionModel->getCurrentSubscription($userId);
            
            // If user has an active subscription, no trial days remaining
            if ($activeSubscription) {
                return 0;
            }
            
            // Get user's creation date (trial start date)
            $sql = "SELECT created_at FROM users WHERE id = :user_id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
            $stmt->execute();
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$result) {
                return $trialDays;
            }
            
            $createdAt = new DateTime($result['created_at']);
            $now = new DateTime();
            
            // Calculate days passed since creation
            $daysPassed = $createdAt->diff($now)->days;
            
            // Calculate remaining days
            $remainingDays = max(0, $trialDays - $daysPassed);
            
            return $remainingDays;
            
        } catch (Exception $e) {
            error_log("Error calculating remaining trial days: " . $e->getMessage());
            return $trialDays;
        }
    }

    /**
     * Get user's trial end date
     * 
     * @param int $userId User ID
     * @param int $trialDays Total trial days (default 60)
     * @return string|null Trial end date or null
     */
    public function getTrialEndDate($userId, $trialDays = 60) {
        try {
            $sql = "SELECT created_at FROM users WHERE id = :user_id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
            $stmt->execute();
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$result) {
                return null;
            }
            
            $createdAt = new DateTime($result['created_at']);
            $createdAt->modify("+{$trialDays} days");
            
            return $createdAt->format('Y-m-d H:i:s');
            
        } catch (Exception $e) {
            error_log("Error calculating trial end date: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Check if user is still in trial period
     * 
     * @param int $userId The user ID
     * @param int $trialDays Total trial days (default 60)
     * @return bool True if still in trial, false otherwise
     */
    public function isInTrialPeriod($userId, $trialDays = 60) {
        try {
            // First check if user has active subscription
            $subscriptionModel = new Subscription();
            $activeSubscription = $subscriptionModel->getCurrentSubscription($userId);
            
            // If they have an active subscription, they're not in trial
            if ($activeSubscription) {
                return false;
            }
            
            // Get user's creation date
            $sql = "SELECT created_at FROM users WHERE id = :user_id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
            $stmt->execute();
            
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$user) {
                return false;
            }
            
            $createdAt = new DateTime($user['created_at']);
            $now = new DateTime();
            $daysPassed = $createdAt->diff($now)->days;
            
            return $daysPassed < $trialDays;
            
        } catch (Exception $e) {
            error_log("Error checking trial period: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get trial status for user
     * 
     * @param int $userId The user ID
     * @param int $trialDays Total trial days
     * @return array Trial status information
     */
    public function getTrialStatus($userId, $trialDays = 60) {
        try {
            $subscriptionModel = new Subscription();
            $activeSubscription = $subscriptionModel->getCurrentSubscription($userId);
            
            if ($activeSubscription) {
                return [
                    'is_trial' => false,
                    'has_subscription' => true,
                    'remaining_days' => 0,
                    'trial_ended' => false,
                    'message' => 'You have an active subscription!'
                ];
            }
            
            $sql = "SELECT created_at FROM users WHERE id = :user_id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
            $stmt->execute();
            
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$user) {
                return [
                    'is_trial' => false,
                    'has_subscription' => false,
                    'remaining_days' => 0,
                    'trial_ended' => true,
                    'message' => 'Account error'
                ];
            }
            
            $createdAt = new DateTime($user['created_at']);
            $now = new DateTime();
            $daysPassed = $createdAt->diff($now)->days;
            $remainingDays = max(0, $trialDays - $daysPassed);
            
            return [
                'is_trial' => $remainingDays > 0,
                'has_subscription' => false,
                'remaining_days' => $remainingDays,
                'trial_ended' => $remainingDays <= 0,
                'trial_start_date' => $user['created_at'],
                'trial_end_date' => $createdAt->modify("+{$trialDays} days")->format('Y-m-d H:i:s'),
                'message' => $remainingDays > 0 ? "Trial ends in {$remainingDays} days" : "Trial has ended"
            ];
            
        } catch (Exception $e) {
            error_log("Error getting trial status: " . $e->getMessage());
            return [
                'is_trial' => false,
                'has_subscription' => false,
                'remaining_days' => 0,
                'trial_ended' => true,
                'message' => 'Error checking trial status'
            ];
        }
    }
}
?>