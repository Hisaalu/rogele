<?php
// File: /controllers/TeacherHomeworkController.php
require_once __DIR__ . '/../models/Homework.php';
require_once __DIR__ . '/../models/Classes.php';
require_once __DIR__ . '/../models/Subject.php';
require_once __DIR__ . '/../models/User.php';

class TeacherHomeworkController {
    private $homeworkModel;
    private $classModel;
    private $subjectModel;
    private $userModel;
    private $db;
    private $conn;
    
    public function __construct() {
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'teacher') {
            header('Location: ' . BASE_URL . '/login');
            exit;
        }
        
        $this->homeworkModel = new Homework();
        $this->classModel = new Classes();
        $this->subjectModel = new Subject();
        $this->userModel = new User();
        $this->db = Database::getInstance();
        $this->conn = $this->db->getConnection();
    }
    
    public function index() {
        $hideFooter = true;
        $teacherId = $_SESSION['user_id'];
        
        $page = $_GET['page'] ?? 1;
        $status = isset($_GET['status']) && !empty($_GET['status']) ? $_GET['status'] : null;
        $limit = 20;
        $offset = ($page - 1) * $limit;
        
        $homeworks = $this->homeworkModel->getTeacherHomeworks($teacherId, $status, $limit, $offset);
        $total = $this->homeworkModel->countByTeacher($teacherId, $status);
        $totalPages = ceil($total / $limit);
        
        $currentStatus = $status;
        
        require_once __DIR__ . '/../views/teacher/homework/index.php';
    }
    
    public function create() {
        $hideFooter = true;
        $teacherId = $_SESSION['user_id'];
        
        $classes = $this->classModel->getAll();
        $allSubjects = $this->subjectModel->getAll();
        $subjectsByClass = [];
        foreach ($allSubjects as $subject) {
            $classId = $subject['class_id'];
            if (!isset($subjectsByClass[$classId])) {
                $subjectsByClass[$classId] = [];
            }
            $subjectsByClass[$classId][] = $subject;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'teacher_id' => $teacherId,
                'class_id' => $_POST['class_id'],
                'subject_id' => $_POST['subject_id'],
                'title' => $_POST['title'],
                'description' => $_POST['description'] ?? '',
                'due_date' => $_POST['due_date']
            ];
            
            $files = $_FILES['attachments'] ?? null;
            
            $result = $this->homeworkModel->create($data, $files);
            
            if ($result['success']) {
                $_SESSION['success'] = 'Homework created successfully!';
                header('Location: ' . BASE_URL . '/teacher/homework');
                exit;
            } else {
                $_SESSION['error'] = $result['error'] ?? 'Failed to create homework';
            }
        }
        
        require_once __DIR__ . '/../views/teacher/homework/create.php';
    }
    
    public function edit($id) {
        $hideFooter = true;
        $teacherId = $_SESSION['user_id'];
        
        $homework = $this->homeworkModel->getById($id);
        
        if (!$homework || $homework['teacher_id'] != $teacherId) {
            $_SESSION['error'] = 'Homework not found';
            header('Location: ' . BASE_URL . '/teacher/homework');
            exit;
        }
        
        $classes = $this->classModel->getAll();
        $allSubjects = $this->subjectModel->getAll();
        
        $subjectsByClass = [];
        foreach ($allSubjects as $subject) {
            $classId = $subject['class_id'];
            if (!isset($subjectsByClass[$classId])) {
                $subjectsByClass[$classId] = [];
            }
            $subjectsByClass[$classId][] = $subject;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'teacher_id' => $teacherId,
                'class_id' => $_POST['class_id'],
                'subject_id' => $_POST['subject_id'],
                'title' => $_POST['title'],
                'description' => $_POST['description'] ?? '',
                'due_date' => $_POST['due_date'],
                'new_files' => $_FILES['new_attachments'] ?? null
            ];
            
            $result = $this->homeworkModel->update($id, $data);
            
            if ($result['success']) {
                $_SESSION['success'] = 'Homework updated successfully!';
                header('Location: ' . BASE_URL . '/teacher/homework');
                exit;
            } else {
                $_SESSION['error'] = $result['error'] ?? 'Failed to update homework';
            }
        }
        
        require_once __DIR__ . '/../views/teacher/homework/edit.php';
    }
    
    public function delete($id) {
        $teacherId = $_SESSION['user_id'];
        $result = $this->homeworkModel->delete($id, $teacherId);
        
        if ($result['success']) {
            $_SESSION['success'] = 'Homework deleted successfully!';
        } else {
            $_SESSION['error'] = $result['error'] ?? 'Failed to delete homework';
        }
        
        header('Location: ' . BASE_URL . '/teacher/homework');
        exit;
    }
    
    public function deleteAttachment($attachmentId) {
        $teacherId = $_SESSION['user_id'];
        $result = $this->homeworkModel->deleteAttachment($attachmentId, $teacherId);
        
        header('Content-Type: application/json');
        echo json_encode($result);
        exit;
    }
    
    public function submissions($homeworkId) {
        $hideFooter = true;
        $teacherId = $_SESSION['user_id'];
        
        $homework = $this->homeworkModel->getById($homeworkId);
        
        if (!$homework || $homework['teacher_id'] != $teacherId) {
            $_SESSION['error'] = 'Homework not found';
            header('Location: ' . BASE_URL . '/teacher/homework');
            exit;
        }
        
        $submissions = $this->homeworkModel->getSubmissions($homeworkId, $teacherId);
        
        require_once __DIR__ . '/../views/teacher/homework/submissions.php';
    }
    
    public function gradeSubmission() {
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'error' => 'Invalid request']);
            exit;
        }
        
        $teacherId = $_SESSION['user_id'];
        $submissionId = $_POST['submission_id'] ?? 0;
        $grade = floatval($_POST['grade'] ?? 0);
        $feedback = $_POST['feedback'] ?? '';
        
        $result = $this->homeworkModel->gradeSubmission($submissionId, $teacherId, $grade, $feedback);
        
        if ($result['success']) {
            echo json_encode(['success' => true, 'message' => 'Submission graded successfully']);
        } else {
            echo json_encode(['success' => false, 'error' => $result['error'] ?? 'Failed to grade']);
        }
        exit;
    }
    
    public function downloadFile($fileId) {
        $teacherId = $_SESSION['user_id'];
        
        try {
            $stmt = $this->conn->prepare("
                SELECT hsf.file_path, hsf.file_name 
                FROM homework_submission_files hsf
                INNER JOIN homework_submissions hs ON hsf.submission_id = hs.id
                INNER JOIN homework h ON hs.homework_id = h.id
                WHERE hsf.id = ? AND h.teacher_id = ?
            ");
            $stmt->execute([$fileId, $teacherId]);
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
            header('Location: ' . BASE_URL . '/teacher/homework');
            exit;
        }
    }

    public function toggleSubmissions() {
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'error' => 'Invalid request method']);
            exit;
        }
        
        $teacherId = $_SESSION['user_id'];
        $data = json_decode(file_get_contents('php://input'), true);
        $homeworkId = $data['homework_id'] ?? 0;
        $isActive = $data['is_active'] ?? 1;
        
        $homework = $this->homeworkModel->getById($homeworkId);
        
        if (!$homework || $homework['teacher_id'] != $teacherId) {
            echo json_encode(['success' => false, 'error' => 'Homework not found']);
            exit;
        }
        
        $result = $this->homeworkModel->updateSubmissionStatus($homeworkId, $isActive);
        
        if ($result) {
            echo json_encode(['success' => true, 'message' => 'Submission status updated']);
        } else {
            echo json_encode(['success' => false, 'error' => 'Failed to update status']);
        }
        exit;
    }
}