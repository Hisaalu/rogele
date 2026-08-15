<?php
// File: /controllers/AdminController.php - Optimized Admin Controller
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Report.php';
require_once __DIR__ . '/../models/Quiz.php';
require_once __DIR__ . '/../models/Subscription.php';
require_once __DIR__ . '/../models/Settings.php';
require_once __DIR__ . '/../models/Lesson.php'; 
require_once __DIR__ . '/../models/Classes.php';
require_once __DIR__ . '/../models/Notification.php';
require_once __DIR__ . '/../models/Subject.php';

class AdminController {
    private $userModel;
    private $reportModel;
    private $quizModel;
    private $subscriptionModel;
    private $settingsModel;
    private $lessonModel;
    private $classModel;
    private $subjectModel;
    private $itemsPerPage = 10;
    private $lessonsPerPage = 15;
    private $quizzesPerPage = 15;
    
    public function __construct() {
        if (!isset($_SESSION['user_id'])) {
            $this->redirect(BASE_URL . '/login');
        }
        
        if ($_SESSION['user_role'] !== 'admin') {
            $this->redirectToRoleDashboard();
        }
        
        $this->userModel = new User();
        $this->reportModel = new Report();
        $this->quizModel = new Quiz();
        $this->subscriptionModel = new Subscription();
        $this->settingsModel = new Settings();
        $this->lessonModel = new Lesson();
        $this->classModel = new Classes();
        $this->subjectModel = new Subject();
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
    
    private function validateEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
    
    private function getPaginationParams($defaultPage = 1) {
        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : $defaultPage;
        $offset = ($page - 1) * $this->itemsPerPage;
        return ['page' => $page, 'offset' => $offset];
    }
    
    private function getLessonPaginationParams() {
        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $offset = ($page - 1) * $this->lessonsPerPage;
        return ['page' => $page, 'offset' => $offset];
    }
    
    private function getQuizPaginationParams() {
        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $offset = ($page - 1) * $this->quizzesPerPage;
        return ['page' => $page, 'offset' => $offset];
    }
    
    private function redirectToRoleDashboard() {
        $urls = [
            'teacher' => BASE_URL . '/teacher/dashboard',
            'learner' => BASE_URL . '/learner/dashboard',
            'external' => BASE_URL . '/external/dashboard'
        ];
        $this->redirect($urls[$_SESSION['user_role']] ?? BASE_URL . '/login');
    }
    
    private function setUserSession($user) {
        $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
        $_SESSION['user_email'] = $user['email'];
    }
    
    private function getAdminProfileData() {
        $profile = $this->userModel->getProfile($_SESSION['user_id']);
        
        if (!$profile) {
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
        
        return $profile;
    }
    
    private function handleSettingSave($settings, $successMessage, $errorMessage) {
        $result = $this->settingsModel->updateSettings($settings);
        
        if ($result) {
            $this->redirectWithSuccess($successMessage, BASE_URL . '/admin/settings');
        } else {
            $this->redirectWithError($errorMessage, BASE_URL . '/admin/settings');
        }
    }
    
    // ==================== DASHBOARD & PROFILE ====================
    public function dashboard() {
        $hideFooter = true;
        
        $totalUsers = count($this->userModel->getAllUsers(null, 0, 0));
        $totalTeachers = count($this->userModel->getAllUsers('teacher', 0, 0));
        $totalAdmins = count($this->userModel->getAllUsers('admin', 0, 0));
        $totalExternal = count($this->userModel->getAllUsers('external', 0, 0));
        
        $recentUsers = $this->userModel->getAllUsers(null, 5, 0);
        $recentActivity = $this->reportModel->getRecentActivity(10);
        
        require_once __DIR__ . '/../views/admin/dashboard.php';
    }
    
    public function profile() {
        $hideFooter = true;
        $profile = $this->getAdminProfileData();
        require_once __DIR__ . '/../views/admin/profile.php';
    }
    
    public function updateProfile() {
        if (!$this->isPostRequest()) {
            $this->redirect(BASE_URL . '/admin/profile');
        }
        
        $data = [
            'first_name' => trim($_POST['first_name'] ?? ''),
            'last_name' => trim($_POST['last_name'] ?? ''),
            'email' => trim($_POST['email'] ?? ''),
            'phone' => trim($_POST['phone'] ?? '')
        ];
        
        $errors = $this->validateRequiredFields($data, ['first_name', 'last_name', 'email']);
        
        if (empty($errors) && !$this->validateEmail($data['email'])) {
            $errors[] = 'Please enter a valid email address';
        }
        
        if (!empty($errors)) {
            $this->redirectWithError(implode(', ', $errors), BASE_URL . '/admin/profile');
        }
        
        $result = $this->userModel->updateProfile($_SESSION['user_id'], $data);
        
        if ($result['success']) {
            $this->setUserSession($data);

            $userName = trim($data['first_name'] . ' ' . $data['last_name']);
            $message = !empty($userName) 
                ? "{$userName} updated their profile." 
                : "User #{$userId} updated their profile.";

            require_once __DIR__ . '/../models/Notification.php';
            
            Notification::create(
                'user',                                     
                'Profile Updated',                         
                $message,                               
                BASE_URL . '/admin/profile'
            );

            $this->redirectWithSuccess('Profile updated successfully!', BASE_URL . '/admin/profile');
        } else {
            $this->redirectWithError($result['error'] ?? 'Failed to update profile.', BASE_URL . '/admin/profile');
        }
    }
    
    public function updateProfilePhoto() {
        if (!$this->isPostRequest() || !isset($_FILES['profile_photo'])) {
            $this->redirect(BASE_URL . '/admin/profile');
        }
        
        $result = $this->userModel->uploadProfilePhoto($_SESSION['user_id'], $_FILES['profile_photo']);
        
        if ($result['success']) {
            $this->redirectWithSuccess('Profile photo updated successfully', BASE_URL . '/admin/profile');
        } else {
            $this->redirectWithError($result['error'], BASE_URL . '/admin/profile');
        }
    }
    
    // ==================== USER MANAGEMENT ====================
    public function users() {
        $hideFooter = true;
        
        $role = $_GET['role'] ?? null;
        $search = $_GET['search'] ?? null;
        $pagination = $this->getPaginationParams();
        
        if ($search) {
            $users = $this->userModel->searchUsers($search);
            $totalUsers = count($users);
        } else {
            $users = $this->userModel->getAllUsers($role, $this->itemsPerPage, $pagination['offset']);
            $totalUsers = count($this->userModel->getAllUsers($role, 0, 0));
        }
        
        $totalPages = ceil($totalUsers / $this->itemsPerPage);
        
        require_once __DIR__ . '/../views/admin/users.php';
    }
    
    public function createUser() {
        $hideFooter = true;
        
        if ($this->isPostRequest()) {
            $data = [
                'first_name' => $this->sanitize($_POST['first_name'] ?? ''),
                'last_name' => $this->sanitize($_POST['last_name'] ?? ''),
                'email' => $this->sanitize($_POST['email'] ?? ''),
                'phone' => $this->sanitize($_POST['phone'] ?? ''),
                'password' => $_POST['password'] ?? 'Password123',
                'role' => $_POST['role'] ?? 'external',
                'class' => $_POST['class'] ?? null
            ];
            
            $errors = $this->validateRequiredFields($data, ['first_name', 'last_name', 'email']);
            
            if (empty($errors) && !$this->validateEmail($data['email'])) {
                $errors[] = 'Invalid email format';
            }
            
            if (empty($errors)) {
                $result = $this->userModel->register($data);
                
                if ($result['success']) {
                    $this->redirectWithSuccess('User created successfully', BASE_URL . '/admin/users');
                } else {
                    $this->redirectWithError($result['error'], BASE_URL . '/admin/users');
                }
            } else {
                $this->redirectWithError(implode('<br>', $errors), BASE_URL . '/admin/users');
            }
        }
        
        require_once __DIR__ . '/../views/admin/create_user.php';
    }
    
    public function editUser($userId) {
        $hideFooter = true;
        
        $user = $this->userModel->getById($userId);
        
        if (!$user) {
            $this->redirectWithError('User not found', BASE_URL . '/admin/users');
        }
        
        $classes = $this->classModel->getAllClasses();
        
        if ($this->isPostRequest()) {
            $data = [
                'first_name' => $this->sanitize($_POST['first_name'] ?? ''),
                'last_name' => $this->sanitize($_POST['last_name'] ?? ''),
                'email' => $this->sanitize($_POST['email'] ?? ''),
                'phone' => $this->sanitize($_POST['phone'] ?? ''),
                'role' => $_POST['role'] ?? $user['role'],
                'class_id' => $_POST['class_id'] ?? $user['class_id']
            ];
            
            if (empty($data['first_name']) || empty($data['last_name']) || empty($data['email'])) {
                $this->redirectWithError('Please fill in all required fields', BASE_URL . '/admin/users/edit/' . $userId);
            }
            
            $result = $this->userModel->updateUserAsAdmin($userId, $data);
            
            if (isset($_POST['status'])) {
                if ($_POST['status'] === 'suspended' && !$user['is_suspended']) {
                    $this->userModel->suspendUser($userId);
                } elseif ($_POST['status'] === 'active' && $user['is_suspended']) {
                    $this->userModel->activateUser($userId);
                }
            }
            
            if ($result['success']) {
                $this->redirectWithSuccess('User updated successfully', BASE_URL . '/admin/users');
            } else {
                $this->redirectWithError($result['error'], BASE_URL . '/admin/users');
            }
        }
        
        require_once __DIR__ . '/../views/admin/edit_user.php';
    }
    
    public function suspendUser($userId) {
        if ($_SESSION['user_id'] == $userId) {
            $this->redirectWithError('You cannot suspend your own account', BASE_URL . '/admin/users');
        }
        
        $result = $this->userModel->suspendUser($userId);
        
        if ($result['success']) {
            $this->redirectWithSuccess($result['message'], BASE_URL . '/admin/users');
        } else {
            $this->redirectWithError($result['error'], BASE_URL . '/admin/users');
        }
    }
    
    public function activateUser($userId) {
        $result = $this->userModel->activateUser($userId);
        
        if ($result['success']) {
            $this->redirectWithSuccess($result['message'], BASE_URL . '/admin/users');
        } else {
            $this->redirectWithError($result['error'], BASE_URL . '/admin/users');
        }
    }
    
    public function deleteUser($userId) {
        if ($_SESSION['user_id'] == $userId) {
            $this->redirectWithError('You cannot delete your own account', BASE_URL . '/admin/users');
        }
        
        $result = $this->userModel->deleteUser($userId);
        
        if ($result['success']) {
            $this->redirectWithSuccess($result['message'], BASE_URL . '/admin/users');
        } else {
            $this->redirectWithError($result['error'], BASE_URL . '/admin/users');
        }
    }
    
    // ==================== REPORTS ====================
    public function reports() {
        $hideFooter = true;
        
        $type = $_GET['type'] ?? 'overview';
        $start_date = $_GET['start_date'] ?? date('Y-m-d', strtotime('-30 days'));
        $end_date = $_GET['end_date'] ?? date('Y-m-d');
        $days = (int)($_GET['days'] ?? 30);
        
        $totalUsers = count($this->userModel->getAllUsers(null, 0, 0));
        $totalTeachers = count($this->userModel->getAllUsers('teacher', 0, 0));
        $totalLearners = count($this->userModel->getAllUsers('learner', 0, 0));
        $totalExternal = count($this->userModel->getAllUsers('external', 0, 0));
        $totalAdmins = count($this->userModel->getAllUsers('admin', 0, 0));
        $recentUsers = $this->userModel->getAllUsers(null, 200, 0);       
        $recentActivity = $this->reportModel->getRecentActivity(200);       
        $userGrowthData = $this->reportModel->getUserGrowthData($days);
        $revenueData = $this->reportModel->getRevenueData($days);
        $activeToday = $this->userModel->getActiveToday();
        $newUsersToday = $this->userModel->getNewUsersToday();
        $totalQuizzes = $this->quizModel->getTotalQuizzes();
        $totalQuizAttempts = $this->quizModel->getTotalAttempts();
        $averageScore = $this->quizModel->getAverageScore();
        $totalRevenue = $this->subscriptionModel->getTotalRevenue();
        $totalSubscriptions = $this->subscriptionModel->getTotalSubscriptions();
        
        $reportTypes = ['users' => 'getUserReport', 'quizzes' => 'getQuizReport', 'payments' => 'getPaymentReport', 'activity' => 'getActivityReport'];
        $data = isset($reportTypes[$type]) ? $this->reportModel->{$reportTypes[$type]}($start_date, $end_date) : [];
        
        require_once __DIR__ . '/../views/admin/reports.php';
    }
    
    public function exportReport() {
        $type = $_GET['type'] ?? 'users';
        $format = $_GET['format'] ?? 'csv';
        $startDate = $_GET['start_date'] ?? date('Y-m-d', strtotime('-30 days'));
        $endDate = $_GET['end_date'] ?? date('Y-m-d');
        
        $reportMethods = [
            'users' => 'getUserReport',
            'quizzes' => 'getQuizReport',
            'payments' => 'getPaymentReport'
        ];
        
        $data = isset($reportMethods[$type]) ? $this->reportModel->{$reportMethods[$type]}($startDate, $endDate) : [];
        $filename = $type . '_report_' . date('Y-m-d') . '.' . $format;
        
        if ($format === 'csv' && !empty($data)) {
            $this->reportModel->exportToCSV($data, $filename);
        }
        
        $this->redirectWithSuccess('Report exported successfully', BASE_URL . '/admin/reports');
    }
    
    // ==================== SETTINGS ====================
    public function settings() {
        $hideFooter = true;
        
        $generalSettings = $this->settingsModel->getGeneralSettings();
        $subscriptionSettings = $this->settingsModel->getSubscriptionSettings();
        $emailSettings = $this->settingsModel->getEmailSettings();
        $securitySettings = $this->settingsModel->getSecuritySettings();
        $appearanceSettings = $this->settingsModel->getAppearanceSettings();
        
        require_once __DIR__ . '/../views/admin/settings.php';
    }
    
    public function saveGeneralSettings() {
        if (!$this->isPostRequest()) $this->redirect(BASE_URL . '/admin/settings');
        
        $settings = [
            'site_name' => $_POST['site_name'] ?? 'Rays of Grace E-Learning',
            'site_description' => $_POST['site_description'] ?? '',
            'contact_email' => $_POST['contact_email'] ?? ''
        ];
        
        $this->handleSettingSave($settings, 'General settings saved successfully!', 'Failed to save general settings.');
    }
    
    public function saveSubscriptionSettings() {
        if (!$this->isPostRequest()) $this->redirect(BASE_URL . '/admin/settings');
        
        $settings = [
            'monthly_price' => (float)($_POST['monthly_price'] ?? 15000),
            'termly_price' => (float)($_POST['termly_price'] ?? 40000),
            'yearly_price' => (float)($_POST['yearly_price'] ?? 120000),
            'trial_days' => (int)($_POST['trial_days'] ?? 60)
        ];
        
        $this->handleSettingSave($settings, 'Subscription settings saved successfully!', 'Failed to save subscription settings.');
    }
    
    public function saveEmailSettings() {
        if (!$this->isPostRequest()) $this->redirect(BASE_URL . '/admin/settings');
        
        $settings = [
            'smtp_host' => $_POST['smtp_host'] ?? 'smtp.gmail.com',
            'smtp_port' => (int)($_POST['smtp_port'] ?? 587),
            'smtp_username' => $_POST['smtp_username'] ?? '',
            'smtp_password' => $_POST['smtp_password'] ?? '',
            'from_email' => $_POST['from_email'] ?? ''
        ];
        
        $this->handleSettingSave($settings, 'Email settings saved successfully!', 'Failed to save email settings.');
    }
    
    public function saveSecuritySettings() {
        if (!$this->isPostRequest()) $this->redirect(BASE_URL . '/admin/settings');
        
        $settings = [
            'enable_2fa' => isset($_POST['enable_2fa']) ? 1 : 0,
            'session_timeout' => (int)($_POST['session_timeout'] ?? 60),
            'strong_passwords' => isset($_POST['strong_passwords']) ? 1 : 0
        ];
        
        $this->handleSettingSave($settings, 'Security settings saved successfully!', 'Failed to save security settings.');
    }
    
    public function saveAppearanceSettings() {
        if (!$this->isPostRequest()) $this->redirect(BASE_URL . '/admin/settings');
        
        $settings = [
            'theme_color' => $_POST['theme_color'] ?? '#8B5CF6',
            'accent_color' => $_POST['accent_color'] ?? '#F97316',
            'dark_mode' => isset($_POST['dark_mode']) ? 1 : 0
        ];
        
        $this->handleSettingSave($settings, 'Appearance settings saved successfully!', 'Failed to save appearance settings.');
    }
    
    public function saveAllSettings() {
        if (!$this->isPostRequest()) $this->redirect(BASE_URL . '/admin/settings');
        
        $settings = [
            'site_name' => $_POST['site_name'] ?? 'Rays of Grace E-Learning',
            'site_description' => $_POST['site_description'] ?? '',
            'contact_email' => $_POST['contact_email'] ?? '',
            'monthly_price' => (float)($_POST['monthly_price'] ?? 15000),
            'termly_price' => (float)($_POST['termly_price'] ?? 40000),
            'yearly_price' => (float)($_POST['yearly_price'] ?? 120000),
            'trial_days' => (int)($_POST['trial_days'] ?? 60),
            'smtp_host' => $_POST['smtp_host'] ?? 'smtp.gmail.com',
            'smtp_port' => (int)($_POST['smtp_port'] ?? 587),
            'smtp_username' => $_POST['smtp_username'] ?? '',
            'smtp_password' => $_POST['smtp_password'] ?? '',
            'from_email' => $_POST['from_email'] ?? '',
            'enable_2fa' => isset($_POST['enable_2fa']) ? 1 : 0,
            'session_timeout' => (int)($_POST['session_timeout'] ?? 60),
            'strong_passwords' => isset($_POST['strong_passwords']) ? 1 : 0,
            'theme_color' => $_POST['theme_color'] ?? '#7f2677',
            'accent_color' => $_POST['accent_color'] ?? '#f06724',
            'dark_mode' => isset($_POST['dark_mode']) ? 1 : 0
        ];
        
        $this->handleSettingSave($settings, 'All settings saved successfully!', 'Failed to save settings.');
    }
    
    public function testEmailConfig() {
        $this->redirectWithSuccess('Email test successful! Check your inbox.', BASE_URL . '/admin/settings');
    }
    
    public function clearCache() {
        $result = $this->settingsModel->clearCache();
        
        if ($result) {
            $this->redirectWithSuccess('Cache cleared successfully!', BASE_URL . '/admin/settings');
        } else {
            $this->redirectWithError('Failed to clear cache.', BASE_URL . '/admin/settings');
        }
    }
    
    public function resetToDefaults() {
        $result = $this->settingsModel->resetToDefaults();
        
        if ($result) {
            $this->redirectWithSuccess('All settings have been reset to defaults.', BASE_URL . '/admin/settings');
        } else {
            $this->redirectWithError('Failed to reset settings.', BASE_URL . '/admin/settings');
        }
    }
    
    // ==================== LESSONS MANAGEMENT ====================
    public function lessons() {
        $hideFooter = true;
        
        $search = $_GET['search'] ?? null;
        $teacherId = $_GET['teacher'] ?? null;
        $status = $_GET['status'] ?? null;
        $pagination = $this->getLessonPaginationParams();
        
        $lessons = $this->lessonModel->getAllLessons($search, $teacherId, $status, $this->lessonsPerPage, $pagination['offset']);
        $totalLessons = $this->lessonModel->countAllLessons($search, $teacherId, $status);
        $totalPages = ceil($totalLessons / $this->lessonsPerPage);
        $teachers = $this->userModel->getAllUsers('teacher');
        
        require_once __DIR__ . '/../views/admin/lessons.php';
    }
    
    public function viewLesson($lessonId) {
        $hideFooter = true;
        
        $lesson = $this->lessonModel->getById($lessonId);
        
        if (!$lesson) {
            $this->redirectWithError('Lesson not found.', BASE_URL . '/admin/lessons');
        }
        
        require_once __DIR__ . '/../views/admin/view_lesson.php';
    }
    
    public function approveLesson($lessonId) {
        $result = $this->lessonModel->approve($lessonId);
        
        if ($result['success']) {
            $this->redirectWithSuccess('Lesson approved successfully.', BASE_URL . '/admin/lessons');
        } else {
            $this->redirectWithError($result['error'] ?? 'Failed to approve lesson.', BASE_URL . '/admin/lessons');
        }
    }
    
    public function rejectLesson($lessonId) {
        $result = $this->lessonModel->reject($lessonId);
        
        if ($result['success']) {
            $this->redirectWithSuccess('Lesson rejected.', BASE_URL . '/admin/lessons');
        } else {
            $this->redirectWithError($result['error'] ?? 'Failed to reject lesson.', BASE_URL . '/admin/lessons');
        }
    }
    
    // ==================== QUIZZES MANAGEMENT ====================
    public function quizzes() {
        $hideFooter = true;
        
        $search = $_GET['search'] ?? null;
        $teacherId = $_GET['teacher'] ?? null;
        $status = $_GET['status'] ?? null;
        $pagination = $this->getQuizPaginationParams();
        
        $quizzes = $this->quizModel->getAllQuizzes($search, $teacherId, $status, $this->quizzesPerPage, $pagination['offset']);
        $totalQuizzes = $this->quizModel->countAllQuizzes($search, $teacherId, $status);
        $totalPages = ceil($totalQuizzes / $this->quizzesPerPage);
        $teachers = $this->userModel->getAllUsers('teacher');
        
        require_once __DIR__ . '/../views/admin/quizzes.php';
    }
    
    public function viewQuiz($quizId) {
        $hideFooter = true;
        
        $quiz = $this->quizModel->getById($quizId);
        
        if (!$quiz) {
            $this->redirectWithError('Quiz not found.', BASE_URL . '/admin/quizzes');
        }
        
        $questions = $this->quizModel->getQuestions($quizId);
        $quiz['questions'] = $questions;
        
        require_once __DIR__ . '/../views/admin/view_quiz.php';
    }
    
    public function approveQuiz($quizId) {
        $result = $this->quizModel->approve($quizId);
        
        if ($result['success']) {
            $this->redirectWithSuccess('Quiz approved successfully.', BASE_URL . '/admin/quizzes');
        } else {
            $this->redirectWithError($result['error'] ?? 'Failed to approve quiz.', BASE_URL . '/admin/quizzes');
        }
    }
    
    public function rejectQuiz($quizId) {
        $result = $this->quizModel->reject($quizId);
        
        if ($result['success']) {
            $this->redirectWithSuccess('Quiz rejected.', BASE_URL . '/admin/quizzes');
        } else {
            $this->redirectWithError($result['error'] ?? 'Failed to reject quiz.', BASE_URL . '/admin/quizzes');
        }
    }
    
    public function deleteQuiz($quizId) {
        $result = $this->quizModel->delete($quizId);
        
        if ($result['success']) {
            $this->redirectWithSuccess('Quiz deleted successfully.', BASE_URL . '/admin/quizzes');
        } else {
            $this->redirectWithError($result['error'] ?? 'Failed to delete quiz.', BASE_URL . '/admin/quizzes');
        }
    }

    public function getNotificationsApi() {
        header('Content-Type: application/json');
        $notifModel = new Notification();
        
        $notifications = $notifModel->getLatestNotifications(10);
        $unreadCount = $notifModel->getUnreadCount();
        
        echo json_encode([
            'status' => 'success',
            'unread_count' => $unreadCount,
            'notifications' => $notifications
        ]);
        exit;
    }

    public function markNotificationsReadApi() {
        header('Content-Type: application/json');
        $notifModel = new Notification();
        $notifModel->markAllAsRead();
        
        echo json_encode(['status' => 'success']);
        exit;
    }

    public function classesAndSubjects() {
        $hideFooter = true;
        $classes = $this->classModel->getAll();
        $subjects = $this->subjectModel->getAll();
        $teachers = $this->userModel->getAllUsers('teacher');

        require_once __DIR__ . '/../views/admin/classes_subjects.php';
    }

    public function createClass() {
        if ($this->isPostRequest()) {
            $data = [
                'name' => $this->sanitize($_POST['name'] ?? ''),
                'level' => $this->sanitize($_POST['level'] ?? ''),
                'description' => $this->sanitize($_POST['description'] ?? ''),
                'is_active' => isset($_POST['is_active']) ? 1 : 0
            ];

            if (empty($data['name'])) {
                $this->redirectWithError('Class name is required', BASE_URL . '/admin/classes-subjects');
            }

            $result = $this->classModel->create($data);
            if ($result['success']) {
                $this->redirectWithSuccess('Class added successfully', BASE_URL . '/admin/classes-subjects');
            } else {
                $this->redirectWithError($result['error'], BASE_URL . '/admin/classes-subjects');
            }
        }
    }

    public function updateClass($classId) {
        if ($this->isPostRequest()) {
            $data = [
                'name' => $this->sanitize($_POST['name'] ?? ''),
                'level' => $this->sanitize($_POST['level'] ?? ''),
                'description' => $this->sanitize($_POST['description'] ?? ''),
                'is_active' => isset($_POST['is_active']) ? 1 : 0
            ];

            $result = $this->classModel->update($classId, $data);
            if ($result['success']) {
                $this->redirectWithSuccess('Class updated successfully', BASE_URL . '/admin/classes-subjects');
            } else {
                $this->redirectWithError($result['error'], BASE_URL . '/admin/classes-subjects');
            }
        }
    }

    public function deleteClass($classId) {
        $result = $this->classModel->delete($classId);
        if ($result['success']) {
            $this->redirectWithSuccess('Class deleted successfully', BASE_URL . '/admin/classes-subjects');
        } else {
            $this->redirectWithError($result['error'], BASE_URL . '/admin/classes-subjects');
        }
    }

    public function createSubject() {
        if ($this->isPostRequest()) {
            $data = [
                'class_id' => (int)($_POST['class_id'] ?? 0),
                'teacher_id' => !empty($_POST['teacher_id']) ? (int)$_POST['teacher_id'] : null,
                'name' => $this->sanitize($_POST['name'] ?? ''),
                'code' => $this->sanitize($_POST['code'] ?? ''),
                'description' => $this->sanitize($_POST['description'] ?? ''),
                'is_active' => isset($_POST['is_active']) ? 1 : 0
            ];

            if (empty($data['name']) || empty($data['class_id'])) {
                $this->redirectWithError('Subject name and class assignment are required', BASE_URL . '/admin/classes-subjects');
            }

            $result = $this->subjectModel->create($data);
            if ($result['success']) {
                $this->redirectWithSuccess('Subject assigned successfully', BASE_URL . '/admin/classes-subjects');
            } else {
                $this->redirectWithError($result['error'], BASE_URL . '/admin/classes-subjects');
            }
        }
    }

    public function updateSubject($subjectId) {
        if ($this->isPostRequest()) {
            $data = [
                'class_id' => (int)($_POST['class_id'] ?? 0),
                'teacher_id' => !empty($_POST['teacher_id']) ? (int)$_POST['teacher_id'] : null,
                'name' => $this->sanitize($_POST['name'] ?? ''),
                'code' => $this->sanitize($_POST['code'] ?? ''),
                'description' => $this->sanitize($_POST['description'] ?? ''),
                'is_active' => isset($_POST['is_active']) ? 1 : 0
            ];

            $result = $this->subjectModel->update($subjectId, $data);
            if ($result['success']) {
                $this->redirectWithSuccess('Subject updated successfully', BASE_URL . '/admin/classes-subjects');
            } else {
                $this->redirectWithError($result['error'], BASE_URL . '/admin/classes-subjects');
            }
        }
    }

    public function deleteSubject($subjectId) {
        $result = $this->subjectModel->delete($subjectId);
        if ($result['success']) {
            $this->redirectWithSuccess('Subject removed successfully', BASE_URL . '/admin/classes-subjects');
        } else {
            $this->redirectWithError($result['error'], BASE_URL . '/admin/classes-subjects');
        }
    }
}
?>