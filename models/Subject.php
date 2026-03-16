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
    
    /**
     * Get all subjects
     */
    public function getAll() {
        try {
            $query = "SELECT s.*, c.name as class_name 
                      FROM subjects s
                      LEFT JOIN classes c ON s.class_id = c.id
                      WHERE s.is_active = 1
                      ORDER BY c.level, s.name";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Get all subjects error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get subjects by class
     */
    public function getByClass($classId) {
        try {
            $query = "SELECT * FROM subjects 
                      WHERE class_id = :class_id AND is_active = 1 
                      ORDER BY name";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([':class_id' => $classId]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Get subjects by class error: " . $e->getMessage());
            return [];
        }
    }
}