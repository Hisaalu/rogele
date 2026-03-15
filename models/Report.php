<?php
// File: /models/Report.php
require_once __DIR__ . '/../config/database.php';

class Report {
    private $db;
    private $conn;
    
    public function __construct() {
        $this->db = Database::getInstance();
        $this->conn = $this->db->getConnection();
    }
    
    /**
     * Get recent activity logs
     */
    public function getRecentActivity($limit = 10) {
        try {
            $query = "SELECT al.*, u.first_name, u.last_name, u.email, u.role 
                      FROM activity_logs al
                      JOIN users u ON al.user_id = u.id
                      ORDER BY al.created_at DESC
                      LIMIT :limit";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Get recent activity error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get user report data
     */
    public function getUserReport($startDate = null, $endDate = null) {
        try {
            $query = "SELECT 
                        DATE(created_at) as date,
                        COUNT(*) as total,
                        SUM(CASE WHEN role = 'admin' THEN 1 ELSE 0 END) as admins,
                        SUM(CASE WHEN role = 'teacher' THEN 1 ELSE 0 END) as teachers,
                        SUM(CASE WHEN role = 'learner' THEN 1 ELSE 0 END) as learners,
                        SUM(CASE WHEN role = 'external' THEN 1 ELSE 0 END) as external
                      FROM users
                      WHERE 1=1";
            
            $params = [];
            
            if ($startDate && $endDate) {
                $query .= " AND DATE(created_at) BETWEEN :start_date AND :end_date";
                $params[':start_date'] = $startDate;
                $params[':end_date'] = $endDate;
            }
            
            $query .= " GROUP BY DATE(created_at)
                        ORDER BY date DESC";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute($params);
            
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Get user report error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get quiz report data
     */
    public function getQuizReport($startDate = null, $endDate = null) {
        try {
            $query = "SELECT 
                        q.id,
                        q.title,
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
            
            if ($startDate && $endDate) {
                $query .= " AND DATE(qa.completed_at) BETWEEN :start_date AND :end_date";
                $params[':start_date'] = $startDate;
                $params[':end_date'] = $endDate;
            }
            
            $query .= " GROUP BY q.id
                        ORDER BY total_attempts DESC";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute($params);
            
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Get quiz report error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get payment report data
     */
    public function getPaymentReport($startDate = null, $endDate = null) {
        try {
            $query = "SELECT 
                        DATE(payment_date) as date,
                        COUNT(*) as transaction_count,
                        SUM(amount) as total_amount,
                        AVG(amount) as avg_amount,
                        payment_method,
                        COUNT(DISTINCT user_id) as unique_users
                      FROM payments
                      WHERE status = 'completed'";
            
            $params = [];
            
            if ($startDate && $endDate) {
                $query .= " AND DATE(payment_date) BETWEEN :start_date AND :end_date";
                $params[':start_date'] = $startDate;
                $params[':end_date'] = $endDate;
            }
            
            $query .= " GROUP BY DATE(payment_date), payment_method
                        ORDER BY date DESC";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute($params);
            
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Get payment report error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get activity report data
     */
    public function getActivityReport($startDate = null, $endDate = null) {
        try {
            $query = "SELECT 
                        DATE(created_at) as date,
                        action,
                        COUNT(*) as count
                      FROM activity_logs
                      WHERE 1=1";
            
            $params = [];
            
            if ($startDate && $endDate) {
                $query .= " AND DATE(created_at) BETWEEN :start_date AND :end_date";
                $params[':start_date'] = $startDate;
                $params[':end_date'] = $endDate;
            }
            
            $query .= " GROUP BY DATE(created_at), action
                        ORDER BY date DESC, count DESC";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute($params);
            
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Get activity report error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get user growth chart data
     */
    public function getUserGrowthData($days = 30) {
        try {
            $query = "SELECT 
                        DATE(created_at) as date,
                        COUNT(*) as new_users
                      FROM users
                      WHERE created_at >= DATE_SUB(NOW(), INTERVAL :days DAY)
                      GROUP BY DATE(created_at)
                      ORDER BY date ASC";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindValue(':days', $days, PDO::PARAM_INT);
            $stmt->execute();
            
            $results = $stmt->fetchAll();
        
            // If no results, return empty array
            if (empty($results)) {
                return [];
            }
            
            return $results;
        } catch (PDOException $e) {
            error_log("Get user growth data error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get quiz performance chart data
     */
    public function getQuizPerformanceData($days = 30) {
        try {
            $query = "SELECT 
                        DATE(qa.completed_at) as date,
                        AVG(qa.score) as avg_score,
                        COUNT(qa.id) as attempts
                      FROM quiz_attempts qa
                      WHERE qa.status = 'completed'
                        AND qa.completed_at >= DATE_SUB(NOW(), INTERVAL :days DAY)
                      GROUP BY DATE(qa.completed_at)
                      ORDER BY date ASC";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindValue(':days', $days, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Get quiz performance data error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get revenue chart data
     */
    public function getRevenueData($days = 30) {
        try {
            $query = "SELECT 
                        DATE(payment_date) as date,
                        SUM(amount) as revenue,
                        COUNT(*) as transactions
                      FROM payments
                      WHERE status = 'completed'
                        AND payment_date >= DATE_SUB(NOW(), INTERVAL :days DAY)
                      GROUP BY DATE(payment_date)
                      ORDER BY date ASC";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindValue(':days', $days, PDO::PARAM_INT);
            $stmt->execute();
            
            $results = $stmt->fetchAll();
        
            // If no results, return empty array
            if (empty($results)) {
                return [];
            }
            
            return $results;
        } catch (PDOException $e) {
            error_log("Get revenue data error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get top performing students
     */
    public function getTopStudents($limit = 10) {
        try {
            $query = "SELECT 
                        u.id,
                        u.first_name,
                        u.last_name,
                        u.email,
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
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Get top students error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get subscription statistics
     */
    public function getSubscriptionStats() {
        try {
            $query = "SELECT 
                        plan_type,
                        COUNT(*) as total,
                        SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active,
                        SUM(CASE WHEN status = 'expired' THEN 1 ELSE 0 END) as expired,
                        SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled
                      FROM subscriptions
                      GROUP BY plan_type";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Get subscription stats error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Export report data to CSV
     */
    public function exportToCSV($data, $filename) {
        // Set headers for download
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        
        // Open output stream
        $output = fopen('php://output', 'w');
        
        // Add headers if data exists
        if (!empty($data)) {
            fputcsv($output, array_keys($data[0]));
            
            // Add data rows
            foreach ($data as $row) {
                fputcsv($output, $row);
            }
        }
        
        fclose($output);
        exit;
    }
}
?>