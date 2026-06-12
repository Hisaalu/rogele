<?php
// File: /models/Homework.php 
require_once __DIR__ . '/../config/database.php';

class Homework {
    private $db;
    private $conn;
    private $attachmentsCache = [];
    private $submissionFilesCache = [];
    private $homeworkCache = [];
    
    private $homeworkUploadDir = '/../public/uploads/homework/';
    private $submissionUploadDir = '/../public/uploads/submissions/';
    
    public function __construct() {
        $this->db = Database::getInstance();
        $this->conn = $this->db->getConnection();
    }
    
    // ==================== HELPER METHODS ====================
    private function executeQuery($query, $params = [], $fetchAll = true) {
        try {
            $stmt = $this->conn->prepare($query);
            
            foreach ($params as $key => $value) {
                $type = is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR;
                $stmt->bindValue($key, $value, $type);
            }
            
            $stmt->execute();
            return $fetchAll ? $stmt->fetchAll(PDO::FETCH_ASSOC) : $stmt;
        } catch (PDOException $e) {
            error_log("Query error: " . $e->getMessage());
            return $fetchAll ? [] : null;
        }
    }
    
    private function executeUpdate($query, $params, $successMsg = null) {
        try {
            $stmt = $this->conn->prepare($query);
            $result = $stmt->execute($params);
            
            if ($result) {
                return ['success' => true];
            }
            return ['success' => false, 'error' => 'Operation failed'];
        } catch (PDOException $e) {
            error_log("Update error: " . $e->getMessage());
            return ['success' => false, 'error' => 'Database error: ' . $e->getMessage()];
        }
    }
    
    private function invalidateCache($homeworkId = null) {
        if ($homeworkId) {
            unset($this->homeworkCache[$homeworkId]);
            unset($this->attachmentsCache[$homeworkId]);
        } else {
            $this->homeworkCache = [];
            $this->attachmentsCache = [];
        }
        $this->submissionFilesCache = [];
    }
    
    private function getCachedAttachments($homeworkId) {
        if (isset($this->attachmentsCache[$homeworkId])) {
            return $this->attachmentsCache[$homeworkId];
        }
        
        $stmt = $this->conn->prepare("SELECT * FROM homework_attachments WHERE homework_id = ? ORDER BY uploaded_at DESC");
        $stmt->execute([$homeworkId]);
        $this->attachmentsCache[$homeworkId] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $this->attachmentsCache[$homeworkId];
    }
    
    private function getCachedSubmissionFiles($submissionId) {
        if (isset($this->submissionFilesCache[$submissionId])) {
            return $this->submissionFilesCache[$submissionId];
        }
        
        $stmt = $this->conn->prepare("SELECT * FROM homework_submission_files WHERE submission_id = ?");
        $stmt->execute([$submissionId]);
        $this->submissionFilesCache[$submissionId] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $this->submissionFilesCache[$submissionId];
    }
    
    private function uploadFiles($homeworkId, $files, $isSubmission = false, $submissionId = null) {
        $targetDir = __DIR__ . ($isSubmission ? $this->submissionUploadDir : $this->homeworkUploadDir);
        
        if (!file_exists($targetDir)) {
            mkdir($targetDir, 0777, true);
        }
        
        for ($i = 0; $i < count($files['name']); $i++) {
            if ($files['error'][$i] !== UPLOAD_ERR_OK) continue;
            
            $fileName = time() . '_' . ($submissionId ? $submissionId . '_' : '') . preg_replace('/[^a-zA-Z0-9._-]/', '_', basename($files['name'][$i]));
            $targetFile = $targetDir . $fileName;
            
            if (move_uploaded_file($files['tmp_name'][$i], $targetFile)) {
                $dbPath = ($isSubmission ? 'uploads/submissions/' : 'uploads/homework/') . $fileName;
                
                if ($isSubmission && $submissionId) {
                    $stmt = $this->conn->prepare("INSERT INTO homework_submission_files (submission_id, file_name, file_path, file_type, file_size) VALUES (?, ?, ?, ?, ?)");
                    $stmt->execute([$submissionId, $files['name'][$i], $dbPath, $files['type'][$i], $files['size'][$i]]);
                    $this->submissionFilesCache[$submissionId] = null;
                } else {
                    $stmt = $this->conn->prepare("INSERT INTO homework_attachments (homework_id, file_name, file_path, file_type, file_size) VALUES (?, ?, ?, ?, ?)");
                    $stmt->execute([$homeworkId, $files['name'][$i], $dbPath, $files['type'][$i], $files['size'][$i]]);
                    $this->attachmentsCache[$homeworkId] = null;
                }
            }
        }
    }
    
    // ==================== HOMEWORK CRUD ====================
    public function create($data, $files = null) {
        try {
            $this->conn->beginTransaction();
            
            $sql = "INSERT INTO homework (teacher_id, class_id, subject_id, title, description, due_date, is_active, created_at) 
                    VALUES (:teacher_id, :class_id, :subject_id, :title, :description, :due_date, 1, NOW())";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([
                ':teacher_id' => $data['teacher_id'],
                ':class_id' => $data['class_id'],
                ':subject_id' => $data['subject_id'],
                ':title' => $data['title'],
                ':description' => $data['description'] ?? null,
                ':due_date' => $data['due_date']
            ]);
            
            $homeworkId = $this->conn->lastInsertId();
            
            if ($files && !empty($files['name'][0])) {
                $this->uploadFiles($homeworkId, $files, false);
            }
            
            $this->conn->commit();
            $this->invalidateCache($homeworkId);
            return ['success' => true, 'homework_id' => $homeworkId];
            
        } catch (Exception $e) {
            $this->conn->rollBack();
            error_log("Create homework error: " . $e->getMessage());
            return ['success' => false, 'error' => 'Failed to create homework: ' . $e->getMessage()];
        }
    }
    
    public function update($homeworkId, $data) {
        try {
            $sql = "UPDATE homework SET 
                    title = :title,
                    description = :description,
                    class_id = :class_id,
                    subject_id = :subject_id,
                    due_date = :due_date,
                    updated_at = NOW()
                    WHERE id = :id AND teacher_id = :teacher_id";
            
            $stmt = $this->conn->prepare($sql);
            $result = $stmt->execute([
                ':title' => $data['title'],
                ':description' => $data['description'] ?? null,
                ':class_id' => $data['class_id'],
                ':subject_id' => $data['subject_id'],
                ':due_date' => $data['due_date'],
                ':id' => $homeworkId,
                ':teacher_id' => $data['teacher_id']
            ]);
            
            if ($result) {
                if (!empty($data['new_files']) && !empty($data['new_files']['name'][0])) {
                    $this->uploadFiles($homeworkId, $data['new_files'], false);
                }
                $this->invalidateCache($homeworkId);
                return ['success' => true];
            }
            
            return ['success' => false, 'error' => 'Failed to update homework'];
            
        } catch (PDOException $e) {
            error_log("Update homework error: " . $e->getMessage());
            return ['success' => false, 'error' => 'Database error: ' . $e->getMessage()];
        }
    }
    
    public function delete($homeworkId, $teacherId) {
        $result = $this->executeUpdate("DELETE FROM homework WHERE id = ? AND teacher_id = ?", [$homeworkId, $teacherId]);
        if ($result['success']) {
            $this->invalidateCache($homeworkId);
        }
        return $result;
    }
    
    public function getById($homeworkId) {
        if (isset($this->homeworkCache[$homeworkId])) {
            return $this->homeworkCache[$homeworkId];
        }
        
        $sql = "SELECT h.*, 
                       c.name as class_name,
                       s.name as subject_name,
                       u.first_name as teacher_first_name,
                       u.last_name as teacher_last_name
                FROM homework h
                LEFT JOIN classes c ON h.class_id = c.id
                LEFT JOIN subjects s ON h.subject_id = s.id
                LEFT JOIN users u ON h.teacher_id = u.id
                WHERE h.id = ?";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$homeworkId]);
        $homework = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($homework) {
            $homework['attachments'] = $this->getCachedAttachments($homeworkId);
            $this->homeworkCache[$homeworkId] = $homework;
        }
        
        return $homework;
    }
    
    public function getByTeacher($teacherId, $limit = 20, $offset = 0, $status = null) {
        try {
            $sql = "SELECT h.*, 
                        c.name as class_name,
                        s.name as subject_name,
                        (SELECT COUNT(*) FROM homework_submissions WHERE homework_id = h.id) as submissions_count,
                        0 as students_count
                    FROM homework h
                    LEFT JOIN classes c ON h.class_id = c.id
                    LEFT JOIN subjects s ON h.subject_id = s.id
                    WHERE h.teacher_id = ?";
            
            $params = [$teacherId];
            
            if ($status === 'active') {
                $sql .= " AND h.due_date > NOW() AND h.is_active = 1";
            } elseif ($status === 'expired') {
                $sql .= " AND h.due_date < NOW()";
            }
            
            $sql .= " ORDER BY h.due_date ASC LIMIT ? OFFSET ?";
            $params[] = $limit;
            $params[] = $offset;
            
            $stmt = $this->conn->prepare($sql);
            $stmt->execute($params);
            
            $homeworks = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            foreach ($homeworks as &$hw) {
                $hw['attachments'] = $this->getCachedAttachments($hw['id']);
            }
            
            return $homeworks;
        } catch (PDOException $e) {
            error_log("Get homework by teacher error: " . $e->getMessage());
            return [];
        }
    }
    
    public function getByStudent($studentId, $classId = null, $status = null) {
        try {
            $sql = "SELECT h.*, 
                           c.name as class_name,
                           s.name as subject_name,
                           u.first_name as teacher_first_name,
                           u.last_name as teacher_last_name,
                           hs.id as submission_id,
                           hs.status as submission_status,
                           hs.submitted_at,
                           hs.grade,
                           hs.feedback,
                           hs.text_answer
                    FROM homework h
                    LEFT JOIN classes c ON h.class_id = c.id
                    LEFT JOIN subjects s ON h.subject_id = s.id
                    LEFT JOIN users u ON h.teacher_id = u.id
                    LEFT JOIN homework_submissions hs ON h.id = hs.homework_id AND hs.student_id = ?
                    WHERE h.is_active = 1";
            
            $params = [$studentId];
            
            if ($classId) {
                $sql .= " AND h.class_id = ?";
                $params[] = $classId;
            }
            
            if ($status === 'pending') {
                $sql .= " AND (hs.id IS NULL OR hs.status = 'pending')";
            } elseif ($status === 'submitted') {
                $sql .= " AND hs.status IN ('submitted', 'late')";
            } elseif ($status === 'graded') {
                $sql .= " AND hs.status = 'graded'";
            }
            
            $sql .= " ORDER BY h.due_date ASC";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->execute($params);
            $homeworks = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            foreach ($homeworks as &$hw) {
                $hw['attachments'] = $this->getCachedAttachments($hw['id']);
                if ($hw['submission_id']) {
                    $hw['submission_files'] = $this->getCachedSubmissionFiles($hw['submission_id']);
                }
            }
            
            return $homeworks;
        } catch (PDOException $e) {
            error_log("Get homework by student error: " . $e->getMessage());
            return [];
        }
    }
    
    public function getAttachments($homeworkId) {
        return $this->getCachedAttachments($homeworkId);
    }
    
    private function uploadAttachments($homeworkId, $files) {
        $this->uploadFiles($homeworkId, $files, false);
    }
    
    public function deleteAttachment($attachmentId, $teacherId) {
        try {
            $stmt = $this->conn->prepare("
                DELETE ha FROM homework_attachments ha
                INNER JOIN homework h ON ha.homework_id = h.id
                WHERE ha.id = ? AND h.teacher_id = ?
            ");
            $stmt->execute([$attachmentId, $teacherId]);
            $this->invalidateCache();
            return ['success' => true];
        } catch (PDOException $e) {
            return ['success' => false, 'error' => 'Failed to delete attachment'];
        }
    }
    
    public function submitHomework($homeworkId, $studentId, $textAnswer, $files = null) {
        try {
            $this->conn->beginTransaction();
            
            $stmt = $this->conn->prepare("SELECT due_date FROM homework WHERE id = ? AND is_active = 1");
            $stmt->execute([$homeworkId]);
            $homework = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$homework) {
                return ['success' => false, 'error' => 'Homework not found'];
            }
            
            $isLate = strtotime($homework['due_date']) < time();
            $status = $isLate ? 'late' : 'submitted';
            
            $checkStmt = $this->conn->prepare("SELECT id FROM homework_submissions WHERE homework_id = ? AND student_id = ?");
            $checkStmt->execute([$homeworkId, $studentId]);
            $existing = $checkStmt->fetch(PDO::FETCH_ASSOC);
            
            if ($existing) {
                $sql = "UPDATE homework_submissions SET 
                        text_answer = :text_answer,
                        status = :status,
                        submitted_at = NOW(),
                        updated_at = NOW()
                        WHERE id = :id";
                $stmt = $this->conn->prepare($sql);
                $stmt->execute([
                    ':text_answer' => $textAnswer,
                    ':status' => $status,
                    ':id' => $existing['id']
                ]);
                $submissionId = $existing['id'];
            } else {
                $sql = "INSERT INTO homework_submissions (homework_id, student_id, text_answer, status, submitted_at, created_at) 
                        VALUES (:homework_id, :student_id, :text_answer, :status, NOW(), NOW())";
                $stmt = $this->conn->prepare($sql);
                $stmt->execute([
                    ':homework_id' => $homeworkId,
                    ':student_id' => $studentId,
                    ':text_answer' => $textAnswer,
                    ':status' => $status
                ]);
                $submissionId = $this->conn->lastInsertId();
            }
            
            if ($files && !empty($files['name'][0])) {
                $this->uploadFiles($homeworkId, $files, true, $submissionId);
            }
            
            $this->conn->commit();
            $this->invalidateCache();
            return ['success' => true, 'submission_id' => $submissionId];
            
        } catch (Exception $e) {
            $this->conn->rollBack();
            error_log("Submit homework error: " . $e->getMessage());
            return ['success' => false, 'error' => 'Failed to submit homework: ' . $e->getMessage()];
        }
    }
    
    private function uploadSubmissionFiles($submissionId, $files) {
        $this->uploadFiles(null, $files, true, $submissionId);
    }
    
    public function getSubmissionFiles($submissionId) {
        return $this->getCachedSubmissionFiles($submissionId);
    }
    
    public function getSubmissions($homeworkId, $teacherId) {
        try {
            $sql = "SELECT hs.*, 
                           u.id as student_id,
                           u.first_name, 
                           u.last_name, 
                           u.email,
                           u.profile_photo
                    FROM homework_submissions hs
                    INNER JOIN users u ON hs.student_id = u.id
                    INNER JOIN homework h ON hs.homework_id = h.id
                    WHERE hs.homework_id = ? AND h.teacher_id = ?
                    ORDER BY hs.submitted_at DESC";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$homeworkId, $teacherId]);
            $submissions = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            foreach ($submissions as &$sub) {
                $sub['files'] = $this->getCachedSubmissionFiles($sub['id']);
            }
            
            return $submissions;
        } catch (PDOException $e) {
            error_log("Get submissions error: " . $e->getMessage());
            return [];
        }
    }
    
    public function gradeSubmission($submissionId, $teacherId, $grade, $feedback) {
        try {
            $stmt = $this->conn->prepare("
                UPDATE homework_submissions hs
                INNER JOIN homework h ON hs.homework_id = h.id
                SET hs.grade = :grade, 
                    hs.feedback = :feedback, 
                    hs.status = 'graded',
                    hs.graded_at = NOW(),
                    hs.updated_at = NOW()
                WHERE hs.id = :submission_id AND h.teacher_id = :teacher_id
            ");
            $stmt->execute([
                ':grade' => $grade,
                ':feedback' => $feedback,
                ':submission_id' => $submissionId,
                ':teacher_id' => $teacherId
            ]);
            
            $this->invalidateCache();
            return ['success' => true];
        } catch (PDOException $e) {
            error_log("Grade submission error: " . $e->getMessage());
            return ['success' => false, 'error' => 'Failed to grade submission'];
        }
    }
    
    public function countByTeacher($teacherId, $status = null) {
        try {
            $sql = "SELECT COUNT(*) as total FROM homework WHERE teacher_id = ?";
            $params = [$teacherId];
            
            if ($status === 'active') {
                $sql .= " AND due_date > NOW() AND is_active = 1";
            } elseif ($status === 'expired') {
                $sql .= " AND due_date < NOW()";
            }
            
            $stmt = $this->conn->prepare($sql);
            $stmt->execute($params);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['total'] ?? 0;
        } catch (PDOException $e) {
            error_log("Count homework by teacher error: " . $e->getMessage());
            return 0;
        }
    }

    public function getStudentSubmission($homeworkId, $studentId) {
        try {
            $stmt = $this->conn->prepare("
                SELECT hs.*, 
                    (SELECT COUNT(*) FROM homework_submission_files WHERE submission_id = hs.id) as files_count
                FROM homework_submissions hs
                WHERE hs.homework_id = ? AND hs.student_id = ?
            ");
            $stmt->execute([$homeworkId, $studentId]);
            $submission = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($submission) {
                $submission['files'] = $this->getSubmissionFiles($submission['id']);
            }
            
            return $submission;
        } catch (PDOException $e) {
            error_log("Get student submission error: " . $e->getMessage());
            return null;
        }
    }

    public function updateSubmissionStatus($homeworkId, $isActive) {
        try {
            $stmt = $this->conn->prepare("UPDATE homework SET is_active = ?, updated_at = NOW() WHERE id = ?");
            return $stmt->execute([$isActive, $homeworkId]);
        } catch (PDOException $e) {
            error_log("Update submission status error: " . $e->getMessage());
            return false;
        }
    }

    public function getSubmissionCount($homeworkId) {
        try {
            $stmt = $this->conn->prepare("
                SELECT COUNT(*) as count 
                FROM homework_submissions 
                WHERE homework_id = ?
            ");
            $stmt->execute([$homeworkId]);
            $result = $stmt->fetch();
            return $result['count'] ?? 0;
        } catch (PDOException $e) {
            error_log("Get submission count error: " . $e->getMessage());
            return 0;
        }
    }

    public function getClassStudentsCount($classId) {
        try {
            $stmt = $this->conn->prepare("
                SELECT COUNT(DISTINCT cs.student_id) as count 
                FROM class_students cs
                WHERE cs.class_id = ?
            ");
            $stmt->execute([$classId]);
            $result = $stmt->fetch();
            return $result['count'] ?? 0;
        } catch (PDOException $e) {
            error_log("Get class students count error: " . $e->getMessage());
            return 0;
        }
    }

    public function getHomeworkSubmissions($homeworkId) {
        try {
            $stmt = $this->conn->prepare("
                SELECT 
                    hs.*,
                    u.id as student_id,
                    u.first_name,
                    u.last_name,
                    u.email,
                    u.profile_photo,
                    hs.submitted_at,
                    hs.score,
                    hs.feedback,
                    hs.status
                FROM homework_submissions hs
                LEFT JOIN users u ON hs.student_id = u.id
                WHERE hs.homework_id = ?
                ORDER BY hs.submitted_at DESC
            ");
            $stmt->execute([$homeworkId]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Get homework submissions error: " . $e->getMessage());
            return [];
        }
    }

    public function getHomeworkClassStudents($homeworkId) {
        try {
            $stmt = $this->conn->prepare("
                SELECT 
                    u.id,
                    u.first_name,
                    u.last_name,
                    u.email,
                    u.profile_photo,
                    hs.id as submission_id,
                    hs.submitted_at,
                    hs.status as submission_status,
                    hs.score
                FROM homework h
                LEFT JOIN class_students cs ON h.class_id = cs.class_id
                LEFT JOIN users u ON cs.student_id = u.id
                LEFT JOIN homework_submissions hs ON h.id = hs.homework_id AND hs.student_id = u.id
                WHERE h.id = ?
                AND u.role = 'learner'
                ORDER BY u.first_name ASC
            ");
            $stmt->execute([$homeworkId]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Get homework class students error: " . $e->getMessage());
            return [];
        }
    }

    public function getTeacherHomeworks($teacherId, $status = null, $limit = 15, $offset = 0) {
        try {
            $query = "
                SELECT 
                    h.*,
                    c.name as class_name,
                    s.name as subject_name,
                    (SELECT COUNT(*) FROM homework_submissions WHERE homework_id = h.id) as submissions_count
                FROM homework h
                LEFT JOIN classes c ON h.class_id = c.id
                LEFT JOIN subjects s ON h.subject_id = s.id
                WHERE h.teacher_id = :teacher_id
            ";
            
            if ($status === 'active') {
                $query .= " AND h.due_date > NOW()";
            } elseif ($status === 'expired') {
                $query .= " AND h.due_date < NOW()";
            }
            
            $query .= " ORDER BY h.created_at DESC LIMIT :limit OFFSET :offset";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindValue(':teacher_id', $teacherId, PDO::PARAM_INT);
            $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
            
            $stmt->execute();
            $homeworks = $stmt->fetchAll();
            
            foreach ($homeworks as &$homework) {
                $homework['students_count'] = $this->getClassStudentsCount($homework['class_id']);
            }
            
            return $homeworks;
        } catch (PDOException $e) {
            error_log("Get teacher homeworks error: " . $e->getMessage());
            return [];
        }
    }
}
?>