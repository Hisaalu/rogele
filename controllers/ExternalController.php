<?php
// File: /controllers/ExternalController.php
require_once __DIR__ . '/../models/Subscription.php';
require_once __DIR__ . '/../models/Lesson.php';
require_once __DIR__ . '/../models/Quiz.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Classes.php';
require_once __DIR__ . '/../models/Homework.php';
require_once __DIR__ . '/../models/Settings.php';
require_once __DIR__ . '/../models/Subject.php';
require_once __DIR__ . '/../helpers/MailHelper.php';
require_once __DIR__ . '/../config/mobile_money.php';
require_once __DIR__ . '/../lib/MobileMoney.php';

class ExternalController {
    private $subscriptionModel;
    private $lessonModel;
    private $quizModel;
    private $userModel;
    private $settingsModel;
    private $subjectModel;
    private $classesModel;
    private $homeworkModel;
    private $userId;
    
    private $publicMethods = ['mobileMoneyIpn', 'mobileMoneyCallback', 'mobileMoneyTest', 'paymentCallback'];
    
    public function __construct() {
        $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2);
        $calledMethod = isset($backtrace[1]['function']) ? $backtrace[1]['function'] : '';
        
        $this->subscriptionModel = new Subscription();
        $this->lessonModel = new Lesson();
        $this->quizModel = new Quiz();
        $this->userModel = new User();
        $this->settingsModel = new Settings();
        $this->subjectModel = new Subject();
        $this->classesModel = new Classes();
        $this->homeworkModel = new Homework();
        $this->quizModel = new Quiz();
        if (in_array($calledMethod, $this->publicMethods)) {
            return;
        }
        
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . BASE_URL . '/login');
            exit;
        }
        
        if ($_SESSION['user_role'] !== 'external') {
            $this->redirectToRoleDashboard();
            exit;
        }
        
        $this->userId = $_SESSION['user_id'];
    }
    
    // ==================== HELPER METHODS ====================
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
    
    private function sanitize($input) {
        return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
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
    
    private function getUserClassId() {
        $user = $this->userModel->getById($this->userId);
        return $user['class_id'] ?? null;
    }
    
    private function getPlanPrice($planType) {
        $subscriptionSettings = $this->settingsModel->getSubscriptionSettings();
        $prices = [
            'monthly' => $subscriptionSettings['monthly_price'] ?? 15000,
            'termly' => $subscriptionSettings['termly_price'] ?? 40000,
            'yearly' => $subscriptionSettings['yearly_price'] ?? 120000
        ];
        return $prices[$planType] ?? 0;
    }
    
    private function logMobileMoneyRequest($type, $get, $post) {
        $logDir = __DIR__ . '/../logs';
        if (!file_exists($logDir)) {
            mkdir($logDir, 0777, true);
        }
        
        $logFile = $logDir . '/mobile_money_' . date('Y-m-d') . '.log';
        
        $logData = [
            'timestamp' => date('Y-m-d H:i:s'),
            'type' => $type,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
            'get' => $get,
            'post' => $post,
            'server' => [
                'request_uri' => $_SERVER['REQUEST_URI'] ?? 'unknown',
                'http_host' => $_SERVER['HTTP_HOST'] ?? 'unknown',
                'request_method' => $_SERVER['REQUEST_METHOD'] ?? 'unknown'
            ]
        ];
        
        file_put_contents($logFile, json_encode($logData) . "\n", FILE_APPEND);
    }
    
    private function checkAccess() {
        $userId = $this->userId;
        $trialDays = $this->settingsModel->get('trial_days', 60);
        
        $currentSubscription = $this->subscriptionModel->getCurrentSubscription($userId);
        
        if ($currentSubscription) {
            return true; 
        }
        
        $trialStatus = $this->userModel->getTrialStatus($userId, $trialDays);
        
        if ($trialStatus['is_trial']) {
            return true;
        }
        
        $_SESSION['error'] = 'Your free trial/plan ended. Please subscribe to continue accessing lessons, quizzes and other features!';
        header('Location: ' . BASE_URL . '/external/subscription');
        exit;
    }
    
    private function hasAccess() {
        $userId = $this->userId;
        $trialDays = $this->settingsModel->get('trial_days', 60);
        
        $currentSubscription = $this->subscriptionModel->getCurrentSubscription($userId);
        if ($currentSubscription) {
            return true;
        }
        
        $trialStatus = $this->userModel->getTrialStatus($userId, $trialDays);
        return $trialStatus['is_trial'];
    }
    
    // ==================== DASHBOARD & PROFILE ====================
    public function dashboard() {
        $hideFooter = true;
        
        $userId = $this->userId;
        $trialDays = $this->settingsModel->get('trial_days', 60);
        $remainingTrialDays = $this->userModel->getRemainingTrialDays($userId, $trialDays);
        $isInTrial = $remainingTrialDays > 0;
        $trialEndDate = $this->userModel->getTrialEndDate($userId, $trialDays);
        $currentSubscription = $this->subscriptionModel->getCurrentSubscription($userId);
        $hasActiveSubscription = !empty($currentSubscription);
        $daysPassed = $trialDays - $remainingTrialDays;
        $trialPercentage = $trialDays > 0 ? min(100, round(($daysPassed / $trialDays) * 100)) : 0;
        $currentPlan = $currentSubscription['plan_type'] ?? null;
        $subscriptionEndDate = $currentSubscription['end_date'] ?? null;
        
        $events = [];
        
        $user = $this->userModel->getById($userId);
        $userClassId = $user['class_id'] ?? null;
        
        if ($userClassId) {
            $homeworkDeadlines = $this->homeworkModel->getUpcomingHomeworkDeadlines($userId, $userClassId, 5);
            $quizDeadlines = $this->quizModel->getUpcomingQuizDeadlines($userId, $userClassId, 5);
            
            foreach ($homeworkDeadlines as $hw) {
                $events[] = [
                    'title' => $hw['title'] . ' (Homework)',
                    'date' => date('Y-m-d', strtotime($hw['due_date'])),
                    'time' => date('h:i A', strtotime($hw['due_date'])),
                    'type' => 'homework',
                    'class' => $hw['class_name'] ?? 'NA',
                    'subject' => $hw['subject_name'] ?? 'NA',
                    'id' => $hw['id'],
                    'has_submitted' => $hw['has_submitted'] ?? 0,
                    'url' => BASE_URL . '/external/homework/view/' . $hw['id']
                ];
            }
            
            foreach ($quizDeadlines as $quiz) {
                $events[] = [
                    'title' => $quiz['title'] . ' (Quiz)',
                    'date' => date('Y-m-d', strtotime($quiz['end_date'])),
                    'time' => date('h:i A', strtotime($quiz['end_date'])),
                    'type' => 'quiz',
                    'class' => $quiz['class_name'] ?? 'NA',
                    'subject' => $quiz['subject_name'] ?? 'NA',
                    'id' => $quiz['id'],
                    'has_attempted' => $quiz['has_attempted'] ?? 0,
                    'url' => BASE_URL . '/external/take-quiz/' . $quiz['id']
                ];
            }
            
            usort($events, function($a, $b) {
                return strtotime($a['date']) - strtotime($b['date']);
            });
            
        }
        
        require_once __DIR__ . '/../views/external/dashboard.php';
    }
    
    public function profile() {
        $this->checkAccess();
        $hideFooter = true;
        
        $userId = $this->userId;
        $profile = $this->userModel->getProfile($userId);
        $classes = $this->classesModel->getAll();
        $trialDays = $this->settingsModel->get('trial_days', 60);
        $trialEndDate = $this->userModel->getTrialEndDate($userId, $trialDays);
        $remainingTrialDays = $this->userModel->getRemainingTrialDays($userId, $trialDays);
        
        if ($profile) {
            $profile['trial_end'] = $trialEndDate;
            $profile['trial_days_remaining'] = $remainingTrialDays;
            $profile['trial_active'] = $remainingTrialDays > 0;
        }
        
        require_once __DIR__ . '/../views/external/profile.php';
    }
    
    public function updateProfile() {
        // $this->checkAccess();
        
        if (!$this->isPostRequest()) {
            $this->redirect(BASE_URL . '/external/profile');
        }
        
        $userId = $this->userId;
        
        $data = [
            'first_name' => trim($_POST['first_name'] ?? ''),
            'last_name' => trim($_POST['last_name'] ?? ''),
            'email' => trim($_POST['email'] ?? ''),
            'phone' => trim($_POST['phone'] ?? ''),
            'class_id' => !empty($_POST['class_id']) ? (int)$_POST['class_id'] : null
        ];
        
        $errors = $this->validateRequiredFields($data, ['first_name', 'last_name', 'email']);
        if (empty($errors) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Invalid email format';
        }
        
        if (!empty($errors)) {
            $this->redirectWithError(implode(', ', $errors), BASE_URL . '/external/profile');
        }
        
        $result = $this->userModel->updateProfile($userId, $data);
        
        if ($result['success']) {
            $_SESSION['user_name'] = $data['first_name'] . ' ' . $data['last_name'];
            $_SESSION['user_email'] = $data['email'];
            $this->redirectWithSuccess('Profile updated successfully!', BASE_URL . '/external/profile');
        } else {
            $this->redirectWithError($result['error'] ?? 'Failed to update profile', BASE_URL . '/external/profile');
        }
    }
    
    public function settings() {
        $hideFooter = true;
        require_once __DIR__ . '/../views/external/settings.php';
    }
    
    public function changePassword() {
        if (!$this->isPostRequest()) {
            $this->redirect(BASE_URL . '/external/settings?tab=password');
        }
        
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        
        if ($newPassword !== $confirmPassword) {
            $this->redirectWithError('New passwords do not match', BASE_URL . '/external/settings?tab=password');
        }
        
        if (strlen($newPassword) < 8) {
            $this->redirectWithError('Password must be at least 8 characters long', BASE_URL . '/external/settings?tab=password');
        }
        
        $result = $this->userModel->changePassword($this->userId, $currentPassword, $newPassword);
        
        if ($result['success']) {
            $this->redirectWithSuccess($result['message'], BASE_URL . '/external/settings?tab=password');
        } else {
            $this->redirectWithError($result['error'], BASE_URL . '/external/settings?tab=password');
        }
    }
    
    public function deleteAccount() {
        if (!$this->isPostRequest()) {
            $this->redirect(BASE_URL . '/external/settings?tab=delete');
        }
        
        $password = $_POST['password'] ?? '';
        
        if (empty($password)) {
            $this->redirectWithError('Please enter your password to confirm account deletion.', BASE_URL . '/external/settings?tab=delete');
        }
        
        $user = $this->userModel->getById($this->userId);
        if (!$user) {
            $_SESSION['error'] = 'User not found.';
            $this->redirect(BASE_URL . '/login');
        }
        
        $result = $this->userModel->deleteAccount($this->userId, $password);
        
        if ($result['success']) {
            session_destroy();
            session_start();
            $_SESSION['success'] = 'Your account has been successfully deleted!';
            $this->redirect(BASE_URL . '/login');
        } else {
            $this->redirectWithError($result['error'], BASE_URL . '/external/settings?tab=delete');
        }
    }
    
    public function trialStatus() {
        $hideFooter = true;
        require_once __DIR__ . '/../views/external/trial_status.php';
    }
    
    // ==================== LESSONS & MATERIALS ====================
    public function materials() {
        // $this->checkAccess();
        $hideFooter = true;
        
        $user = $this->userModel->getById($this->userId);
        $userClassId = $user['class_id'] ?? null;
        
        $search = isset($_GET['search']) ? trim($_GET['search']) : null;
        $subject = isset($_GET['subject']) ? (int)$_GET['subject'] : null;
        
        if ($search) {
            $lessons = $this->lessonModel->searchPublishedByClass($search, $userClassId, $subject);
        } else {
            $lessons = $this->lessonModel->getPublishedLessonsByClass($userClassId, $subject);
        }
        
        $bookmarkedLessonIds = $this->lessonModel->getUserBookmarkedIds($this->userId);
        
        foreach ($lessons as $key => &$lesson) {
            $lesson['is_bookmarked'] = in_array($lesson['id'], $bookmarkedLessonIds);
        }
        unset($lesson);
        
        $uniqueLessons = [];
        $seenIds = [];
        foreach ($lessons as $lesson) {
            if (!in_array($lesson['id'], $seenIds)) {
                $seenIds[] = $lesson['id'];
                $uniqueLessons[] = $lesson;
            }
        }
        
        if (count($uniqueLessons) != count($lessons)) {
            $lessons = $uniqueLessons;
        }
        
        $subjects = $this->subjectModel->getByClassId($userClassId);
        
        usort($subjects, function($a, $b) {
            return strcmp($a['name'], $b['name']);
        });
        
        $selectedSubject = $subject;
        
        require_once __DIR__ . '/../views/external/materials.php';
    }
    
    public function viewLesson($lessonId) {
        // $this->checkAccess();
        $hideFooter = true;
        
        $lesson = $this->lessonModel->getPublishedLessonById($lessonId, $this->userId);
        
        if (!$lesson) {
            $this->redirectWithError('Lesson not found or not available.', BASE_URL . '/external/materials');
        }
        
        require_once __DIR__ . '/../views/external/view_lesson.php';
    }
    
    public function toggleBookmark($lessonId) {
        header('Content-Type: application/json');
        
        if (!isset($this->userId)) {
            echo json_encode(['success' => false, 'error' => 'Please login first']);
            exit;
        }
        
        $userId = $this->userId;
        
        $isBookmarked = $this->lessonModel->isBookmarked($userId, $lessonId);
        
        if ($isBookmarked) {
            $result = $this->lessonModel->removeBookmark($userId, $lessonId);
            $message = 'Removed from your Bookmarks';
            $newStatus = false;
        } else {
            $result = $this->lessonModel->addBookmark($userId, $lessonId);
            $message = 'Added to your Bookmarks';
            $newStatus = true;
        }
        
        if ($result['success']) {
            $count = $this->lessonModel->getBookmarkCount($userId);
            
            echo json_encode([
                'success' => true, 
                'message' => $message, 
                'bookmarked' => $newStatus,
                'count' => $count
            ]);
        } else {
            echo json_encode(['success' => false, 'error' => $result['error'] ?? 'Operation failed']);
        }
        exit;
    }
    
    public function bookmarks() {
        $hideFooter = true;
        
        // $this->checkAccess();
        
        $bookmarks = $this->lessonModel->getBookmarks($this->userId);
        
        require_once __DIR__ . '/../views/external/bookmarks.php';
    }
    
    public function getBookmarkCount() {
        header('Content-Type: application/json');
        
        if (!isset($this->userId)) {
            echo json_encode(['success' => true, 'count' => 0]);
            exit;
        }
        
        $count = $this->lessonModel->getBookmarkCount($this->userId);
        echo json_encode(['success' => true, 'count' => $count]);
        exit;
    }
    
    // ==================== QUIZZES ====================
    public function quizzes() {
        // $this->checkAccess();
        $hideFooter = true;
        
        $user = $this->userModel->getById($this->userId);
        $userClassId = $user['class_id'] ?? null;
        
        if (!$userClassId) {
            $_SESSION['warning'] = 'Please select a class in your profile to access quizzes.';
            $this->redirect(BASE_URL . '/profile');
        }
        
        $quizzes = $this->quizModel->getQuizzesByClass($userClassId);
        $results = $this->quizModel->getUserQuizResults($this->userId);
        
        require_once __DIR__ . '/../views/external/quizzes.php';
    }
    
    public function takeQuiz($quizId) {
        // $this->checkAccess();
        $hideFooter = true;
        
        $availability = $this->quizModel->getQuizAvailabilityStatus($quizId);
        
        if (!$availability['available']) {
            $this->redirectWithError($availability['message'], BASE_URL . '/external/quizzes');
        }
        
        $remainingAttempts = $this->quizModel->getRemainingAttempts($this->userId, $quizId);
        
        if ($remainingAttempts <= 0) {
            $this->redirectWithError('You have used all your attempts for this quiz. Maximum attempts reached.', BASE_URL . '/external/quizzes');
        }
        
        if ($this->isPostRequest()) {
            $attemptId = $_POST['attempt_id'] ?? null;
            $answers = $_POST['answers'] ?? [];
            
            if (!$attemptId) {
                $this->redirectWithError('Invalid quiz attempt', BASE_URL . '/external/quizzes');
            }
            
            if (isset($_SESSION['current_quiz_attempt']) && $_SESSION['current_quiz_attempt'] != $attemptId) {
                $this->redirectWithError('Invalid quiz submission', BASE_URL . '/external/quizzes');
            }
            
            $result = $this->quizModel->submitAttempt($attemptId, $answers);
            
            if ($result['success']) {
                unset($_SESSION['current_quiz_attempt']);
                unset($_SESSION['current_quiz_id']);
                unset($_SESSION['quiz_start_time']);
                
                $_SESSION['quiz_result'] = $result;
                $this->redirect(BASE_URL . "/external/quiz-result/" . $attemptId);
            } else {
                $this->redirectWithError($result['error'] ?? 'Failed to submit quiz', BASE_URL . "/external/take-quiz/" . $quizId);
            }
        } else {
            if ($this->quizModel->hasReachedMaxAttempts($this->userId, $quizId)) {
                $this->redirectWithError('You have used all your attempts for this quiz. Maximum attempts reached.', BASE_URL . '/external/quizzes');
            }
            
            $questionCount = $this->quizModel->getQuestionCount($quizId);
            if ($questionCount == 0) {
                $this->redirectWithError('This quiz has no questions yet. Please contact the teacher.', BASE_URL . '/external/quizzes');
            }
            
            $result = $this->quizModel->startAttempt($quizId, $this->userId);
            
            if ($result['success']) {
                $quiz = $this->quizModel->getById($quizId);
                $questions = $result['questions'];
                $attemptId = $result['attempt_id'];
                
                $_SESSION['current_quiz_attempt'] = $attemptId;
                $_SESSION['current_quiz_id'] = $quizId;
                $_SESSION['quiz_start_time'] = time();
                
                require_once __DIR__ . '/../views/external/take_quiz.php';
            } else {
                $this->redirectWithError($result['error'] ?? 'Failed to start quiz', BASE_URL . '/external/quizzes');
            }
        }
    }
    
    public function quizResult($attemptId) {
        $hideFooter = true;
        
        // $this->checkAccess();
        
        $result = $_SESSION['quiz_result'] ?? null;
        unset($_SESSION['quiz_result']);
        
        $attemptDetails = $this->quizModel->getAttemptDetails($attemptId);
        
        if (!$attemptDetails || $attemptDetails['user_id'] != $this->userId) {
            header('HTTP/1.0 404 Not Found');
            echo "Result not found";
            exit;
        }
        
        $questions = $this->quizModel->getQuestions($attemptDetails['quiz_id']);
        $userAnswers = $this->quizModel->getUserAnswers($attemptId);
        
        if (empty($attemptDetails['time_taken']) && !empty($attemptDetails['started_at']) && !empty($attemptDetails['completed_at'])) {
            $startTime = strtotime($attemptDetails['started_at']);
            $endTime = strtotime($attemptDetails['completed_at']);
            $attemptDetails['time_taken'] = $endTime - $startTime;
        }
        
        $timeTaken = isset($attemptDetails['time_taken']) ? (int)$attemptDetails['time_taken'] : 0;
        $minutes = floor($timeTaken / 60);
        $seconds = $timeTaken % 60;
        $attemptDetails['time_formatted'] = $minutes . ':' . ($seconds < 10 ? '0' : '') . $seconds;
        
        $attemptDetails['questions'] = $questions;
        $attemptDetails['user_answers'] = $userAnswers;
        
        require_once __DIR__ . '/../views/external/quiz_result.php';
    }
    
    public function subscription() {
        $hideFooter = true;
        $currentSubscription = $this->subscriptionModel->getCurrentSubscription($this->userId);
        $subscriptionSettings = $this->settingsModel->getSubscriptionSettings();
        $paymentHistory = $this->subscriptionModel->getCombinedHistory($this->userId);
        $rawPaymentHistory = $this->subscriptionModel->getUserPaymentHistory($this->userId);
        require_once __DIR__ . '/../views/external/subscription.php';
    }
    
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
    
    public function processMobilePayment() {
        if (!$this->isPostRequest()) {
            $this->redirect(BASE_URL . '/external/subscription');
        }

        $userId = $this->userId;
        $planType = $_POST['plan_type'] ?? 'monthly';
        $provider = $_POST['provider'] ?? 'NA';
        $phone = $_POST['phone_number'] ?? '';

        if (empty($phone)) {
            $this->redirectWithError('Phone number is required.', BASE_URL . '/external/subscription');
        }

        $subscriptionSettings = $this->settingsModel->getSubscriptionSettings();
        $defaultPrices = ['monthly' => 15000, 'termly' => 40000, 'yearly' => 120000];
        $amount = $defaultPrices[$planType] ?? 15000;

        if (!empty($subscriptionSettings[$planType . '_price'])) {
            $amount = (float)$subscriptionSettings[$planType . '_price'];
        }

        $paymentResult = $this->subscriptionModel->createPendingPayment(
            $userId,
            $planType,
            $amount,
            $provider,
            $phone
        );

        if (!$paymentResult['success']) {
            $this->redirectWithError($paymentResult['error'], BASE_URL . '/external/subscription');
        }

        $transactionId = $paymentResult['transaction_id'];
        $mobileMoney = new MobileMoney();

        if ($provider === 'airtel') {
            $result = $mobileMoney->requestAirtelPayment($phone, $amount, $transactionId);
        } else {
            $result = $mobileMoney->requestMtnPayment($phone, $amount, $transactionId);
        }

        if ($result['success']) {
            $_SESSION['pending_tx_id'] = $transactionId;
            $message = 'Payment prompt sent. Check your phone and enter your Mobile Money PIN to complete payment.';
            $this->redirectWithSuccess($message, BASE_URL . '/external/subscription?tx_id=' . urlencode($transactionId));
        } else {
            $this->redirectWithError($result['message'] ?? 'Unable to send push notification.', BASE_URL . '/external/subscription');
        }
    }
    
    public function upgradeConfirmation() {
        $hideFooter = true;
        
        $fromPlan = $_GET['from'] ?? '';
        $toPlan = $_GET['to'] ?? '';
        
        if (empty($fromPlan) || empty($toPlan)) {
            $this->redirectWithError('Invalid upgrade request', BASE_URL . '/external/subscription');
        }
        
        $currentSubscription = $this->subscriptionModel->getCurrentSubscription($this->userId);
        
        if (!$currentSubscription) {
            $this->redirectWithError('No active subscription found', BASE_URL . '/external/subscription');
        }
        
        $subscriptionSettings = $this->settingsModel->getSubscriptionSettings();
        
        $plans = [
            'monthly' => [
                'name' => 'Monthly',
                'price' => $subscriptionSettings['monthly_price'] ?? 15000,
                'features' => [
                    'Full access to all lessons',
                    'Practice quizzes',
                    'Progress tracking',
                    'Email support'
                ]
            ],
            'termly' => [
                'name' => 'Termly',
                'price' => $subscriptionSettings['termly_price'] ?? 40000,
                'features' => [
                    'Everything in Monthly',
                    'Priority support',
                    'Downloadable materials',
                    'Answers to Quizzes'
                ]
            ],
            'yearly' => [
                'name' => 'Yearly',
                'price' => $subscriptionSettings['yearly_price'] ?? 120000,
                'features' => [
                    'Everything in Termly',
                    '2 months free',
                    'Full access to all resources',
                    'AI Integration',
                    'Certificate of completion',
                    '1-on-1 tutoring sessions'
                ]
            ]
        ];
        
        $prices = [
            'monthly' => $subscriptionSettings['monthly_price'] ?? 15000,
            'termly' => $subscriptionSettings['termly_price'] ?? 40000,
            'yearly' => $subscriptionSettings['yearly_price'] ?? 120000
        ];
        
        $currentPrice = $prices[$fromPlan] ?? 0;
        $newPrice = $prices[$toPlan] ?? 0;
        
        $endDate = new DateTime($currentSubscription['end_date']);
        $now = new DateTime();
        $daysRemaining = $now->diff($endDate)->days;
        
        $totalDays = $fromPlan === 'monthly' ? 30 : ($fromPlan === 'termly' ? 90 : 365);
        $dailyRate = $currentPrice / $totalDays;
        $remainingValue = $dailyRate * $daysRemaining;
        $upgradePrice = max(0, $newPrice - $remainingValue);
        
        $priceCalculation = [
            'success' => true,
            'current_price' => $currentPrice,
            'new_price' => $newPrice,
            'days_remaining' => $daysRemaining,
            'remaining_value' => round($remainingValue),
            'upgrade_price' => round($upgradePrice)
        ];
        
        $fromPlanDetails = $plans[$fromPlan] ?? ['name' => ucfirst($fromPlan), 'price' => 0, 'features' => []];
        $toPlanDetails = $plans[$toPlan] ?? ['name' => ucfirst($toPlan), 'price' => 0, 'features' => []];
        
        require_once __DIR__ . '/../views/external/upgrade-confirmation.php';
    }
    
    public function processUpgrade() {
        if (!$this->isPostRequest()) {
            $this->redirect(BASE_URL . '/external/subscription');
        }
        
        $userId = $this->userId;
        $fromPlan = $_POST['from_plan'] ?? '';
        $toPlan = $_POST['to_plan'] ?? '';
        $amount = (float)($_POST['amount'] ?? 0);
        $provider = $_POST['provider'] ?? 'NA';
        $phone = $_POST['phone_number'] ?? '';
        
        if (empty($fromPlan) || empty($toPlan) || $amount <= 0) {
            $this->redirectWithError('Invalid upgrade request or amount.', BASE_URL . '/external/subscription');
        }

        if (empty($phone)) {
            $this->redirectWithError('Phone number is required for payment.', BASE_URL . '/external/subscription');
        }
        
        $user = $this->userModel->getById($userId);
        if (!$user) {
            $this->redirectWithError('User not found.', BASE_URL . '/external/subscription');
        }
        
        $paymentResult = $this->subscriptionModel->createPendingPayment(
            $userId, 
            $toPlan, 
            $amount, 
            $provider,
            $phone
        );
        
        if (!$paymentResult['success']) {
            $this->redirectWithError($paymentResult['error'], BASE_URL . '/external/subscription');
        }
        
        $transactionId = $paymentResult['transaction_id'];
        $mobileMoney = new MobileMoney();
        
        if ($provider === 'airtel') {
            $result = $mobileMoney->requestAirtelPayment($phone, $amount, $transactionId);
        } else {
            $result = $mobileMoney->requestMtnPayment($phone, $amount, $transactionId);
        }
        
        if ($result['success']) {
            $_SESSION['pending_tx_id'] = $transactionId;
            $_SESSION['pending_upgrade'] = [
                'transaction_id' => $transactionId,
                'from_plan' => $fromPlan,
                'to_plan' => $toPlan,
                'amount' => $amount,
                'payment_id' => $paymentResult['payment_id'] ?? null
            ];

            $message = 'Upgrade payment prompt sent. Check your phone and enter your PIN to complete the upgrade.';
            $this->redirectWithSuccess($message, BASE_URL . '/external/subscription?tx_id=' . urlencode($transactionId));
        } else {
            $this->redirectWithError($result['message'] ?? 'Payment processing failed. Please try again.', BASE_URL . '/external/subscription');
        }
    }
    
    public function upgradeSuccess() {
        $hideFooter = true;
        
        $subscriptionId = $_GET['subscription_id'] ?? 0;
        
        $upgradeDetails = $this->subscriptionModel->getUpgradeDetails($subscriptionId);
        
        $toPlan = $upgradeDetails['plan_type'] ?? '';
        $upgradePrice = $upgradeDetails['amount'] ?? 0;
        $newEndDate = $upgradeDetails['end_date'] ?? date('Y-m-d H:i:s');
        
        require_once __DIR__ . '/../views/external/upgrade-success.php';
    }
    
    public function mobileMoneyCallback() {
        $this->logMobileMoneyRequest('CALLBACK', $_GET, $_POST);
        
        $orderTrackingId = $_GET['OrderTrackingId'] ?? $_GET['order_tracking_id'] ?? null;
        $orderMerchantReference = $_GET['OrderMerchantReference'] ?? $_GET['merchant_reference'] ?? null;
        
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!$orderTrackingId || !$orderMerchantReference) {
            $_SESSION['error'] = 'Invalid payment callback received.';
            $this->redirect(BASE_URL . '/external/subscription');
        }
        
        try {
            $mobileMoney = new MobileMoney();
            $status = null;
            $paymentStatus = '';
            
            for ($i = 0; $i < 5; $i++) {
                $status = $mobileMoney->queryPaymentStatus($orderTrackingId);
                
                if (!empty($status['status'])) {
                    $paymentStatus = strtoupper(trim($status['status']));
                    if (in_array($paymentStatus, ['COMPLETED', 'PAID'])) {
                        break;
                    }
                }
                sleep(3);
            }
            
            if (in_array($paymentStatus, ['COMPLETED', 'PAID'])) {
                $payment = $this->subscriptionModel->getPaymentByTransactionId($orderMerchantReference);
                
                if (!$payment) {
                    $_SESSION['error'] = 'Payment completed but payment record was not found.';
                    $this->redirect(BASE_URL . '/external/subscription');
                }
                
                if ($payment['status'] !== 'completed') {
                    $this->subscriptionModel->updatePaymentStatus(
                        $orderMerchantReference,
                        'completed',
                        json_encode($status)
                    );
                    
                    $this->sendPaymentConfirmationEmail(
                        $payment['user_id'],
                        $payment['plan_type'] ?? 'monthly',
                        $status['amount'] ?? $payment['amount']
                    );
                }
                
                $_SESSION['success'] = 'Payment successful! Your subscription is now active! Enjoy.';
            } elseif (in_array($paymentStatus, ['PENDING', 'PROCESSING'])) {
                $_SESSION['info'] = 'Your payment is still being processed. Please refresh after a few moments.';
            } else {
                $_SESSION['error'] = 'Payment was not completed. Status: ' . $paymentStatus;
            }
        } catch (Exception $e) {
            $_SESSION['error'] = 'An error occurred while verifying your payment.';
        }
        
        $this->redirect(BASE_URL . '/external/subscription');
    }
    
    public function mobileMoneyIpn() {
        $rawInput = file_get_contents('php://input');
        $data = json_decode($rawInput, true);

        if (empty($data)) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Invalid payload']);
            return;
        }

        $transactionId = $data['transaction_id'] ?? $data['tx_ref'] ?? $data['OrderMerchantReference'] ?? null;
        $status        = strtolower($data['status'] ?? 'completed');

        if (!$transactionId) {
            http_response_code(422);
            echo json_encode(['status' => 'error', 'message' => 'Missing transaction reference']);
            return;
        }

        $result = $this->subscriptionModel->processCompletedPayment($transactionId, $status, $data);

        if ($result['success']) {
            http_response_code(200);
            echo json_encode(['status' => 'success', 'message' => 'Payment processed successfully']);
        } else {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => $result['error'] ?? 'Processing failed']);
        }
    }
    
    public function mobileMoneyTest() {
        $this->logMobileMoneyRequest('TEST', $_GET, $_POST);
        
        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'ok',
            'message' => 'Mobile Money endpoint is reachable',
            'timestamp' => date('Y-m-d H:i:s'),
            'callback_url' => MOBILE_MONEY_CALLBACK_URL,
            'ipn_url' => MOBILE_MONEY_IPN_URL,
            'php_version' => PHP_VERSION,
            'server' => $_SERVER['SERVER_SOFTWARE'] ?? 'unknown'
        ]);
        exit;
    }

    public function checkPaymentStatus() {
        header('Content-Type: application/json');
        
        $transactionId = $_GET['transaction_id'] ?? $_SESSION['pending_tx_id'] ?? null;

        if (!$transactionId) {
            echo json_encode(['status' => 'error', 'message' => 'Missing transaction ID']);
            return;
        }

        $payment = $this->subscriptionModel->getPaymentByTransactionId($transactionId);

        if (!$payment) {
            echo json_encode(['status' => 'not_found']);
            return;
        }
        
        if ($payment['status'] === 'pending') {
            $mobileMoney = new MobileMoney();
            $provider = strtolower($payment['payment_method'] ?? 'mtn');

            $apiCheck = ($provider === 'airtel') 
                ? $mobileMoney->checkAirtelStatus($transactionId)
                : $mobileMoney->checkMtnStatus($transactionId);

            if (!empty($apiCheck['status'])) {
                $statusStr = strtoupper($apiCheck['status']);
                if (in_array($statusStr, ['SUCCESSFUL', 'SUCCESS', 'COMPLETED'])) {
                    $this->subscriptionModel->processCompletedPayment($transactionId, 'completed', $apiCheck['raw'] ?? []);
                    $payment['status'] = 'completed';
                } elseif (in_array($statusStr, ['FAILED', 'REJECTED', 'EXPIRED'])) {
                    $this->subscriptionModel->processCompletedPayment($transactionId, 'failed', $apiCheck['raw'] ?? []);
                    $payment['status'] = 'failed';
                }
            }
        }

        echo json_encode([
            'status' => $payment['status'],
            'redirect_url' => BASE_URL . '/external/subscription?status=' . $payment['status']
        ]);
    }
}
?>