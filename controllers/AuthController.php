<!-- File: /controllers/AuthController.php -->
<?php
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../helpers/MailHelper.php';

class AuthController {
    private $userModel;
    private $mailHelper;
    
    public function __construct() {
        $this->userModel = new User();
        $this->mailHelper = new MailHelper();
        $this->classes = new Classes();
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
    
    /**
     * Process registration
     */
    public function register() {
        $hideFooter = true;
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            // Get classes for the form
            if (isset($this->classModel)) {
                $classes = $this->classModel->getAllClasses();
            } else {
                $classes = [];
            }
            require_once __DIR__ . '/../views/auth/register.php';
            return;
        }
        
        // Get form data
        $firstName = trim($_POST['first_name'] ?? '');
        $lastName = trim($_POST['last_name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $classId = trim($_POST['class_id'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        
        // Validation
        if (empty($firstName) || empty($lastName) || empty($email) || empty($phone) || empty($classId) || empty($password)) {
            $_SESSION['error'] = 'Please fill in all fields';
            header('Location: ' . BASE_URL . '/register');
            exit;
        }
        
        if ($password !== $confirmPassword) {
            $_SESSION['error'] = 'Passwords do not match';
            header('Location: ' . BASE_URL . '/register');
            exit;
        }
        
        if (strlen($password) < 8) {
            $_SESSION['error'] = 'Password must be at least 8 characters';
            header('Location: ' . BASE_URL . '/register');
            exit;
        }
        
        // Register user with class_id
        $userData = [
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => $email,
            'phone' => $phone,
            'class_id' => $classId,
            'password' => $password,
            'role' => 'external'
        ];
        
        $result = $this->userModel->register($userData);
        
        if ($result['success']) {
            $_SESSION['success'] = 'Registration successful! Please login.';
            header('Location: ' . BASE_URL . '/login');
            exit;
        } else {
            $_SESSION['error'] = $result['error'] ?? 'Registration failed. Please try again.';
            header('Location: ' . BASE_URL . '/register');
            exit;
        }
    }
    
    // Handle logout
    public function logout() {
        session_destroy();
        header('Location: ' . BASE_URL . '/');
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
     * Process forgot password request (updated)
     */
    public function processForgotPassword() {
        $hideFooter = true;
        
        error_log("=== PROCESS FORGOT PASSWORD START ===");
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/forgot-password');
            exit;
        }
        
        $email = $_POST['email'] ?? '';
        error_log("Email submitted: " . $email);
        
        if (empty($email)) {
            $_SESSION['error'] = 'Please enter your email address';
            header('Location: ' . BASE_URL . '/forgot-password');
            exit;
        }
        
        // Validate email format
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['error'] = 'Please enter a valid email address';
            header('Location: ' . BASE_URL . '/forgot-password');
            exit;
        }
        
        // Check if user exists
        $user = $this->userModel->getByEmail($email);
        error_log("User found: " . ($user ? "YES" : "NO"));
        
        if ($user) {
            $token = bin2hex(random_bytes(32));
            $expires = date('Y-m-d H:i:s', strtotime('+20 minutes'));
            error_log("Token generated: " . $token);
            
            $saved = $this->userModel->saveResetToken($user['id'], $token, $expires);
            error_log("Token saved: " . ($saved ? "YES" : "NO"));
            
            if ($saved) {
                $resetLink = BASE_URL . "/reset-password?token=" . $token;
                error_log("Reset link: " . $resetLink);
                
                $sent = $this->mailHelper->sendResetEmail($email, $user['first_name'], $resetLink);
                error_log("Email sent: " . ($sent ? "YES" : "NO"));
                
                if ($sent) {
                    $_SESSION['success'] = 'Password reset link sent to email.';
                } else {
                    $_SESSION['debug_reset_link'] = $resetLink;
                    $_SESSION['info'] = 'Email could not be sent. Please use the debug link below to reset your password.';
                }
            } else {
                $_SESSION['error'] = 'Failed to process request. Please try again.';
                error_log("Failed to save reset token");
            }
        } else {
            $_SESSION['success'] = 'If an account exists with this email, you will receive a password reset link.';
            error_log("No user found with email: $email");
        }
        
        header('Location: ' . BASE_URL . '/forgot-password');
        exit;
    }

    /**
     * Process reset password
     */
    public function processResetPassword() {
        $hideFooter = true;
        
        error_log("=== PROCESS RESET PASSWORD ===");
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/login');
            exit;
        }
        
        $token = $_POST['token'] ?? '';
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        
        error_log("Token from POST: " . $token);
        error_log("Password length: " . strlen($password));
        
        if (empty($token) || empty($password) || empty($confirmPassword)) {
            error_log("Missing fields");
            $_SESSION['error'] = 'All fields are required';
            header('Location: ' . BASE_URL . '/reset-password?token=' . urlencode($token));
            exit;
        }
        
        if ($password !== $confirmPassword) {
            error_log("Passwords do not match");
            $_SESSION['error'] = 'Passwords do not match';
            header('Location: ' . BASE_URL . '/reset-password?token=' . urlencode($token));
            exit;
        }
        
        if (strlen($password) < 8) {
            error_log("Password too short");
            $_SESSION['error'] = 'Password must be at least 8 characters long';
            header('Location: ' . BASE_URL . '/reset-password?token=' . urlencode($token));
            exit;
        }
        
        // Verify token and get user
        $user = $this->userModel->getUserByResetToken($token);
        error_log("User found with token: " . ($user ? "YES" : "NO"));
        
        if (!$user) {
            error_log("No user found with token: " . $token);
            $_SESSION['error'] = 'Invalid or expired reset link. Please request a new one.';
            header('Location: ' . BASE_URL . '/forgot-password');
            exit;
        }
        
        error_log("User found: " . $user['email']);
        
        // Update password
        $result = $this->userModel->updatePassword($user['id'], $password);
        error_log("Password update result: " . ($result['success'] ? "SUCCESS" : "FAILED"));
        
        if ($result['success']) {
            // Clear reset token
            $this->userModel->clearResetToken($user['id']);
            
            $_SESSION['success'] = 'Password reset successful! You can now login with your new password.';
            error_log("Password reset successful for user: " . $user['email']);
            header('Location: ' . BASE_URL . '/login');
            exit;
        } else {
            error_log("Failed to reset password: " . ($result['error'] ?? 'Unknown error'));
            $_SESSION['error'] = 'Failed to reset password. Please try again.';
            header('Location: ' . BASE_URL . '/reset-password?token=' . urlencode($token));
            exit;
        }
    }

    /**
     * Send reset email (updated with actual email sending)
     */
    private function sendResetEmail($email, $token, $name) {
        $resetLink = BASE_URL . "/reset-password?token=" . $token;
        
        $subject = "Password Reset Request - Rays of Grace";
        
        $message = $this->getResetEmailTemplate($name, $resetLink);
        
        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
        $headers .= "From: Rays of Grace <noreply@raysofgrace.com>" . "\r\n";
        $headers .= "Reply-To: support@raysofgrace.com" . "\r\n";
        
        // For local development using XAMPP, we need to configure sendmail
        // or use SMTP. Let's use a simple mail() function first
        
        error_log("Attempting to send email to: " . $email);
        error_log("Reset link: " . $resetLink);
        
        // Try to send email
        $mailSent = mail($email, $subject, $message, $headers);
        
        if ($mailSent) {
            error_log("Email sent successfully to: " . $email);
        } else {
            error_log("Failed to send email to: " . $email);
            // For development, still show the link
            $_SESSION['debug_reset_link'] = $resetLink;
        }
        
        return $mailSent;
    }

    /**
     * Get reset email template
     */
    private function getResetEmailTemplate($name, $resetLink) {
        return '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <style>
                body {
                    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen, Ubuntu, sans-serif;
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
                    background: linear-gradient(135deg, #7f2677, #f06724);
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
            <div class="email-container">
                <div class="email-header">
                    <h1>🔐 Password Reset Request</h1>
                    <p>Rays of Grace E-Learning</p>
                </div>
                
                <div class="email-body">
                    <div class="greeting">
                        Hello ' . htmlspecialchars($name) . '! 👋
                    </div>
                    
                    <div class="message">
                        We received a request to reset the password for your Rays of Grace E-Learning account. 
                        No changes have been made to your account yet.
                    </div>
                    
                    <div class="message">
                        To reset your password, click the button below:
                    </div>
                    
                    <div class="reset-button">
                        <a href="' . $resetLink . '">🔓 Reset Your Password</a>
                    </div>
                    
                    <div class="expiry-note">
                        <span>⏰</span>
                        <strong>Note:</strong> This password reset link will expire in 20 minutes for security reasons.
                    </div>
                    
                    <div class="message">
                        If you didn\'t request a password reset, you can safely ignore this email. 
                        Your account is still secure and no changes have been made.
                    </div>
                    
                    <div class="footer-note">
                        <p>For security assistance, please contact our support team at 
                        <a href="mailto:support@raysofgrace.com">support@raysofgrace.com</a>
                        </p>
                        <p style="margin-top: 15px;">© ' . date('Y') . ' Rays of Grace Junior School. All rights reserved.</p>
                    </div>
                </div>
            </div>
        </body>
        </html>
        ';
    }

    /**
     * Show reset password form
     */
    public function resetPassword($token = null) {
        $hideFooter = true;
        
        error_log("=== RESET PASSWORD FORM ===");
        
        // If token is passed as a parameter (from route), use it
        if ($token) {
            $resetToken = $token;
            error_log("Token from route parameter: " . $resetToken);
        } else {
            // Fallback: try to get from GET or URL
            $resetToken = $_GET['token'] ?? '';
            error_log("Token from GET: " . $resetToken);
            
            // If still empty, try to get from REQUEST_URI
            if (empty($resetToken)) {
                $requestUri = $_SERVER['REQUEST_URI'];
                if (preg_match('/reset-password\/([a-f0-9]+)/', $requestUri, $matches)) {
                    $resetToken = $matches[1];
                    error_log("Token from URI regex: " . $resetToken);
                }
            }
        }
        
        error_log("Final token: " . $resetToken);
        error_log("Token length: " . strlen($resetToken));
        
        if (empty($resetToken)) {
            error_log("Token is empty - redirecting to login");
            $_SESSION['error'] = 'Invalid reset link';
            header('Location: ' . BASE_URL . '/login');
            exit;
        }
        
        // Verify token is valid and not expired
        $user = $this->userModel->getUserByResetToken($resetToken);
        error_log("User found with token: " . ($user ? "YES" : "NO"));
        
        if (!$user) {
            error_log("No user found or token expired/invalid");
            $_SESSION['error'] = 'Invalid or expired reset link. Please request a new one.';
            header('Location: ' . BASE_URL . '/forgot-password');
            exit;
        }
        
        error_log("Token verified for user: " . $user['email']);
        
        // Pass token to the view
        $token = $resetToken;
        require_once __DIR__ . '/../views/auth/reset-password.php';
    }
}
?>