<?php
// File: /models/Subject.php
require_once __DIR__ . '/../config/database.php';

class Subject {
    private $db;
    private $conn;
    
    public function __construct() {
        $this->db = Database::getInstance();
        $this->conn = $this->db->getConnection();
    }
    
    public function getAll() {
        try {
            $query = "SELECT s.*, c.name as class_name, u.first_name, u.last_name 
                      FROM subjects s
                      LEFT JOIN classes c ON s.class_id = c.id
                      LEFT JOIN users u ON s.teacher_id = u.id
                      ORDER BY c.level ASC, s.name ASC";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }
    
    public function getById($id) {
        try {
            $query = "SELECT s.*, c.name as class_name 
                      FROM subjects s 
                      LEFT JOIN classes c ON s.class_id = c.id 
                      WHERE s.id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([':id' => $id]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            return null;
        }
    }

    public function create($data) {
        try {
            $query = "INSERT INTO subjects (class_id, teacher_id, name, code, description, is_active, created_at) 
                      VALUES (:class_id, :teacher_id, :name, :code, :description, :is_active, NOW())";
            $stmt = $this->conn->prepare($query);
            $result = $stmt->execute([
                ':class_id' => $data['class_id'],
                ':teacher_id' => !empty($data['teacher_id']) ? $data['teacher_id'] : null,
                ':name' => $data['name'],
                ':code' => $data['code'] ?? null,
                ':description' => $data['description'] ?? null,
                ':is_active' => $data['is_active'] ?? 1
            ]);
            
            return $result ? ['success' => true] : ['success' => false, 'error' => 'Failed to create subject'];
        } catch (PDOException $e) {
            return ['success' => false, 'error' => 'Database error: ' . $e->getMessage()];
        }
    }

    public function update($id, $data) {
        try {
            $query = "UPDATE subjects SET 
                      class_id = :class_id,
                      teacher_id = :teacher_id,
                      name = :name,
                      code = :code,
                      description = :description,
                      is_active = :is_active,
                      updated_at = NOW()
                      WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $result = $stmt->execute([
                ':class_id' => $data['class_id'],
                ':teacher_id' => !empty($data['teacher_id']) ? $data['teacher_id'] : null,
                ':name' => $data['name'],
                ':code' => $data['code'] ?? null,
                ':description' => $data['description'] ?? null,
                ':is_active' => $data['is_active'] ?? 1,
                ':id' => $id
            ]);
            
            return $result ? ['success' => true] : ['success' => false, 'error' => 'Failed to update subject'];
        } catch (PDOException $e) {
            return ['success' => false, 'error' => 'Database error'];
        }
    }

    public function delete($id) {
        try {
            $query = "DELETE FROM subjects WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $result = $stmt->execute([':id' => $id]);
            return $result ? ['success' => true] : ['success' => false, 'error' => 'Failed to delete subject'];
        } catch (PDOException $e) {
            return ['success' => false, 'error' => 'Database error'];
        }
    }

    public function getByClass($classId) {
        try {
            $query = "SELECT * FROM subjects WHERE class_id = :class_id AND is_active = 1 ORDER BY name";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([':class_id' => $classId]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }

    public function getByClassId($classId) {
        return $this->getByClass($classId);
    }

    public function getByTeacher($teacherId) {
        try {
            $query = "SELECT s.*, c.name as class_name 
                    FROM subjects s
                    LEFT JOIN classes c ON s.class_id = c.id
                    WHERE s.teacher_id = :teacher_id AND s.is_active = 1
                    ORDER BY c.name, s.name";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([':teacher_id' => $teacherId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }
}