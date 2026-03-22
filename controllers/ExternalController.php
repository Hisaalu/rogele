<?php
// File: /controllers/ExternalController.php
require_once __DIR__ . '/../models/Subscription.php';
require_once __DIR__ . '/../models/Lesson.php';
require_once __DIR__ . '/../models/Quiz.php';
require_once __DIR__ . '/../models/User.php';
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
        
        // Calculate remaining trial days dynamically
        $remainingTrialDays = $this->userModel->getRemainingTrialDays($_SESSION['user_id'], $trialDays);
        
        // Get trial end date
        $trialEndDate = $this->userModel->getTrialEndDate($_SESSION['user_id'], $trialDays);
        
        // Calculate percentage of trial used (for progress bar)
        $daysPassed = $trialDays - $remainingTrialDays;
        $trialPercentage = $trialDays > 0 ? min(100, round(($daysPassed / $trialDays) * 100)) : 0;
        
        // Get current plan if subscribed
        $currentPlan = $currentSubscription['plan_type'] ?? null;
        $subscriptionEndDate = $currentSubscription['end_date'] ?? null;
        
        // Pass to view
        require_once __DIR__ . '/../views/external/dashboard.php';
    }
    
    /**
     * Display learning materials for external users
     */
    public function materials() {
        $this->checkAccess();
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
     * Display quizzes
     */
    public function quizzes() {
        $this->checkAccess();
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
        $this->checkAccess();
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
            header('Location: ' . BASE_URL . '/external/subscription');
            exit;
        }
        
        // Get current subscription
        $currentSubscription = $this->subscriptionModel->getCurrentSubscription($_SESSION['user_id']);
        
        if (!$currentSubscription) {
            $_SESSION['error'] = 'No active subscription found';
            header('Location: ' . BASE_URL . '/external/subscription');
            exit;
        }
        
        // Get subscription settings
        $subscriptionSettings = $this->settingsModel->getSubscriptionSettings();
        
        // Plan details
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
        
        // Calculate upgrade price
        $prices = [
            'monthly' => $subscriptionSettings['monthly_price'] ?? 15000,
            'termly' => $subscriptionSettings['termly_price'] ?? 40000,
            'yearly' => $subscriptionSettings['yearly_price'] ?? 120000
        ];
        
        $currentPrice = $prices[$fromPlan] ?? 0;
        $newPrice = $prices[$toPlan] ?? 0;
        
        // Calculate remaining days
        $endDate = new DateTime($currentSubscription['end_date']);
        $now = new DateTime();
        $daysRemaining = $now->diff($endDate)->days;
        
        // Calculate prorated value
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
     * Process upgrade (local test version)
     */
    public function processUpgrade() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/external/subscription');
            exit;
        }
        
        $fromPlan = $_POST['from_plan'] ?? '';
        $toPlan = $_POST['to_plan'] ?? '';
        $amount = $_POST['amount'] ?? 0;
        
        if (empty($fromPlan) || empty($toPlan)) {
            $_SESSION['error'] = 'Invalid upgrade request';
            header('Location: ' . BASE_URL . '/external/subscription');
            exit;
        }
        
        // Get current subscription
        $currentSubscription = $this->subscriptionModel->getCurrentSubscription($_SESSION['user_id']);
        
        if (!$currentSubscription) {
            $_SESSION['error'] = 'No active subscription found';
            header('Location: ' . BASE_URL . '/external/subscription');
            exit;
        }
        
        // Process upgrade
        $result = $this->subscriptionModel->upgradeSubscription(
            $_SESSION['user_id'],
            $fromPlan,
            $toPlan,
            [
                'method' => $_POST['payment_method'] ?? 'mobile_money',
                'transaction_id' => 'UPG_' . time() . '_' . $_SESSION['user_id'],
                'amount' => $amount
            ]
        );
        
        if ($result['success']) {
            $_SESSION['success'] = $result['message'];
            header('Location: ' . BASE_URL . '/external/upgrade-success?subscription_id=' . $result['new_subscription_id']);
            exit;
        } else {
            $_SESSION['error'] = $result['error'];
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
        
        // Get upgrade details
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


    /**
     * Process payment with Pesapal
     */
    public function processPesapalPayment() {
        // Debug logging
        error_log("=== processPesapalPayment called ===");
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            error_log("Not a POST request");
            header('Location: ' . BASE_URL . '/external/subscription');
            exit;
        }
        
        $planType = $_POST['plan'] ?? '';
        $paymentMethod = $_POST['payment_method'] ?? 'mobile_money';
        $phoneNumber = $_POST['phone_number'] ?? '';
        
        error_log("Plan: $planType, Method: $paymentMethod, Phone: $phoneNumber");
        
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
        
        // Get plan amount
        $subscriptionSettings = $this->settingsModel->getSubscriptionSettings();
        $amounts = [
            'monthly' => $subscriptionSettings['monthly_price'] ?? 15000,
            'termly' => $subscriptionSettings['termly_price'] ?? 40000,
            'yearly' => $subscriptionSettings['yearly_price'] ?? 120000
        ];
        
        $amount = $amounts[$planType] ?? 0;
        error_log("Amount: $amount UGX");
        
        // Create pending payment record
        $paymentResult = $this->subscriptionModel->createPendingPayment(
            $_SESSION['user_id'],
            $planType,
            $amount,
            $paymentMethod,
            $phoneNumber
        );
        
        if (!$paymentResult['success']) {
            error_log("Failed to create payment record: " . ($paymentResult['error'] ?? 'Unknown error'));
            $_SESSION['error'] = $paymentResult['error'];
            header('Location: ' . BASE_URL . '/external/subscription');
            exit;
        }
        
        error_log("Payment record created: " . print_r($paymentResult, true));
        
        // Get user details
        $user = $this->userModel->getById($_SESSION['user_id']);
        $nameParts = explode(' ', $user['first_name'] . ' ' . $user['last_name']);
        $firstName = $nameParts[0] ?? $user['first_name'];
        $lastName = isset($nameParts[1]) ? implode(' ', array_slice($nameParts, 1)) : '';
        
        // Initialize Pesapal
        require_once __DIR__ . '/../lib/Pesapal.php';
        $pesapal = new Pesapal();
        
        // Prepare payment data
        $paymentData = [
            'amount' => $amount,
            'phone' => $phoneNumber,
            'email' => $user['email'],
            'first_name' => $firstName,
            'last_name' => $lastName,
            'reference' => $paymentResult['transaction_id'],
            'description' => ucfirst($planType) . ' Subscription - Rays of Grace'
        ];
        
        error_log("Submitting to Pesapal: " . json_encode($paymentData));
        
        // Submit payment to Pesapal
        $response = $pesapal->submitPayment($paymentData);
        
        error_log("Pesapal Response: " . json_encode($response));
        
        if (isset($response['error']) && $response['error']) {
            $_SESSION['error'] = $response['message'];
            header('Location: ' . BASE_URL . '/external/subscription');
            exit;
        }
        
        // Store pending info in session
        $_SESSION['pending_payment_id'] = $paymentResult['payment_id'];
        $_SESSION['pending_transaction_id'] = $paymentResult['transaction_id'];
        $_SESSION['pending_plan'] = $planType;
        $_SESSION['pending_amount'] = $amount;
        $_SESSION['pesapal_tracking_id'] = $response['tracking_id'] ?? '';
        
        // Redirect to Pesapal
        if (isset($response['redirect_url'])) {
            error_log("Redirecting to: " . $response['redirect_url']);
            header('Location: ' . $response['redirect_url']);
            exit;
        } else {
            $_SESSION['error'] = 'No redirect URL from Pesapal';
            header('Location: ' . BASE_URL . '/external/subscription');
            exit;
        }
    }

    /**
     * Pesapal callback (after payment)
     */
    public function pesapalCallback() {
        $pesapalTrackingId = $_GET['pesapal_transaction_tracking_id'] ?? '';
        $merchantReference = $_GET['pesapal_merchant_reference'] ?? '';
        
        if (empty($pesapalTrackingId)) {
            $_SESSION['error'] = 'Invalid payment callback';
            header('Location: ' . BASE_URL . '/external/subscription');
            exit;
        }
        
        // Verify payment status
        $pesapal = new Pesapal();
        $verification = $pesapal->queryPaymentStatus($pesapalTrackingId);
        
        if (!$verification['success']) {
            $_SESSION['error'] = 'Payment verification failed';
            header('Location: ' . BASE_URL . '/external/subscription');
            exit;
        }
        
        // Check if payment was successful
        if ($verification['status'] == 'COMPLETED') {
            // Update payment record
            $this->subscriptionModel->updatePaymentStatus(
                $merchantReference,
                'completed',
                $verification
            );
            
            // Activate subscription
            $this->activatePesapalSubscription($merchantReference);
            
            $_SESSION['success'] = 'Payment successful! Your subscription is now active.';
            header('Location: ' . BASE_URL . '/external/dashboard');
            exit;
        } else {
            $_SESSION['error'] = 'Payment was not successful. Status: ' . $verification['status'];
            header('Location: ' . BASE_URL . '/external/subscription');
            exit;
        }
    }

    /**
     * Pesapal IPN (Instant Payment Notification)
     */
    public function pesapalIpn() {
        $pesapalTrackingId = $_GET['pesapal_transaction_tracking_id'] ?? '';
        $merchantReference = $_GET['pesapal_merchant_reference'] ?? '';
        
        if (empty($pesapalTrackingId)) {
            http_response_code(400);
            echo 'Invalid IPN request';
            exit;
        }
        
        // Verify payment status
        $pesapal = new Pesapal();
        $verification = $pesapal->queryPaymentStatus($pesapalTrackingId);
        
        if ($verification['success'] && $verification['status'] == 'COMPLETED') {
            $this->subscriptionModel->updatePaymentStatus(
                $merchantReference,
                'completed',
                $verification
            );
            
            $this->activatePesapalSubscription($merchantReference);
            
            echo 'OK';
            exit;
        }
        
        echo 'FAILED';
        exit;
    }

    /**
     * Activate subscription after successful payment
     */
    private function activatePesapalSubscription($reference) {
        $payment = $this->subscriptionModel->getPaymentByTransactionId($reference);
        
        if (!$payment) {
            error_log("Payment not found for reference: $reference");
            return false;
        }
        
        // Create subscription
        $subscriptionResult = $this->subscriptionModel->createSubscription(
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
            
            error_log("Subscription activated for user: " . $payment['user_id']);
        }
        
        return $subscriptionResult;
    }

    /**
     * Check if user has access to content (trial or subscription)
     * Redirects to subscription page if no access
     */
    private function checkAccess() {
        $userId = $_SESSION['user_id'];
        $trialDays = $this->settingsModel->get('trial_days', 60);
        
        // Check if user has active subscription
        $currentSubscription = $this->subscriptionModel->getCurrentSubscription($userId);
        
        if ($currentSubscription) {
            return true; // Has active subscription
        }
        
        // Check if still in trial period
        $trialStatus = $this->userModel->getTrialStatus($userId, $trialDays);
        
        if ($trialStatus['is_trial']) {
            return true; // Still in trial
        }
        
        // No access - redirect to subscription
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
}
?>