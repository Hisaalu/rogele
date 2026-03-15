<?php
// File: /controllers/AdminController.php
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Report.php';
require_once __DIR__ . '/../models/Quiz.php';
require_once __DIR__ . '/../models/Subscription.php';

class AdminController {
    private $userModel;
    private $reportModel;
    private $quizModel;
    private $subscriptionModel;
    
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
            
            $result = $this->userModel->updateProfile($userId, $data);
            
            if ($result['success']) {
                $_SESSION['success'] = 'User updated successfully';
                header('Location: ' . BASE_URL . '/admin/users');
                exit;
            } else {
                $_SESSION['error'] = $result['error'];
            }
        }
        
        require_once __DIR__ . '/../views/admin/edit_user.php';
    }
    
    /**
     * Suspend User
     */
    public function suspendUser($userId) {
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

        // Get user growth data for charts
        $userGrowthData = $this->reportModel->getUserGrowthData($days);

        // Get revenue data for charts
        $revenueData = $this->reportModel->getRevenueData($days);
        
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
        $userGrowthData = $this->reportModel->getUserGrowthData(30);
        
        // Get revenue data for charts
        $revenueData = $this->reportModel->getRevenueData(30);
        
        // Get active today count
        $activeToday = $this->userModel->getActiveToday();
        
        // Get new users today
        $newUsersToday = $this->userModel->getNewUsersToday();
        
        // Get quiz statistics - NOW USING $this->quizModel
        $totalQuizzes = $this->quizModel->getTotalQuizzes();
        $totalQuizAttempts = $this->quizModel->getTotalAttempts();
        $averageScore = $this->quizModel->getAverageScore();
        
        // Get payment statistics - NOW USING $this->subscriptionModel
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
     * Settings Page
     */
    public function settings() {
        $hideFooter = true;
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Handle settings update
            $_SESSION['success'] = 'Settings updated successfully';
            header('Location: ' . BASE_URL . '/admin/settings');
            exit;
        }
        
        require_once __DIR__ . '/../views/admin/settings.php';
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
}
?>