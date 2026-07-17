<?php
// File: /models/Homework.php 
require_once __DIR__ . '/../config/database.php';
use Aws\S3\S3Client;
use Aws\Exception\AwsException;

class Homework {
    private $db;
    private $conn;
    private $attachmentsCache = [];
    private $submissionFilesCache = [];
    private $homeworkCache = [];
    
    private $r2BucketName = 'rogele-platform';
    private $r2BaseUrl = 'https://raysofgrace.ac.ug/rogele-platform'; 
    
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
        try {
            $s3 = new S3Client([
                'version'     => 'latest',
                'region'      => 'auto',
                'endpoint'    => getenv('R2_ENDPOINT_URL'),
                'credentials' => [
                    'key'    => getenv('R2_ACCESS_KEY_ID'),
                    'secret' => getenv('R2_SECRET_ACCESS_KEY'),
                ],
            ]);

            for ($i = 0; $i < count($files['name']); $i++) {
                if ($files['error'][$i] !== UPLOAD_ERR_OK) continue;
                
                $fileName = time() . '_' . ($submissionId ? $submissionId . '_' : '') . preg_replace('/[^a-zA-Z0-9._-]/', '_', basename($files['name'][$i]));
                
                $r2Folder = $isSubmission ? 'uploads/submissions/' : 'uploads/homework/';
                $objectKey = $r2Folder . $fileName;

                $result = $s3->putObject([
                    'Bucket'     => $this->r2BucketName,
                    'Key'        => $objectKey,
                    'SourceFile' => $files['tmp_name'][$i],
                    'ContentType'=> $files['type'][$i]
                ]);
                
                $dbPath = $this->r2BaseUrl . '/' . $objectKey;
                
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
        } catch (AwsException $e) {
            error_log("R2 Upload Error: " . $e->getMessage());
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
                ':teacher_id'  => $data['teacher_id'],
                ':class_id'    => $data['class_id'],
                ':subject_id'  => $data['subject_id'],
                ':title'       => $data['title'],
                ':description' => $data['description'] ?? null,
                ':due_date'    => $data['due_date']
            ]);
            
            $homeworkId = $this->conn->lastInsertId();
            
            $this->conn->commit();
            $this->invalidateCache($homeworkId);
            
            if ($files && isset($files['name'][0]) && $files['name'][0] !== '' && $files['error'][0] === UPLOAD_ERR_OK) {
                $this->uploadFiles($homeworkId, $files, false);
            }
            
            return ['success' => true, 'homework_id' => $homeworkId];
            
        } catch (Exception $e) {
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }
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
            return ['success' => false, 'error' => 'Database error: ' . $e->getMessage()];
        }
    }
    
    public function delete($homeworkId, $teacherId) {
        try {
            $stmtFiles = $this->conn->prepare("SELECT file_path FROM homework_attachments WHERE homework_id = ?");
            $stmtFiles->execute([$homeworkId]);
            $homeworkFiles = $stmtFiles->fetchAll(PDO::FETCH_ASSOC);

            $stmtSubs = $this->conn->prepare("
                SELECT hsf.file_path 
                FROM homework_submission_files hsf
                INNER JOIN homework_submissions hs ON hsf.submission_id = hs.id
                WHERE hs.homework_id = ?
            ");
            $stmtSubs->execute([$homeworkId]);
            $submissionFiles = $stmtSubs->fetchAll(PDO::FETCH_ASSOC);

            $result = $this->executeUpdate("DELETE FROM homework WHERE id = ? AND teacher_id = ?", [$homeworkId, $teacherId]);
            
            if ($result['success']) {
                $this->invalidateCache($homeworkId);

                $s3 = new S3Client([
                    'version'     => 'latest',
                    'region'      => 'auto',
                    'endpoint'    => getenv('R2_ENDPOINT_URL'),
                    'credentials' => [
                        'key'    => getenv('R2_ACCESS_KEY_ID'),
                        'secret' => getenv('R2_SECRET_ACCESS_KEY'),
                    ],
                ]);

                if (!empty($homeworkFiles)) {
                    foreach ($homeworkFiles as $file) {
                        if (empty($file['file_path'])) continue;
                        $cleanName = basename($file['file_path']);
                        
                        try {
                            $s3->deleteObject([
                                'Bucket' => $this->r2BucketName,
                                'Key'    => 'uploads/homework/' . $cleanName
                            ]);
                        } catch (AwsException $e) {
                            error_log("R2 Delete Homework File Error: " . $e->getMessage());
                        }
                    }
                }

                if (!empty($submissionFiles)) {
                    foreach ($submissionFiles as $file) {
                        if (empty($file['file_path'])) continue;
                        $cleanSubName = basename($file['file_path']);
                        
                        try {
                            $s3->deleteObject([
                                'Bucket' => $this->r2BucketName,
                                'Key'    => 'uploads/submissions/' . $cleanSubName
                            ]);
                        } catch (AwsException $e) {
                            error_log("R2 Delete Submission File Error: " . $e->getMessage());
                        }
                    }
                }
            }

            return $result;

        } catch (Exception $e) {
            error_log("Failed during homework cleanup execution: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
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
            $stmtFile = $this->conn->prepare("
                SELECT ha.file_path 
                FROM homework_attachments ha
                INNER JOIN homework h ON ha.homework_id = h.id
                WHERE ha.id = ? AND h.teacher_id = ?
            ");
            $stmtFile->execute([$attachmentId, $teacherId]);
            $attachment = $stmtFile->fetch(PDO::FETCH_ASSOC);

            if (!$attachment) {
                return ['success' => false, 'error' => 'Attachment not found or unauthorized'];
            }

            $stmtDel = $this->conn->prepare("
                DELETE ha FROM homework_attachments ha
                INNER JOIN homework h ON ha.homework_id = h.id
                WHERE ha.id = ? AND h.teacher_id = ?
            ");
            $stmtDel->execute([$attachmentId, $teacherId]);

            if ($stmtDel->rowCount() > 0) {
                $this->invalidateCache();

                $s3 = new S3Client([
                    'version'     => 'latest',
                    'region'      => 'auto',
                    'endpoint'    => getenv('R2_ENDPOINT_URL'),
                    'credentials' => [
                        'key'    => getenv('R2_ACCESS_KEY_ID'),
                        'secret' => getenv('R2_SECRET_ACCESS_KEY'),
                    ],
                ]);

                $cleanName = basename($attachment['file_path']);
                $s3->deleteObject([
                    'Bucket' => $this->r2BucketName,
                    'Key'    => 'uploads/homework/' . $cleanName
                ]);
            }

            return ['success' => true];
        } catch (Exception $e) {
            error_log("Failed to delete individual attachment from R2: " . $e->getMessage());
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
            
            $oldFilesToDelete = [];

            if ($existing) {
                $submissionId = $existing['id'];

                if ($files && !empty($files['name'][0])) {
                    $stmtOldFiles = $this->conn->prepare("SELECT file_path FROM homework_submission_files WHERE submission_id = ?");
                    $stmtOldFiles->execute([$submissionId]);
                    $oldFilesToDelete = $stmtOldFiles->fetchAll(PDO::FETCH_ASSOC);

                    $stmtDelOld = $this->conn->prepare("DELETE FROM homework_submission_files WHERE submission_id = ?");
                    $stmtDelOld->execute([$submissionId]);
                }

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
                    ':id' => $submissionId
                ]);
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

            if (!empty($oldFilesToDelete)) {
                $s3 = new S3Client([
                    'version'     => 'latest',
                    'region'      => 'auto',
                    'endpoint'    => getenv('R2_ENDPOINT_URL'),
                    'credentials' => [
                        'key'    => getenv('R2_ACCESS_KEY_ID'),
                        'secret' => getenv('R2_SECRET_ACCESS_KEY'),
                    ],
                ]);

                foreach ($oldFilesToDelete as $oldFile) {
                    if (empty($oldFile['file_path'])) continue;
                    $cleanName = basename($oldFile['file_path']);
                    try {
                        $s3->deleteObject([
                            'Bucket' => $this->r2BucketName,
                            'Key'    => 'uploads/submissions/' . $cleanName
                        ]);
                    } catch (AwsException $e) {
                        error_log("Failed to clear old resubmitted file from R2: " . $e->getMessage());
                    }
                }
            }

            return ['success' => true, 'submission_id' => $submissionId];
            
        } catch (Exception $e) {
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }
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
            return null;
        }
    }

    public function updateSubmissionStatus($homeworkId, $isActive) {
        try {
            $stmt = $this->conn->prepare("UPDATE homework SET is_active = ?, updated_at = NOW() WHERE id = ?");
            return $stmt->execute([$isActive, $homeworkId]);
        } catch (PDOException $e) {
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
            return [];
        }
    }

    public function getUpcomingHomeworkDeadlines($studentId, $classId, $limit = 5) {
        try {
            
            $limit = (int)$limit;
            
            $query = "
                SELECT 
                    h.*,
                    c.name as class_name,
                    s.name as subject_name,
                    (SELECT COUNT(*) FROM homework_submissions WHERE homework_id = h.id AND student_id = ?) as has_submitted
                FROM homework h
                LEFT JOIN classes c ON h.class_id = c.id
                LEFT JOIN subjects s ON h.subject_id = s.id
                WHERE h.class_id = ?
                AND h.due_date >= NOW()
                AND h.is_active = 1
                ORDER BY h.due_date ASC
                LIMIT ?
            ";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindValue(1, $studentId, PDO::PARAM_INT);
            $stmt->bindValue(2, $classId, PDO::PARAM_INT);
            $stmt->bindValue(3, $limit, PDO::PARAM_INT);
            $stmt->execute();
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return $results;
            
        } catch (PDOException $e) {
            return [];
        }
    }

    public function getUpcomingQuizDeadlines($studentId, $limit = 10) {
        try {
            $stmt = $this->conn->prepare("
                SELECT class_id FROM class_students WHERE student_id = ?
            ");
            $stmt->execute([$studentId]);
            $classes = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            if (empty($classes)) {
                return [];
            }
            
            $placeholders = implode(',', array_fill(0, count($classes), '?'));
            
            $query = "
                SELECT 
                    q.*,
                    c.name as class_name,
                    s.name as subject_name,
                    (SELECT COUNT(*) FROM quiz_attempts WHERE quiz_id = q.id AND user_id = ?) as has_attempted
                FROM quizzes q
                LEFT JOIN classes c ON q.class_id = c.id
                LEFT JOIN subjects s ON q.subject_id = s.id
                WHERE q.class_id IN ($placeholders)
                AND q.due_date >= NOW()
                AND q.is_published = 1
                ORDER BY q.due_date ASC
                LIMIT ?
            ";
            
            $params = array_merge([$studentId], $classes, [$limit]);
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            return [];
        }
    }
}
?>