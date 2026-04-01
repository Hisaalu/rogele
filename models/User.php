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
     * Registration of new users
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
            
            $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);
            
            $registrationNumber = null;
            if (isset($data['role']) && $data['role'] === 'learner') {
                $registrationNumber = $this->generateRegistrationNumber($data['class_id'] ?? 'P1');
            }
            
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
                ':first_name' => $data['first_name'],
                ':last_name' => $data['last_name'],
                ':phone' => $data['phone'],
                ':role' => $data['role'] ?? 'learner',
                ':class_id' => $data['class_id'] ?? null
            ]);
            
            $userId = $this->conn->lastInsertId();
            
            $this->logActivity($userId, 'REGISTRATION', 'User registered successfully');
            
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
            
            $updateQuery = "UPDATE users SET last_login = NOW() WHERE id = :id";
            $updateStmt = $this->conn->prepare($updateQuery);
            $updateStmt->execute([':id' => $user['id']]);
            
            $this->logActivity($user['id'], 'LOGIN', 'User logged in successfully');
            
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
     * Update user profile
     */
    public function updateProfile($userId, $data) {
        try {
            error_log("=== UPDATE PROFILE IN MODEL ===");
            error_log("User ID: $userId");
            error_log("Data: " . json_encode($data));
            
            $user = $this->getById($userId);
            if (!$user) {
                error_log("User not found: $userId");
                return ['success' => false, 'error' => 'User not found'];
            }
            
            if ($user['email'] !== $data['email']) {
                $checkQuery = "SELECT id FROM users WHERE email = :email AND id != :id";
                $checkStmt = $this->conn->prepare($checkQuery);
                $checkStmt->execute([
                    ':email' => $data['email'],
                    ':id' => $userId
                ]);
                if ($checkStmt->fetch()) {
                    error_log("Email already taken: " . $data['email']);
                    return ['success' => false, 'error' => 'Email already taken by another user'];
                }
            }
            
            $query = "UPDATE users SET 
                    first_name = :first_name,
                    last_name = :last_name,
                    email = :email,
                    phone = :phone,
                    bio = :bio,
                    qualification = :qualification,
                    specialization = :specialization,
                    updated_at = NOW() 
                    WHERE id = :id";
            
            $stmt = $this->conn->prepare($query);
            $result = $stmt->execute([
                ':first_name' => $data['first_name'],
                ':last_name' => $data['last_name'],
                ':email' => $data['email'],
                ':phone' => $data['phone'] ?? null,
                ':bio' => $data['bio'] ?? null,
                ':qualification' => $data['qualification'] ?? null,
                ':specialization' => $data['specialization'] ?? null,
                ':id' => $userId
            ]);
            
            if ($result) {
                error_log("Profile updated successfully for user: $userId");
                $this->logActivity($userId, 'PROFILE_UPDATE', 'User updated profile');
                return ['success' => true, 'message' => 'Profile updated successfully'];
            }
            
            error_log("Failed to update profile for user: $userId");
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
     * Delete user account
     * 
     * @param int $userId User ID
     * @param string $password User's password for verification
     * @return array Result with success status and message
     */
    public function deleteAccount($userId, $password) {
        try {
            error_log("=== deleteAccount method in User model called ===");
            error_log("User ID: $userId");
            
            $sql = "SELECT password FROM users WHERE id = :user_id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
            $stmt->execute();
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$user) {
                error_log("User not found for ID: $userId");
                return ['success' => false, 'error' => 'User not found'];
            }
            
            if (!password_verify($password, $user['password'])) {
                error_log("Password verification failed for user: $userId");
                return ['success' => false, 'error' => 'Incorrect password. Please try again.'];
            }
            
            error_log("Password verified, starting deletion...");
            
            $this->conn->beginTransaction();
            
            // Delete quiz attempt answers
            try {
                $sql = "DELETE qaa FROM quiz_attempt_answers qaa 
                        INNER JOIN quiz_attempts qa ON qaa.attempt_id = qa.id 
                        WHERE qa.user_id = :user_id";
                $stmt = $this->conn->prepare($sql);
                $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
                $stmt->execute();
                $count = $stmt->rowCount();
                error_log("Deleted $count quiz_attempt_answers records");
            } catch (PDOException $e) {
                error_log("Error deleting quiz_attempt_answers: " . $e->getMessage());
            }
            
            try {
                $sql = "DELETE FROM quiz_attempts WHERE user_id = :user_id";
                $stmt = $this->conn->prepare($sql);
                $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
                $stmt->execute();
                $count = $stmt->rowCount();
                error_log("Deleted $count quiz_attempts records");
            } catch (PDOException $e) {
                error_log("Error deleting quiz_attempts: " . $e->getMessage());
            }
            
            try {
                $sql = "DELETE FROM subscriptions WHERE user_id = :user_id";
                $stmt = $this->conn->prepare($sql);
                $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
                $stmt->execute();
                $count = $stmt->rowCount();
                error_log("Deleted $count subscriptions records");
            } catch (PDOException $e) {
                error_log("Error deleting subscriptions: " . $e->getMessage());
            }
            
            try {
                $sql = "DELETE FROM payments WHERE user_id = :user_id";
                $stmt = $this->conn->prepare($sql);
                $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
                $stmt->execute();
                $count = $stmt->rowCount();
                error_log("Deleted $count payments records");
            } catch (PDOException $e) {
                error_log("Error deleting payments: " . $e->getMessage());
            }
            
            try {
                $sql = "DELETE FROM bookmarks WHERE user_id = :user_id";
                $stmt = $this->conn->prepare($sql);
                $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
                $stmt->execute();
                $count = $stmt->rowCount();
                error_log("Deleted $count bookmarks records");
            } catch (PDOException $e) {
                error_log("Error deleting bookmarks: " . $e->getMessage());
            }
            
            try {
                $sql = "DELETE FROM lesson_progress WHERE user_id = :user_id";
                $stmt = $this->conn->prepare($sql);
                $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
                $stmt->execute();
                $count = $stmt->rowCount();
                error_log("Deleted $count lesson_progress records");
            } catch (PDOException $e) {
                error_log("Error deleting lesson_progress: " . $e->getMessage());
            }
            
            $sql = "DELETE FROM users WHERE id = :user_id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
            $stmt->execute();
            $count = $stmt->rowCount();
            error_log("Deleted $count user record");
            
            if ($count == 0) {
                error_log("User record not deleted - user might not exist");
                throw new Exception("User record not deleted");
            }
            
            $this->conn->commit();
            
            error_log("Account deleted successfully for user: $userId");
            
            return ['success' => true, 'message' => 'Account deleted successfully'];
            
        } catch (PDOException $e) {
            $this->conn->rollBack();
            error_log("PDOException in deleteAccount: " . $e->getMessage());
            error_log("Error code: " . $e->getCode());
            error_log("Error line: " . $e->getLine());
            return ['success' => false, 'error' => 'Failed to delete account. Database error: ' . $e->getMessage()];
        } catch (Exception $e) {
            $this->conn->rollBack();
            error_log("Exception in deleteAccount: " . $e->getMessage());
            return ['success' => false, 'error' => 'Failed to delete account. Error: ' . $e->getMessage()];
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
            
            $check = getimagesize($file['tmp_name']);
            if ($check === false) {
                return ['success' => false, 'error' => 'File is not an image'];
            }
            
            if ($file['size'] > 2097152) {
                return ['success' => false, 'error' => 'File size must be less than 2MB'];
            }
            
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
            
            if (in_array($user['role'], ['learner', 'teacher', 'admin'])) {
                return true;
            }
            
            $trialQuery = "SELECT * FROM free_trials WHERE user_id = :user_id AND end_date > NOW()";
            $trialStmt = $this->conn->prepare($trialQuery);
            $trialStmt->execute([':user_id' => $userId]);
            if ($trialStmt->fetch()) {
                return true;
            }
            
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
     * Save password reset token
     */
    public function saveResetToken($userId, $token, $expires) {
        try {
            error_log("=== SAVE RESET TOKEN ===");
            error_log("User ID: " . $userId);
            error_log("Token: " . $token);
            error_log("Expires: " . $expires);
            
            $stmt = $this->conn->prepare("DELETE FROM password_resets WHERE user_id = ?");
            $stmt->execute([$userId]);
            error_log("Deleted existing tokens for user: " . $userId);
            
            $stmt = $this->conn->prepare("
                INSERT INTO password_resets (user_id, token, expires_at) 
                VALUES (?, ?, ?)
            ");
            $result = $stmt->execute([$userId, $token, $expires]);
            error_log("Insert result: " . ($result ? "SUCCESS" : "FAILED"));
            
            if ($result) {
                $insertId = $this->conn->lastInsertId();
                error_log("Inserted token ID: " . $insertId);
            }
            
            return $result;
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
            error_log("=== GET USER BY RESET TOKEN ===");
            error_log("Token to check: " . $token);
            
            $stmt = $this->conn->prepare("
                SELECT pr.*, u.*, pr.token as reset_token, pr.expires_at 
                FROM password_resets pr
                INNER JOIN users u ON pr.user_id = u.id
                WHERE pr.token = ? 
                AND pr.expires_at > NOW()
                AND pr.used = 0
            ");
            $stmt->execute([$token]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($result) {
                error_log("Token found and valid for user: " . $result['email']);
                error_log("Token expires at: " . $result['expires_at']);
                error_log("Current time: " . date('Y-m-d H:i:s'));
                return $result;
            } else {
                $stmt2 = $this->conn->prepare("
                    SELECT * FROM password_resets 
                    WHERE token = ?
                ");
                $stmt2->execute([$token]);
                $tokenRecord = $stmt2->fetch(PDO::FETCH_ASSOC);
                
                if ($tokenRecord) {
                    error_log("Token found but expired or used");
                    error_log("Token expires at: " . $tokenRecord['expires_at']);
                    error_log("Token used: " . $tokenRecord['used']);
                } else {
                    error_log("Token not found in database");
                }
                
                return null;
            }
        } catch (PDOException $e) {
            error_log("Get user by reset token error: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Clear reset token after password change
     */
    public function clearResetToken($userId) {
        try {
            $stmt = $this->conn->prepare("
                UPDATE password_resets 
                SET used = 1 
                WHERE user_id = ?
            ");
            return $stmt->execute([$userId]);
        } catch (PDOException $e) {
            error_log("Clear reset token error: " . $e->getMessage());
            return false;
        }
    }
    
     /**
     * Update user password
     */
    public function updatePassword($userId, $newPassword) {
        try {
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            
            $stmt = $this->conn->prepare("UPDATE users SET password = ? WHERE id = ?");
            $success = $stmt->execute([$hashedPassword, $userId]);
            
            return [
                'success' => $success,
                'error' => $success ? null : 'Failed to update password'
            ];
        } catch (PDOException $e) {
            error_log("Update password error: " . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Database error occurred'
            ];
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
        
        $subject = "Password Reset Request - Rays of Grace";
        
        $message = "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <style>
                body {
                    font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
                    background: #f8fafc;
                    margin: 0;
                    padding: 40px 20px;
                }
                .email-container {
                    max-width: 600px;
                    margin: 0 auto;
                    background: white;
                    border-radius: 30px;
                    overflow: hidden;
                    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
                }
                .email-header {
                    background: linear-gradient(135deg, #7f2677);
                    padding: 40px 30px;
                    text-align: center;
                }
                .email-header h1 {
                    color: white;
                    margin: 0;
                    font-size: 2rem;
                    font-weight: 700;
                }
                .email-header p {
                    color: rgba(255,255,255,0.9);
                    margin: 10px 0 0;
                    font-size: 1.1rem;
                }
                .email-body {
                    padding: 40px 30px;
                }
                .greeting {
                    font-size: 1.2rem;
                    color: #1E293B;
                    margin-bottom: 20px;
                    font-weight: 600;
                }
                .message {
                    color: #64748B;
                    line-height: 1.6;
                    margin-bottom: 30px;
                }
                .reset-button {
                    text-align: center;
                    margin: 35px 0;
                }
                .reset-button a {
                    display: inline-block;
                    background: linear-gradient(135deg, #7f2677, #f06724);
                    color: white;
                    text-decoration: none;
                    padding: 16px 40px;
                    border-radius: 50px;
                    font-weight: 600;
                    font-size: 1.1rem;
                    box-shadow: 0 4px 6px rgba(139, 92, 246, 0.3);
                    transition: all 0.3s ease;
                }
                .reset-button a:hover {
                    transform: translateY(-3px);
                    box-shadow: 0 10px 25px rgba(139, 92, 246, 0.4);
                }
                .expiry-note {
                    background: #FEF2F2;
                    border: 1px solid #FECACA;
                    border-radius: 12px;
                    padding: 15px;
                    margin: 30px 0;
                    color: #B91C1C;
                    font-size: 0.95rem;
                    display: flex;
                    align-items: center;
                    gap: 10px;
                }
                .footer-note {
                    border-top: 2px solid #F1F5F9;
                    padding-top: 25px;
                    margin-top: 25px;
                    color: #94A3B8;
                    font-size: 0.9rem;
                }
                .footer-note a {
                    color: #7f2677;
                    text-decoration: none;
                    font-weight: 600;
                }
            </style>
        </head>
        <body>
            <div class='email-container'>
                <div class='email-header'>
                    <h1>🔐 Password Reset Request</h1>
                    <p>Rays of Grace E-Learning</p>
                </div>
                
                <div class='email-body'>
                    <div class='greeting'>
                        Hello " . htmlspecialchars($name) . "! 👋
                    </div>
                    
                    <div class='message'>
                        We received a request to reset the password for your Rays of Grace E-Learning account. 
                        No changes have been made to your account yet.
                    </div>
                    
                    <div class='message'>
                        To reset your password, click the button below:
                    </div>
                    
                    <div class='reset-button'>
                        <a href='" . $resetLink . "'>🔓 Reset Your Password</a>
                    </div>
                    
                    <div class='expiry-note'>
                        <span>⏰</span>
                        <strong>Note:</strong> This password reset link will expire in 20 minutes for security reasons.
                    </div>
                    
                    <div class='message'>
                        If you didn't request a password reset, you can safely ignore this email. 
                        Your account is still secure and no changes have been made.
                    </div>
                    
                    <div class='footer-note'>
                        <p>For security assistance, please contact our support team at 
                        <a href='mailto:support@raysofgrace.com'>support@raysofgrace.com</a>
                        </p>
                        <p style='margin-top: 15px;'>© " . date('Y') . " Rays of Grace Junior School. All rights reserved.</p>
                    </div>
                </div>
            </div>
        </body>
        </html>
        ";
        
        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
        $headers .= "From: Rays of Grace <noreply@raysofgrace.com>" . "\r\n";
        $headers .= "Reply-To: support@raysofgrace.com" . "\r\n";
        $headers .= "X-Mailer: PHP/" . phpversion();
        
        $mailSent = mail($email, $subject, $message, $headers);
        
        if ($mailSent) {
            error_log("Password reset email sent successfully to: $email");
            error_log("Reset link: $resetLink");
        } else {
            error_log("Failed to send password reset email to: $email");
            error_log("Reset link (would have been sent): $resetLink");
        }
        
        return $mailSent;
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
     * Search users by name, email, or ID - FIXED VERSION
     */
    public function searchUsers($searchTerm) {
        try {
            $searchPattern = '%' . $searchTerm . '%';
            
            $sql = "SELECT * FROM users 
                    WHERE (first_name LIKE :search1 
                        OR last_name LIKE :search2 
                        OR CONCAT(first_name, ' ', last_name) LIKE :search3 
                        OR email LIKE :search4 
                        OR registration_number LIKE :search5)";
            
            if (is_numeric($searchTerm)) {
                $sql .= " OR id = :id_search";
            }
            
            $sql .= " ORDER BY created_at DESC";
            
            $stmt = $this->conn->prepare($sql);
            
            $stmt->bindValue(':search1', $searchPattern);
            $stmt->bindValue(':search2', $searchPattern);
            $stmt->bindValue(':search3', $searchPattern);
            $stmt->bindValue(':search4', $searchPattern);
            $stmt->bindValue(':search5', $searchPattern);
            
            if (is_numeric($searchTerm)) {
                $stmt->bindValue(':id_search', (int)$searchTerm, PDO::PARAM_INT);
            }
            
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
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
            $user = $this->getById($userId);
            if (!$user) {
                return ['success' => false, 'error' => 'User not found'];
            }
            
            $this->conn->beginTransaction();
            
            $tables = ['subscriptions', 'free_trials', 'payments', 'activity_logs', 'bookmarks', 'quiz_attempts'];
            
            foreach ($tables as $table) {
                $deleteQuery = "DELETE FROM $table WHERE user_id = :user_id";
                $deleteStmt = $this->conn->prepare($deleteQuery);
                $deleteStmt->execute([':user_id' => $userId]);
            }
            
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
     * Get remaining trial days for a user
     * 
     * @param int $userId The user ID
     * @param int $trialDays Total trial days (default 60)
     * @return int Remaining trial days (0 if expired or subscribed)
     */
    public function getRemainingTrialDays($userId, $trialDays = 60) {
        try {
            $subscriptionModel = new Subscription();
            $activeSubscription = $subscriptionModel->getCurrentSubscription($userId);
            
            if ($activeSubscription) {
                return 0;
            }
            
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
            
            $daysPassed = $createdAt->diff($now)->days;
            
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
            $subscriptionModel = new Subscription();
            $activeSubscription = $subscriptionModel->getCurrentSubscription($userId);
            
            if ($activeSubscription) {
                return false;
            }
            
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

    /**
     * Get ALL students with their statistics
     */
    public function getStudentsWithStats($teacherId, $classId = null, $search = null) {
        try {
            error_log("=== GET STUDENTS WITH STATS ===");
            
            $conn = $this->conn;
            
            $query = "
                SELECT 
                    u.id,
                    u.first_name,
                    u.last_name,
                    u.email,
                    u.phone,
                    u.role,
                    u.profile_photo,
                    u.class_id,
                    c.name as class_name,
                    COUNT(DISTINCT qa.id) as quizzes_taken,
                    COALESCE(AVG(qa.score), 0) as avg_score,
                    MAX(qa.score) as highest_score,
                    MIN(qa.score) as lowest_score,
                    COUNT(DISTINCT lv.id) as lessons_viewed
                FROM users u
                LEFT JOIN classes c ON u.class_id = c.id
                LEFT JOIN quiz_attempts qa ON u.id = qa.user_id 
                    AND qa.completed_at IS NOT NULL
                LEFT JOIN lesson_views lv ON u.id = lv.user_id
                WHERE u.role IN ('learner', 'external')
                AND u.is_active = 1
            ";
            
            $params = [];
            
            if ($classId) {
                $query .= " AND u.class_id = ?";
                $params[] = $classId;
            }
            
            if ($search) {
                $query .= " AND (u.first_name LIKE ? OR u.last_name LIKE ? OR u.email LIKE ?)";
                $searchTerm = "%$search%";
                $params[] = $searchTerm;
                $params[] = $searchTerm;
                $params[] = $searchTerm;
            }
            
            $query .= " GROUP BY u.id, u.first_name, u.last_name, u.email, u.phone, u.role, u.profile_photo, u.class_id, c.name
                        ORDER BY u.first_name ASC";
            
            error_log("Query: " . $query);
            
            $stmt = $conn->prepare($query);
            $stmt->execute($params);
            
            $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            foreach ($students as &$student) {
                $student['avg_score'] = round($student['avg_score'] ?? 0, 1);
                $student['quizzes_taken'] = (int)$student['quizzes_taken'];
                $student['lessons_viewed'] = (int)$student['lessons_viewed'];
            }
            
            error_log("Students found: " . count($students));
            
            return $students;
            
        } catch (PDOException $e) {
            error_log("Get students with stats error: " . $e->getMessage());
            error_log("Error code: " . $e->getCode());
            return [];
        }
    }

    /**
     * Add student to class (for backward compatibility)
     */
    public function addStudentToClass($userId, $classId) {
        try {
            $stmt = $this->conn->prepare("UPDATE users SET class_id = ? WHERE id = ?");
            $stmt->execute([$classId, $userId]);
            return true;
        } catch (PDOException $e) {
            error_log("Add student to class error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Count total students (learners and external users)
     */
    public function countTotalStudents() {
        try {
            $stmt = $this->conn->prepare("
                SELECT COUNT(*) as total 
                FROM users 
                WHERE role IN ('learner', 'external') 
                AND is_active = 1
            ");
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['total'] ?? 0;
        } catch (PDOException $e) {
            error_log("Count total students error: " . $e->getMessage());
            return 0;
        }
    }
}
?>