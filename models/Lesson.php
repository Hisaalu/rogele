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
    
    // ============= MAIN METHODS =============
    
    /**
     * Create lesson with files
     */
    public function create($data, $files = null) {
        try {
            $this->conn->beginTransaction();
            
            // Insert lesson
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
            
            // Handle file uploads if any
            if ($files && !empty($files['name'][0])) {
                $this->uploadMaterials($lessonId, $files);
            }
            
            $this->conn->commit();
            return ['success' => true, 'lesson_id' => $lessonId];
            
        } catch (Exception $e) {
            $this->conn->rollBack();
            error_log("Lesson creation error: " . $e->getMessage());
            return ['success' => false, 'error' => 'Failed to create lesson'];
        }
    }
    
    /**
     * Get lesson by ID with materials
     */
    public function getById($lessonId) {
        try {
            // Increment view count
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
                // Get materials
                $materialQuery = "SELECT * FROM lesson_materials WHERE lesson_id = :lesson_id";
                $materialStmt = $this->conn->prepare($materialQuery);
                $materialStmt->execute([':lesson_id' => $lessonId]);
                $lesson['materials'] = $materialStmt->fetchAll();
            }
            
            return $lesson;
        } catch (PDOException $e) {
            error_log("Get by ID error: " . $e->getMessage());
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
            error_log("Get by class error: " . $e->getMessage());
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
            
            error_log("Lesson Model - getByTeacher for teacher $teacherId found " . count($results) . " lessons");
            
            return $results;
        } catch (PDOException $e) {
            error_log("Get lessons by teacher error: " . $e->getMessage());
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
            error_log("Get all lessons error: " . $e->getMessage());
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
            error_log("Lesson update error: " . $e->getMessage());
            return ['success' => false, 'error' => 'Database error'];
        }
    }
    
    /**
     * Delete lesson
     */
    public function delete($lessonId) {
        try {
            // First delete related materials
            $materialQuery = "DELETE FROM lesson_materials WHERE lesson_id = :lesson_id";
            $materialStmt = $this->conn->prepare($materialQuery);
            $materialStmt->execute([':lesson_id' => $lessonId]);
            
            // Then delete lesson
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
            error_log("Search error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Search lessons by teacher
     */
    public function searchByTeacher($teacherId, $keyword) {
        try {
            $query = "SELECT l.*, s.name as subject_name, c.name as class_name,
                      (SELECT COUNT(*) FROM lesson_materials WHERE lesson_id = l.id) as materials_count
                      FROM lessons l
                      LEFT JOIN subjects s ON l.subject_id = s.id
                      LEFT JOIN classes c ON l.class_id = c.id
                      WHERE l.teacher_id = :teacher_id 
                      AND (l.title LIKE :keyword OR l.content LIKE :keyword)
                      ORDER BY l.created_at DESC
                      LIMIT 50";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute([
                ':teacher_id' => $teacherId,
                ':keyword' => '%' . $keyword . '%'
            ]);
            
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Search lessons by teacher error: " . $e->getMessage());
            return [];
        }
    }
    
    // ============= UTILITY METHODS =============
    
    /**
     * Increment view count
     */
    private function incrementViews($lessonId) {
        try {
            $query = "UPDATE lessons SET views = views + 1 WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([':id' => $lessonId]);
        } catch (PDOException $e) {
            // Silently fail
        }
    }
    
    /**
     * Upload lesson materials
     */
    private function uploadMaterials($lessonId, $files) {
        try {
            $targetDir = UPLOAD_PATH . 'lessons/';
            
            // Create directory if it doesn't exist
            if (!file_exists($targetDir)) {
                mkdir($targetDir, 0777, true);
            }
            
            for ($i = 0; $i < count($files['name']); $i++) {
                if ($files['error'][$i] === UPLOAD_ERR_OK) {
                    $fileName = time() . '_' . basename($files['name'][$i]);
                    $targetFile = $targetDir . $fileName;
                    
                    if (move_uploaded_file($files['tmp_name'][$i], $targetFile)) {
                        $query = "INSERT INTO lesson_materials (lesson_id, file_name, file_path, file_type, file_size) 
                                  VALUES (:lesson_id, :file_name, :file_path, :file_type, :file_size)";
                        
                        $stmt = $this->conn->prepare($query);
                        $stmt->execute([
                            ':lesson_id' => $lessonId,
                            ':file_name' => $files['name'][$i],
                            ':file_path' => 'uploads/lessons/' . $fileName,
                            ':file_type' => $files['type'][$i],
                            ':file_size' => $files['size'][$i]
                        ]);
                    }
                }
            }
        } catch (PDOException $e) {
            error_log("Material upload error: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Bookmark lesson
     */
    public function bookmark($userId, $lessonId) {
        try {
            // Check if already bookmarked
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
            error_log("Bookmark error: " . $e->getMessage());
            return ['success' => false, 'error' => 'Failed to bookmark lesson'];
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
                return ['success' => true, 'message' => 'Bookmark removed successfully'];
            }
            
            return ['success' => false, 'error' => 'Failed to remove bookmark'];
        } catch (PDOException $e) {
            error_log("Remove bookmark error: " . $e->getMessage());
            return ['success' => false, 'error' => 'Failed to remove bookmark'];
        }
    }
    
    /**
     * Get bookmarked lessons
     */
    public function getBookmarks($userId) {
        try {
            $query = "SELECT l.*, s.name as subject_name,
                     b.created_at as bookmarked_at
                     FROM bookmarks b
                     JOIN lessons l ON b.lesson_id = l.id
                     LEFT JOIN subjects s ON l.subject_id = s.id
                     WHERE b.user_id = :user_id
                     ORDER BY b.created_at DESC";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute([':user_id' => $userId]);
            
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Get bookmarks error: " . $e->getMessage());
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
            error_log("Get popular error: " . $e->getMessage());
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
            error_log("Approve lesson error: " . $e->getMessage());
            return ['success' => false, 'error' => 'Failed to approve lesson'];
        }
    }
    
    /**
     * Get user progress for lessons (placeholder)
     */
    public function getUserProgress($userId) {
        try {
            // This would track which lessons a user has viewed/completed
            // For now, return empty array
            return [];
        } catch (PDOException $e) {
            error_log("Get user progress error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get views by teacher for analytics
     */
    public function getViewsByTeacher($teacherId) {
        try {
            $query = "SELECT 
                        DATE(l.created_at) as date,
                        SUM(l.views) as total_views,
                        COUNT(l.id) as lesson_count
                      FROM lessons l
                      WHERE l.teacher_id = :teacher_id
                      GROUP BY DATE(l.created_at)
                      ORDER BY date DESC
                      LIMIT 30";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute([':teacher_id' => $teacherId]);
            
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Get views by teacher error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Delete lesson material
     */
    public function deleteMaterial($materialId) {
        try {
            // Get file path first
            $query = "SELECT file_path FROM lesson_materials WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([':id' => $materialId]);
            $material = $stmt->fetch();
            
            if ($material) {
                // Delete file from server
                $filePath = __DIR__ . '/../public/' . $material['file_path'];
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
                
                // Delete from database
                $deleteQuery = "DELETE FROM lesson_materials WHERE id = :id";
                $deleteStmt = $this->conn->prepare($deleteQuery);
                $deleteStmt->execute([':id' => $materialId]);
                
                return ['success' => true];
            }
            
            return ['success' => false, 'error' => 'Material not found'];
        } catch (PDOException $e) {
            error_log("Delete material error: " . $e->getMessage());
            return ['success' => false, 'error' => 'Database error'];
        }
    }
}
?>