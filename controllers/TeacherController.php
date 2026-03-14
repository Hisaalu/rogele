// File: /controllers/TeacherController.php
<?php
require_once __DIR__ . '/../models/Lesson.php';
require_once __DIR__ . '/../models/Quiz.php';

class TeacherController {
    private $lessonModel;
    private $quizModel;
    
    public function __construct() {
        // Check if user is teacher
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'teacher') {
            header('Location: /login');
            exit;
        }
        
        $this->lessonModel = new Lesson();
        $this->quizModel = new Quiz();
    }
    
    // Dashboard
    public function dashboard() {
        // Get teacher's stats
        require_once __DIR__ . '/../views/teacher/dashboard.php';
    }
    
    // Lessons management
    public function lessons() {
        $lessons = $this->lessonModel->getByClass($_SESSION['user_class'] ?? null);
        require_once __DIR__ . '/../views/teacher/lessons.php';
    }
    
    // Create lesson
    public function createLesson() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = $_POST;
            $data['teacher_id'] = $_SESSION['user_id'];
            
            // Handle file uploads
            if (isset($_FILES['materials']) && !empty($_FILES['materials']['name'][0])) {
                $data['files'] = $_FILES['materials'];
            }
            
            $result = $this->lessonModel->create($data);
            
            if ($result['success']) {
                $_SESSION['success'] = 'Lesson created successfully';
                header('Location: /teacher/lessons');
                exit;
            } else {
                $_SESSION['error'] = 'Failed to create lesson: ' . $result['error'];
            }
        }
        
        require_once __DIR__ . '/../views/teacher/create_lesson.php';
    }
    
    // Quizzes management
    public function quizzes() {
        require_once __DIR__ . '/../views/teacher/quizzes.php';
    }
    
    // Create quiz
    public function createQuiz() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = $_POST;
            $data['teacher_id'] = $_SESSION['user_id'];
            
            $result = $this->quizModel->create($data);
            
            if ($result['success']) {
                // Add questions
                if (isset($_POST['questions']) && !empty($_POST['questions'])) {
                    $this->quizModel->addQuestions($result['quiz_id'], $_POST['questions']);
                }
                
                $_SESSION['success'] = 'Quiz created successfully';
                header('Location: /teacher/quizzes');
                exit;
            } else {
                $_SESSION['error'] = 'Failed to create quiz: ' . $result['error'];
            }
        }
        
        require_once __DIR__ . '/../views/teacher/create_quiz.php';
    }
    
    // View quiz results
    public function quizResults($quizId) {
        $stats = $this->quizModel->getQuizStats($quizId);
        require_once __DIR__ . '/../views/teacher/quiz_results.php';
    }
    
    // Analytics
    public function analytics() {
        require_once __DIR__ . '/../views/teacher/analytics.php';
    }
}
?>