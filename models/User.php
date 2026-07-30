<?php
// File: /models/User.php 
require_once __DIR__ . '/../config/database.php';

class User {
    private $db;
    private $conn;
    private $subscriptionModel = null;
    private $userCache = [];
    private $classNameCache = [];
    
    public function __construct() {
        $this->db = Database::getInstance();
        $this->conn = $this->db->getConnection();
    }
    
    // ==================== HELPER METHODS ====================
    private function getSubscriptionModel() {
        if ($this->subscriptionModel === null) {
            require_once __DIR__ . '/Subscription.php';
            $this->subscriptionModel = new Subscription();
        }
        return $this->subscriptionModel;
    }
    
    private function getCachedUser($id, $forceRefresh = false) {
        if ($forceRefresh || !isset($this->userCache[$id])) {
            $this->userCache[$id] = $this->getByIdFromDB($id);
        }
        return $this->userCache[$id];
    }
    
    private function getByIdFromDB($id) {
        try {
            $query = "SELECT u.*, c.name as class_name 
                    FROM users u 
                    LEFT JOIN classes c ON u.class_id = c.id 
                    WHERE u.id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([':id' => $id]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            return null;
        }
    }
    
    private function isEmailTaken($email, $excludeUserId = null) {
        $query = "SELECT id FROM users WHERE email = :email";
        $params = [':email' => $email];
        
        if ($excludeUserId) {
            $query .= " AND id != :id";
            $params[':id'] = $excludeUserId;
        }
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute($params);
        return $stmt->fetch() !== false;
    }
    
    private function deleteRelatedRecords($userId, $table) {
        try {
            $sql = "DELETE FROM {$table} WHERE user_id = :user_id";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([':user_id' => $userId]);
            return true;
        } catch (PDOException $e) {
            error_log("Error deleting from {$table}: " . $e->getMessage());
            return false;
        }
    }
    
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
            'computer' => 'COMP', 'computer class' => 'COMP'
        ];
        
        $classCode = $classMap[strtolower(trim($class))] ?? 'P1';
        $unique = str_pad(mt_rand(1, 999), 3, '0', STR_PAD_LEFT);
        
        return $prefix . '-' . $classCode . '-' . $unique;
    }
    
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
                    background-color: #7f2677;
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
                    font-size: 0.95rem;
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
                    background-color: #f06724;
                    color: white;
                    text-decoration: none;
                    padding: 16px 40px;
                    border-radius: 50px;
                    font-weight: 600;
                    font-size: 0.95rem;
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
                    <h1>Password Reset Request</h1>
                    <p>Rays of Grace E-Learning</p>
                </div>
                
                <div class='email-body'>
                    <div class='greeting'>
                        Hello " . htmlspecialchars($name) . "! 
                    </div>
                    
                    <div class='message'>
                        We received a request to reset the password for your Rays of Grace E-Learning account. 
                        No changes have been made to your account yet.
                    </div>
                    
                    <div class='message'>
                        To reset your password, click the button below:
                    </div>
                    
                    <div class='reset-button'>
                        <a href='" . $resetLink . "'>Reset Your Password</a>
                    </div>
                    
                    <div class='expiry-note'>
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
        } else {
            error_log("Failed to send password reset email to: $email");
            error_log("Reset link: $resetLink");
        }
        
        return $mailSent;
    }
    
    // ==================== USER MANAGEMENT ====================
    public function register($data) {
        try {
            if ($this->isEmailTaken($data['email'])) {
                return ['success' => false, 'error' => 'Email already registered!'];
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
            
            $this->logActivity($userId, 'REGISTRATION', 'User registered successfully!');
            
            $userData = $this->getById($userId);
            unset($userData['password']);
            
            return [
                'success' => true, 
                'user_id' => $userId, 
                'user' => $userData,
                'message' => 'Registration successful!'
            ];
            
        } catch (PDOException $e) {
            return ['success' => false, 'error' => 'Registration failed. Please try again!'];
        }
    }
    
    public function login($username, $password) {
        try {
            $query = "SELECT u.*, c.name as class_name 
                    FROM users u 
                    LEFT JOIN classes c ON u.class_id = c.id 
                    WHERE u.email = :email OR u.registration_number = :reg_no";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindValue(':email', $username);
            $stmt->bindValue(':reg_no', $username);
            $stmt->execute();
            
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$user || !password_verify($password, $user['password'])) {
                return ['success' => false, 'error' => 'Invalid email/password!'];
            }
            
            if (!$user['is_active']) {
                return ['success' => false, 'error' => 'Your account is not active. Please contact support!'];
            }
            
            if ($user['is_suspended']) {
                return ['success' => false, 'error' => 'Account suspended. Please contact support!'];
            }
            
            $updateQuery = "UPDATE users SET last_login = NOW() WHERE id = :id";
            $updateStmt = $this->conn->prepare($updateQuery);
            $updateStmt->execute([':id' => $user['id']]);
            
            $this->logActivity($user['id'], 'LOGIN', 'User logged in successfully!');
            
            unset($user['password']);
            
            return ['success' => true, 'user' => $user];
            
        } catch (PDOException $e) {
            return ['success' => false, 'error' => 'Database error: ' . $e->getMessage()];
        }
    }
    
    public function getById($id) {
        return $this->getCachedUser($id);
    }
    
    public function getByEmail($email) {
        try {
            $query = "SELECT * FROM users WHERE email = :email";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([':email' => $email]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            return null;
        }
    }
    
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
            return null;
        }
    }
    
    public function updateProfile($userId, $data) {
        try {
            $currentUser = $this->getCachedUser($userId, true);
            
            if ($currentUser && $currentUser['email'] !== $data['email']) {
                if ($this->isEmailTaken($data['email'], $userId)) {
                    return ['success' => false, 'error' => 'Email already taken by another user!'];
                }
            }
            
            $query = "UPDATE users SET 
                    first_name = :first_name,
                    last_name = :last_name,
                    email = :email,
                    phone = :phone,
                    class_id = :class_id,
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
                ':phone' => $data['phone'],
                ':class_id' => $data['class_id'],
                ':bio' => $data['bio'] ?? null,
                ':qualification' => $data['qualification'] ?? null,
                ':specialization' => $data['specialization'] ?? null,
                ':id' => $userId
            ]);
            
            if ($result) {
                unset($this->userCache[$userId]);
                
                if ($currentUser && isset($currentUser['class_id']) && $currentUser['class_id'] != $data['class_id']) {
                    $oldClass = $this->getClassName($currentUser['class_id']);
                    $newClass = $this->getClassName($data['class_id']);
                    $this->logActivity($userId, 'CLASS_CHANGE', "Class changed from {$oldClass} to {$newClass}");
                }
                
                $this->logActivity($userId, 'PROFILE_UPDATE', 'User updated profile');
                return ['success' => true, 'message' => 'Profile updated successfully!'];
            }
            
            return ['success' => false, 'error' => 'Failed to update profile!'];
            
        } catch (PDOException $e) {
            return ['success' => false, 'error' => 'Database error occurred!'];
        }
    }
    
    private function getClassName($classId) {
        if (!$classId) return 'None';
        
        if (isset($this->classNameCache[$classId])) {
            return $this->classNameCache[$classId];
        }
        
        try {
            $query = "SELECT name FROM classes WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([':id' => $classId]);
            $result = $stmt->fetch();
            $this->classNameCache[$classId] = $result ? $result['name'] : 'Unknown';
            return $this->classNameCache[$classId];
        } catch (PDOException $e) {
            return 'Unknown';
        }
    }
    
    public function updateUserAsAdmin($userId, $data) {
        try {
            $currentUser = $this->getCachedUser($userId, true);
            
            if ($currentUser && $currentUser['email'] !== $data['email']) {
                if ($this->isEmailTaken($data['email'], $userId)) {
                    return ['success' => false, 'error' => 'Email already taken by another user!'];
                }
            }
            
            $query = "UPDATE users SET 
                    first_name = :first_name,
                    last_name = :last_name,
                    email = :email,
                    phone = :phone,
                    role = :role,
                    class_id = :class_id,
                    updated_at = NOW()
                    WHERE id = :id";
            
            $stmt = $this->conn->prepare($query);
            $result = $stmt->execute([
                ':first_name' => $data['first_name'],
                ':last_name' => $data['last_name'],
                ':email' => $data['email'],
                ':phone' => $data['phone'] ?? null,
                ':role' => $data['role'],
                ':class_id' => isset($data['class_id']) && !empty($data['class_id']) ? $data['class_id'] : null,
                ':id' => $userId
            ]);
            
            if ($result) {
                unset($this->userCache[$userId]);
                $this->logActivity($userId, 'ADMIN_UPDATE', 'User updated by admin!');
                return ['success' => true, 'message' => 'User updated successfully!'];
            }
            
            return ['success' => false, 'error' => 'Failed to update user!'];
            
        } catch (PDOException $e) {
            return ['success' => false, 'error' => 'Database error occurred: ' . $e->getMessage()];
        }
    }
    
    public function changePassword($userId, $currentPassword, $newPassword) {
        try {
            $query = "SELECT password FROM users WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([':id' => $userId]);
            $user = $stmt->fetch();
            
            if (!$user) {
                return ['success' => false, 'error' => 'User not found!'];
            }
            
            if (!password_verify($currentPassword, $user['password'])) {
                return ['success' => false, 'error' => 'Current password is incorrect!'];
            }
            
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            $updateQuery = "UPDATE users SET password = :password, updated_at = NOW() WHERE id = :id";
            $updateStmt = $this->conn->prepare($updateQuery);
            $result = $updateStmt->execute([
                ':password' => $hashedPassword,
                ':id' => $userId
            ]);
            
            if ($result) {
                $this->logActivity($userId, 'PASSWORD_CHANGE', 'User changed password!');
                return ['success' => true, 'message' => 'Password changed successfully!'];
            }
            
            return ['success' => false, 'error' => 'Failed to change password!'];
            
        } catch (PDOException $e) {
            return ['success' => false, 'error' => 'Database error occurred!'];
        }
    }
    
    public function deleteAccount($userId, $password) {
        try {
            $sql = "SELECT password FROM users WHERE id = :user_id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
            $stmt->execute();
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$user) {
                return ['success' => false, 'error' => 'User not found'];
            }
            
            if (!password_verify($password, $user['password'])) {
                return ['success' => false, 'error' => 'Incorrect password. Please try again!'];
            }
            
            $this->conn->beginTransaction();
            
            $relatedTables = ['quiz_attempt_answers', 'quiz_attempts', 'subscriptions', 'payments', 'bookmarks', 'lesson_progress'];
            
            foreach ($relatedTables as $table) {
                if ($table === 'quiz_attempt_answers') {
                    try {
                        $sql = "DELETE qaa FROM quiz_attempt_answers qaa 
                                INNER JOIN quiz_attempts qa ON qaa.attempt_id = qa.id 
                                WHERE qa.user_id = :user_id";
                        $stmt = $this->conn->prepare($sql);
                        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
                        $stmt->execute();
                    } catch (PDOException $e) {
                        error_log("Error deleting quiz_attempt_answers: " . $e->getMessage());
                    }
                } else {
                    $this->deleteRelatedRecords($userId, $table);
                }
            }
            
            $sql = "DELETE FROM users WHERE id = :user_id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
            $stmt->execute();
            
            if ($stmt->rowCount() == 0) {
                throw new Exception("User record not deleted");
            }
            
            $this->conn->commit();
            
            return ['success' => true, 'message' => 'Account deleted successfully!'];
            
        } catch (PDOException $e) {
            $this->conn->rollBack();
            return ['success' => false, 'error' => 'Failed to delete account. Database error: ' . $e->getMessage()];
        } catch (Exception $e) {
            $this->conn->rollBack();
            return ['success' => false, 'error' => 'Failed to delete account. Error: ' . $e->getMessage()];
        }
    }
    
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
            return false;
        }
    }
    
    public function hasAccess($userId) {
        try {
            $userQuery = "SELECT role, created_at FROM users WHERE id = :id";
            $userStmt = $this->conn->prepare($userQuery);
            $userStmt->execute([':id' => $userId]);
            $user = $userStmt->fetch();
            
            if (!$user) return false;
            
            if (in_array($user['role'], ['learner', 'teacher', 'admin'])) {
                return true;
            }
            
            if ($user['role'] === 'external') {
                $createdAt = new DateTime($user['created_at']);
                $now = new DateTime();
                $daysPassed = $createdAt->diff($now)->days;
                $trialDays = 60;
                
                if ($daysPassed < $trialDays) {
                    return true;
                }
            }
            
            $subQuery = "SELECT * FROM subscriptions WHERE user_id = :user_id AND status = 'active' AND end_date > NOW()";
            $subStmt = $this->conn->prepare($subQuery);
            $subStmt->execute([':user_id' => $userId]);
            
            return $subStmt->fetch() !== false;
            
        } catch (PDOException $e) {
            return false;
        }
    }
    
    public function requestPasswordReset($email) {
        try {
            $user = $this->getByEmail($email);
            
            if (!$user) {
                return ['success' => false, 'error' => 'Email not found!'];
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
            
            return ['success' => true, 'message' => 'Password reset instructions sent to your email!'];
            
        } catch (PDOException $e) {
            return ['success' => false, 'error' => 'Failed to process request'];
        }
    }
    
    public function resetPassword($token, $newPassword) {
        try {
            $query = "SELECT id FROM users WHERE reset_token = :token AND reset_expires > NOW()";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([':token' => $token]);
            $user = $stmt->fetch();
            
            if (!$user) {
                return ['success' => false, 'error' => 'Invalid/expired reset token!'];
            }
            
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            $updateQuery = "UPDATE users SET password = :password, reset_token = NULL, reset_expires = NULL WHERE id = :id";
            $updateStmt = $this->conn->prepare($updateQuery);
            $updateStmt->execute([
                ':password' => $hashedPassword,
                ':id' => $user['id']
            ]);
            
            unset($this->userCache[$user['id']]);
            
            return ['success' => true, 'message' => 'Password reset successful!'];
            
        } catch (PDOException $e) {
            return ['success' => false, 'error' => 'Failed to reset password!'];
        }
    }
    
    public function saveResetToken($userId, $token, $expires) {
        try {
            $stmt = $this->conn->prepare("DELETE FROM password_resets WHERE user_id = ?");
            $stmt->execute([$userId]);
            
            $stmt = $this->conn->prepare("
                INSERT INTO password_resets (user_id, token, expires_at) 
                VALUES (?, ?, ?)
            ");
            return $stmt->execute([$userId, $token, $expires]);
        } catch (PDOException $e) {
            return false;
        }
    }
    
    public function getUserByResetToken($token) {
        try {
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
                return $result;
            }
            
            $stmt2 = $this->conn->prepare("SELECT * FROM password_resets WHERE token = ?");
            $stmt2->execute([$token]);
            $tokenRecord = $stmt2->fetch(PDO::FETCH_ASSOC);
            
            if ($tokenRecord) {
                error_log("Token found but expired or used: " . $token);
            } else {
                error_log("Token not found in database: " . $token);
            }
            
            return null;
        } catch (PDOException $e) {
            error_log("Error getting user by reset token: " . $e->getMessage());
            return null;
        }
    }
    
    public function clearResetToken($userId) {
        try {
            $stmt = $this->conn->prepare("UPDATE password_resets SET used = 1 WHERE user_id = ?");
            return $stmt->execute([$userId]);
        } catch (PDOException $e) {
            return false;
        }
    }
    
    public function updatePassword($userId, $newPassword) {
        try {
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            
            $stmt = $this->conn->prepare("UPDATE users SET password = ? WHERE id = ?");
            $success = $stmt->execute([$hashedPassword, $userId]);
            
            if ($success) {
                unset($this->userCache[$userId]);
            }
            
            return [
                'success' => $success,
                'error' => $success ? null : 'Failed to update password!'
            ];
        } catch (PDOException $e) {
            return [
                'success' => false,
                'error' => 'Database error occurred!'
            ];
        }
    }
    
    // ==================== ADMIN METHODS ====================
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
            return [];
        }
    }
    
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
            return [];
        }
    }
    
    public function suspendUser($userId) {
        try {
            $query = "UPDATE users SET is_suspended = 1, updated_at = NOW() WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([':id' => $userId]);
            
            if ($stmt->rowCount() > 0) {
                unset($this->userCache[$userId]);
                $this->logActivity($userId, 'SUSPEND', 'User account suspended');
                return ['success' => true, 'message' => 'User suspended successfully'];
            }
            
            return ['success' => false, 'error' => 'User not found'];
            
        } catch (PDOException $e) {
            return ['success' => false, 'error' => 'Failed to suspend user!'];
        }
    }
    
    public function activateUser($userId) {
        try {
            $query = "UPDATE users SET is_suspended = 0, updated_at = NOW() WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([':id' => $userId]);
            
            if ($stmt->rowCount() > 0) {
                unset($this->userCache[$userId]);
                $this->logActivity($userId, 'ACTIVATE', 'User account activated!');
                return ['success' => true, 'message' => 'User activated successfully!'];
            }
            
            return ['success' => false, 'error' => 'User not found!'];
            
        } catch (PDOException $e) {
            return ['success' => false, 'error' => 'Failed to activate user!'];
        }
    }
    
    public function deleteUser($userId) {
        try {
            $user = $this->getById($userId);
            if (!$user) {
                return ['success' => false, 'error' => 'User not found!'];
            }
            
            $this->conn->beginTransaction();
            
            $tables = ['subscriptions', 'free_trials', 'payments', 'activity_logs', 'bookmarks', 'quiz_attempts'];
            
            foreach ($tables as $table) {
                $this->deleteRelatedRecords($userId, $table);
            }
            
            $deleteUser = "DELETE FROM users WHERE id = :id";
            $deleteUserStmt = $this->conn->prepare($deleteUser);
            $deleteUserStmt->execute([':id' => $userId]);
            
            $this->conn->commit();
            
            unset($this->userCache[$userId]);
            $this->logActivity($userId, 'DELETE', 'User account deleted by admin!');
            
            return ['success' => true, 'message' => 'User deleted successfully!'];
            
        } catch (PDOException $e) {
            $this->conn->rollBack();
            return ['success' => false, 'error' => 'Failed to delete user!'];
        }
    }
    
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
            return 0;
        }
    }
    
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
            return 0;
        }
    }
    
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
            return 0;
        }
    }
    
    public function getRemainingTrialDays($userId, $trialDays = 60) {
        try {
            $subscriptionModel = $this->getSubscriptionModel();
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
            return $trialDays;
        }
    }
    
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
            return null;
        }
    }
    
    public function isInTrialPeriod($userId, $trialDays = 60) {
        try {
            $subscriptionModel = $this->getSubscriptionModel();
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
            return false;
        }
    }
    
    public function getTrialStatus($userId, $trialDays = 60) {
        try {
            $subscriptionModel = $this->getSubscriptionModel();
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
            
            $endDate = clone $createdAt;
            $endDate->modify("+{$trialDays} days");
            
            return [
                'is_trial' => $remainingDays > 0,
                'has_subscription' => false,
                'remaining_days' => $remainingDays,
                'trial_ended' => $remainingDays <= 0,
                'trial_start_date' => $user['created_at'],
                'trial_end_date' => $endDate->format('Y-m-d H:i:s'),
                'message' => $remainingDays > 0 ? "Trial ends in {$remainingDays} days" : "Trial has ended!"
            ];
            
        } catch (Exception $e) {
            return [
                'is_trial' => false,
                'has_subscription' => false,
                'remaining_days' => 0,
                'trial_ended' => true,
                'message' => 'Error checking trial status'
            ];
        }
    }
    
    public function getStudentsWithStats($teacherId, $classId = null, $search = null) {
        try {
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
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute($params);
            
            $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            foreach ($students as &$student) {
                $student['avg_score'] = round($student['avg_score'] ?? 0, 1);
                $student['quizzes_taken'] = (int)$student['quizzes_taken'];
                $student['lessons_viewed'] = (int)$student['lessons_viewed'];
            }
            
            return $students;
            
        } catch (PDOException $e) {
            return [];
        }
    }
    
    public function addStudentToClass($userId, $classId) {
        try {
            $stmt = $this->conn->prepare("UPDATE users SET class_id = ? WHERE id = ?");
            $stmt->execute([$classId, $userId]);
            unset($this->userCache[$userId]);
            return true;
        } catch (PDOException $e) {
            return false;
        }
    }
    
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
            return 0;
        }
    }
}
?>