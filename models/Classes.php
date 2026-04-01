<?php
// File: /models/Classes.php
require_once __DIR__ . '/../config/database.php';

class Classes {
    private $db;
    private $conn;
    
    public function __construct() {
        $this->db = Database::getInstance();
        $this->conn = $this->db->getConnection();
    }
    
    /**
     * Get all classes
     */
    public function getAll() {
        try {
            $query = "SELECT * FROM classes ORDER BY level";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }
    
    /**
     * Get class by ID
     */
    public function getById($id) {
        try {
            $query = "SELECT * FROM classes WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([':id' => $id]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            return null;
        }
    }
    
    /**
     * Get classes by teacher
     */
    public function getByTeacher($teacherId) {
        try {
            $query = "SELECT DISTINCT c.* FROM classes c 
                      LEFT JOIN subjects s ON c.id = s.class_id 
                      WHERE s.teacher_id = :teacher_id 
                      ORDER BY c.level";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([':teacher_id' => $teacherId]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }
    
    /**
     * Create class
     */
    public function create($data) {
        try {
            $query = "INSERT INTO classes (name, level, description, is_active, created_at) 
                      VALUES (:name, :level, :description, :is_active, NOW())";
            $stmt = $this->conn->prepare($query);
            $result = $stmt->execute([
                ':name' => $data['name'],
                ':level' => $data['level'],
                ':description' => $data['description'] ?? null,
                ':is_active' => $data['is_active'] ?? 1
            ]);
            
            if ($result) {
                return ['success' => true, 'id' => $this->conn->lastInsertId()];
            }
            return ['success' => false, 'error' => 'Failed to create class'];
        } catch (PDOException $e) {
            return ['success' => false, 'error' => 'Database error'];
        }
    }
    
    /**
     * Update class
     */
    public function update($id, $data) {
        try {
            $query = "UPDATE classes SET 
                      name = :name,
                      level = :level,
                      description = :description,
                      is_active = :is_active,
                      updated_at = NOW()
                      WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $result = $stmt->execute([
                ':name' => $data['name'],
                ':level' => $data['level'],
                ':description' => $data['description'] ?? null,
                ':is_active' => $data['is_active'] ?? 1,
                ':id' => $id
            ]);
            
            if ($result) {
                return ['success' => true, 'message' => 'Class updated successfully'];
            }
            return ['success' => false, 'error' => 'Failed to update class'];
        } catch (PDOException $e) {
            return ['success' => false, 'error' => 'Database error'];
        }
    }
    
    /**
     * Delete class
     */
    public function delete($id) {
        try {
            $checkQuery = "SELECT COUNT(*) as count FROM subjects WHERE class_id = :class_id";
            $checkStmt = $this->conn->prepare($checkQuery);
            $checkStmt->execute([':class_id' => $id]);
            $result = $checkStmt->fetch();
            
            if ($result['count'] > 0) {
                return ['success' => false, 'error' => 'Cannot delete class with existing subjects'];
            }
            
            $query = "DELETE FROM classes WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $result = $stmt->execute([':id' => $id]);
            
            if ($result) {
                return ['success' => true, 'message' => 'Class deleted successfully'];
            }
            return ['success' => false, 'error' => 'Failed to delete class'];
        } catch (PDOException $e) {
            return ['success' => false, 'error' => 'Database error'];
        }
    }
    
    /**
     * Get class by level
     */
    public function getByLevel($level) {
        try {
            $query = "SELECT * FROM classes WHERE level = :level";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([':level' => $level]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            return null;
        }
    }
    
    /**
     * Get active classes
     */
    public function getActive() {
        try {
            $query = "SELECT * FROM classes WHERE is_active = 1 ORDER BY level";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }

    /**
     * Get classes taught by a teacher
     */
    public function getClassesByTeacher($teacherId) {
        try {
            $query = "SELECT DISTINCT c.*
                    FROM classes c
                    JOIN subjects s ON c.id = s.class_id
                    WHERE s.teacher_id = :teacher_id
                    AND c.is_active = 1
                    ORDER BY c.level";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute([':teacher_id' => $teacherId]);
            
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }

    /**
     * Count classes taught by a teacher
     */
    public function countClassesByTeacher($teacherId) {
        try {
            $query = "SELECT COUNT(DISTINCT c.id) as total
                    FROM classes c
                    JOIN subjects s ON c.id = s.class_id
                    WHERE s.teacher_id = :teacher_id
                    AND c.is_active = 1";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute([':teacher_id' => $teacherId]);
            $result = $stmt->fetch();
            
            return $result['total'] ?? 0;
        } catch (PDOException $e) {
            return 0;
        }
    }

    /**
     * Get ALL classes
     */
    public function getAllClasses() {
        try {
            $stmt = $this->conn->prepare("SELECT id, name FROM classes ORDER BY name");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }
}
?>