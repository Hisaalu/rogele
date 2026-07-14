<?php
// File: /controllers/AuthController.php - Optimized Auth Controller
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Classes.php';
require_once __DIR__ . '/../helpers/MailHelper.php';

class AuthController {
    private $userModel;
    private $mailHelper;
    private $classModel;
    
    public function __construct() {
        $this->userModel = new User();
        $this->mailHelper = new MailHelper();
        $this->classModel = new Classes();
    }
    
    // ==================== HELPER METHODS ====================
    private function redirect($url) {
        header('Location: ' . $url);
        exit;
    }
    
    private function redirectWithError($message, $url) {
        $_SESSION['error'] = $message;
        $this->redirect($url);
    }
    
    private function redirectWithSuccess($message, $url) {
        $_SESSION['success'] = $message;
        $this->redirect($url);
    }
    
    private function isPostRequest() {
        return $_SERVER['REQUEST_METHOD'] === 'POST';
    }
    
    private function validateRequiredFields($data, $fields) {
        $errors = [];
        foreach ($fields as $field) {
            if (empty($data[$field])) {
                $errors[] = ucfirst(str_replace('_', ' ', $field)) . ' is required';
            }
        }
        return $errors;
    }
    
    private function validatePassword($password, $confirmPassword) {
        if ($password !== $confirmPassword) {
            return 'Passwords do not match';
        }
        if (strlen($password) < 8) {
            return 'Password must be at least 8 characters!';
        }
        return null;
    }
    
    private function redirectToDashboard() {
        $urls = [
            'admin' => BASE_URL . '/admin/dashboard',
            'teacher' => BASE_URL . '/teacher/dashboard',
            'learner' => BASE_URL . '/learner/dashboard',
            'external' => BASE_URL . '/external/dashboard'
        ];
        $this->redirect($urls[$_SESSION['user_role']] ?? BASE_URL . '/login');
    }
    
    private function setUserSession($user) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
        $_SESSION['user_email'] = $user['email'];
        
        if (isset($user['force_password_change']) && $user['force_password_change']) {
            $_SESSION['force_password_change'] = true;
        }
    }
    
    // ==================== PUBLIC METHODS ====================
    public function login() {
        if (isset($_SESSION['user_id'])) {
            $this->redirectToDashboard();
            return;
        }
        
        $hideFooter = true;
        
        if ($this->isPostRequest()) {
            $username = trim($_POST['username'] ?? '');
            $password = $_POST['password'] ?? '';
            
            if (empty($username) || empty($password)) {
                $this->redirectWithError('Please enter both your email and password!', BASE_URL . '/login');
            }
            
            $result = $this->userModel->login($username, $password);
            
            if (!$result['success']) {
                error_log("Error message: " . ($result['error'] ?? 'Unknown error'));
            }
            
            if ($result['success']) {
                $this->setUserSession($result['user']);
                $this->redirectToDashboard();
            } else {
                $this->redirectWithError($result['error'] ?? 'Login failed. Please try again!', BASE_URL . '/login');
            }
        }
        
        require_once __DIR__ . '/../views/auth/login.php';
    }
    
    public function register() {
        $hideFooter = true;
        
        if (!$this->isPostRequest()) {
            $classes = $this->classModel->getAllClasses();
            require_once __DIR__ . '/../views/auth/register.php';
            return;
        }
        
        $data = [
            'first_name' => trim($_POST['first_name'] ?? ''),
            'last_name' => trim($_POST['last_name'] ?? ''),
            'email' => trim($_POST['email'] ?? ''),
            'phone' => trim($_POST['phone'] ?? ''),
            'class_id' => trim($_POST['class_id'] ?? ''),
            'password' => $_POST['password'] ?? '',
            'confirm_password' => $_POST['confirm_password'] ?? ''
        ];
        
        $errors = $this->validateRequiredFields($data, ['first_name', 'last_name', 'email', 'phone', 'class_id', 'password']);
        
        if (empty($errors)) {
            $passwordError = $this->validatePassword($data['password'], $data['confirm_password']);
            if ($passwordError) {
                $errors[] = $passwordError;
            }
        }
        
        if (!empty($errors)) {
            $this->redirectWithError(implode(', ', $errors), BASE_URL . '/register');
        }
        
        $userData = [
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'class_id' => $data['class_id'],
            'password' => $data['password'],
            'role' => 'external'
        ];
        
        $result = $this->userModel->register($userData);
        
        if ($result['success']) {
            $this->setUserSession($result['user']);
            $this->redirectToDashboard();
        } else {
            $this->redirectWithError($result['error'] ?? 'Registration failed. Please try again!', BASE_URL . '/register');
        }
    }
    
    public function logout() {
        session_destroy();
        $this->redirect(BASE_URL . '/login');
    }
    
    public function changePassword() {
        if (!isset($_SESSION['user_id'])) {
            $this->redirect(BASE_URL . '/login');
        }
        
        if ($this->isPostRequest()) {
            $oldPassword = $_POST['old_password'] ?? '';
            $newPassword = $_POST['new_password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';
            
            if ($newPassword !== $confirmPassword) {
                $_SESSION['error'] = 'New passwords do not match!';
            } else {
                $result = $this->userModel->changePassword($_SESSION['user_id'], $oldPassword, $newPassword);
                
                if ($result['success']) {
                    unset($_SESSION['force_password_change']);
                    $this->redirectWithSuccess('Password changed successfully!', BASE_URL . '/dashboard');
                } else {
                    $_SESSION['error'] = $result['error'];
                }
            }
        }
        
        require_once __DIR__ . '/../views/auth/change_password.php';
    }
    
    public function forgotPassword() {
        $hideFooter = true;
        require_once __DIR__ . '/../views/auth/forgot-password.php';
    }
    
    public function processForgotPassword() {
        $hideFooter = true;
        
        if (!$this->isPostRequest()) {
            $this->redirect(BASE_URL . '/forgot-password');
        }
        
        $email = $_POST['email'] ?? '';
        
        if (empty($email)) {
            $this->redirectWithError('Please enter your email address!', BASE_URL . '/forgot-password');
        }
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->redirectWithError('Please enter a valid email address!', BASE_URL . '/forgot-password');
        }
        
        $user = $this->userModel->getByEmail($email);
        
        if ($user) {
            $token = bin2hex(random_bytes(32));
            $expires = date('Y-m-d H:i:s', strtotime('+20 minutes'));
            
            $saved = $this->userModel->saveResetToken($user['id'], $token, $expires);
            
            if ($saved) {
                $resetLink = BASE_URL . "/reset-password?token=" . $token;
                $sent = $this->mailHelper->sendResetEmail($email, $user['first_name'], $resetLink);
                
                if ($sent) {
                    $_SESSION['success'] = 'Password reset link sent to your email!';
                } else {
                    $_SESSION['debug_reset_link'] = $resetLink;
                    $_SESSION['info'] = 'Email could not be sent. Please use the debug link below to reset your password.';
                }
            } else {
                $_SESSION['error'] = 'Failed to process request. Please try again.';
            }
        } else {
            $_SESSION['error'] = 'We couldn\'t find a user with the provided email address!';
        }
        
        $this->redirect(BASE_URL . '/forgot-password');
    }
    
    public function resetPassword($token = null) {
        $hideFooter = true;
        
        if ($token) {
            $resetToken = $token;
        } else {
            $resetToken = $_GET['token'] ?? '';
            
            if (empty($resetToken)) {
                $requestUri = $_SERVER['REQUEST_URI'];
                if (preg_match('/reset-password\/([a-f0-9]+)/', $requestUri, $matches)) {
                    $resetToken = $matches[1];
                }
            }
        }
        
        if (empty($resetToken)) {
            $this->redirectWithError('Invalid reset link', BASE_URL . '/login');
        }
        
        $user = $this->userModel->getUserByResetToken($resetToken);
        
        if (!$user) {
            $this->redirectWithError('Invalid/expired reset link!', BASE_URL . '/forgot-password');
        }
        
        $token = $resetToken;
        require_once __DIR__ . '/../views/auth/reset-password.php';
    }
    
    public function processResetPassword() {
        $hideFooter = true;
        
        if (!$this->isPostRequest()) {
            $this->redirect(BASE_URL . '/login');
        }
        
        $token = $_POST['token'] ?? '';
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        
        if (empty($token) || empty($password) || empty($confirmPassword)) {
            $_SESSION['error'] = 'All fields are required';
            $this->redirect(BASE_URL . '/reset-password?token=' . urlencode($token));
        }
        
        $passwordError = $this->validatePassword($password, $confirmPassword);
        if ($passwordError) {
            $this->redirectWithError($passwordError, BASE_URL . '/reset-password?token=' . urlencode($token));
        }
        
        $user = $this->userModel->getUserByResetToken($token);
        
        if (!$user) {
            $this->redirectWithError('Invalid/expired reset link!', BASE_URL . '/forgot-password');
        }
        
        $result = $this->userModel->updatePassword($user['id'], $password);
        
        if ($result['success']) {
            $this->userModel->clearResetToken($user['id']);
            $this->redirectWithSuccess('Your password has been reset successfully!', BASE_URL . '/login');
        } else {
            $this->redirectWithError('Failed to reset password. Please try again.', BASE_URL . '/reset-password?token=' . urlencode($token));
        }
    }
}
?>