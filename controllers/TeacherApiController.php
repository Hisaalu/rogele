<?php
// File: /controllers/TeacherApiController.php
require_once __DIR__ . '/../models/Quiz.php';
require_once __DIR__ . '/../models/Lesson.php';

class TeacherApiController {
    private $quizModel;
    private $lessonModel;
    
    public function __construct() {
        // Check if user is logged in and is teacher
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'teacher') {
            http_response_code(403);
            echo json_encode(['error' => 'Unauthorized']);
            exit;
        }
        
        $this->quizModel = new Quiz();
        $this->lessonModel = new Lesson();
    }
    
    /**
     * Get quiz performance data for charts
     */
    public function quizPerformance() {
        $teacherId = $_SESSION['user_id'];
        $days = $_GET['days'] ?? 30;
        
        header('Content-Type: application/json');
        
        try {
            $data = $this->quizModel->getDailyPerformance($teacherId, $days);
            
            $labels = [];
            $scores = [];
            $attempts = [];
            
            foreach ($data as $row) {
                $labels[] = date('M d', strtotime($row['date']));
                $scores[] = round($row['avg_score'], 1);
                $attempts[] = $row['attempts'];
            }
            
            echo json_encode([
                'labels' => $labels,
                'scores' => $scores,
                'attempts' => $attempts
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
        exit;
    }
    
    /**
     * Get lesson views data for charts
     */
    public function lessonViews() {
        $teacherId = $_SESSION['user_id'];
        $days = $_GET['days'] ?? 30;
        
        header('Content-Type: application/json');
        
        try {
            $data = $this->lessonModel->getDailyViews($teacherId, $days);
            
            $labels = [];
            $views = [];
            
            foreach ($data as $row) {
                $labels[] = date('M d', strtotime($row['date']));
                $views[] = $row['views'];
            }
            
            echo json_encode([
                'labels' => $labels,
                'views' => $views
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
        exit;
    }
}