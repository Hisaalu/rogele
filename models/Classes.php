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
            error_log("Get all classes error: " . $e->getMessage());
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
            error_log("Get class by ID error: " . $e->getMessage());
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
            error_log("Get classes by teacher error: " . $e->getMessage());
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
            error_log("Create class error: " . $e->getMessage());
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
            error_log("Update class error: " . $e->getMessage());
            return ['success' => false, 'error' => 'Database error'];
        }
    }
    
    /**
     * Delete class
     */
    public function delete($id) {
        try {
            // Check if class has subjects
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
            error_log("Delete class error: " . $e->getMessage());
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
            error_log("Get class by level error: " . $e->getMessage());
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
            error_log("Get active classes error: " . $e->getMessage());
            return [];
        }
    }
}
?>