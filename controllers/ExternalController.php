<?php
// File: /controllers/ExternalController.php
require_once __DIR__ . '/../models/Subscription.php';
require_once __DIR__ . '/../models/Lesson.php';
require_once __DIR__ . '/../models/Quiz.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Settings.php';
require_once __DIR__ . '/../models/Subject.php';
require_once __DIR__ . '/../helpers/MailHelper.php';

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
     * External User Dashboard
     */
    public function dashboard() {
        $hideFooter = true;
        
        // Check if user has active subscription
        $currentSubscription = $this->subscriptionModel->getCurrentSubscription($_SESSION['user_id']);
        $hasActiveSubscription = !empty($currentSubscription);
        
        // Get trial days from settings
        $trialDays = $this->settingsModel->get('trial_days', 60);
        
        // Get current plan if subscribed
        $currentPlan = $currentSubscription['plan_type'] ?? null;
        
        // Pass variables to view
        require_once __DIR__ . '/../views/external/dashboard.php';
    }
    
    /**
     * Display learning materials for external users
     */
    public function materials() {
        $hideFooter = true;
        
        // Check if user has access
        if (!$this->userModel->hasAccess($_SESSION['user_id'])) {
            header('Location: ' . BASE_URL . '/external/subscription');
            exit;
        }
        
        // Get filter parameters
        $subject = $_GET['subject'] ?? null;
        $search = $_GET['search'] ?? null;
        
        // Get all published lessons
        if ($search) {
            $lessons = $this->lessonModel->searchPublished($search, $subject);
        } else {
            $lessons = $this->lessonModel->getPublishedLessons($subject);
        }
        
        // Get unique subjects for filter dropdown
        $allSubjects = $this->subjectModel->getAll();
        
        // Filter to get unique subject names (remove duplicates)
        $uniqueSubjects = [];
        $seen = [];
        foreach ($allSubjects as $subject) {
            if (!in_array($subject['name'], $seen)) {
                $uniqueSubjects[] = $subject;
                $seen[] = $subject['name'];
            }
        }
        
        // Sort subjects alphabetically
        usort($uniqueSubjects, function($a, $b) {
            return strcmp($a['name'], $b['name']);
        });
        
        $subjects = $uniqueSubjects;
        
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
     * Show subscription page
     */
    public function subscription() {
        $hideFooter = true;
        
        // Get current subscription
        $currentSubscription = $this->subscriptionModel->getCurrentSubscription($_SESSION['user_id']);
        
        // Get subscription settings
        $subscriptionSettings = $this->settingsModel->getSubscriptionSettings();
        
        // Get payment history - use the combined history for better display
        $paymentHistory = $this->subscriptionModel->getCombinedHistory($_SESSION['user_id']);
        
        // Also get raw payment history if you want separate tables
        $rawPaymentHistory = $this->subscriptionModel->getUserPaymentHistory($_SESSION['user_id']);
        
        // Pass to view
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

    /**
     * Show upgrade confirmation page
     */
    public function upgradeConfirmation() {
        $hideFooter = true;
        
        $fromPlan = $_GET['from'] ?? '';
        $toPlan = $_GET['to'] ?? '';
        
        if (empty($fromPlan) || empty($toPlan)) {
            $_SESSION['error'] = 'Invalid upgrade request';
            header('Location: /rays-of-grace/external/subscription');
            exit;
        }
        
        // Get current subscription
        $currentSubscription = $this->subscriptionModel->getCurrentSubscription($_SESSION['user_id']);
        
        if (!$currentSubscription) {
            $_SESSION['error'] = 'No active subscription found';
            header('Location: /rays-of-grace/external/subscription');
            exit;
        }
        
        // Get subscription settings
        $subscriptionSettings = $this->settingsModel->getSubscriptionSettings();
        
        // Calculate upgrade price
        $priceCalculation = $this->subscriptionModel->calculateUpgradePrice(
            $fromPlan, 
            $toPlan, 
            $currentSubscription
        );
        
        if (!$priceCalculation['success']) {
            $_SESSION['error'] = $priceCalculation['error'];
            header('Location: /rays-of-grace/external/subscription');
            exit;
        }
        
        // Plan details with features
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
                    'Save ' . number_format((($subscriptionSettings['monthly_price'] ?? 15000) * 3) - ($subscriptionSettings['termly_price'] ?? 40000)) . ' UGX'
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
        
        $fromPlanDetails = $plans[$fromPlan] ?? ['name' => ucfirst($fromPlan), 'price' => 0, 'features' => []];
        $toPlanDetails = $plans[$toPlan] ?? ['name' => ucfirst($toPlan), 'price' => 0, 'features' => []];
        
        // Load the view
        require_once __DIR__ . '/../views/external/upgrade-confirmation.php';
    }

    /**
     * Process the upgrade
     */
    public function processUpgrade() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /rays-of-grace/external/subscription');
            exit;
        }
        
        $fromPlan = $_POST['from_plan'] ?? '';
        $toPlan = $_POST['to_plan'] ?? '';
        $amount = $_POST['amount'] ?? 0;
        $paymentMethod = $_POST['payment_method'] ?? 'mobile_money';
        $phoneNumber = $_POST['phone_number'] ?? '';
        $provider = $_POST['provider'] ?? '';
        
        // Validate
        if (empty($fromPlan) || empty($toPlan) || $amount <= 0) {
            $_SESSION['error'] = 'Invalid upgrade request';
            header('Location: /rays-of-grace/external/subscription');
            exit;
        }
        
        // Get current subscription
        $currentSubscription = $this->subscriptionModel->getCurrentSubscription($_SESSION['user_id']);
        
        if (!$currentSubscription) {
            $_SESSION['error'] = 'No active subscription found';
            header('Location: /rays-of-grace/external/subscription');
            exit;
        }
        
        // Here you would integrate with your payment gateway
        // For demo, we'll simulate a successful payment
        
        $paymentDetails = [
            'method' => $paymentMethod,
            'phone' => $phoneNumber,
            'provider' => $provider,
            'reference' => 'UPG_' . time() . '_' . $_SESSION['user_id'],
            'transaction_id' => 'TXN_' . uniqid(),
            'timestamp' => date('Y-m-d H:i:s'),
            'amount' => $amount
        ];
        
        // Process the upgrade
        $result = $this->subscriptionModel->upgradeSubscription(
            $_SESSION['user_id'],
            $fromPlan,
            $toPlan,
            $paymentDetails
        );
        
        if ($result['success']) {
            $_SESSION['success'] = $result['message'];
            
            // Send confirmation email
            $this->sendUpgradeConfirmationEmail($_SESSION['user_id'], $fromPlan, $toPlan, $amount, $result['new_end_date']);
            
            header('Location: /rays-of-grace/external/upgrade-success?subscription_id=' . $result['new_subscription_id']);
        } else {
            $_SESSION['error'] = $result['error'];
            header('Location: /rays-of-grace/external/subscription');
        }
        exit;
    }

    /**
     * Show upgrade success page
     */
    public function upgradeSuccess() {
        $hideFooter = true;
        
        $subscriptionId = $_GET['subscription_id'] ?? 0;
        
        if (!$subscriptionId) {
            header('Location: /rays-of-grace/external/dashboard');
            exit;
        }
        
        // Get upgrade details from the subscription
        $upgradeDetails = $this->subscriptionModel->getUpgradeDetails($subscriptionId);
        
        if (!$upgradeDetails) {
            $_SESSION['error'] = 'Upgrade details not found';
            header('Location: /rays-of-grace/external/subscription');
            exit;
        }
        
        // Get the new plan type
        $toPlan = $upgradeDetails['plan_type'] ?? '';
        
        // Get the original subscription to find the from plan
        $originalSubscriptionId = $upgradeDetails['original_subscription_id'] ?? null;
        $fromPlan = '';
        
        if ($originalSubscriptionId) {
            $originalSubscription = $this->subscriptionModel->getUpgradeDetails($originalSubscriptionId);
            $fromPlan = $originalSubscription['plan_type'] ?? '';
        }
        
        // Get payment details for this subscription
        $paymentDetails = $this->subscriptionModel->getPaymentForSubscription($subscriptionId);
        
        // Calculate price breakdown (or get from payment)
        $priceCalculation = [
            'upgrade_price' => $upgradeDetails['amount'] ?? 0,
            'new_price' => $this->getPlanPrice($toPlan),
            'remaining_value' => 0 // You might want to calculate this properly
        ];
        
        // New end date
        $newEndDate = $upgradeDetails['end_date'] ?? date('Y-m-d H:i:s', strtotime('+1 year'));
        
        // Pass all variables to the view
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
        // Get user details
        $user = $this->userModel->getById($userId);
        
        $to = $user['email'];
        $subject = "Your Rays of Grace Subscription Has Been Upgraded! 🎉";
        
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
        
        // Send email using your mail function
        $this->sendEmail($to, $subject, $message);
    }

    /**
     * Send email using PHP's mail function or your preferred mail library
     */
    private function sendEmail($to, $subject, $message, $headers = []) {
        try {
            // Set content-type header for HTML emails
            $defaultHeaders = [
                'MIME-Version: 1.0',
                'Content-type: text/html; charset=utf-8',
                'From: Rays of Grace E-Learning <noreply@raysofgrace.com>',
                'Reply-To: support@raysofgrace.com',
                'X-Mailer: PHP/' . phpversion()
            ];
            
            $allHeaders = array_merge($defaultHeaders, $headers);
            
            // Use PHP's mail function
            if (mail($to, $subject, $message, implode("\r\n", $allHeaders))) {
                error_log("Email sent successfully to: " . $to);
                return true;
            } else {
                error_log("Failed to send email to: " . $to);
                return false;
            }
        } catch (Exception $e) {
            error_log("Error sending email: " . $e->getMessage());
            return false;
        }
    }
}
?>