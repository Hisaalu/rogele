<?php
// File: /controllers/AdminHomeworkController.php
require_once __DIR__ . '/../models/Homework.php';
require_once __DIR__ . '/../models/Classes.php';
require_once __DIR__ . '/../models/User.php';

class AdminHomeworkController {
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

        if ($_SESSION['user_role'] !== 'admin') {
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

        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $status = isset($_GET['status']) && !empty($_GET['status']) ? $_GET['status'] : null;
        $search = isset($_GET['search']) ? trim($_GET['search']) : null;
        $teacherFilter = isset($_GET['teacher']) && !empty($_GET['teacher']) ? (int)$_GET['teacher'] : null;
        $classFilter = isset($_GET['class_id']) && !empty($_GET['class_id']) ? (int)$_GET['class_id'] : null;

        $limit = 20;
        $offset = ($page - 1) * $limit;

        $teachers = $this->userModel->getByRole('teacher');
        $classes = $this->classModel->getAll();

        $homeworks = $this->getAdminHomeworks($teacherFilter, $status, $search, $classFilter, $limit, $offset);
        $total = $this->countAdminHomeworks($teacherFilter, $status, $search, $classFilter);
        $totalPages = ceil($total / $limit);

        require_once __DIR__ . '/../views/admin/homework/index.php';
    }

    public function view($id) {
        $hideFooter = true;

        $homework = $this->homeworkModel->getById($id);

        if (!$homework) {
            $_SESSION['error'] = 'Homework not found.';
            header('Location: ' . BASE_URL . '/admin/homework');
            exit;
        }

        $submissions = $this->homeworkModel->getHomeworkSubmissions($id);

        require_once __DIR__ . '/../views/admin/homework/view.php';
    }

    public function toggleStatus($id) {
        $homework = $this->homeworkModel->getById($id);

        if (!$homework) {
            $_SESSION['error'] = 'Homework not found.';
            header('Location: ' . BASE_URL . '/admin/homework');
            exit;
        }

        $newStatus = $homework['is_active'] ? 0 : 1;
        $result = $this->homeworkModel->updateSubmissionStatus($id, $newStatus);

        if ($result) {
            $_SESSION['success'] = 'Homework status updated successfully.';
        } else {
            $_SESSION['error'] = 'Failed to update homework status.';
        }

        header('Location: ' . BASE_URL . '/admin/homework');
        exit;
    }

    public function delete($id) {
        $homework = $this->homeworkModel->getById($id);

        if (!$homework) {
            $_SESSION['error'] = 'Homework not found.';
            header('Location: ' . BASE_URL . '/admin/homework');
            exit;
        }

        $result = $this->homeworkModel->delete($id, $homework['teacher_id']);

        if ($result['success']) {
            $_SESSION['success'] = 'Homework deleted successfully.';
        } else {
            $_SESSION['error'] = $result['error'] ?? 'Failed to delete homework.';
        }

        header('Location: ' . BASE_URL . '/admin/homework');
        exit;
    }

    private function getAdminHomeworks($teacherId = null, $status = null, $search = null, $classId = null, $limit = 20, $offset = 0) {
        try {
            $query = "
                SELECT 
                    h.*,
                    c.name as class_name,
                    s.name as subject_name,
                    CONCAT_WS(' ', u.first_name, u.last_name) as teacher_name,
                    (SELECT COUNT(*) FROM homework_submissions WHERE homework_id = h.id) as submissions_count
                FROM homework h
                LEFT JOIN classes c ON h.class_id = c.id
                LEFT JOIN subjects s ON h.subject_id = s.id
                LEFT JOIN users u ON h.teacher_id = u.id
                WHERE 1=1
            ";

            if (!empty($teacherId)) {
                $query .= " AND h.teacher_id = :teacher_id";
            }
            if (!empty($classId)) {
                $query .= " AND h.class_id = :class_id";
            }
            if ($status === 'active') {
                $query .= " AND h.due_date > NOW() AND h.is_active = 1";
            } elseif ($status === 'expired') {
                $query .= " AND h.due_date < NOW()";
            } elseif ($status === 'disabled') {
                $query .= " AND h.is_active = 0";
            }
            if (!empty($search)) {
                $query .= " AND CONCAT_WS(' ', h.title, h.description) LIKE :search";
            }

            $query .= " ORDER BY h.created_at DESC LIMIT :limit OFFSET :offset";

            $stmt = $this->conn->prepare($query);

            if (!empty($teacherId)) {
                $stmt->bindValue(':teacher_id', $teacherId, PDO::PARAM_INT);
            }
            if (!empty($classId)) {
                $stmt->bindValue(':class_id', $classId, PDO::PARAM_INT);
            }
            if (!empty($search)) {
                $stmt->bindValue(':search', '%' . $search . '%', PDO::PARAM_STR);
            }

            $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);

            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error in getAdminHomeworks: " . $e->getMessage());
            return [];
        }
    }

    private function countAdminHomeworks($teacherId = null, $status = null, $search = null, $classId = null) {
        try {
            $query = "SELECT COUNT(*) as total FROM homework h WHERE 1=1";

            if (!empty($teacherId)) {
                $query .= " AND h.teacher_id = :teacher_id";
            }
            if (!empty($classId)) {
                $query .= " AND h.class_id = :class_id";
            }
            if ($status === 'active') {
                $query .= " AND h.due_date > NOW() AND h.is_active = 1";
            } elseif ($status === 'expired') {
                $query .= " AND h.due_date < NOW()";
            } elseif ($status === 'disabled') {
                $query .= " AND h.is_active = 0";
            }
            if (!empty($search)) {
                $query .= " AND CONCAT_WS(' ', h.title, h.description) LIKE :search";
            }

            $stmt = $this->conn->prepare($query);

            if (!empty($teacherId)) {
                $stmt->bindValue(':teacher_id', $teacherId, PDO::PARAM_INT);
            }
            if (!empty($classId)) {
                $stmt->bindValue(':class_id', $classId, PDO::PARAM_INT);
            }
            if (!empty($search)) {
                $stmt->bindValue(':search', '%' . $search . '%', PDO::PARAM_STR);
            }

            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['total'] ?? 0;
        } catch (PDOException $e) {
            return 0;
        }
    }

    public function getHomeworkById($id) {
        $sql = "SELECT 
                    h.*, 
                    c.name AS class_name, 
                    s.name AS subject_name,
                    CONCAT(u.first_name, ' ', u.last_name) AS teacher_name
                FROM homework h
                LEFT JOIN classes c ON h.class_id = c.id
                LEFT JOIN subjects s ON h.subject_id = s.id
                LEFT JOIN users u ON h.teacher_id = u.id
                WHERE h.id = :id";
                
        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}