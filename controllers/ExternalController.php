<?php
// File: /controllers/ExternalController.php
require_once __DIR__ . '/../models/Subscription.php';
require_once __DIR__ . '/../models/Lesson.php';
require_once __DIR__ . '/../models/Quiz.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Settings.php';
require_once __DIR__ . '/../models/Subject.php';

class ExternalController {
    private $subscriptionModel;
    private $lessonModel;
    private $quizModel;
    private $userModel;
    private $settingsModel;
    private $subjectModel;
    
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
        $this->settingsModel = new Settings();
        $this->subjectModel = new Subject();
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
     * Display learning materials
     */
    public function materials() {
        $hideFooter = true;
        
        if (!$this->userModel->hasAccess($_SESSION['user_id'])) {
            header('Location: ' . BASE_URL . '/external/subscription');
            exit;
        }
        
        $subject = $_GET['subject'] ?? null;
        $search = $_GET['search'] ?? null;
        
        if ($search) {
            $lessons = $this->lessonModel->searchPublished($search, $subject);
        } else {
            $lessons = $this->lessonModel->getPublishedLessons($subject);
        }
        
        $subjects = $this->subjectModel->getAll();
        
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
        
        $lesson = $this->lessonModel->getPublishedLessonById($lessonId, $_SESSION['user_id']);
        
        if (!$lesson) {
            $_SESSION['error'] = 'Lesson not found or not available.';
            header('Location: ' . BASE_URL . '/external/materials');
            exit;
        }
        
        require_once __DIR__ . '/../views/external/view_lesson.php';
    }
    
    /**
     * Toggle bookmark
     */
    public function toggleBookmark($lessonId) {
        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['success' => false, 'error' => 'Please login first']);
            exit;
        }
        
        $userId = $_SESSION['user_id'];
        $isBookmarked = $this->lessonModel->isBookmarked($userId, $lessonId);
        
        if ($isBookmarked) {
            $result = $this->lessonModel->removeBookmark($userId, $lessonId);
            $message = 'Bookmark removed';
        } else {
            $result = $this->lessonModel->addBookmark($userId, $lessonId);
            $message = 'Lesson bookmarked';
        }
        
        if ($result['success']) {
            echo json_encode(['success' => true, 'message' => $message, 'bookmarked' => !$isBookmarked]);
        } else {
            echo json_encode(['success' => false, 'error' => $result['error']]);
        }
        exit;
    }
    
    /**
     * Get user's bookmarks
     */
    public function bookmarks() {
        $hideFooter = true;
        
        if (!$this->userModel->hasAccess($_SESSION['user_id'])) {
            header('Location: ' . BASE_URL . '/external/subscription');
            exit;
        }
        
        $bookmarks = $this->lessonModel->getBookmarks($_SESSION['user_id']);
        
        require_once __DIR__ . '/../views/external/bookmarks.php';
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
        
        $quizzes = $this->quizModel->getPublishedQuizzes();
        $results = $this->quizModel->getUserResults($_SESSION['user_id']);
        
        require_once __DIR__ . '/../views/external/quizzes.php';
    }
    
    /**
     * Take a quiz
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
     * View quiz result
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
        
        $subscriptionSettings = $this->settingsModel->getSubscriptionSettings();
        
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
     * Display profile page
     */
    public function profile() {
        $hideFooter = true;
        
        $profile = $this->userModel->getProfile($_SESSION['user_id']);
        require_once __DIR__ . '/../views/external/profile.php';
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
        
        $result = $this->userModel->updateProfile($_SESSION['user_id'], $data);
        
        if ($result['success']) {
            $_SESSION['user_name'] = $data['first_name'] . ' ' . $data['last_name'];
            $_SESSION['user_email'] = $data['email'];
            $_SESSION['success'] = $result['message'];
        } else {
            $_SESSION['error'] = $result['error'];
        }
        
        header('Location: ' . BASE_URL . '/external/profile');
        exit;
    }
    
    /**
     * Display settings page
     */
    public function settings() {
        $hideFooter = true;
        require_once __DIR__ . '/../views/external/settings.php';
    }
    
    /**
     * Change password
     */
    public function changePassword() {
        $hideFooter = true;
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/external/settings?tab=password');
            exit;
        }
        
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        
        if ($newPassword !== $confirmPassword) {
            $_SESSION['error'] = 'New passwords do not match';
            header('Location: ' . BASE_URL . '/external/settings?tab=password');
            exit;
        }
        
        if (strlen($newPassword) < 8) {
            $_SESSION['error'] = 'Password must be at least 8 characters long';
            header('Location: ' . BASE_URL . '/external/settings?tab=password');
            exit;
        }
        
        $result = $this->userModel->changePassword($_SESSION['user_id'], $currentPassword, $newPassword);
        
        if ($result['success']) {
            $_SESSION['success'] = $result['message'];
        } else {
            $_SESSION['error'] = $result['error'];
        }
        
        header('Location: ' . BASE_URL . '/external/settings?tab=password');
        exit;
    }
    
    /**
     * Delete account
     */
    public function deleteAccount() {
        $hideFooter = true;
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/external/settings?tab=delete');
            exit;
        }
        
        $password = $_POST['password'] ?? '';
        
        if (empty($password)) {
            $_SESSION['error'] = 'Please enter your password';
            header('Location: ' . BASE_URL . '/external/settings?tab=delete');
            exit;
        }
        
        $result = $this->userModel->deleteAccount($_SESSION['user_id'], $password);
        
        if ($result['success']) {
            session_destroy();
            session_start();
            $_SESSION['success'] = 'Your account has been successfully deleted.';
            header('Location: ' . BASE_URL . '/login');
            exit;
        } else {
            $_SESSION['error'] = $result['error'];
            header('Location: ' . BASE_URL . '/external/settings?tab=delete');
            exit;
        }
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