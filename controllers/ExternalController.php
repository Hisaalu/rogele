<?php
// File: /controllers/ExternalController.php
require_once __DIR__ . '/../models/Subscription.php';
require_once __DIR__ . '/../models/Lesson.php';
require_once __DIR__ . '/../models/Quiz.php';
require_once __DIR__ . '/../models/User.php';

class ExternalController {
    private $subscriptionModel;
    private $lessonModel;
    private $quizModel;
    private $userModel;
    
    public function __construct() {
        // Check if user is logged in
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . BASE_URL . '/login');
            exit;
        }
        
        // Check if user has external role
        if ($_SESSION['user_role'] !== 'external') {
            $this->redirectToRoleDashboard();
            exit;
        }
        
        $this->subscriptionModel = new Subscription();
        $this->lessonModel = new Lesson();
        $this->quizModel = new Quiz();
        $this->userModel = new User();
    }
    
    private function redirectToRoleDashboard() {
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
            default:
                header('Location: ' . BASE_URL . '/login');
        }
        exit;
    }
    
    /**
     * Display dashboard
     */
    public function dashboard() {
        $hideFooter = true;
        $hasAccess = $this->userModel->hasAccess($_SESSION['user_id']);
        $subscription = $this->subscriptionModel->checkStatus($_SESSION['user_id']);
        
        require_once __DIR__ . '/../views/external/dashboard.php';
    }
    
    /**
     * Display profile page
     */
    public function profile() {
        $hideFooter = true;
        
        try {
            $profile = $this->userModel->getProfile($_SESSION['user_id']);
            
            if (!$profile) {
                // Create basic profile from session
                $nameParts = explode(' ', $_SESSION['user_name'] ?? 'User');
                $profile = [
                    'id' => $_SESSION['user_id'],
                    'first_name' => $nameParts[0] ?? '',
                    'last_name' => $nameParts[1] ?? '',
                    'email' => $_SESSION['user_email'] ?? '',
                    'phone' => '',
                    'role' => $_SESSION['user_role'] ?? 'external',
                    'created_at' => date('Y-m-d H:i:s'),
                    'profile_photo' => null
                ];
            }
            
            require_once __DIR__ . '/../views/external/profile.php';
            
        } catch (Exception $e) {
            error_log("Profile error: " . $e->getMessage());
            $_SESSION['error'] = 'Could not load profile';
            header('Location: ' . BASE_URL . '/external/dashboard');
            exit;
        }
    }
    
    /**
     * Display settings page
     */
    public function settings() {
        $hideFooter = true;
        
        try {
            $profile = $this->userModel->getProfile($_SESSION['user_id']);
            require_once __DIR__ . '/../views/external/settings.php';
        } catch (Exception $e) {
            error_log("Settings error: " . $e->getMessage());
            $_SESSION['error'] = 'Could not load settings';
            header('Location: ' . BASE_URL . '/external/dashboard');
            exit;
        }
    }
    
    /**
     * Update profile
     */
    public function updateProfile() {
        $hideFooter = true;
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/external/profile');
            exit;
        }
        
        $data = [
            'first_name' => $_POST['first_name'] ?? '',
            'last_name' => $_POST['last_name'] ?? '',
            'email' => $_POST['email'] ?? '',
            'phone' => $_POST['phone'] ?? ''
        ];
        
        // Validate input
        if (empty($data['first_name']) || empty($data['last_name']) || empty($data['email'])) {
            $_SESSION['error'] = 'Please fill in all required fields';
            header('Location: ' . BASE_URL . '/external/profile');
            exit;
        }
        
        $result = $this->userModel->updateProfile($_SESSION['user_id'], $data);
        
        if ($result['success']) {
            $_SESSION['success'] = $result['message'];
        } else {
            $_SESSION['error'] = $result['error'];
        }
        
        header('Location: ' . BASE_URL . '/external/profile');
        exit;
    }
    
    /**
     * Change password
     */
    public function changePassword() {
        $hideFooter = true;
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/external/settings');
            exit;
        }
        
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        
        if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
            $_SESSION['error'] = 'Please fill in all password fields';
            header('Location: ' . BASE_URL . '/external/settings');
            exit;
        }
        
        if ($newPassword !== $confirmPassword) {
            $_SESSION['error'] = 'New passwords do not match';
            header('Location: ' . BASE_URL . '/external/settings');
            exit;
        }
        
        if (strlen($newPassword) < 8) {
            $_SESSION['error'] = 'Password must be at least 8 characters long';
            header('Location: ' . BASE_URL . '/external/settings');
            exit;
        }
        
        $result = $this->userModel->changePassword($_SESSION['user_id'], $currentPassword, $newPassword);
        
        if ($result['success']) {
            $_SESSION['success'] = $result['message'];
        } else {
            $_SESSION['error'] = $result['error'];
        }
        
        header('Location: ' . BASE_URL . '/external/settings');
        exit;
    }
    
    /**
     * Delete account
     */
    public function deleteAccount() {
        $hideFooter = true;
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/external/settings');
            exit;
        }
        
        $password = $_POST['password'] ?? '';
        
        if (empty($password)) {
            $_SESSION['error'] = 'Please enter your password';
            header('Location: ' . BASE_URL . '/external/settings');
            exit;
        }
        
        $result = $this->userModel->deleteAccount($_SESSION['user_id'], $password);
        
        if ($result['success']) {
            // Logout the user
            session_destroy();
            session_start();
            $_SESSION['success'] = 'Your account has been successfully deleted.';
            header('Location: ' . BASE_URL . '/login');
            exit;
        } else {
            $_SESSION['error'] = $result['error'];
            header('Location: ' . BASE_URL . '/external/settings');
            exit;
        }
    }
    
    /**
     * Display materials
     */
    public function materials() {
        $hideFooter = true;
        
        if (!$this->userModel->hasAccess($_SESSION['user_id'])) {
            header('Location: ' . BASE_URL . '/external/subscription');
            exit;
        }
        
        $search = $_GET['search'] ?? null;
        
        if ($search) {
            $lessons = $this->lessonModel->search($search);
        } else {
            $lessons = $this->lessonModel->getAll(1, 20);
        }
        
        require_once __DIR__ . '/../views/external/materials.php';
    }
    
    /**
     * View single lesson
     */
    public function viewLesson($lessonId) {
        $hideFooter = true;
        
        if (!$this->userModel->hasAccess($_SESSION['user_id'])) {
            header('Location: ' . BASE_URL . '/external/subscription');
            exit;
        }
        
        $lesson = $this->lessonModel->getById($lessonId);
        
        if (!$lesson) {
            header('HTTP/1.0 404 Not Found');
            echo "Lesson not found";
            exit;
        }
        
        require_once __DIR__ . '/../views/external/view_lesson.php';
    }
    
    /**
     * Display quizzes
     */
    public function quizzes() {
        $hideFooter = true;
        
        if (!$this->userModel->hasAccess($_SESSION['user_id'])) {
            header('Location: ' . BASE_URL . '/external/subscription');
            exit;
        }
        
        $user = $this->userModel->getById($_SESSION['user_id']);
        $quizzes = $this->quizModel->getAvailableQuizzes($_SESSION['user_id'], $user['class_id'] ?? null);
        $results = $this->quizModel->getUserResults($_SESSION['user_id']);
        
        require_once __DIR__ . '/../views/external/quizzes.php';
    }
    
    /**
     * Take quiz
     */
    public function takeQuiz($quizId) {
        $hideFooter = true;
        
        if (!$this->userModel->hasAccess($_SESSION['user_id'])) {
            header('Location: ' . BASE_URL . '/external/subscription');
            exit;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $attemptId = $_POST['attempt_id'] ?? null;
            $answers = $_POST['answers'] ?? [];
            
            if (!$attemptId) {
                $_SESSION['error'] = 'Invalid quiz attempt';
                header('Location: ' . BASE_URL . '/external/quizzes');
                exit;
            }
            
            $result = $this->quizModel->submitAttempt($attemptId, $answers);
            
            if ($result['success']) {
                $_SESSION['quiz_result'] = $result;
                header("Location: " . BASE_URL . "/external/quiz-result/" . $attemptId);
                exit;
            } else {
                $_SESSION['error'] = $result['error'] ?? 'Failed to submit quiz';
                header("Location: " . BASE_URL . "/external/take-quiz/" . $quizId);
                exit;
            }
        } else {
            $result = $this->quizModel->startAttempt($quizId, $_SESSION['user_id']);
            
            if ($result['success']) {
                $quiz = $this->quizModel->getById($quizId);
                $questions = $result['questions'];
                $attemptId = $result['attempt_id'];
                require_once __DIR__ . '/../views/external/take_quiz.php';
            } else {
                $_SESSION['error'] = $result['error'] ?? 'Failed to start quiz';
                header('Location: ' . BASE_URL . '/external/quizzes');
                exit;
            }
        }
    }
    
    /**
     * Display quiz result
     */
    public function quizResult($attemptId) {
        $hideFooter = true;
        
        if (!$this->userModel->hasAccess($_SESSION['user_id'])) {
            header('Location: ' . BASE_URL . '/external/subscription');
            exit;
        }
        
        $result = $_SESSION['quiz_result'] ?? null;
        unset($_SESSION['quiz_result']);
        
        $attemptDetails = $this->quizModel->getAttemptDetails($attemptId);
        
        if (!$attemptDetails || $attemptDetails['user_id'] != $_SESSION['user_id']) {
            header('HTTP/1.0 404 Not Found');
            echo "Result not found";
            exit;
        }
        
        require_once __DIR__ . '/../views/external/quiz_result.php';
    }
    
    /**
     * Display subscription page
     */
    public function subscription() {
        $hideFooter = true;
        
        $currentSubscription = $this->subscriptionModel->checkStatus($_SESSION['user_id']);
        $paymentHistory = $this->subscriptionModel->getPaymentHistory($_SESSION['user_id']);
        
        require_once __DIR__ . '/../views/external/subscription.php';
    }
    
    /**
     * Display purchase page
     */
    public function purchase() {
        $hideFooter = true;
        
        $plan = $_GET['plan'] ?? 'monthly';
        $validPlans = ['monthly', 'termly', 'yearly'];
        
        if (!in_array($plan, $validPlans)) {
            $plan = 'monthly';
        }
        
        require_once __DIR__ . '/../views/external/purchase.php';
    }
    
    /**
     * Process payment
     */
    public function processPayment() {
        $hideFooter = true;
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/external/subscription');
            exit;
        }
        
        $plan = $_POST['plan'] ?? 'monthly';
        $phoneNumber = $_POST['phone_number'] ?? '';
        $paymentMethod = $_POST['payment_method'] ?? '';
        
        if (empty($phoneNumber) || empty($paymentMethod)) {
            $_SESSION['error'] = 'Please fill in all payment details';
            header('Location: ' . BASE_URL . '/external/purchase?plan=' . $plan);
            exit;
        }
        
        $result = $this->subscriptionModel->create($_SESSION['user_id'], $plan, $paymentMethod);
        
        if ($result['success']) {
            $amount = SUBSCRIPTION_PLANS[$plan] ?? 0;
            $paymentResult = $this->subscriptionModel->processPayment(
                $_SESSION['user_id'],
                $result['subscription_id'],
                $phoneNumber,
                $amount
            );
            
            if ($paymentResult['success']) {
                $_SESSION['success'] = 'Payment successful! Your subscription is now active.';
                header('Location: ' . BASE_URL . '/external/dashboard');
                exit;
            } else {
                $_SESSION['error'] = 'Payment failed: ' . ($paymentResult['error'] ?? 'Unknown error');
            }
        } else {
            $_SESSION['error'] = 'Failed to create subscription: ' . ($result['error'] ?? 'Unknown error');
        }
        
        header('Location: ' . BASE_URL . '/external/purchase?plan=' . $plan);
        exit;
    }
    
    /**
     * Display trial status
     */
    public function trialStatus() {
        $hideFooter = true;
        require_once __DIR__ . '/../views/external/trial_status.php';
    }
}
?>