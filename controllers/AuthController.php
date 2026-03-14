<!-- File: /controllers/AuthController.php -->
<?php
require_once __DIR__ . '/../models/User.php';

class AuthController {
    private $userModel;
    
    public function __construct() {
        $this->userModel = new User();
    }
    
    // Handle login
    public function login() {
        // If already logged in, redirect to appropriate dashboard
        if (isset($_SESSION['user_id'])) {
            $this->redirectToDashboard();
            return;
        }
        
        // Set flag to hide footer
        $hideFooter = true;
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = trim($_POST['username'] ?? '');
            $password = $_POST['password'] ?? '';
            
            // Debug logging
            error_log("Login POST request received");
            error_log("Username: " . $username);
            error_log("Password length: " . strlen($password));
            
            if (empty($username) || empty($password)) {
                error_log("Empty username or password");
                $_SESSION['error'] = 'Please enter both username and password';
                header('Location: ' . BASE_URL . '/login');
                exit;
            }
            
            $result = $this->userModel->login($username, $password);
            
            error_log("Login result: " . ($result['success'] ? 'SUCCESS' : 'FAILED'));
            if (!$result['success']) {
                error_log("Error message: " . ($result['error'] ?? 'Unknown error'));
            }
            
            if ($result['success']) {
                // Set session variables
                $_SESSION['user_id'] = $result['user']['id'];
                $_SESSION['user_role'] = $result['user']['role'];
                $_SESSION['user_name'] = $result['user']['first_name'] . ' ' . $result['user']['last_name'];
                $_SESSION['user_email'] = $result['user']['email'];
                
                error_log("Session set for user ID: " . $_SESSION['user_id']);
                
                if (isset($result['user']['force_password_change']) && $result['user']['force_password_change']) {
                    $_SESSION['force_password_change'] = true;
                }
                
                $this->redirectToDashboard();
                exit;
            } else {
                $_SESSION['error'] = $result['error'] ?? 'Login failed. Please try again.';
                header('Location: ' . BASE_URL . '/login');
                exit;
            }
        }
        
        // Show login form
        require_once __DIR__ . '/../views/auth/login.php';
    }
    
    // Handle registration
    public function register() {
        // Set flag to hide footer
        $hideFooter = true;
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Validate input
            $errors = [];
            
            if (empty($_POST['first_name'])) {
                $errors[] = 'First name is required';
            }
            
            if (empty($_POST['last_name'])) {
                $errors[] = 'Last name is required';
            }
            
            if (empty($_POST['email'])) {
                $errors[] = 'Email is required';
            } elseif (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
                $errors[] = 'Invalid email format';
            }
            
            if (empty($_POST['phone'])) {
                $errors[] = 'Phone number is required';
            }
            
            if (empty($_POST['password'])) {
                $errors[] = 'Password is required';
            } elseif (strlen($_POST['password']) < 8) {
                $errors[] = 'Password must be at least 8 characters';
            }
            
            if ($_POST['password'] !== $_POST['confirm_password']) {
                $errors[] = 'Passwords do not match';
            }
            
            if (!isset($_POST['terms'])) {
                $errors[] = 'You must accept the Terms and Conditions';
            }
            
            if (empty($errors)) {
                $data = [
                    'first_name' => $_POST['first_name'],
                    'last_name' => $_POST['last_name'],
                    'email' => $_POST['email'],
                    'phone' => $_POST['phone'],
                    'password' => $_POST['password'],
                    'class' => $_POST['class'] ?? null,
                    'role' => 'external' // Default role, you can modify based on user type
                ];
                
                // Check if user selected a specific type (you can add a hidden field or radio buttons)
                if (isset($_POST['user_type'])) {
                    $data['role'] = $_POST['user_type'];
                }
                
                $result = $this->userModel->register($data);
                
                if ($result['success']) {
                    // Auto-login the user
                    $_SESSION['user_id'] = $result['user']['id'];
                    $_SESSION['user_role'] = $result['user']['role'];
                    $_SESSION['user_name'] = $result['user']['first_name'] . ' ' . $result['user']['last_name'];
                    $_SESSION['user_email'] = $result['user']['email'];
                    
                    // Redirect to appropriate dashboard based on role
                    $this->redirectToDashboard();
                    exit;
                } else {
                    $_SESSION['error'] = $result['error'];
                    header('Location: ' . BASE_URL . '/register');
                    exit;
                }
            } else {
                $_SESSION['error'] = implode('<br>', $errors);
                header('Location: ' . BASE_URL . '/register');
                exit;
            }
        }
        
        // Show registration form with footer hidden
        require_once __DIR__ . '/../views/auth/register.php';
    }
    
    // Handle logout
    public function logout() {
        session_destroy();
        header('Location: ' . BASE_URL . '/login');
        exit;
    }
    
    // Handle password change
    public function changePassword() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . BASE_URL . '/login');
            exit;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $oldPassword = $_POST['old_password'] ?? '';
            $newPassword = $_POST['new_password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';
            
            if ($newPassword !== $confirmPassword) {
                $_SESSION['error'] = 'New passwords do not match';
            } else {
                $result = $this->userModel->changePassword($_SESSION['user_id'], $oldPassword, $newPassword);
                
                if ($result['success']) {
                    unset($_SESSION['force_password_change']);
                    $_SESSION['success'] = 'Password changed successfully';
                    header('Location: ' . BASE_URL . '/dashboard');
                    exit;
                } else {
                    $_SESSION['error'] = $result['error'];
                }
            }
        }
        
        // Show change password form
        require_once __DIR__ . '/../views/auth/change_password.php';
    }
    
    // Redirect to appropriate dashboard based on role
    private function redirectToDashboard() {
        switch ($_SESSION['user_role']) {
            case 'admin':
                header('Location: ' . BASE_URL . '/admin/dashboard');
                break;
            case 'teacher':
                header('Location: ' . BASE_URL . '/teacher/dashboard');
                break;
            case 'learner':
                header('Location: ' . BASE_URL . '/learner/dashboard');
                break;
            case 'external':
                header('Location: ' . BASE_URL . '/external/dashboard');
                break;
            default:
                header('Location: ' . BASE_URL . '/login');
        }
        exit;
    }

        /**
     * Show forgot password form
     */
    public function forgotPassword() {
        $hideFooter = true;
        require_once __DIR__ . '/../views/auth/forgot-password.php';
    }

    /**
     * Process forgot password request
     */
    public function processForgotPassword() {
        $hideFooter = true;
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/forgot-password');
            exit;
        }
        
        $email = $_POST['email'] ?? '';
        
        if (empty($email)) {
            $_SESSION['error'] = 'Please enter your email address';
            header('Location: ' . BASE_URL . '/forgot-password');
            exit;
        }
        
        // Check if user exists
        $user = $this->userModel->getByEmail($email);
        
        // Always show success message for security (don't reveal if email exists)
        if ($user) {
            // Generate reset token (valid for 20 minutes)
            $token = bin2hex(random_bytes(32));
            $expires = date('Y-m-d H:i:s', strtotime('+20 minutes'));
            
            // Save token to database
            $this->userModel->saveResetToken($user['id'], $token, $expires);
            
            // Send reset email
            $this->sendResetEmail($email, $token, $user['first_name']);
        }
        
        // Success message - shown to ALL users for security
        $_SESSION['success'] = 'A password reset link has been sent to your email address. Please check your inbox and follow the instructions.';
        
        header('Location: ' . BASE_URL . '/forgot-password');
        exit;
    }

    /**
     * Show reset password form
     */
    public function resetPassword() {
        $hideFooter = true;
        
        $token = $_GET['token'] ?? '';
        
        if (empty($token)) {
            $_SESSION['error'] = 'Invalid reset link';
            header('Location: ' . BASE_URL . '/login');
            exit;
        }
        
        // Verify token is valid and not expired
        $user = $this->userModel->getUserByResetToken($token);
        
        if (!$user) {
            $_SESSION['error'] = 'Invalid or expired reset link. Please request a new one.';
            header('Location: ' . BASE_URL . '/forgot-password');
            exit;
        }
        
        require_once __DIR__ . '/../views/auth/reset-password.php';
    }

    /**
     * Process reset password
     */
    public function processResetPassword() {
        $hideFooter = true;
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/login');
            exit;
        }
        
        $token = $_POST['token'] ?? '';
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        
        if (empty($token) || empty($password) || empty($confirmPassword)) {
            $_SESSION['error'] = 'All fields are required';
            header('Location: ' . BASE_URL . '/reset-password?token=' . urlencode($token));
            exit;
        }
        
        if ($password !== $confirmPassword) {
            $_SESSION['error'] = 'Passwords do not match';
            header('Location: ' . BASE_URL . '/reset-password?token=' . urlencode($token));
            exit;
        }
        
        if (strlen($password) < 8) {
            $_SESSION['error'] = 'Password must be at least 8 characters long';
            header('Location: ' . BASE_URL . '/reset-password?token=' . urlencode($token));
            exit;
        }
        
        // Verify token and get user
        $user = $this->userModel->getUserByResetToken($token);
        
        if (!$user) {
            $_SESSION['error'] = 'Invalid or expired reset link. Please request a new one.';
            header('Location: ' . BASE_URL . '/forgot-password');
            exit;
        }
        
        // Update password
        $result = $this->userModel->updatePassword($user['id'], $password);
        
        if ($result['success']) {
            // Clear reset token
            $this->userModel->clearResetToken($user['id']);
            
            $_SESSION['success'] = 'Password reset successful! You can now login with your new password.';
            header('Location: ' . BASE_URL . '/login');
            exit;
        } else {
            $_SESSION['error'] = 'Failed to reset password. Please try again.';
            header('Location: ' . BASE_URL . '/reset-password?token=' . urlencode($token));
            exit;
        }
    }

    /**
     * Send password reset email
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
                    box-shadow: 0 25px 50px -12px rgba(139, 92, 246, 0.25);
                }
                .email-header {
                    background: linear-gradient(135deg, #8B5CF6, #F97316);
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
                    background: linear-gradient(135deg, #8B5CF6, #F97316);
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
                .expiry-note i {
                    color: #EF4444;
                    font-size: 1.2rem;
                }
                .footer-note {
                    border-top: 2px solid #F1F5F9;
                    padding-top: 25px;
                    margin-top: 25px;
                    color: #94A3B8;
                    font-size: 0.9rem;
                }
                .footer-note a {
                    color: #8B5CF6;
                    text-decoration: none;
                    font-weight: 600;
                }
                .footer-note a:hover {
                    text-decoration: underline;
                }
                @media (max-width: 480px) {
                    .email-header h1 {
                        font-size: 1.6rem;
                    }
                    .email-body {
                        padding: 30px 20px;
                    }
                    .reset-button a {
                        display: block;
                    }
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
                        <i class='fas fa-clock'></i>
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
        
        // For development, log the email
        error_log("Password reset email would be sent to: $email");
        error_log("Reset link: $resetLink");
        
        // In production, use PHP mail or SMTP
        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
        $headers .= 'From: Rays of Grace <noreply@raysofgrace.com>' . "\r\n";
        
        // mail($email, $subject, $message, $headers);
    }
}
?>