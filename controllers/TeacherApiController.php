<?php
// File: /controllers/TeacherApiController.php 

require_once __DIR__ . '/../models/Quiz.php';
require_once __DIR__ . '/../models/Lesson.php';

class TeacherApiController {
    private $quizModel;
    private $lessonModel;
    private $teacherId;
    
    public function __construct() {
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'teacher') {
            http_response_code(403);
            echo json_encode(['error' => 'Unauthorized']);
            exit;
        }
        
        $this->teacherId = $_SESSION['user_id'];
        $this->quizModel = new Quiz();
        $this->lessonModel = new Lesson();
    }
    
    private function sendError(string $message, int $code = 500): void {
        http_response_code($code);
        echo json_encode(['error' => $message]);
        exit;
    }
    
    public function quizPerformance(): void {
        $days = isset($_GET['days']) ? max(1, (int)$_GET['days']) : 30;
        
        header('Content-Type: application/json');
        
        try {
            $data = $this->quizModel->getDailyPerformance($this->teacherId, $days);
            
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
            $this->sendError($e->getMessage());
        }
        exit;
    }
    
    public function lessonViews(): void {
        $days = isset($_GET['days']) ? max(1, (int)$_GET['days']) : 30;
        
        header('Content-Type: application/json');
        
        try {
            $data = $this->lessonModel->getDailyViews($this->teacherId, $days);
            
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
            $this->sendError($e->getMessage());
        }
        exit;
    }
}
?>