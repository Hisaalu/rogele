<?php
// File: /controllers/ExternalController.php
require_once __DIR__ . '/../models/Subscription.php';
require_once __DIR__ . '/../models/Lesson.php';
require_once __DIR__ . '/../models/Quiz.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Classes.php';
require_once __DIR__ . '/../models/Settings.php';
require_once __DIR__ . '/../models/Subject.php';
require_once __DIR__ . '/../helpers/MailHelper.php';
require_once __DIR__ . '/../config/pesapal.php';
require_once __DIR__ . '/../lib/Pesapal.php';

class ExternalController {
    private $subscriptionModel;
    private $lessonModel;
    private $quizModel;
    private $userModel;
    private $settingsModel;
    private $subjectModel;
    private $classesModel; 
    
    public function __construct() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . BASE_URL . '/login');
            exit;
        }
        
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
        $this->classesModel = new Classes();
    }
    
    /**
     * Redirect to dashboard based on user role
     */
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
     * External User Dashboard
     */
    public function dashboard() {
        $hideFooter = true;

        $userId = $_SESSION['user_id'];
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

        require_once __DIR__ . '/../views/external/dashboard.php';
    }
    
    /**
     * Display learning materials for external users
     */
    public function materials() {
        $this->checkAccess();
        $hideFooter = true;
        
        if (!$this->userModel->hasAccess($_SESSION['user_id'])) {
            header('Location: ' . BASE_URL . '/external/subscription');
            exit;
        }
        
        $user = $this->userModel->getById($_SESSION['user_id']);
        $userClassId = $user['class_id'] ?? null;
        
        $search = isset($_GET['search']) ? trim($_GET['search']) : null;
        $subject = isset($_GET['subject']) ? (int)$_GET['subject'] : null;
        
        if ($search) {
            $lessons = $this->lessonModel->searchPublishedByClass($search, $userClassId, $subject);
        } else {
            $lessons = $this->lessonModel->getPublishedLessonsByClass($userClassId, $subject);
        }
        
        $subjects = $this->subjectModel->getByClassId($userClassId);
        
        usort($subjects, function($a, $b) {
            return strcmp($a['name'], $b['name']);
        });
        
        $selectedSubject = $subject;
        
        require_once __DIR__ . '/../views/external/materials.php';
    }
    
    /**
     * View single lesson
     */
    public function viewLesson($lessonId) {
        $this->checkAccess();
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
     * Display quizzes for external users based on their class
     */
    public function quizzes() {
        $this->checkAccess();
        $hideFooter = true;
        
        if (!$this->userModel->hasAccess($_SESSION['user_id'])) {
            header('Location: ' . BASE_URL . '/external/subscription');
            exit;
        }
        
        $user = $this->userModel->getById($_SESSION['user_id']);
        $userClassId = $user['class_id'] ?? null;
        
        if (!$userClassId) {
            $this->setFlashMessage('warning', 'Please select a class in your profile to access quizzes.');
            header('Location: ' . BASE_URL . '/profile');
            exit;
        }

        $quizzes = $this->quizModel->getQuizzesByClass($userClassId);
        
        $results = $this->quizModel->getUserQuizResults($_SESSION['user_id']);
        
        require_once __DIR__ . '/../views/external/quizzes.php';
    }
    
    /**
     * Take a quiz
     */
    public function takeQuiz($quizId) {
        $this->checkAccess();
        $hideFooter = true;
        
        if (!$this->userModel->hasAccess($_SESSION['user_id'])) {
            header('Location: ' . BASE_URL . '/external/subscription');
            exit;
        }
        
        $availability = $this->quizModel->getQuizAvailabilityStatus($quizId);
        
        if (!$availability['available']) {
            $_SESSION['error'] = $availability['message'];
            header('Location: ' . BASE_URL . '/external/quizzes');
            exit;
        }
        
        $remainingAttempts = $this->quizModel->getRemainingAttempts($_SESSION['user_id'], $quizId);
        
        if ($remainingAttempts <= 0) {
            $_SESSION['error'] = 'You have used all your attempts for this quiz. Maximum attempts reached.';
            header('Location: ' . BASE_URL . '/external/quizzes');
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
            
            // Verify this is the current attempt
            if (isset($_SESSION['current_quiz_attempt']) && $_SESSION['current_quiz_attempt'] != $attemptId) {
                $_SESSION['error'] = 'Invalid quiz submission';
                header('Location: ' . BASE_URL . '/external/quizzes');
                exit;
            }
            
            $result = $this->quizModel->submitAttempt($attemptId, $answers);
            
            if ($result['success']) {
                unset($_SESSION['current_quiz_attempt']);
                unset($_SESSION['current_quiz_id']);
                unset($_SESSION['quiz_start_time']);
                
                $_SESSION['quiz_result'] = $result;
                header("Location: " . BASE_URL . "/external/quiz-result/" . $attemptId);
                exit;
            } else {
                $_SESSION['error'] = $result['error'] ?? 'Failed to submit quiz';
                header("Location: " . BASE_URL . "/external/take-quiz/" . $quizId);
                exit;
            }
        } else {
            if ($this->quizModel->hasReachedMaxAttempts($_SESSION['user_id'], $quizId)) {
                $_SESSION['error'] = 'You have used all your attempts for this quiz. Maximum attempts reached.';
                header('Location: ' . BASE_URL . '/external/quizzes');
                exit;
            }
            
            $questionCount = $this->quizModel->getQuestionCount($quizId);
            if ($questionCount == 0) {
                $_SESSION['error'] = 'This quiz has no questions yet. Please contact the teacher.';
                header('Location: ' . BASE_URL . '/external/quizzes');
                exit;
            }
            
            $result = $this->quizModel->startAttempt($quizId, $_SESSION['user_id']);
            
            if ($result['success']) {
                $quiz = $this->quizModel->getById($quizId);
                $questions = $result['questions'];
                $attemptId = $result['attempt_id'];
                
                $_SESSION['current_quiz_attempt'] = $attemptId;
                $_SESSION['current_quiz_id'] = $quizId;
                $_SESSION['quiz_start_time'] = time();
                
                require_once __DIR__ . '/../views/external/take_quiz.php';
            } else {
                $_SESSION['error'] = $result['error'] ?? 'Failed to start quiz';
                header('Location: ' . BASE_URL . '/external/quizzes');
                exit;
            }
        }
    }

    /**
     * Get user's quiz results
     */
    public function getUserQuizResults($userId) {
        try {
            $sql = "SELECT a.*, q.title as quiz_title 
                    FROM quiz_attempts a
                    LEFT JOIN quizzes q ON a.quiz_id = q.id
                    WHERE a.user_id = :user_id AND a.status = 'completed'
                    ORDER BY a.completed_at DESC";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            return [];
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
    
    /**
     * Show subscription page
     */
    public function subscription() {
        $hideFooter = true;
        $currentSubscription = $this->subscriptionModel->getCurrentSubscription($_SESSION['user_id']);
        $subscriptionSettings = $this->settingsModel->getSubscriptionSettings();
        $paymentHistory = $this->subscriptionModel->getCombinedHistory($_SESSION['user_id']);
        $rawPaymentHistory = $this->subscriptionModel->getUserPaymentHistory($_SESSION['user_id']);
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
     * Send payment confirmation email
     */
    private function sendPaymentConfirmationEmail($userId, $planType, $amount) {
        $user = $this->userModel->getById($userId);
        
        if (!$user) {
            return;
        }
        
        $subject = "Payment Confirmation - " . ucfirst($planType) . " Subscription";
        
        $message = "
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: linear-gradient(135deg, #8B5CF6, #F97316); color: white; padding: 30px; text-align: center; }
                .content { padding: 30px; background: #f9f9f9; }
                .amount { font-size: 24px; font-weight: bold; color: #8B5CF6; }
                .button { background: #8B5CF6; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; display: inline-block; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h2>Payment Confirmation</h2>
                </div>
                <div class='content'>
                    <h3>Hello " . htmlspecialchars($user['first_name']) . "!</h3>
                    <p>Thank you for your subscription payment. Your account has been successfully activated.</p>
                    <p><strong>Plan:</strong> " . ucfirst($planType) . "</p>
                    <p><strong>Amount Paid:</strong> <span class='amount'>UGX " . number_format($amount) . "</span></p>
                    <p>You now have full access to all premium features!</p>
                    <p style='text-align: center; margin-top: 30px;'>
                        <a href='" . BASE_URL . "/external/dashboard' class='button'>Go to Dashboard</a>
                    </p>
                </div>
            </div>
        </body>
        </html>
        ";
        
        $headers = "MIME-Version: 1.0\r\n";
        $headers .= "Content-type: text/html; charset=utf-8\r\n";
        $headers .= "From: Rays of Grace <noreply@raysofgrace.com>\r\n";
        
        mail($user['email'], $subject, $message, $headers);
    }
    
    /**
     * Display profile page
     */
    public function profile() {
        $this->checkAccess();
        $hideFooter = true;
        
        $userId = $_SESSION['user_id'];
        $profile = $this->userModel->getProfile($userId);
        
        $classes = $this->classesModel->getAll();
        
        $userClassId = $profile['class_id'] ?? null;
        
        $trialDays = $this->settingsModel->get('trial_days', 60);
        $trialEndDate = $this->userModel->getTrialEndDate($_SESSION['user_id'], $trialDays);
        $remainingTrialDays = $this->userModel->getRemainingTrialDays($_SESSION['user_id'], $trialDays);
        
        if ($profile) {
            $profile['trial_end'] = $trialEndDate;
            $profile['trial_days_remaining'] = $remainingTrialDays;
            $profile['trial_active'] = $remainingTrialDays > 0;
        }
        
        require_once __DIR__ . '/../views/external/profile.php';
    }

    /**
     * Update profile with class selection
     */
    public function updateProfile() {
        $this->checkAccess();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/external/profile');
            exit;
        }
        
        $userId = $_SESSION['user_id'];
        
        $data = [
            'first_name' => trim($_POST['first_name'] ?? ''),
            'last_name' => trim($_POST['last_name'] ?? ''),
            'email' => trim($_POST['email'] ?? ''),
            'phone' => trim($_POST['phone'] ?? ''),
            'class_id' => !empty($_POST['class_id']) ? (int)$_POST['class_id'] : null
        ];
        
        $errors = [];
        if (empty($data['first_name'])) $errors[] = 'First name is required';
        if (empty($data['last_name'])) $errors[] = 'Last name is required';
        if (empty($data['email'])) $errors[] = 'Email is required';
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) $errors[] = 'Invalid email format';
        
        if (!empty($errors)) {
            $_SESSION['error'] = implode(', ', $errors);
            header('Location: ' . BASE_URL . '/external/profile');
            exit;
        }
        
        $result = $this->userModel->updateProfile($userId, $data);
        
        if ($result['success']) {
            $_SESSION['user_name'] = $data['first_name'] . ' ' . $data['last_name'];
            $_SESSION['user_email'] = $data['email'];
            
            $oldClassId = $this->userModel->getById($userId)['class_id'] ?? null;
            if ($oldClassId != $data['class_id']) {
                $_SESSION['success'] = 'Profile updated successfully! Your class has been updated.';
            } else {
                $_SESSION['success'] = 'Profile updated successfully!';
            }
        } else {
            $_SESSION['error'] = $result['error'] ?? 'Failed to update profile';
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
            $_SESSION['error'] = 'Please enter your password to confirm account deletion.';
            header('Location: ' . BASE_URL . '/external/settings?tab=delete');
            exit;
        }
        
        $user = $this->userModel->getById($_SESSION['user_id']);
        if (!$user) {
            $_SESSION['error'] = 'User not found.';
            header('Location: ' . BASE_URL . '/login');
            exit;
        }
        
        $result = $this->userModel->deleteAccount($_SESSION['user_id'], $password);
        
        if ($result['success']) {
            session_destroy();
            session_start();
            $_SESSION['success'] = 'Your account has been successfully deleted!';
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

    /**
     * Show upgrade confirmation page
     */
    public function upgradeConfirmation() {
        $hideFooter = true;
        
        $fromPlan = $_GET['from'] ?? '';
        $toPlan = $_GET['to'] ?? '';
        
        if (empty($fromPlan) || empty($toPlan)) {
            $_SESSION['error'] = 'Invalid upgrade request';
            header('Location: ' . BASE_URL . '/external/subscription');
            exit;
        }
        
        $currentSubscription = $this->subscriptionModel->getCurrentSubscription($_SESSION['user_id']);
        
        if (!$currentSubscription) {
            $_SESSION['error'] = 'No active subscription found';
            header('Location: ' . BASE_URL . '/external/subscription');
            exit;
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
                    'Downloadable materials'
                ]
            ],
            'yearly' => [
                'name' => 'Yearly',
                'price' => $subscriptionSettings['yearly_price'] ?? 120000,
                'features' => [
                    'Everything in Termly',
                    '2 months free',
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

    /**
     * Process upgrade via PesaPal
     */
    public function processUpgrade() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/external/subscription');
            exit;
        }

        $userId = $_SESSION['user_id'];
        $fromPlan = $_POST['from_plan'] ?? '';
        $toPlan = $_POST['to_plan'] ?? '';
        $amount = (float)($_POST['amount'] ?? 0);

        // 1. Basic Validation
        if (empty($fromPlan) || empty($toPlan) || $amount <= 0) {
            $_SESSION['error'] = 'Invalid upgrade request or amount.';
            header('Location: ' . BASE_URL . '/external/subscription');
            exit;
        }

        // 2. Prepare PesaPal Data
        $user = $this->userModel->getById($userId);
        
        if (!$user) {
            $_SESSION['error'] = 'User not found.';
            header('Location: ' . BASE_URL . '/external/subscription');
            exit;
        }
        
        $pesapal = new Pesapal();
        
        // Create a unique reference for this upgrade
        $reference = 'UPG_' . time() . '_' . $userId . '_' . rand(1000, 9999);
        
        // Store payment intent in database first
        $paymentResult = $this->subscriptionModel->createPendingPayment(
            $userId, 
            $toPlan, 
            $amount, 
            'pesapal',
            $user['phone'] ?? ''
        );
        
        if (!$paymentResult['success']) {
            $_SESSION['error'] = $paymentResult['error'];
            header('Location: ' . BASE_URL . '/external/subscription');
            exit;
        }
        
        $paymentData = [
            'amount' => $amount,
            'description' => "Upgrade from " . ucfirst($fromPlan) . " to " . ucfirst($toPlan),
            'reference' => $paymentResult['transaction_id'], // Use the stored transaction ID
            'first_name' => $user['first_name'],
            'last_name' => $user['last_name'],
            'email' => $user['email'],
            'phone' => $user['phone'] ?? ''
        ];

        // 3. Initiate PesaPal Order
        $result = $pesapal->submitPayment($paymentData);

        if ($result['success'] && isset($result['redirect_url'])) {
            // Store upgrade intent in session
            $_SESSION['pending_upgrade'] = [
                'transaction_id' => $paymentResult['transaction_id'],
                'from_plan' => $fromPlan,
                'to_plan' => $toPlan,
                'amount' => $amount,
                'payment_id' => $paymentResult['payment_id']
            ];
            
            // Redirect to PesaPal
            header('Location: ' . $result['redirect_url']);
            exit;
        } else {
            $_SESSION['error'] = $result['message'] ?? 'Payment processing failed. Please try again.';
            header('Location: ' . BASE_URL . '/external/subscription');
            exit;
        }
    }

    /**
     * Upgrade success page
     */
    public function upgradeSuccess() {
        $hideFooter = true;
        
        $subscriptionId = $_GET['subscription_id'] ?? 0;
        
        $upgradeDetails = $this->subscriptionModel->getUpgradeDetails($subscriptionId);
        
        $toPlan = $upgradeDetails['plan_type'] ?? '';
        $upgradePrice = $upgradeDetails['amount'] ?? 0;
        $newEndDate = $upgradeDetails['end_date'] ?? date('Y-m-d H:i:s');
        
        require_once __DIR__ . '/../views/external/upgrade-success.php';
    }

    /**
     * Helper method to get plan price
     */
    private function getPlanPrice($planType) {
        $prices = [
            'monthly' => 15000,
            'termly' => 40000,
            'yearly' => 120000
        ];
        
        return $prices[$planType] ?? 0;
    }

    /**
     * Send upgrade confirmation email
     */
    private function sendUpgradeConfirmationEmail($userId, $fromPlan, $toPlan, $amount, $newEndDate) {
        $user = $this->userModel->getById($userId);
        
        $to = $user['email'];
        $subject = "Your ROGELE Subscription Has Been Upgraded! 🎉";
        
        $message = "
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: linear-gradient(135deg, #667eea, #764ba2); color: white; padding: 30px; text-align: center; }
                .content { padding: 30px; background: #f9f9f9; }
                .button { background: linear-gradient(135deg, #667eea, #764ba2); color: white; padding: 12px 30px; text-decoration: none; border-radius: 50px; display: inline-block; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h2>Subscription Upgrade Confirmation</h2>
                </div>
                <div class='content'>
                    <h3>Congratulations, {$user['first_name']}! 🎊</h3>
                    <p>Your subscription has been successfully upgraded from <strong>" . ucfirst($fromPlan) . "</strong> to <strong>" . ucfirst($toPlan) . "</strong>!</p>
                    
                    <h4>Upgrade Summary:</h4>
                    <ul>
                        <li><strong>Previous Plan:</strong> " . ucfirst($fromPlan) . "</li>
                        <li><strong>New Plan:</strong> " . ucfirst($toPlan) . "</li>
                        <li><strong>Upgrade Amount Paid:</strong> UGX " . number_format($amount) . "</li>
                        <li><strong>New Expiry Date:</strong> " . date('F j, Y', strtotime($newEndDate)) . "</li>
                    </ul>
                    
                    <p>You now have access to all premium features of the " . ucfirst($toPlan) . " plan!</p>
                    
                    <p style='text-align: center; margin-top: 30px;'>
                        <a href='" . BASE_URL . "/external/dashboard' class='button'>Go to Dashboard</a>
                    </p>
                    
                    <p>Thank you for choosing Rays of Grace E-Learning!</p>
                </div>
            </div>
        </body>
        </html>
        ";
        
        $this->sendEmail($to, $subject, $message);
    }

    /**
     * Send email using PHP's mail function or your preferred mail library
     */
    private function sendEmail($to, $subject, $message, $headers = []) {
        try {
            $defaultHeaders = [
                'MIME-Version: 1.0',
                'Content-type: text/html; charset=utf-8',
                'From: ROGELE <noreply@raysofgrace.com>',
                'Reply-To: support@raysofgrace.com',
                'X-Mailer: PHP/' . phpversion()
            ];
            
            $allHeaders = array_merge($defaultHeaders, $headers);
            
            if (mail($to, $subject, $message, implode("\r\n", $allHeaders))) {
                return true;
            } else {
                return false;
            }
        } catch (Exception $e) {
            return false;
        }
    }


    /**
     * Process payment with Pesapal
     */
    public function processPesapalPayment() {
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/external/subscription');
            exit;
        }
        
        $planType = $_POST['plan'] ?? '';
        $paymentMethod = $_POST['payment_method'] ?? 'mobile_money';
        $phoneNumber = $_POST['phone_number'] ?? '';
        
        if (empty($planType)) {
            $_SESSION['error'] = 'Please select a subscription plan';
            header('Location: ' . BASE_URL . '/external/subscription');
            exit;
        }
        
        if ($paymentMethod == 'mobile_money' && empty($phoneNumber)) {
            $_SESSION['error'] = 'Phone number is required for mobile money payments';
            header('Location: ' . BASE_URL . '/external/subscription');
            exit;
        }
        
        $subscriptionSettings = $this->settingsModel->getSubscriptionSettings();
        $amounts = [
            'monthly' => $subscriptionSettings['monthly_price'] ?? 15000,
            'termly' => $subscriptionSettings['termly_price'] ?? 40000,
            'yearly' => $subscriptionSettings['yearly_price'] ?? 120000
        ];
        
        $amount = $amounts[$planType] ?? 0;
        
        $paymentResult = $this->subscriptionModel->createPendingPayment(
            $_SESSION['user_id'],
            $planType,
            $amount,
            $paymentMethod,
            $phoneNumber
        );
        
        if (!$paymentResult['success']) {
            $_SESSION['error'] = $paymentResult['error'];
            header('Location: ' . BASE_URL . '/external/subscription');
            exit;
        }
        
        $user = $this->userModel->getById($_SESSION['user_id']);
        $nameParts = explode(' ', $user['first_name'] . ' ' . $user['last_name']);
        $firstName = $nameParts[0] ?? $user['first_name'];
        $lastName = isset($nameParts[1]) ? implode(' ', array_slice($nameParts, 1)) : '';
        
        require_once __DIR__ . '/../lib/Pesapal.php';
        $pesapal = new Pesapal();
        
        $paymentData = [
            'amount' => $amount,
            'phone' => $phoneNumber,
            'email' => $user['email'],
            'first_name' => $firstName,
            'last_name' => $lastName,
            'reference' => $paymentResult['transaction_id'],
            'description' => ucfirst($planType) . ' Subscription - ROGELE'
        ];
        
        $response = $pesapal->submitPayment($paymentData);
        
        if (isset($response['error']) && $response['error']) {
            $_SESSION['error'] = $response['message'] ?? 'Payment submission failed. Please try again.';
            header('Location: ' . BASE_URL . '/external/subscription');
            exit;
        }
        
        $_SESSION['pending_payment_id'] = $paymentResult['payment_id'];
        $_SESSION['pending_transaction_id'] = $paymentResult['transaction_id'];
        $_SESSION['pending_plan'] = $planType;
        $_SESSION['pending_amount'] = $amount;
        $_SESSION['pesapal_tracking_id'] = $response['tracking_id'] ?? '';
        
        if (isset($response['redirect_url'])) {
            header('Location: ' . $response['redirect_url']);
            exit;
        } else {
            $_SESSION['error'] = 'No redirect URL from Pesapal. Response: ' . json_encode($response);
            header('Location: ' . BASE_URL . '/external/subscription');
            exit;
        }
    }

    /**
     * Handle PesaPal callback (user returns from PesaPal)
     */
    public function pesapalCallback() {
        error_log("[PesaPal Callback] Received callback");
        error_log("[PesaPal Callback] GET params: " . print_r($_GET, true));
        
        // Get parameters from callback
        $orderTrackingId = $_GET['OrderTrackingId'] ?? $_GET['order_tracking_id'] ?? null;
        $orderMerchantReference = $_GET['OrderMerchantReference'] ?? $_GET['merchant_reference'] ?? null;
        
        if (!$orderTrackingId || !$orderMerchantReference) {
            error_log("[PesaPal Callback] Missing required parameters");
            $_SESSION['error'] = 'Invalid payment callback received.';
            header('Location: ' . BASE_URL . '/external/subscription');
            exit;
        }
        
        // Query payment status
        $pesapal = new Pesapal();
        $status = $pesapal->queryPaymentStatus($orderTrackingId);
        
        error_log("[PesaPal Callback] Payment status: " . print_r($status, true));
        
        if ($status['success'] && $status['status'] === 'COMPLETED') {
            // Update payment and subscription
            $result = $this->subscriptionModel->updatePaymentStatus(
                $orderMerchantReference,
                'completed',
                $status
            );
            
            if ($result['success']) {
                // Check if this was an upgrade
                if (isset($_SESSION['pending_upgrade'])) {
                    $upgrade = $_SESSION['pending_upgrade'];
                    
                    // Process the upgrade
                    $upgradeResult = $this->subscriptionModel->upgradeSubscription(
                        $_SESSION['user_id'],
                        $upgrade['from_plan'],
                        $upgrade['to_plan'],
                        [
                            'amount' => $upgrade['amount'],
                            'method' => 'pesapal',
                            'transaction_id' => $orderMerchantReference
                        ]
                    );
                    
                    if ($upgradeResult['success']) {
                        // Send confirmation email
                        $this->sendPaymentConfirmationEmail(
                            $_SESSION['user_id'],
                            $upgrade['to_plan'],
                            $upgrade['amount']
                        );
                        
                        $_SESSION['success'] = $upgradeResult['message'];
                    } else {
                        $_SESSION['warning'] = 'Payment confirmed but upgrade processing had issues. Please contact support.';
                    }
                    
                    unset($_SESSION['pending_upgrade']);
                } else {
                    // Regular subscription
                    $this->subscriptionModel->createOrUpdateSubscription(
                        $_SESSION['user_id'],
                        'monthly', // or get from session
                        $status['amount'] ?? 0,
                        $orderMerchantReference
                    );
                    
                    $_SESSION['success'] = 'Payment completed successfully! Your subscription is now active.';
                }
            } else {
                $_SESSION['error'] = 'Payment completed but could not update your subscription. Please contact support.';
            }
        } else {
            $_SESSION['error'] = 'Payment was not completed. Status: ' . ($status['status'] ?? 'Unknown');
        }
        
        header('Location: ' . BASE_URL . '/external/subscription');
        exit;
    }

    /**
     * Handle PesaPal IPN (Instant Payment Notification from PesaPal)
     */
    public function pesapalIpn() {
        error_log("[PesaPal IPN] Received IPN");
        error_log("[PesaPal IPN] GET params: " . print_r($_GET, true));
        
        // Get parameters from IPN
        $orderTrackingId = $_GET['OrderTrackingId'] ?? $_GET['order_tracking_id'] ?? null;
        $orderMerchantReference = $_GET['OrderMerchantReference'] ?? $_GET['merchant_reference'] ?? null;
        
        if (!$orderTrackingId || !$orderMerchantReference) {
            error_log("[PesaPal IPN] Missing required parameters");
            http_response_code(400);
            echo "Missing parameters";
            exit;
        }
        
        // Query payment status
        $pesapal = new Pesapal();
        $status = $pesapal->queryPaymentStatus($orderTrackingId);
        
        error_log("[PesaPal IPN] Payment status: " . print_r($status, true));
        
        if ($status['success'] && $status['status'] === 'COMPLETED') {
            // Get payment record
            $payment = $this->subscriptionModel->getPaymentByTransactionId($orderMerchantReference);
            
            if ($payment && $payment['status'] !== 'completed') {
                // Update payment status
                $result = $this->subscriptionModel->updatePaymentStatus(
                    $orderMerchantReference,
                    'completed',
                    $status
                );
                
                if ($result['success']) {
                    // Activate subscription
                    $subscriptionResult = $this->subscriptionModel->createOrUpdateSubscription(
                        $payment['user_id'],
                        $payment['plan_type'] ?? 'monthly',
                        $status['amount'] ?? $payment['amount'],
                        $orderMerchantReference
                    );
                    
                    error_log("[PesaPal IPN] Subscription activated: " . print_r($subscriptionResult, true));
                    
                    // Send confirmation email
                    $this->sendPaymentConfirmationEmail(
                        $payment['user_id'],
                        $payment['plan_type'] ?? 'monthly',
                        $status['amount'] ?? $payment['amount']
                    );
                    
                    echo "IPN processed successfully";
                } else {
                    error_log("[PesaPal IPN] Failed to update payment status");
                    http_response_code(500);
                    echo "Failed to update payment";
                }
            } else {
                error_log("[PesaPal IPN] Payment already processed or not found");
                echo "Payment already processed";
            }
        } else {
            error_log("[PesaPal IPN] Payment not completed. Status: " . ($status['status'] ?? 'Unknown'));
            http_response_code(400);
            echo "Payment not completed";
        }
        exit;
    }

    /**
     * Activate subscription after successful payment
     */
    private function activatePesapalSubscription($reference) {
        // Get payment by transaction ID (merchant reference)
        $payment = $this->subscriptionModel->getPaymentByTransactionId($reference);
        
        if (!$payment) {
            error_log("Payment not found for reference: " . $reference);
            return false;
        }
        
        // Check if subscription already exists and is active
        $existingSubscription = $this->subscriptionModel->getCurrentSubscription($payment['user_id']);
        if ($existingSubscription && $existingSubscription['status'] === 'active') {
            error_log("User already has active subscription: " . $payment['user_id']);
            return true;
        }
        
        // Create or update subscription
        $subscriptionResult = $this->subscriptionModel->createOrUpdateSubscription(
            $payment['user_id'],
            $payment['plan_type'],
            $payment['amount'],
            $reference
        );
        
        if ($subscriptionResult['success']) {
            // Send confirmation email
            $this->sendPaymentConfirmationEmail(
                $payment['user_id'],
                $payment['plan_type'],
                $payment['amount']
            );
            
            // Log the successful activation
            error_log("Subscription activated for user: " . $payment['user_id'] . 
                    ", plan: " . $payment['plan_type'] . 
                    ", reference: " . $reference);
            
            return true;
        }
        
        return false;
    }

    /**
     * Check if user has access to content (trial or subscription)
     * Redirects to subscription page if no access
     */
    private function checkAccess() {
        $userId = $_SESSION['user_id'];
        $trialDays = $this->settingsModel->get('trial_days', 60);
        
        $currentSubscription = $this->subscriptionModel->getCurrentSubscription($userId);
        
        if ($currentSubscription) {
            return true; 
        }
        
        $trialStatus = $this->userModel->getTrialStatus($userId, $trialDays);
        
        if ($trialStatus['is_trial']) {
            return true;
        }
        
        $_SESSION['error'] = 'Your free trial has ended. Please subscribe to continue accessing lessons and quizzes.';
        header('Location: ' . BASE_URL . '/external/subscription');
        exit;
    }

    /**
     * Check if user has access (returns boolean, no redirect)
     */
    private function hasAccess() {
        $userId = $_SESSION['user_id'];
        $trialDays = $this->settingsModel->get('trial_days', 60);
        
        $currentSubscription = $this->subscriptionModel->getCurrentSubscription($userId);
        if ($currentSubscription) {
            return true;
        }
        
        $trialStatus = $this->userModel->getTrialStatus($userId, $trialDays);
        return $trialStatus['is_trial'];
    }

    /**
     * Get user's answers for a specific attempt
     * 
     * @param int $attemptId The attempt ID
     * @return array Array of answers with question_id as key
     */
    public function getUserAnswers($attemptId) {
        try {
            $sql = "SELECT question_id, selected_answer, is_correct 
                    FROM quiz_attempt_answers 
                    WHERE attempt_id = :attempt_id";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':attempt_id', $attemptId, PDO::PARAM_INT);
            $stmt->execute();
            
            $answers = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $answer = $row['selected_answer'];
                if (is_numeric($answer)) {
                    $answer = (int)$answer;
                }
                $answers[$row['question_id']] = $answer;
            }
            
            return $answers;
            
        } catch (PDOException $e) {
            return [];
        }
    }

    /**
     * Submit an attempt
     */
    public function submitAttempt($attemptId, $answers) {
        try {
            $this->conn->beginTransaction();
            
            $sql = "SELECT * FROM quiz_attempts WHERE id = :attempt_id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':attempt_id', $attemptId, PDO::PARAM_INT);
            $stmt->execute();
            $attempt = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$attempt) {
                return ['success' => false, 'error' => 'Attempt not found'];
            }
            
            if ($attempt['status'] == 'completed') {
                return ['success' => false, 'error' => 'Quiz already submitted'];
            }
            
            $quizId = $attempt['quiz_id'];
            $questions = $this->getQuestions($quizId);
            
            $correctAnswers = 0;
            $totalQuestions = count($questions);
            
            $deleteSql = "DELETE FROM quiz_attempt_answers WHERE attempt_id = :attempt_id";
            $deleteStmt = $this->conn->prepare($deleteSql);
            $deleteStmt->bindValue(':attempt_id', $attemptId, PDO::PARAM_INT);
            $deleteStmt->execute();
            
            $answerSql = "INSERT INTO quiz_attempt_answers (attempt_id, question_id, selected_answer, is_correct) 
                        VALUES (:attempt_id, :question_id, :selected_answer, :is_correct)";
            
            $answerStmt = $this->conn->prepare($answerSql);
            
            foreach ($questions as $question) {
                $correctOption = $question['correct_option'];
                $userAnswer = isset($answers[$question['id']]) ? $answers[$question['id']] : null;
                
                if (is_numeric($userAnswer)) {
                    $userAnswer = (int)$userAnswer;
                }
                
                if (is_string($userAnswer) && in_array(strtoupper($userAnswer), ['A', 'B', 'C', 'D'])) {
                    $letterToIndex = ['A' => 0, 'B' => 1, 'C' => 2, 'D' => 3];
                    $userAnswer = $letterToIndex[strtoupper($userAnswer)];
                }
                
                $isCorrect = ($userAnswer !== null && $userAnswer == $correctOption) ? 1 : 0;
                
                if ($isCorrect) {
                    $correctAnswers++;
                }
                
                
                $answerStmt->bindValue(':attempt_id', $attemptId, PDO::PARAM_INT);
                $answerStmt->bindValue(':question_id', $question['id'], PDO::PARAM_INT);
                $answerStmt->bindValue(':selected_answer', $userAnswer);
                $answerStmt->bindValue(':is_correct', $isCorrect, PDO::PARAM_INT);
                $answerStmt->execute();
            }
            
            $score = ($totalQuestions > 0) ? round(($correctAnswers / $totalQuestions) * 100) : 0;
            
            $sql = "UPDATE quiz_attempts 
                    SET score = :score, 
                        correct_answers = :correct_answers,
                        total_questions = :total_questions,
                        status = 'completed',
                        completed_at = NOW()
                    WHERE id = :attempt_id";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':score', $score);
            $stmt->bindValue(':correct_answers', $correctAnswers);
            $stmt->bindValue(':total_questions', $totalQuestions);
            $stmt->bindValue(':attempt_id', $attemptId, PDO::PARAM_INT);
            $stmt->execute();
            
            $this->conn->commit();
            
            return [
                'success' => true,
                'score' => $score,
                'correct' => $correctAnswers,
                'total' => $totalQuestions,
                'attempt_id' => $attemptId
            ];
            
        } catch (PDOException $e) {
            $this->conn->rollBack();
            return ['success' => false, 'error' => 'Failed to submit quiz: ' . $e->getMessage()];
        }
    }

    /**
     * Get quiz by ID (with status check for external users)
     */
    public function getById($quizId, $checkPublished = true) {
        try {
            $sql = "SELECT q.*, 
                        (SELECT COUNT(*) FROM quiz_questions WHERE quiz_id = q.id) as question_count
                    FROM quizzes q
                    WHERE q.id = :id";
            
            if ($checkPublished) {
                $sql .= " AND q.status = 'published'";
            }
            
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':id', $quizId, PDO::PARAM_INT);
            $stmt->execute();
            
            $quiz = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($quiz) {
                $quiz['time_limit'] = $quiz['time_limit'] ?? 15;
                $quiz['passing_score'] = $quiz['passing_score'] ?? 70;
                $quiz['max_attempts'] = $quiz['max_attempts'] ?? 3;
            }
            
            return $quiz;
            
        } catch (PDOException $e) {
            return null;
        }
    }

    /**
     * Get all questions for a quiz
     */
    public function getQuestions($quizId) {
        try {
            $sql = "SELECT * FROM quiz_questions WHERE quiz_id = :quiz_id ORDER BY id ASC";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':quiz_id', $quizId, PDO::PARAM_INT);
            $stmt->execute();
            
            $questions = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            foreach ($questions as &$question) {
                $options = [];
                if (!empty($question['option_a'])) $options[] = $question['option_a'];
                if (!empty($question['option_b'])) $options[] = $question['option_b'];
                if (!empty($question['option_c'])) $options[] = $question['option_c'];
                if (!empty($question['option_d'])) $options[] = $question['option_d'];
                
                $question['options'] = $options;
                
                $correctMap = [
                    'A' => 0,
                    'B' => 1,
                    'C' => 2,
                    'D' => 3
                ];
                $correctAnswer = strtoupper(trim($question['correct_answer']));
                $question['correct_option'] = $correctMap[$correctAnswer] ?? 0;
                
                $question['question_text'] = $question['question'];
            }
            
            return $questions;
            
        } catch (PDOException $e) {
            return [];
        }
    }
}
?>