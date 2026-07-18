<?php
// File: /controllers/ExternalHomeworkController.php
require_once __DIR__ . '/../models/Homework.php';
require_once __DIR__ . '/../models/Classes.php';
require_once __DIR__ . '/../models/User.php';

class ExternalHomeworkController {
    private $homeworkModel;
    private $classModel;
    private $userModel;
    private $db;
    private $conn;
    
    public function __construct() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . BASE_URL . '/login');
            exit;
        }
        
        if (!in_array($_SESSION['user_role'], ['external', 'learner'])) {
            header('Location: ' . BASE_URL . '/dashboard');
            exit;
        }
        
        $this->homeworkModel = new Homework();
        $this->classModel = new Classes();
        $this->userModel = new User();
        $this->db = Database::getInstance();
        $this->conn = $this->db->getConnection();
    }
    
    public function index() {
        $hideFooter = true;
        $studentId = $_SESSION['user_id'];
        $user = $this->userModel->getById($studentId);
        $classId = $user['class_id'] ?? null;
        
        $status = $_GET['status'] ?? '';

        $allHomeworks = $this->homeworkModel->getByStudent($studentId, $classId, null);
        
        $stats = [
            'total' => count($allHomeworks),
            'pending' => 0,
            'submitted' => 0,
            'graded' => 0,
            'late' => 0
        ];
        
        $filteredHomeworks = [];
        $now = time();
        
        foreach ($allHomeworks as $hw) {
            $isLate = !($hw['submission_id'] ?? null) && strtotime($hw['due_date']) < $now;
            $subStatus = $hw['submission_status'] ?? 'pending';
            
            if ($subStatus === 'graded') {
                $computedStatus = 'graded';
            } elseif ($subStatus === 'submitted') {
                $computedStatus = 'submitted';
            } elseif ($isLate) {
                $computedStatus = 'late';
            } else {
                $computedStatus = 'pending';
            }
            
            $stats[$computedStatus]++;
            
            if (empty($status) || $status === $computedStatus) {
                $filteredHomeworks[] = $hw;
            }
        }
        
        $homeworks = $filteredHomeworks;
        $currentStatus = $status;
        
        require_once __DIR__ . '/../views/external/homework/index.php';
    }
    
    public function view($homeworkId) {
        $hideFooter = true;
        $studentId = $_SESSION['user_id'];
        
        $homework = $this->homeworkModel->getById($homeworkId);
        
        if (!$homework) {
            $_SESSION['error'] = 'Homework not found';
            header('Location: ' . BASE_URL . '/external/homework');
            exit;
        }
        
        $user = $this->userModel->getById($studentId);
        if ($homework['class_id'] != ($user['class_id'] ?? null)) {
            $_SESSION['error'] = 'You do not have access to this homework';
            header('Location: ' . BASE_URL . '/external/homework');
            exit;
        }
        
        $submission = $this->homeworkModel->getStudentSubmission($homeworkId, $studentId);
        
        require_once __DIR__ . '/../views/external/homework/view.php';
    }
    
    public function submit($homeworkId) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/external/homework');
            exit;
        }
        
        $studentId = $_SESSION['user_id'];
        $textAnswer = $_POST['text_answer'] ?? '';
        $files = $_FILES['submission_files'] ?? null;
        
        $result = $this->homeworkModel->submitHomework($homeworkId, $studentId, $textAnswer, $files);
        
        if ($result['success']) {
            $_SESSION['success'] = 'Homework submitted successfully!';
        } else {
            $_SESSION['error'] = $result['error'] ?? 'Failed to submit homework';
        }
        
        header('Location: ' . BASE_URL . '/external/homework/view/' . $homeworkId);
        exit;
    }
    
    public function downloadAttachment($attachmentId) {
        try {
            $stmt = $this->conn->prepare("
                SELECT file_path, file_name FROM homework_attachments WHERE id = ?
            ");
            $stmt->execute([$attachmentId]);
            $file = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$file) {
                throw new Exception('File not found');
            }
            
            $filePath = __DIR__ . '/../public/' . $file['file_path'];
            
            if (!file_exists($filePath)) {
                throw new Exception('File not found on server');
            }
            
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="' . $file['file_name'] . '"');
            header('Content-Length: ' . filesize($filePath));
            readfile($filePath);
            exit;
            
        } catch (Exception $e) {
            $_SESSION['error'] = $e->getMessage();
            header('Location: ' . BASE_URL . '/external/homework');
            exit;
        }
    }

    public function deleteSubmission($submissionId) {
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'DELETE') {
            echo json_encode(['success' => false, 'error' => 'Invalid request method']);
            exit;
        }
        
        $studentId = $_SESSION['user_id'];
        
        try {
            $stmt = $this->conn->prepare("
                SELECT status 
                FROM homework_submissions 
                WHERE id = ? AND student_id = ?
            ");
            $stmt->execute([$submissionId, $studentId]);
            $submission = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$submission) {
                echo json_encode(['success' => false, 'error' => 'Submission not found or unauthorized']);
                exit;
            }
            
            if ($submission['status'] === 'graded') {
                echo json_encode(['success' => false, 'error' => 'Cannot delete a graded submission']);
                exit;
            }
            
            $result = $this->homeworkModel->deleteSubmission($submissionId, $studentId);
            
            if ($result['success']) {
                echo json_encode(['success' => true, 'message' => 'Submission deleted successfully']);
            } else {
                echo json_encode(['success' => false, 'error' => $result['error'] ?? 'Failed to delete submission files']);
            }
            
        } catch (Exception $e) {
            error_log("Delete submission controller error: " . $e->getMessage());
            echo json_encode(['success' => false, 'error' => 'An error occurred during submission removal']);
        }
        exit;
    }
}