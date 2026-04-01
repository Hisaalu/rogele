<?php
// File: /controllers/VerificationController.php
require_once __DIR__ . '/../models/User.php';

class VerificationController {
    private $userModel;
    
    public function __construct() {
        $this->userModel = new User();
    }
    
    public function verifyEmail() {
        $token = $_GET['token'] ?? '';
        
        if (empty($token)) {
            $this->showForm();
        } else {
            $result = $this->userModel->verifyEmail($token);
            
            if ($result['success']) {
                $_SESSION['success'] = 'Email verified successfully! You can now login.';
                header('Location: ' . BASE_URL . '/login');
            } else {
                $_SESSION['error'] = $result['error'];
                header('Location: ' . BASE_URL . '/verify-email');
            }
        }
    }
    
    private function showForm() {
        require_once __DIR__ . '/../views/auth/verify.php';
    }
    
    public function resendVerification() {
        $email = $_POST['email'] ?? '';
        
        if (empty($email)) {
            $_SESSION['error'] = 'Please enter your email address';
            header('Location: ' . BASE_URL . '/verify-email');
            return;
        }
        
        $user = $this->userModel->getByEmail($email);
        
        if (!$user) {
            $_SESSION['error'] = 'Email not found in our system';
            header('Location: ' . BASE_URL . '/verify-email');
            return;
        }
        
        if ($user['email_verified']) {
            $_SESSION['success'] = 'Your email is already verified. You can login.';
            header('Location: ' . BASE_URL . '/login');
            return;
        }
        
        $token = bin2hex(random_bytes(32));
        
        
        $_SESSION['success'] = 'Verification email sent! Please check your inbox.';
        header('Location: ' . BASE_URL . '/login');
    }
}
?>