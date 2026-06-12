<?php
// File: /models/Report.php - Optimized Report Model
require_once __DIR__ . '/../config/database.php';

class Report {
    private $db;
    private $conn;
    private $cachedData = [];
    
    public function __construct() {
        $this->db = Database::getInstance();
        $this->conn = $this->db->getConnection();
    }
    
    // ==================== HELPER METHODS ====================
    private function addDateRangeFilter(&$query, &$params, $startDate, $endDate, $dateField = 'created_at') {
        if ($startDate && $endDate) {
            $query .= " AND DATE($dateField) BETWEEN :start_date AND :end_date";
            $params[':start_date'] = $startDate;
            $params[':end_date'] = $endDate;
        }
    }
    
    private function addLimit(&$query, $limit) {
        if ($limit > 0) {
            $query .= " LIMIT :limit";
        }
    }
    
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
            return [];
        }
    }
    
    private function getCached($cacheKey, $query, $params = [], $ttl = 300) {
        $cacheKey = md5($cacheKey . serialize($params));
        
        if (isset($this->cachedData[$cacheKey])) {
            return $this->cachedData[$cacheKey];
        }
        
        $result = $this->executeQuery($query, $params);
        $this->cachedData[$cacheKey] = $result;
        return $result;
    }
    
    private function buildUserGroupBy() {
        return "SUM(CASE WHEN role = 'admin' THEN 1 ELSE 0 END) as admins,
                SUM(CASE WHEN role = 'teacher' THEN 1 ELSE 0 END) as teachers,
                SUM(CASE WHEN role = 'learner' THEN 1 ELSE 0 END) as learners,
                SUM(CASE WHEN role = 'external' THEN 1 ELSE 0 END) as external";
    }
    
    // ==================== PUBLIC METHODS ====================
    public function getRecentActivity($limit = 10) {
        $query = "SELECT al.*, u.first_name, u.last_name, u.email, u.role 
                  FROM activity_logs al
                  JOIN users u ON al.user_id = u.id
                  ORDER BY al.created_at DESC
                  LIMIT :limit";
        
        return $this->executeQuery($query, [':limit' => $limit]);
    }
    
    public function getUserReport($startDate = null, $endDate = null) {
        $query = "SELECT DATE(created_at) as date,
                         COUNT(*) as total,
                         {$this->buildUserGroupBy()}
                  FROM users
                  WHERE 1=1";
        
        $params = [];
        $this->addDateRangeFilter($query, $params, $startDate, $endDate, 'created_at');
        $query .= " GROUP BY DATE(created_at) ORDER BY date DESC";
        
        return $this->executeQuery($query, $params);
    }
    
    public function getQuizReport($startDate = null, $endDate = null) {
        $query = "SELECT q.id, q.title,
                         COUNT(DISTINCT qa.id) as total_attempts,
                         COUNT(DISTINCT qa.user_id) as unique_students,
                         AVG(qa.score) as avg_score,
                         MAX(qa.score) as highest_score,
                         MIN(qa.score) as lowest_score,
                         SUM(CASE WHEN qa.score >= q.passing_score THEN 1 ELSE 0 END) as passed_count
                  FROM quizzes q
                  LEFT JOIN quiz_attempts qa ON q.id = qa.quiz_id AND qa.status = 'completed'
                  WHERE 1=1";
        
        $params = [];
        $this->addDateRangeFilter($query, $params, $startDate, $endDate, 'qa.completed_at');
        $query .= " GROUP BY q.id ORDER BY total_attempts DESC";
        
        return $this->executeQuery($query, $params);
    }
    
    public function getPaymentReport($startDate = null, $endDate = null) {
        $query = "SELECT DATE(payment_date) as date,
                         COUNT(*) as transaction_count,
                         SUM(amount) as total_amount,
                         AVG(amount) as avg_amount,
                         payment_method,
                         COUNT(DISTINCT user_id) as unique_users
                  FROM payments
                  WHERE status = 'completed'";
        
        $params = [];
        $this->addDateRangeFilter($query, $params, $startDate, $endDate, 'payment_date');
        $query .= " GROUP BY DATE(payment_date), payment_method ORDER BY date DESC";
        
        return $this->executeQuery($query, $params);
    }
    
    public function getActivityReport($startDate = null, $endDate = null) {
        $query = "SELECT DATE(created_at) as date,
                         action,
                         COUNT(*) as count
                  FROM activity_logs
                  WHERE 1=1";
        
        $params = [];
        $this->addDateRangeFilter($query, $params, $startDate, $endDate, 'created_at');
        $query .= " GROUP BY DATE(created_at), action ORDER BY date DESC, count DESC";
        
        return $this->executeQuery($query, $params);
    }
    
    public function getUserGrowthData($days = 30) {
        $query = "SELECT DATE(created_at) as date,
                         COUNT(*) as new_users
                  FROM users
                  WHERE created_at >= DATE_SUB(NOW(), INTERVAL :days DAY)
                  GROUP BY DATE(created_at)
                  ORDER BY date ASC";
        
        return $this->executeQuery($query, [':days' => $days]);
    }
    
    public function getQuizPerformanceData($days = 30) {
        $query = "SELECT DATE(qa.completed_at) as date,
                         AVG(qa.score) as avg_score,
                         COUNT(qa.id) as attempts
                  FROM quiz_attempts qa
                  WHERE qa.status = 'completed'
                    AND qa.completed_at >= DATE_SUB(NOW(), INTERVAL :days DAY)
                  GROUP BY DATE(qa.completed_at)
                  ORDER BY date ASC";
        
        return $this->executeQuery($query, [':days' => $days]);
    }
    
    public function getRevenueData($days = 30) {
        $query = "SELECT DATE(payment_date) as date,
                         SUM(amount) as revenue,
                         COUNT(*) as transactions
                  FROM payments
                  WHERE status = 'completed'
                    AND payment_date >= DATE_SUB(NOW(), INTERVAL :days DAY)
                  GROUP BY DATE(payment_date)
                  ORDER BY date ASC";
        
        return $this->executeQuery($query, [':days' => $days]);
    }
    
    public function getTopStudents($limit = 10) {
        $query = "SELECT u.id, u.first_name, u.last_name, u.email,
                         COUNT(DISTINCT qa.id) as quizzes_taken,
                         AVG(qa.score) as avg_score,
                         MAX(qa.score) as highest_score,
                         c.name as class_name
                  FROM users u
                  LEFT JOIN quiz_attempts qa ON u.id = qa.user_id AND qa.status = 'completed'
                  LEFT JOIN classes c ON u.class_id = c.id
                  WHERE u.role = 'learner'
                  GROUP BY u.id
                  HAVING quizzes_taken > 0
                  ORDER BY avg_score DESC
                  LIMIT :limit";
        
        return $this->executeQuery($query, [':limit' => $limit]);
    }
    
    public function getSubscriptionStats() {
        $query = "SELECT plan_type,
                         COUNT(*) as total,
                         SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active,
                         SUM(CASE WHEN status = 'expired' THEN 1 ELSE 0 END) as expired,
                         SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled
                  FROM subscriptions
                  GROUP BY plan_type";
        
        return $this->executeQuery($query);
    }
    
    public function exportToCSV($data, $filename) {
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        
        $output = fopen('php://output', 'w');
        
        if (!empty($data)) {
            fputcsv($output, array_keys($data[0]));
            foreach ($data as $row) {
                fputcsv($output, $row);
            }
        }
        
        fclose($output);
        exit;
    }
}
?>