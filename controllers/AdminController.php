// File: /controllers/AdminController.php
<?php
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Report.php';

class AdminController {
    private $userModel;
    private $reportModel;
    
    public function __construct() {
        // Check if user is admin
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
            header('Location: /login');
            exit;
        }
        
        $this->userModel = new User();
        $this->reportModel = new Report();
    }
    
    // Dashboard
    public function dashboard() {
        $analytics = $this->reportModel->getAnalytics();
        require_once __DIR__ . '/../views/admin/dashboard.php';
    }
    
    // User management
    public function users() {
        // Get all users with pagination
        $page = $_GET['page'] ?? 1;
        $limit = 20;
        $offset = ($page - 1) * $limit;
        
        // Get users from database
        require_once __DIR__ . '/../views/admin/users.php';
    }
    
    // Create user
    public function createUser() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Handle user creation
            $data = $_POST;
            
            // Generate registration number for learners
            if ($data['role'] === 'learner') {
                $data['registration_number'] = $this->generateRegistrationNumber($data['class']);
                $data['password'] = $data['registration_number']; // Default password
            }
            
            $result = $this->userModel->register($data);
            
            if ($result['success']) {
                $_SESSION['success'] = 'User created successfully';
            } else {
                $_SESSION['error'] = 'Failed to create user: ' . $result['error'];
            }
            
            header('Location: /admin/users');
            exit;
        }
        
        // Show create user form
        require_once __DIR__ . '/../views/admin/create_user.php';
    }
    
    // Generate registration number
    private function generateRegistrationNumber($class) {
        $prefix = 'ROG';
        $classMap = [
            'P1' => 'P1',
            'P2' => 'P2',
            'P3' => 'P3',
            'P4' => 'P4',
            'P5' => 'P5',
            'P6' => 'P6',
            'P7' => 'P7'
        ];
        
        $classCode = $classMap[$class] ?? 'P1';
        $unique = str_pad(mt_rand(1, 999), 3, '0', STR_PAD_LEFT);
        
        return $prefix . '-' . $classCode . '-' . $unique;
    }
    
    // Reports
    public function reports() {
        $type = $_GET['type'] ?? 'learners';
        $startDate = $_GET['start_date'] ?? date('Y-m-d', strtotime('-30 days'));
        $endDate = $_GET['end_date'] ?? date('Y-m-d');
        
        switch ($type) {
            case 'learners':
                $data = $this->reportModel->learnerPerformance(null, $startDate, $endDate);
                break;
            case 'quizzes':
                $data = $this->reportModel->quizPerformance();
                break;
            case 'payments':
                $data = $this->reportModel->paymentReport($startDate, $endDate);
                break;
            case 'teachers':
                $data = $this->reportModel->teacherActivity($startDate, $endDate);
                break;
            default:
                $data = [];
        }
        
        require_once __DIR__ . '/../views/admin/reports.php';
    }
    
    // Export report
    public function exportReport() {
        $type = $_GET['type'] ?? 'learners';
        $format = $_GET['format'] ?? 'pdf';
        
        // Generate and download report
        // Implementation depends on PDF/Excel library
    }
    
    // System settings
    public function settings() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Update settings
            $_SESSION['success'] = 'Settings updated successfully';
            header('Location: /admin/settings');
            exit;
        }
        
        require_once __DIR__ . '/../views/admin/settings.php';
    }
}
?>