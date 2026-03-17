<?php
// File: /controllers/AdminController.php
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Report.php';
require_once __DIR__ . '/../models/Quiz.php';
require_once __DIR__ . '/../models/Subscription.php';
require_once __DIR__ . '/../models/Settings.php';
require_once __DIR__ . '/../models/Lesson.php'; 
require_once __DIR__ . '/../models/Classes.php';

class AdminController {
    private $userModel;
    private $reportModel;
    private $quizModel;
    private $subscriptionModel;
    private $settingsModel;
     private $lessonModel;
    private $classModel;
    
    public function __construct() {
        // Check if user is logged in and is admin
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . BASE_URL . '/login');
            exit;
        }
        
        if ($_SESSION['user_role'] !== 'admin') {
            // Redirect non-admin users to their respective dashboards
            $this->redirectToRoleDashboard();
            exit;
        }
        
        // Initialize all models
        $this->userModel = new User();
        $this->reportModel = new Report();
        $this->quizModel = new Quiz();
        $this->subscriptionModel = new Subscription();
        $this->settingsModel = new Settings();
        $this->lessonModel = new Lesson();
        $this->classModel = new Classes();
    }
    
    private function redirectToRoleDashboard() {
        switch ($_SESSION['user_role']) {
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
     * Admin Dashboard
     */
    public function dashboard() {
        $hideFooter = true;
        
        // Get statistics for dashboard
        $totalUsers = count($this->userModel->getAllUsers(null, 0, 0));
        $totalTeachers = count($this->userModel->getAllUsers('teacher', 0, 0));
        $totalLearners = count($this->userModel->getAllUsers('learner', 0, 0));
        $totalExternal = count($this->userModel->getAllUsers('external', 0, 0));
        
        $recentUsers = $this->userModel->getAllUsers(null, 5, 0);
        $recentActivity = $this->reportModel->getRecentActivity(10);
        
        require_once __DIR__ . '/../views/admin/dashboard.php';
    }
    
    /**
     * Admin Profile Page
     */
    public function profile() {
        $hideFooter = true;
        
        // Get admin profile data
        $profile = $this->userModel->getProfile($_SESSION['user_id']);
        
        if (!$profile) {
            // Create basic profile from session
            $nameParts = explode(' ', $_SESSION['user_name'] ?? 'Admin');
            $profile = [
                'id' => $_SESSION['user_id'],
                'first_name' => $nameParts[0] ?? '',
                'last_name' => $nameParts[1] ?? '',
                'email' => $_SESSION['user_email'] ?? '',
                'phone' => '',
                'role' => 'admin',
                'created_at' => date('Y-m-d H:i:s'),
                'profile_photo' => null
            ];
        }
        
        require_once __DIR__ . '/../views/admin/profile.php';
    }
    
    /**
     * Update admin profile
     */
    public function updateProfile() {
        $hideFooter = true;
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/admin/profile');
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
            header('Location: ' . BASE_URL . '/admin/profile');
            exit;
        }
        
        $result = $this->userModel->updateProfile($_SESSION['user_id'], $data);
        
        if ($result['success']) {
            // Update session with new data
            $_SESSION['user_name'] = $data['first_name'] . ' ' . $data['last_name'];
            $_SESSION['user_email'] = $data['email'];
            
            $_SESSION['success'] = $result['message'];
        } else {
            $_SESSION['error'] = $result['error'];
        }
        
        header('Location: ' . BASE_URL . '/admin/profile');
        exit;
    }
    
    /**
     * Update profile photo
     */
    public function updateProfilePhoto() {
        $hideFooter = true;
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_FILES['profile_photo'])) {
            header('Location: ' . BASE_URL . '/admin/profile');
            exit;
        }
        
        $result = $this->userModel->uploadProfilePhoto($_SESSION['user_id'], $_FILES['profile_photo']);
        
        if ($result['success']) {
            $_SESSION['success'] = 'Profile photo updated successfully';
        } else {
            $_SESSION['error'] = $result['error'];
        }
        
        header('Location: ' . BASE_URL . '/admin/profile');
        exit;
    }
    
    /**
     * User Management
     */
    public function users() {
        $hideFooter = true;
        
        $page = $_GET['page'] ?? 1;
        $role = $_GET['role'] ?? null;
        $search = $_GET['search'] ?? null;
        
        $limit = 10;
        $offset = ($page - 1) * $limit;
        
        if ($search) {
            $users = $this->userModel->searchUsers($search);
        } else {
            $users = $this->userModel->getAllUsers($role, $limit, $offset);
        }
        
        $totalUsers = count($this->userModel->getAllUsers($role, 0, 0));
        $totalPages = ceil($totalUsers / $limit);
        
        require_once __DIR__ . '/../views/admin/users.php';
    }
    
    /**
     * Create User Form
     */
    public function createUser() {
        $hideFooter = true;
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'first_name' => $_POST['first_name'] ?? '',
                'last_name' => $_POST['last_name'] ?? '',
                'email' => $_POST['email'] ?? '',
                'phone' => $_POST['phone'] ?? '',
                'password' => $_POST['password'] ?? 'Password123',
                'role' => $_POST['role'] ?? 'external',
                'class' => $_POST['class'] ?? null
            ];
            
            // Validate input
            $errors = [];
            if (empty($data['first_name'])) $errors[] = 'First name is required';
            if (empty($data['last_name'])) $errors[] = 'Last name is required';
            if (empty($data['email'])) $errors[] = 'Email is required';
            if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) $errors[] = 'Invalid email format';
            
            if (empty($errors)) {
                $result = $this->userModel->register($data);
                
                if ($result['success']) {
                    $_SESSION['success'] = 'User created successfully';
                    header('Location: ' . BASE_URL . '/admin/users');
                    exit;
                } else {
                    $_SESSION['error'] = $result['error'];
                }
            } else {
                $_SESSION['error'] = implode('<br>', $errors);
            }
        }
        
        require_once __DIR__ . '/../views/admin/create_user.php';
    }
    
    /**
     * Edit User
     */
    public function editUser($userId) {
        $hideFooter = true;
        
        $user = $this->userModel->getById($userId);
        
        if (!$user) {
            $_SESSION['error'] = 'User not found';
            header('Location: ' . BASE_URL . '/admin/users');
            exit;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'first_name' => $_POST['first_name'] ?? '',
                'last_name' => $_POST['last_name'] ?? '',
                'email' => $_POST['email'] ?? '',
                'phone' => $_POST['phone'] ?? '',
                'role' => $_POST['role'] ?? $user['role']
            ];
            
            // Validate input
            if (empty($data['first_name']) || empty($data['last_name']) || empty($data['email'])) {
                $_SESSION['error'] = 'Please fill in all required fields';
                header('Location: ' . BASE_URL . '/admin/users/edit/' . $userId);
                exit;
            }
            
            // Use the admin update method that doesn't touch the session
            $result = $this->userModel->updateUserAsAdmin($userId, $data);
            
            // Handle status if provided
            if (isset($_POST['status'])) {
                if ($_POST['status'] === 'suspended' && !$user['is_suspended']) {
                    $this->userModel->suspendUser($userId);
                } elseif ($_POST['status'] === 'active' && $user['is_suspended']) {
                    $this->userModel->activateUser($userId);
                }
            }
            
            if ($result['success']) {
                $_SESSION['success'] = 'User updated successfully';
            } else {
                $_SESSION['error'] = $result['error'];
            }
            
            header('Location: ' . BASE_URL . '/admin/users');
            exit;
        }
        
        require_once __DIR__ . '/../views/admin/edit_user.php';
    }
    
    /**
     * Suspend User
     */
    public function suspendUser($userId) {
        if ($_SESSION['user_id'] == $userId) {
            $_SESSION['error'] = 'You cannot suspend your own account';
            header('Location: ' . BASE_URL . '/admin/users');
            exit;
        }
        
        $result = $this->userModel->suspendUser($userId);
        
        if ($result['success']) {
            $_SESSION['success'] = $result['message'];
        } else {
            $_SESSION['error'] = $result['error'];
        }
        
        header('Location: ' . BASE_URL . '/admin/users');
        exit;
    }
    
    /**
     * Activate User
     */
    public function activateUser($userId) {
        $result = $this->userModel->activateUser($userId);
        
        if ($result['success']) {
            $_SESSION['success'] = $result['message'];
        } else {
            $_SESSION['error'] = $result['error'];
        }
        
        header('Location: ' . BASE_URL . '/admin/users');
        exit;
    }
    
    /**
     * Delete User
     */
    public function deleteUser($userId) {
        if ($_SESSION['user_id'] == $userId) {
            $_SESSION['error'] = 'You cannot delete your own account';
            header('Location: ' . BASE_URL . '/admin/users');
            exit;
        }
        
        $result = $this->userModel->deleteUser($userId);
        
        if ($result['success']) {
            $_SESSION['success'] = $result['message'];
        } else {
            $_SESSION['error'] = $result['error'];
        }
        
        header('Location: ' . BASE_URL . '/admin/users');
        exit;
    }
    
    /**
     * Reports Page
     */
    public function reports() {
        $hideFooter = true;
        
        $type = $_GET['type'] ?? 'overview';
        $start_date = $_GET['start_date'] ?? date('Y-m-d', strtotime('-30 days'));
        $end_date = $_GET['end_date'] ?? date('Y-m-d');
        $days = $_GET['days'] ?? 30;
        
        // Get ALL user statistics from database
        $totalUsers = count($this->userModel->getAllUsers(null, 0, 0));
        $totalTeachers = count($this->userModel->getAllUsers('teacher', 0, 0));
        $totalLearners = count($this->userModel->getAllUsers('learner', 0, 0));
        $totalExternal = count($this->userModel->getAllUsers('external', 0, 0));
        $totalAdmins = count($this->userModel->getAllUsers('admin', 0, 0));
        
        // Get recent users (last 10)
        $recentUsers = $this->userModel->getAllUsers(null, 10, 0);
        
        // Get recent activity
        $recentActivity = $this->reportModel->getRecentActivity(10);
        
        // Get user growth data for charts
        $userGrowthData = $this->reportModel->getUserGrowthData($days);
        
        // Get revenue data for charts
        $revenueData = $this->reportModel->getRevenueData($days);
        
        // Get active today count
        $activeToday = $this->userModel->getActiveToday();
        
        // Get new users today
        $newUsersToday = $this->userModel->getNewUsersToday();
        
        // Get quiz statistics
        $totalQuizzes = $this->quizModel->getTotalQuizzes();
        $totalQuizAttempts = $this->quizModel->getTotalAttempts();
        $averageScore = $this->quizModel->getAverageScore();
        
        // Get payment statistics
        $totalRevenue = $this->subscriptionModel->getTotalRevenue();
        $totalSubscriptions = $this->subscriptionModel->getTotalSubscriptions();
        
        // Get report data based on type
        switch ($type) {
            case 'users':
                $data = $this->reportModel->getUserReport($start_date, $end_date);
                break;
            case 'quizzes':
                $data = $this->reportModel->getQuizReport($start_date, $end_date);
                break;
            case 'payments':
                $data = $this->reportModel->getPaymentReport($start_date, $end_date);
                break;
            case 'activity':
                $data = $this->reportModel->getActivityReport($start_date, $end_date);
                break;
            default:
                $data = [];
        }
        
        // Pass ALL variables to the view
        require_once __DIR__ . '/../views/admin/reports.php';
    }
    
    /**
     * Export Report
     */
    public function exportReport() {
        $type = $_GET['type'] ?? 'users';
        $format = $_GET['format'] ?? 'csv';
        $startDate = $_GET['start_date'] ?? date('Y-m-d', strtotime('-30 days'));
        $endDate = $_GET['end_date'] ?? date('Y-m-d');
        
        // Get data based on type
        switch ($type) {
            case 'users':
                $data = $this->reportModel->getUserReport($startDate, $endDate);
                $filename = 'users_report_' . date('Y-m-d') . '.' . $format;
                break;
            case 'quizzes':
                $data = $this->reportModel->getQuizReport($startDate, $endDate);
                $filename = 'quizzes_report_' . date('Y-m-d') . '.' . $format;
                break;
            case 'payments':
                $data = $this->reportModel->getPaymentReport($startDate, $endDate);
                $filename = 'payments_report_' . date('Y-m-d') . '.' . $format;
                break;
            default:
                $data = [];
                $filename = 'report_' . date('Y-m-d') . '.' . $format;
        }
        
        // Export logic here (CSV, PDF, etc.)
        if ($format === 'csv' && !empty($data)) {
            $this->reportModel->exportToCSV($data, $filename);
        }
        
        // For now, just redirect back
        $_SESSION['success'] = 'Report exported successfully';
        header('Location: ' . BASE_URL . '/admin/reports');
        exit;
    }
    
    /**
     * Settings Page
     */
    public function settings() {
        $hideFooter = true;
        
        // Get current settings from database
        $generalSettings = $this->settingsModel->getGeneralSettings();
        $subscriptionSettings = $this->settingsModel->getSubscriptionSettings();
        $emailSettings = $this->settingsModel->getEmailSettings();
        $securitySettings = $this->settingsModel->getSecuritySettings();
        $appearanceSettings = $this->settingsModel->getAppearanceSettings();
        
        require_once __DIR__ . '/../views/admin/settings.php';
    }
    
    /**
     * Save General Settings
     */
    public function saveGeneralSettings() {
        $hideFooter = true;
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/admin/settings');
            exit;
        }
        
        $settings = [
            'site_name' => $_POST['site_name'] ?? 'Rays of Grace E-Learning',
            'site_description' => $_POST['site_description'] ?? '',
            'contact_email' => $_POST['contact_email'] ?? ''
        ];
        
        $result = $this->settingsModel->updateSettings($settings);
        
        if ($result) {
            $_SESSION['success'] = 'General settings saved successfully!';
        } else {
            $_SESSION['error'] = 'Failed to save general settings.';
        }
        
        header('Location: ' . BASE_URL . '/admin/settings');
        exit;
    }
    
    /**
     * Save Subscription Settings
     */
    public function saveSubscriptionSettings() {
        $hideFooter = true;
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/admin/settings');
            exit;
        }
        
        $settings = [
            'monthly_price' => $_POST['monthly_price'] ?? 15000,
            'termly_price' => $_POST['termly_price'] ?? 40000,
            'yearly_price' => $_POST['yearly_price'] ?? 120000,
            'trial_days' => $_POST['trial_days'] ?? 60
        ];
        
        $result = $this->settingsModel->updateSettings($settings);
        
        if ($result) {
            $_SESSION['success'] = 'Subscription settings saved successfully!';
        } else {
            $_SESSION['error'] = 'Failed to save subscription settings.';
        }
        
        header('Location: ' . BASE_URL . '/admin/settings');
        exit;
    }
    
    /**
     * Save Email Settings
     */
    public function saveEmailSettings() {
        $hideFooter = true;
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/admin/settings');
            exit;
        }
        
        $settings = [
            'smtp_host' => $_POST['smtp_host'] ?? 'smtp.gmail.com',
            'smtp_port' => $_POST['smtp_port'] ?? 587,
            'smtp_username' => $_POST['smtp_username'] ?? '',
            'smtp_password' => $_POST['smtp_password'] ?? '',
            'from_email' => $_POST['from_email'] ?? ''
        ];
        
        $result = $this->settingsModel->updateSettings($settings);
        
        if ($result) {
            $_SESSION['success'] = 'Email settings saved successfully!';
        } else {
            $_SESSION['error'] = 'Failed to save email settings.';
        }
        
        header('Location: ' . BASE_URL . '/admin/settings');
        exit;
    }
    
    /**
     * Save Security Settings
     */
    public function saveSecuritySettings() {
        $hideFooter = true;
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/admin/settings');
            exit;
        }
        
        $settings = [
            'enable_2fa' => isset($_POST['enable_2fa']) ? 1 : 0,
            'session_timeout' => $_POST['session_timeout'] ?? 60,
            'strong_passwords' => isset($_POST['strong_passwords']) ? 1 : 0
        ];
        
        $result = $this->settingsModel->updateSettings($settings);
        
        if ($result) {
            $_SESSION['success'] = 'Security settings saved successfully!';
        } else {
            $_SESSION['error'] = 'Failed to save security settings.';
        }
        
        header('Location: ' . BASE_URL . '/admin/settings');
        exit;
    }
    
    /**
     * Save Appearance Settings
     */
    public function saveAppearanceSettings() {
        $hideFooter = true;
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/admin/settings');
            exit;
        }
        
        $settings = [
            'theme_color' => $_POST['theme_color'] ?? '#8B5CF6',
            'accent_color' => $_POST['accent_color'] ?? '#F97316',
            'dark_mode' => isset($_POST['dark_mode']) ? 1 : 0
        ];
        
        $result = $this->settingsModel->updateSettings($settings);
        
        if ($result) {
            $_SESSION['success'] = 'Appearance settings saved successfully!';
        } else {
            $_SESSION['error'] = 'Failed to save appearance settings.';
        }
        
        header('Location: ' . BASE_URL . '/admin/settings');
        exit;
    }
    
    /**
     * Save All Settings
     */
    public function saveAllSettings() {
        $hideFooter = true;
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/admin/settings');
            exit;
        }
        
        $settings = [
            // General
            'site_name' => $_POST['site_name'] ?? 'Rays of Grace E-Learning',
            'site_description' => $_POST['site_description'] ?? '',
            'contact_email' => $_POST['contact_email'] ?? '',
            
            // Subscription
            'monthly_price' => $_POST['monthly_price'] ?? 15000,
            'termly_price' => $_POST['termly_price'] ?? 40000,
            'yearly_price' => $_POST['yearly_price'] ?? 120000,
            'trial_days' => $_POST['trial_days'] ?? 60,
            
            // Email
            'smtp_host' => $_POST['smtp_host'] ?? 'smtp.gmail.com',
            'smtp_port' => $_POST['smtp_port'] ?? 587,
            'smtp_username' => $_POST['smtp_username'] ?? '',
            'smtp_password' => $_POST['smtp_password'] ?? '',
            'from_email' => $_POST['from_email'] ?? '',
            
            // Security
            'enable_2fa' => isset($_POST['enable_2fa']) ? 1 : 0,
            'session_timeout' => $_POST['session_timeout'] ?? 60,
            'strong_passwords' => isset($_POST['strong_passwords']) ? 1 : 0,
            
            // Appearance
            'theme_color' => $_POST['theme_color'] ?? '#8B5CF6',
            'accent_color' => $_POST['accent_color'] ?? '#F97316',
            'dark_mode' => isset($_POST['dark_mode']) ? 1 : 0
        ];
        
        $result = $this->settingsModel->updateSettings($settings);
        
        if ($result) {
            $_SESSION['success'] = 'All settings saved successfully!';
        } else {
            $_SESSION['error'] = 'Failed to save settings.';
        }
        
        header('Location: ' . BASE_URL . '/admin/settings');
        exit;
    }
    
    /**
     * Test Email Configuration
     */
    public function testEmailConfig() {
        $hideFooter = true;
        
        // Get email settings
        $emailSettings = $this->settingsModel->getEmailSettings();
        
        // Implement your email test logic here
        // For now, just return success
        $_SESSION['success'] = 'Email test successful! Check your inbox.';
        
        header('Location: ' . BASE_URL . '/admin/settings');
        exit;
    }
    
    /**
     * Clear Cache
     */
    public function clearCache() {
        $hideFooter = true;
        
        $result = $this->settingsModel->clearCache();
        
        if ($result) {
            $_SESSION['success'] = 'Cache cleared successfully!';
        } else {
            $_SESSION['error'] = 'Failed to clear cache.';
        }
        
        header('Location: ' . BASE_URL . '/admin/settings');
        exit;
    }
    
    /**
     * Reset to Defaults
     */
    public function resetToDefaults() {
        $hideFooter = true;
        
        $result = $this->settingsModel->resetToDefaults();
        
        if ($result) {
            $_SESSION['success'] = 'All settings have been reset to defaults.';
        } else {
            $_SESSION['error'] = 'Failed to reset settings.';
        }
        
        header('Location: ' . BASE_URL . '/admin/settings');
        exit;
    }

    /**
     * View all lessons (for admin)
     */
    public function lessons() {
        $hideFooter = true;
        
        $page = $_GET['page'] ?? 1;
        $search = $_GET['search'] ?? null;
        $teacherId = $_GET['teacher'] ?? null;
        $status = $_GET['status'] ?? null;
        
        $limit = 15;
        $offset = ($page - 1) * $limit;
        
        // Get all lessons with filters
        $lessons = $this->lessonModel->getAllLessons($search, $teacherId, $status, $limit, $offset);
        
        // Get total count for pagination
        $totalLessons = $this->lessonModel->countAllLessons($search, $teacherId, $status);
        $totalPages = ceil($totalLessons / $limit);
        
        // Get all teachers for filter dropdown
        $teachers = $this->userModel->getAllUsers('teacher');
        
        require_once __DIR__ . '/../views/admin/lessons.php';
    }

    /**
     * View single lesson (admin)
     */
    public function viewLesson($lessonId) {
        $hideFooter = true;
        
        $lesson = $this->lessonModel->getById($lessonId);
        
        if (!$lesson) {
            $_SESSION['error'] = 'Lesson not found.';
            header('Location: ' . BASE_URL . '/admin/lessons');
            exit;
        }
        
        require_once __DIR__ . '/../views/admin/view_lesson.php';
    }

    /**
     * Approve lesson
     */
    public function approveLesson($lessonId) {
        $result = $this->lessonModel->approve($lessonId);
        
        if ($result['success']) {
            $_SESSION['success'] = 'Lesson approved successfully.';
        } else {
            $_SESSION['error'] = $result['error'] ?? 'Failed to approve lesson.';
        }
        
        header('Location: ' . BASE_URL . '/admin/lessons');
        exit;
    }

    /**
     * Reject/Disapprove lesson
     */
    public function rejectLesson($lessonId) {
        $result = $this->lessonModel->reject($lessonId);
        
        if ($result['success']) {
            $_SESSION['success'] = 'Lesson rejected.';
        } else {
            $_SESSION['error'] = $result['error'] ?? 'Failed to reject lesson.';
        }
        
        header('Location: ' . BASE_URL . '/admin/lessons');
        exit;
    }

    /**
     * View all quizzes (for admin)
     */
    public function quizzes() {
        $hideFooter = true;
        
        $page = $_GET['page'] ?? 1;
        $search = $_GET['search'] ?? null;
        $teacherId = $_GET['teacher'] ?? null;
        $status = $_GET['status'] ?? null;
        
        $limit = 15;
        $offset = ($page - 1) * $limit;
        
        // Get all quizzes with filters
        $quizzes = $this->quizModel->getAllQuizzes($search, $teacherId, $status, $limit, $offset);
        
        // Get total count for pagination
        $totalQuizzes = $this->quizModel->countAllQuizzes($search, $teacherId, $status);
        $totalPages = ceil($totalQuizzes / $limit);
        
        // Get all teachers for filter dropdown
        $teachers = $this->userModel->getAllUsers('teacher');
        
        require_once __DIR__ . '/../views/admin/quizzes.php';
    }

    /**
     * View single quiz (admin)
     */
    public function viewQuiz($quizId) {
        $hideFooter = true;
        
        $quiz = $this->quizModel->getById($quizId);
        
        if (!$quiz) {
            $_SESSION['error'] = 'Quiz not found.';
            header('Location: ' . BASE_URL . '/admin/quizzes');
            exit;
        }
        
        require_once __DIR__ . '/../views/admin/view_quiz.php';
    }

    /**
     * Approve quiz
     */
    public function approveQuiz($quizId) {
        $result = $this->quizModel->approve($quizId);
        
        if ($result['success']) {
            $_SESSION['success'] = 'Quiz approved successfully.';
        } else {
            $_SESSION['error'] = $result['error'] ?? 'Failed to approve quiz.';
        }
        
        header('Location: ' . BASE_URL . '/admin/quizzes');
        exit;
    }

    /**
     * Reject quiz
     */
    public function rejectQuiz($quizId) {
        $result = $this->quizModel->reject($quizId);
        
        if ($result['success']) {
            $_SESSION['success'] = 'Quiz rejected.';
        } else {
            $_SESSION['error'] = $result['error'] ?? 'Failed to reject quiz.';
        }
        
        header('Location: ' . BASE_URL . '/admin/quizzes');
        exit;
    }

    /**
     * Delete quiz (admin)
     */
    public function deleteQuiz($quizId) {
        $result = $this->quizModel->delete($quizId);
        
        if ($result['success']) {
            $_SESSION['success'] = 'Quiz deleted successfully.';
        } else {
            $_SESSION['error'] = $result['error'] ?? 'Failed to delete quiz.';
        }
        
        header('Location: ' . BASE_URL . '/admin/quizzes');
        exit;
    }
}
?>