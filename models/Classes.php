<?php
// File: /models/Classes.php
require_once __DIR__ . '/../config/database.php';

class Classes {
    private $db;
    private $conn;
    private $classCache = [];
    private $allClassesCache = null;
    private $activeClassesCache = null;
    
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
            return $fetchAll ? $stmt->fetchAll() : $stmt;
        } catch (PDOException $e) {
            return $fetchAll ? [] : null;
        }
    }
    
    private function executeUpdate($query, $params, $successMessage, $errorMessage) {
        try {
            $stmt = $this->conn->prepare($query);
            $result = $stmt->execute($params);
            
            if ($result) {
                $this->invalidateCache();
                return ['success' => true, 'message' => $successMessage];
            }
            return ['success' => false, 'error' => $errorMessage];
        } catch (PDOException $e) {
            return ['success' => false, 'error' => 'Database error'];
        }
    }
    
    private function invalidateCache() {
        $this->classCache = [];
        $this->allClassesCache = null;
        $this->activeClassesCache = null;
    }
    
    private function getCachedClass($id) {
        if (isset($this->classCache[$id])) {
            return $this->classCache[$id];
        }
        
        $query = "SELECT * FROM classes WHERE id = :id";
        $result = $this->executeQuery($query, [':id' => $id], false);
        $this->classCache[$id] = $result ? $result->fetch() : null;
        return $this->classCache[$id];
    }
    
    private function getBaseQuery($activeOnly = false) {
        $query = "SELECT * FROM classes";
        if ($activeOnly) {
            $query .= " WHERE is_active = 1";
        }
        $query .= " ORDER BY level";
        return $query;
    }
    
    // ==================== PUBLIC METHODS ====================
    public function getAll() {
        if ($this->allClassesCache !== null) {
            return $this->allClassesCache;
        }
        
        $query = $this->getBaseQuery(false);
        $this->allClassesCache = $this->executeQuery($query);
        return $this->allClassesCache;
    }
    
    public function getById($id) {
        return $this->getCachedClass($id);
    }
    
    public function getByTeacher($teacherId) {
        $query = "SELECT DISTINCT c.* FROM classes c 
                  LEFT JOIN subjects s ON c.id = s.class_id 
                  WHERE s.teacher_id = :teacher_id 
                  ORDER BY c.level";
        
        return $this->executeQuery($query, [':teacher_id' => $teacherId]);
    }
    
    public function create($data) {
        $query = "INSERT INTO classes (name, level, description, is_active, created_at) 
                  VALUES (:name, :level, :description, :is_active, NOW())";
        
        try {
            $stmt = $this->conn->prepare($query);
            $result = $stmt->execute([
                ':name' => $data['name'],
                ':level' => $data['level'],
                ':description' => $data['description'] ?? null,
                ':is_active' => $data['is_active'] ?? 1
            ]);
            
            if ($result) {
                $this->invalidateCache();
                return ['success' => true, 'id' => $this->conn->lastInsertId()];
            }
            return ['success' => false, 'error' => 'Failed to create class'];
        } catch (PDOException $e) {
            return ['success' => false, 'error' => 'Database error'];
        }
    }
    
    public function update($id, $data) {
        $query = "UPDATE classes SET 
                  name = :name,
                  level = :level,
                  description = :description,
                  is_active = :is_active,
                  updated_at = NOW()
                  WHERE id = :id";
        
        return $this->executeUpdate(
            $query,
            [
                ':name' => $data['name'],
                ':level' => $data['level'],
                ':description' => $data['description'] ?? null,
                ':is_active' => $data['is_active'] ?? 1,
                ':id' => $id
            ],
            'Class updated successfully',
            'Failed to update class'
        );
    }
    
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
                $this->invalidateCache();
                return ['success' => true, 'message' => 'Class deleted successfully'];
            }
            return ['success' => false, 'error' => 'Failed to delete class'];
        } catch (PDOException $e) {
            return ['success' => false, 'error' => 'Database error'];
        }
    }
    
    public function getByLevel($level) {
        $query = "SELECT * FROM classes WHERE level = :level";
        $result = $this->executeQuery($query, [':level' => $level], false);
        return $result ? $result->fetch() : null;
    }
    
    public function getActive() {
        if ($this->activeClassesCache !== null) {
            return $this->activeClassesCache;
        }
        
        $query = $this->getBaseQuery(true);
        $this->activeClassesCache = $this->executeQuery($query);
        return $this->activeClassesCache;
    }
    
    public function getClassesByTeacher($teacherId) {
        $query = "SELECT DISTINCT c.*
                  FROM classes c
                  JOIN subjects s ON c.id = s.class_id
                  WHERE s.teacher_id = :teacher_id
                  AND c.is_active = 1
                  ORDER BY c.level";
        
        return $this->executeQuery($query, [':teacher_id' => $teacherId]);
    }
    
    public function countClassesByTeacher($teacherId) {
        $query = "SELECT COUNT(DISTINCT c.id) as total
                  FROM classes c
                  JOIN subjects s ON c.id = s.class_id
                  WHERE s.teacher_id = :teacher_id
                  AND c.is_active = 1";
        
        $result = $this->executeQuery($query, [':teacher_id' => $teacherId], false);
        $data = $result ? $result->fetch() : null;
        return $data['total'] ?? 0;
    }
    
    public function getAllClasses() {
        if ($this->allClassesCache !== null) {
            return $this->allClassesCache;
        }
        
        $this->getAll(); 
        return $this->allClassesCache;
    }
}
?>