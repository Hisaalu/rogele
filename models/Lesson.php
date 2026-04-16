<?php
// File: /models/Lesson.php
require_once __DIR__ . '/../config/database.php';

class Lesson {
    private $db;
    private $conn;
    
    public function __construct() {
        $this->db = Database::getInstance();
        $this->conn = $this->db->getConnection();
    }

    /**
     * Get database connection
     * 
     * @return PDO Database connection
     */
    public function getConnection() {
        return $this->conn;
    }
    
    /**
     * Create lesson with files
     */
    public function create($data, $files = null) {
        try {
            // Ensure upload directory exists before any file operations
            $uploadDir = __DIR__ . '/../public/uploads/lessons/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            chmod($uploadDir, 0777);

            $this->conn->beginTransaction();

            $query = "INSERT INTO lessons (title, content, subject_id, class_id, teacher_id, video_url, duration, is_published, created_at) 
                      VALUES (:title, :content, :subject_id, :class_id, :teacher_id, :video_url, :duration, :is_published, NOW())";
            
            $stmt = $this->conn->prepare($query);
            $result = $stmt->execute([
                ':title' => $data['title'],
                ':content' => $data['content'] ?? null,
                ':subject_id' => $data['subject_id'] ?? null,
                ':class_id' => $data['class_id'] ?? null,
                ':teacher_id' => $data['teacher_id'],
                ':video_url' => $data['video_url'] ?? null,
                ':duration' => $data['duration'] ?? null,
                ':is_published' => $data['is_published'] ?? 0
            ]);
            
            if (!$result) {
                throw new Exception('Failed to create lesson');
            }
            
            $lessonId = $this->conn->lastInsertId();
            
            if ($files && !empty($files['name'][0])) {
                $this->uploadMaterials($lessonId, $files);
            }
            
            $this->conn->commit();
            return ['success' => true, 'lesson_id' => $lessonId];
            
        } catch (Exception $e) {
            $this->conn->rollBack();
            return ['success' => false, 'error' => 'Failed to create lesson'];
        }
    }
    
    /**
     * Get lesson by ID with materials
     */
    public function getById($lessonId) {
        try {
            $this->incrementViews($lessonId);
            
            $query = "SELECT l.*, s.name as subject_name, u.first_name as teacher_name, u.last_name as teacher_last_name, 
                     c.name as class_name
                     FROM lessons l
                     LEFT JOIN subjects s ON l.subject_id = s.id
                     LEFT JOIN users u ON l.teacher_id = u.id
                     LEFT JOIN classes c ON l.class_id = c.id
                     WHERE l.id = :id";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute([':id' => $lessonId]);
            
            $lesson = $stmt->fetch();
            
            if ($lesson) {
                $materialQuery = "SELECT * FROM lesson_materials WHERE lesson_id = :lesson_id";
                $materialStmt = $this->conn->prepare($materialQuery);
                $materialStmt->execute([':lesson_id' => $lessonId]);
                $lesson['materials'] = $materialStmt->fetchAll();
            }
            
            return $lesson;
        } catch (PDOException $e) {
            return null;
        }
    }
    
    /**
     * Get lessons by class
     */
    public function getByClass($classId, $limit = null) {
        try {
            $query = "SELECT l.*, s.name as subject_name, u.first_name as teacher_name,
                     (SELECT COUNT(*) FROM lesson_materials WHERE lesson_id = l.id) as materials_count
                     FROM lessons l
                     LEFT JOIN subjects s ON l.subject_id = s.id
                     LEFT JOIN users u ON l.teacher_id = u.id
                     WHERE l.class_id = :class_id AND l.is_published = 1 AND l.is_approved = 1
                     ORDER BY l.created_at DESC";
            
            if ($limit) {
                $query .= " LIMIT " . intval($limit);
            }
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute([':class_id' => $classId]);
            
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }
    
    /**
     * Get lessons by teacher
     */
    public function getByTeacher($teacherId, $limit = null, $offset = 0) {
        try {
            $query = "SELECT l.*, s.name as subject_name, c.name as class_name,
                    (SELECT COUNT(*) FROM lesson_materials WHERE lesson_id = l.id) as materials_count
                    FROM lessons l
                    LEFT JOIN subjects s ON l.subject_id = s.id
                    LEFT JOIN classes c ON l.class_id = c.id
                    WHERE l.teacher_id = :teacher_id
                    ORDER BY l.created_at DESC";
            
            if ($limit) {
                $query .= " LIMIT :limit OFFSET :offset";
            }
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindValue(':teacher_id', $teacherId, PDO::PARAM_INT);
            
            if ($limit) {
                $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
                $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            }
            
            $stmt->execute();
            $results = $stmt->fetchAll();
            
            
            return $results;
        } catch (PDOException $e) {
            return [];
        }
    }
    
    /**
     * Get all lessons (with pagination)
     */
    public function getAll($page = 1, $limit = 20) {
        try {
            $offset = ($page - 1) * $limit;
            
            $query = "SELECT l.*, s.name as subject_name, c.name as class_name, 
                     u.first_name as teacher_name, u.last_name as teacher_last_name,
                     (SELECT COUNT(*) FROM lesson_materials WHERE lesson_id = l.id) as materials_count
                     FROM lessons l
                     LEFT JOIN subjects s ON l.subject_id = s.id
                     LEFT JOIN classes c ON l.class_id = c.id
                     LEFT JOIN users u ON l.teacher_id = u.id
                     WHERE l.is_published = 1 AND l.is_approved = 1
                     ORDER BY l.created_at DESC
                     LIMIT :limit OFFSET :offset";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }
    
    /**
     * Update lesson
     */
    public function update($lessonId, $data) {
        try {
            $query = "UPDATE lessons SET 
                      title = :title,
                      content = :content,
                      subject_id = :subject_id,
                      class_id = :class_id,
                      video_url = :video_url,
                      duration = :duration,
                      is_published = :is_published,
                      updated_at = NOW()
                      WHERE id = :id";
            
            $stmt = $this->conn->prepare($query);
            $result = $stmt->execute([
                ':title' => $data['title'],
                ':content' => $data['content'] ?? null,
                ':subject_id' => $data['subject_id'] ?? null,
                ':class_id' => $data['class_id'] ?? null,
                ':video_url' => $data['video_url'] ?? null,
                ':duration' => $data['duration'] ?? null,
                ':is_published' => $data['is_published'] ?? 0,
                ':id' => $lessonId
            ]);
            
            if ($result) {
                return ['success' => true, 'message' => 'Lesson updated successfully'];
            }
            
            return ['success' => false, 'error' => 'Failed to update lesson'];
        } catch (PDOException $e) {
            return ['success' => false, 'error' => 'Database error'];
        }
    }
    
    /**
     * Delete lesson
     */
    public function delete($lessonId) {
        try {
            $materialQuery = "DELETE FROM lesson_materials WHERE lesson_id = :lesson_id";
            $materialStmt = $this->conn->prepare($materialQuery);
            $materialStmt->execute([':lesson_id' => $lessonId]);
            
            $query = "DELETE FROM lessons WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $result = $stmt->execute([':id' => $lessonId]);
            
            if ($result) {
                return ['success' => true, 'message' => 'Lesson deleted successfully'];
            }
            
            return ['success' => false, 'error' => 'Failed to delete lesson'];
        } catch (PDOException $e) {
            error_log("Lesson deletion error: " . $e->getMessage());
            return ['success' => false, 'error' => 'Failed to delete lesson'];
        }
    }
    
    /**
     * Search lessons
     */
    public function search($keyword, $classId = null) {
        try {
            $query = "SELECT l.*, s.name as subject_name, u.first_name as teacher_name, u.last_name as teacher_last_name
                     FROM lessons l
                     LEFT JOIN subjects s ON l.subject_id = s.id
                     LEFT JOIN users u ON l.teacher_id = u.id
                     WHERE (l.title LIKE :keyword OR l.content LIKE :keyword)
                     AND l.is_published = 1 AND l.is_approved = 1";
            
            $params = [':keyword' => '%' . $keyword . '%'];
            
            if ($classId) {
                $query .= " AND l.class_id = :class_id";
                $params[':class_id'] = $classId;
            }
            
            $query .= " ORDER BY l.views DESC LIMIT 20";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute($params);
            
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }
    
    /**
     * Search lessons by teacher
     */
    public function searchByTeacher($teacherId, $keyword) {
        try {
            $searchPattern = '%' . $keyword . '%';
            
            $query = "SELECT l.*, s.name as subject_name, c.name as class_name,
                        (SELECT COUNT(*) FROM lesson_materials WHERE lesson_id = l.id) as materials_count
                    FROM lessons l
                    LEFT JOIN subjects s ON l.subject_id = s.id
                    LEFT JOIN classes c ON l.class_id = c.id
                    WHERE l.teacher_id = :teacher_id 
                    AND (l.title LIKE :search1 OR l.content LIKE :search2)
                    ORDER BY l.created_at DESC
                    LIMIT 50";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindValue(':teacher_id', $teacherId, PDO::PARAM_INT);
            $stmt->bindValue(':search1', $searchPattern);
            $stmt->bindValue(':search2', $searchPattern);
            $stmt->execute();
            
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return array();
        }
    }
    
    /**
     * Increment view count
     */
    private function incrementViews($lessonId) {
        try {
            $query = "UPDATE lessons SET views = views + 1 WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([':id' => $lessonId]);
        } catch (PDOException $e) {
        }
    }
    
    /**
     * Upload lesson materials with automatic permission fixing
     */
    public function uploadMaterials($lessonId, $files) {
        try {
            // Get absolute path to uploads directory
            $baseDir = realpath(__DIR__ . '/../public/');
            $targetDir = $baseDir . '/uploads/lessons/';
            
            // Create directory if it doesn't exist (recursive)
            if (!is_dir($targetDir)) {
                mkdir($targetDir, 0777, true);
            }
            
            // Try to set writable permission
            chmod($targetDir, 0777);
            
            $uploadedCount = 0;
            
            for ($i = 0; $i < count($files['name']); $i++) {
                if ($files['error'][$i] === UPLOAD_ERR_OK) {
                    $fileName = time() . '_' . basename($files['name'][$i]);
                    $targetFile = $targetDir . $fileName;
                    
                    if (move_uploaded_file($files['tmp_name'][$i], $targetFile)) {
                        // Set file permission
                        chmod($targetFile, 0666);
                        
                        $dbPath = 'uploads/lessons/' . $fileName;
                        
                        $stmt = $this->conn->prepare("INSERT INTO lesson_materials (lesson_id, file_name, file_path, file_type, file_size) VALUES (?, ?, ?, ?, ?)");
                        $stmt->execute([$lessonId, $files['name'][$i], $dbPath, $files['type'][$i], $files['size'][$i]]);
                        
                        $uploadedCount++;
                    }
                }
            }
            
            return $uploadedCount > 0;
            
        } catch (Exception $e) {
            error_log("Upload error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Bookmark lesson
     */
    public function bookmark($userId, $lessonId) {
        try {
            $checkQuery = "SELECT id FROM bookmarks WHERE user_id = :user_id AND lesson_id = :lesson_id";
            $checkStmt = $this->conn->prepare($checkQuery);
            $checkStmt->execute([
                ':user_id' => $userId,
                ':lesson_id' => $lessonId
            ]);
            
            if ($checkStmt->fetch()) {
                return ['success' => false, 'error' => 'Lesson already bookmarked'];
            }
            
            $query = "INSERT INTO bookmarks (user_id, lesson_id, created_at) VALUES (:user_id, :lesson_id, NOW())";
            $stmt = $this->conn->prepare($query);
            $result = $stmt->execute([
                ':user_id' => $userId,
                ':lesson_id' => $lessonId
            ]);
            
            if ($result) {
                return ['success' => true, 'message' => 'Lesson bookmarked successfully'];
            }
            
            return ['success' => false, 'error' => 'Failed to bookmark lesson'];
        } catch (PDOException $e) {
            return ['success' => false, 'error' => 'Failed to bookmark lesson'];
        }
    }
    
    /**
     * Check if lesson is bookmarked by user
     */
    public function isBookmarked($userId, $lessonId) {
        try {
            $query = "SELECT id FROM bookmarks WHERE user_id = :user_id AND lesson_id = :lesson_id";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([
                ':user_id' => $userId,
                ':lesson_id' => $lessonId
            ]);
            
            return $stmt->fetch() ? true : false;
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Add bookmark
     */
    public function addBookmark($userId, $lessonId) {
        try {
            if ($this->isBookmarked($userId, $lessonId)) {
                return ['success' => false, 'error' => 'Already bookmarked'];
            }
            
            $query = "INSERT INTO bookmarks (user_id, lesson_id, created_at) VALUES (:user_id, :lesson_id, NOW())";
            $stmt = $this->conn->prepare($query);
            $result = $stmt->execute([
                ':user_id' => $userId,
                ':lesson_id' => $lessonId
            ]);
            
            if ($result) {
                return ['success' => true, 'message' => 'Bookmark added'];
            }
            
            return ['success' => false, 'error' => 'Failed to add bookmark'];
        } catch (PDOException $e) {
            return ['success' => false, 'error' => 'Database error'];
        }
    }

    /**
     * Remove bookmark
     */
    public function removeBookmark($userId, $lessonId) {
        try {
            $query = "DELETE FROM bookmarks WHERE user_id = :user_id AND lesson_id = :lesson_id";
            $stmt = $this->conn->prepare($query);
            $result = $stmt->execute([
                ':user_id' => $userId,
                ':lesson_id' => $lessonId
            ]);
            
            if ($result) {
                return ['success' => true, 'message' => 'Bookmark removed'];
            }
            
            return ['success' => false, 'error' => 'Failed to remove bookmark'];
        } catch (PDOException $e) {
            return ['success' => false, 'error' => 'Database error'];
        }
    }

    /**
     * Get user's bookmarked lessons
     */
    public function getBookmarks($userId) {
        try {
            $query = "SELECT l.*, 
                    s.name as subject_name,
                    c.name as class_name,
                    u.first_name as teacher_name,
                    u.last_name as teacher_last_name,
                    b.created_at as bookmarked_at,
                    (SELECT COUNT(*) FROM lesson_materials WHERE lesson_id = l.id) as materials_count
                    FROM bookmarks b
                    JOIN lessons l ON b.lesson_id = l.id
                    LEFT JOIN subjects s ON l.subject_id = s.id
                    LEFT JOIN classes c ON l.class_id = c.id
                    LEFT JOIN users u ON l.teacher_id = u.id
                    WHERE b.user_id = :user_id
                    ORDER BY b.created_at DESC";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute([':user_id' => $userId]);
            
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }
    
    /**
     * Get popular lessons
     */
    public function getPopular($limit = 10) {
        try {
            $query = "SELECT l.*, s.name as subject_name,
                     (SELECT COUNT(*) FROM bookmarks WHERE lesson_id = l.id) as bookmark_count
                     FROM lessons l
                     LEFT JOIN subjects s ON l.subject_id = s.id
                     WHERE l.is_published = 1 AND l.is_approved = 1
                     ORDER BY l.views DESC, bookmark_count DESC
                     LIMIT :limit";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }
    
    /**
     * Approve lesson (admin function)
     */
    public function approve($lessonId) {
        try {
            $query = "UPDATE lessons SET is_approved = 1 WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $result = $stmt->execute([':id' => $lessonId]);
            
            if ($result) {
                return ['success' => true, 'message' => 'Lesson approved successfully'];
            }
            
            return ['success' => false, 'error' => 'Failed to approve lesson'];
        } catch (PDOException $e) {
            return ['success' => false, 'error' => 'Failed to approve lesson'];
        }
    }
    
    /**
     * Get user progress for lessons (placeholder)
     */
    public function getUserProgress($userId) {
        try {
            return [];
        } catch (PDOException $e) {
            return [];
        }
    }
    
    /**
     * Get views by teacher for analytics
     */
    public function getViewsByTeacher($teacherId, $limit = 10) {
        try {
            $query = "SELECT l.id, l.title, l.views, l.created_at
                    FROM lessons l
                    WHERE l.teacher_id = :teacher_id
                    ORDER BY l.views DESC
                    LIMIT :limit";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindValue(':teacher_id', $teacherId, PDO::PARAM_INT);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }

    /**
     * Get material by ID
     */
    public function getMaterialById($materialId) {
        try {
            $query = "SELECT * FROM lesson_materials WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([':id' => $materialId]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            return null;
        }
    }

    /**
     * Delete lesson material
     */
    public function deleteMaterial($materialId) {
        try {
            $material = $this->getMaterialById($materialId);
            
            if (!$material) {
                return ['success' => false, 'error' => 'Material not found'];
            }
            
            $filePath = __DIR__ . '/../public/' . $material['file_path'];
            if (file_exists($filePath)) {
                unlink($filePath);
            }
            
            $query = "DELETE FROM lesson_materials WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $result = $stmt->execute([':id' => $materialId]);
            
            if ($result) {
                return ['success' => true, 'message' => 'Material deleted successfully'];
            }
            
            return ['success' => false, 'error' => 'Failed to delete material'];
            
        } catch (PDOException $e) {
            return ['success' => false, 'error' => 'Database error: ' . $e->getMessage()];
        }
    }

    /**
     * Get daily lesson views for teacher
     */
    public function getDailyViews($teacherId, $days = 30) {
        try {
            $query = "SELECT 
                        DATE(viewed_at) as date,
                        COUNT(*) as views
                    FROM lesson_views lv
                    JOIN lessons l ON lv.lesson_id = l.id
                    WHERE l.teacher_id = :teacher_id
                        AND lv.viewed_at >= DATE_SUB(NOW(), INTERVAL :days DAY)
                    GROUP BY DATE(lv.viewed_at)
                    ORDER BY date ASC";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute([
                ':teacher_id' => $teacherId,
                ':days' => $days
            ]);
            
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }

    /**
     * Get all published lessons
     */
    public function getPublishedLessons($subjectId = null) {
        try {
            $sql = "SELECT l.*, 
                        s.name as subject_name,
                        c.name as class_name,
                        u.first_name as teacher_name,
                        u.last_name as teacher_last_name,
                        (SELECT COUNT(*) FROM lesson_materials WHERE lesson_id = l.id) as materials_count
                    FROM lessons l
                    LEFT JOIN subjects s ON l.subject_id = s.id
                    LEFT JOIN classes c ON l.class_id = c.id
                    LEFT JOIN users u ON l.teacher_id = u.id
                    WHERE l.is_published = 1";
            
            $params = [];
            
            if ($subjectId) {
                $sql .= " AND l.subject_id = :subject_id";
                $params[':subject_id'] = $subjectId;
            }
            
            $sql .= " ORDER BY l.created_at DESC";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->execute($params);
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            return array();
        }
    }

    /**
     * Get published lesson by ID (regardless of approval status)
     */
    public function getPublishedLessonById($lessonId, $userId = null) {
        try {
            $query = "SELECT l.*, 
                    s.name as subject_name, 
                    c.name as class_name,
                    u.first_name as teacher_name,
                    u.last_name as teacher_last_name
                    FROM lessons l
                    LEFT JOIN subjects s ON l.subject_id = s.id
                    LEFT JOIN classes c ON l.class_id = c.id
                    LEFT JOIN users u ON l.teacher_id = u.id
                    WHERE l.id = :id AND l.is_published = 1"; 
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute([':id' => $lessonId]);
            
            $lesson = $stmt->fetch();
            
            if ($lesson) {
                $materialQuery = "SELECT * FROM lesson_materials WHERE lesson_id = :lesson_id";
                $materialStmt = $this->conn->prepare($materialQuery);
                $materialStmt->execute([':lesson_id' => $lessonId]);
                $lesson['materials'] = $materialStmt->fetchAll();
                
                if ($userId) {
                    $lesson['is_bookmarked'] = $this->isBookmarked($userId, $lessonId);
                }
                
                $this->incrementViews($lessonId);
            }
            
            return $lesson;
        } catch (PDOException $e) {
            return null;
        }
    }

    /**
     * Search published lessons
     */
    public function searchPublished($searchTerm, $subjectId = null) {
        try {
            $searchPattern = '%' . $searchTerm . '%';
            
            $sql = "SELECT l.*, 
                        s.name as subject_name,
                        c.name as class_name,
                        u.first_name as teacher_name,
                        u.last_name as teacher_last_name,
                        (SELECT COUNT(*) FROM lesson_materials WHERE lesson_id = l.id) as materials_count
                    FROM lessons l
                    LEFT JOIN subjects s ON l.subject_id = s.id
                    LEFT JOIN classes c ON l.class_id = c.id
                    LEFT JOIN users u ON l.teacher_id = u.id
                    WHERE l.is_published = 1 
                    AND (l.title LIKE :search1 
                        OR l.content LIKE :search2)";
            
            $params = array(
                ':search1' => $searchPattern,
                ':search2' => $searchPattern
            );
            
            if ($subjectId) {
                $sql .= " AND l.subject_id = :subject_id";
                $params[':subject_id'] = $subjectId;
            }
            
            $sql .= " ORDER BY l.created_at DESC";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->execute($params);
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            return array();
        }
    }

    /**
     * Get all lessons with filters (for admin)
     */
    public function getAllLessons($search = null, $teacherId = null, $status = null, $limit = 15, $offset = 0) {
        try {
            $query = "SELECT l.*, 
                    s.name as subject_name, 
                    c.name as class_name,
                    u.first_name as teacher_name,
                    u.last_name as teacher_last_name,
                    u.email as teacher_email,
                    (SELECT COUNT(*) FROM lesson_materials WHERE lesson_id = l.id) as materials_count
                    FROM lessons l
                    LEFT JOIN subjects s ON l.subject_id = s.id
                    LEFT JOIN classes c ON l.class_id = c.id
                    LEFT JOIN users u ON l.teacher_id = u.id
                    WHERE 1=1";
            
            $params = [];
            
            if (!empty($search)) {
                $query .= " AND (l.title LIKE ? OR l.content LIKE ?)";
                $params[] = '%' . $search . '%';
                $params[] = '%' . $search . '%';
            }
            
            if (!empty($teacherId)) {
                $query .= " AND l.teacher_id = ?";
                $params[] = $teacherId;
            }
            
            if ($status === 'published') {
                $query .= " AND l.is_published = 1";
            } elseif ($status === 'draft') {
                $query .= " AND l.is_published = 0";
            } elseif ($status === 'approved') {
                $query .= " AND l.is_approved = 1";
            } elseif ($status === 'pending') {
                $query .= " AND l.is_approved = 0";
            }
            
            $query .= " ORDER BY l.created_at DESC LIMIT " . (int)$limit . " OFFSET " . (int)$offset;
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute($params);
            
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }

    /**
     * Count all lessons with filters (for admin)
     */
    public function countAllLessons($search = null, $teacherId = null, $status = null) {
        try {
            $query = "SELECT COUNT(*) as total FROM lessons l WHERE 1=1";
            
            $params = [];
            
            if (!empty($search)) {
                $query .= " AND (l.title LIKE ? OR l.content LIKE ?)";
                $params[] = '%' . $search . '%';
                $params[] = '%' . $search . '%';
            }
            
            if (!empty($teacherId)) {
                $query .= " AND l.teacher_id = ?";
                $params[] = $teacherId;
            }
            
            if ($status === 'published') {
                $query .= " AND l.is_published = 1";
            } elseif ($status === 'draft') {
                $query .= " AND l.is_published = 0";
            } elseif ($status === 'approved') {
                $query .= " AND l.is_approved = 1";
            } elseif ($status === 'pending') {
                $query .= " AND l.is_approved = 0";
            }
            
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute($params);
            
            $result = $stmt->fetch();
            
            return $result['total'] ?? 0;
        } catch (PDOException $e) {
            return 0;
        }
    }

    /**
     * Reject lesson
     */
    public function reject($lessonId) {
        try {
            $query = "UPDATE lessons SET is_approved = 0 WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $result = $stmt->execute([':id' => $lessonId]);
            
            if ($result) {
                return ['success' => true, 'message' => 'Lesson rejected'];
            }
            
            return ['success' => false, 'error' => 'Failed to reject lesson'];
        } catch (PDOException $e) {
            return ['success' => false, 'error' => 'Database error'];
        }
    }

    /**
     * Get total lessons count by teacher
     */
    public function getTotalLessonsByTeacher($teacherId) {
        try {
            $stmt = $this->conn->prepare("SELECT COUNT(*) as total FROM lessons WHERE teacher_id = ?");
            $stmt->execute([$teacherId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['total'] ?? 0;
        } catch (PDOException $e) {
            return 0;
        }
    }

    /**
     * Get published lessons by class
     */
    public function getPublishedLessonsByClass($classId, $subjectId = null) {
        try {
            $sql = "SELECT l.*, 
                        s.name as subject_name,
                        c.name as class_name,
                        u.first_name as teacher_name,
                        u.last_name as teacher_last_name,
                        (SELECT COUNT(*) FROM lesson_materials WHERE lesson_id = l.id) as materials_count
                    FROM lessons l
                    LEFT JOIN subjects s ON l.subject_id = s.id
                    LEFT JOIN classes c ON l.class_id = c.id
                    LEFT JOIN users u ON l.teacher_id = u.id
                    WHERE l.is_published = 1 
                    AND l.class_id = :class_id";
            
            $params = [':class_id' => $classId];
            
            if ($subjectId) {
                $sql .= " AND l.subject_id = :subject_id";
                $params[':subject_id'] = $subjectId;
            }
            
            $sql .= " ORDER BY l.created_at DESC";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->execute($params);
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Error getting published lessons by class: " . $e->getMessage());
            return array();
        }
    }

    /**
     * Search published lessons by class
     */
    public function searchPublishedByClass($searchTerm, $classId, $subjectId = null) {
        try {
            $searchPattern = '%' . $searchTerm . '%';
            
            $sql = "SELECT l.*, 
                        s.name as subject_name,
                        c.name as class_name,
                        u.first_name as teacher_name,
                        u.last_name as teacher_last_name,
                        (SELECT COUNT(*) FROM lesson_materials WHERE lesson_id = l.id) as materials_count
                    FROM lessons l
                    LEFT JOIN subjects s ON l.subject_id = s.id
                    LEFT JOIN classes c ON l.class_id = c.id
                    LEFT JOIN users u ON l.teacher_id = u.id
                    WHERE l.is_published = 1 
                    AND l.class_id = :class_id
                    AND (l.title LIKE :search1 
                        OR l.content LIKE :search2)";
            
            $params = array(
                ':class_id' => $classId,
                ':search1' => $searchPattern,
                ':search2' => $searchPattern
            );
            
            if ($subjectId) {
                $sql .= " AND l.subject_id = :subject_id";
                $params[':subject_id'] = $subjectId;
            }
            
            $sql .= " ORDER BY l.created_at DESC";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->execute($params);
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Error searching published lessons by class: " . $e->getMessage());
            return array();
        }
    }
}
?>