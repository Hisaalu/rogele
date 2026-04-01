<?php
// File: /controllers/LearnerController.php
require_once __DIR__ . '/../models/Lesson.php';
require_once __DIR__ . '/../models/Quiz.php';
require_once __DIR__ . '/../models/User.php';

class LearnerController {
    private $lessonModel;
    private $quizModel;
    private $userModel;
    
    public function __construct() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . BASE_URL . '/login');
            exit;
        }
        
        if ($_SESSION['user_role'] !== 'learner') {
            $this->redirectToRoleDashboard();
            exit;
        }
        
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
            case 'external':
                header('Location: ' . BASE_URL . '/external/dashboard');
                break;
            default:
                header('Location: ' . BASE_URL . '/login');
        }
        exit;
    }
    
    public function dashboard() {
        $user = $this->userModel->getById($_SESSION['user_id']);
        $lessons = $this->lessonModel->getByClass($user['class_id'] ?? null, 5);
        $quizResults = $this->quizModel->getUserResults($_SESSION['user_id']);
        
        require_once __DIR__ . '/../views/learner/dashboard.php';
    }
    
    public function materials() {
        $user = $this->userModel->getById($_SESSION['user_id']);
        $subject = $_GET['subject'] ?? null;
        $search = $_GET['search'] ?? null;
        
        if ($search) {
            $lessons = $this->lessonModel->search($search, $user['class_id'] ?? null);
        } else {
            $lessons = $this->lessonModel->getByClass($user['class_id'] ?? null);
        }
        
        require_once __DIR__ . '/../views/learner/materials.php';
    }
    
    public function quizzes() {
        $user = $this->userModel->getById($_SESSION['user_id']);
        $results = $this->quizModel->getUserResults($_SESSION['user_id']);
        
        require_once __DIR__ . '/../views/learner/quizzes.php';
    }
    
    public function progress() {
        require_once __DIR__ . '/../views/learner/progress.php';
    }
}
?>