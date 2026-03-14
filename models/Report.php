// File: /models/Report.php
<?php
require_once __DIR__ . '/../config/database.php';

class Report {
    private $db;
    private $conn;
    
    public function __construct() {
        $this->db = Database::getInstance();
        $this->conn = $this->db->getConnection();
    }
    
    // Generate learner performance report
    public function learnerPerformance($classId = null, $startDate = null, $endDate = null) {
        try {
            $query = "SELECT 
                        u.id,
                        u.registration_number,
                        u.first_name,
                        u.last_name,
                        c.name as class_name,
                        COUNT(DISTINCT qa.id) as quizzes_taken,
                        AVG(qa.score) as avg_score,
                        SUM(CASE WHEN qa.score >= q.passing_score THEN 1 ELSE 0 END) as quizzes_passed,
                        COUNT(DISTINCT l.id) as lessons_viewed
                      FROM users u
                      LEFT JOIN classes c ON u.class_id = c.id
                      LEFT JOIN quiz_attempts qa ON u.id = qa.user_id AND qa.status = 'completed'
                      LEFT JOIN quizzes q ON qa.quiz_id = q.id
                      LEFT JOIN lessons l ON u.id = l.teacher_id
                      WHERE u.role = 'learner'";
            
            $params = [];
            
            if ($classId) {
                $query .= " AND u.class_id = :class_id";
                $params[':class_id'] = $classId;
            }
            
            if ($startDate && $endDate) {
                $query .= " AND qa.completed_at BETWEEN :start_date AND :end_date";
                $params[':start_date'] = $startDate;
                $params[':end_date'] = $endDate;
            }
            
            $query .= " GROUP BY u.id ORDER BY avg_score DESC";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute($params);
            
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }
    
    // Generate quiz performance report
    public function quizPerformance($classId = null, $subjectId = null) {
        try {
            $query = "SELECT 
                        q.id,
                        q.title,
                        c.name as class_name,
                        s.name as subject_name,
                        u.first_name as teacher_name,
                        COUNT(DISTINCT qa.id) as total_attempts,
                        AVG(qa.score) as avg_score,
                        MAX(qa.score) as highest_score,
                        MIN(qa.score) as lowest_score,
                        COUNT(DISTINCT CASE WHEN qa.score >= q.passing_score THEN qa.user_id END) as students_passed
                      FROM quizzes q
                      LEFT JOIN classes c ON q.class_id = c.id
                      LEFT JOIN subjects s ON q.subject_id = s.id
                      LEFT JOIN users u ON q.teacher_id = u.id
                      LEFT JOIN quiz_attempts qa ON q.id = qa.quiz_id AND qa.status = 'completed'
                      WHERE 1=1";
            
            $params = [];
            
            if ($classId) {
                $query .= " AND q.class_id = :class_id";
                $params[':class_id'] = $classId;
            }
            
            if ($subjectId) {
                $query .= " AND q.subject_id = :subject_id";
                $params[':subject_id'] = $subjectId;
            }
            
            $query .= " GROUP BY q.id ORDER BY total_attempts DESC";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute($params);
            
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }
    
    // Generate payment report
    public function paymentReport($startDate = null, $endDate = null) {
        try {
            $query = "SELECT 
                        DATE(p.payment_date) as date,
                        COUNT(p.id) as transaction_count,
                        SUM(p.amount) as total_amount,
                        AVG(p.amount) as avg_amount,
                        s.plan_type,
                        COUNT(DISTINCT p.user_id) as unique_users
                      FROM payments p
                      JOIN subscriptions s ON p.subscription_id = s.id
                      WHERE p.status = 'completed'";
            
            $params = [];
            
            if ($startDate && $endDate) {
                $query .= " AND DATE(p.payment_date) BETWEEN :start_date AND :end_date";
                $params[':start_date'] = $startDate;
                $params[':end_date'] = $endDate;
            }
            
            $query .= " GROUP BY DATE(p.payment_date), s.plan_type
                        ORDER BY date DESC";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute($params);
            
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }
    
    // Generate teacher activity report
    public function teacherActivity($startDate = null, $endDate = null) {
        try {
            $query = "SELECT 
                        u.id,
                        u.first_name,
                        u.last_name,
                        u.email,
                        COUNT(DISTINCT l.id) as lessons_created,
                        COUNT(DISTINCT q.id) as quizzes_created,
                        COUNT(DISTINCT a.id) as announcements_made,
                        MAX(l.created_at) as last_activity
                      FROM users u
                      LEFT JOIN lessons l ON u.id = l.teacher_id
                      LEFT JOIN quizzes q ON u.id = q.teacher_id
                      LEFT JOIN announcements a ON u.id = a.created_by
                      WHERE u.role = 'teacher'";
            
            $params = [];
            
            if ($startDate && $endDate) {
                $query .= " AND (l.created_at BETWEEN :start_date AND :end_date
                           OR q.created_at BETWEEN :start_date AND :end_date)";
                $params[':start_date'] = $startDate;
                $params[':end_date'] = $endDate;
            }
            
            $query .= " GROUP BY u.id
                        ORDER BY lessons_created DESC";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute($params);
            
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }
    
    // Get dashboard analytics
    public function getAnalytics() {
        try {
            $analytics = [];
            
            // Total users by role
            $userQuery = "SELECT role, COUNT(*) as count FROM users GROUP BY role";
            $userStmt = $this->conn->query($userQuery);
            $analytics['users_by_role'] = $userStmt->fetchAll();
            
            // Active subscriptions
            $subQuery = "SELECT plan_type, COUNT(*) as count 
                        FROM subscriptions 
                        WHERE status = 'active' AND end_date > NOW()
                        GROUP BY plan_type";
            $subStmt = $this->conn->query($subQuery);
            $analytics['active_subscriptions'] = $subStmt->fetchAll();
            
            // Total revenue
            $revenueQuery = "SELECT SUM(amount) as total_revenue 
                            FROM payments 
                            WHERE status = 'completed'";
            $revenueStmt = $this->conn->query($revenueQuery);
            $analytics['total_revenue'] = $revenueStmt->fetch()['total_revenue'];
            
            // Monthly revenue
            $monthlyQuery = "SELECT DATE_FORMAT(payment_date, '%Y-%m') as month,
                            SUM(amount) as revenue
                            FROM payments
                            WHERE status = 'completed'
                            GROUP BY DATE_FORMAT(payment_date, '%Y-%m')
                            ORDER BY month DESC
                            LIMIT 6";
            $monthlyStmt = $this->conn->query($monthlyQuery);
            $analytics['monthly_revenue'] = $monthlyStmt->fetchAll();
            
            // Content statistics
            $contentQuery = "SELECT 
                            (SELECT COUNT(*) FROM lessons) as total_lessons,
                            (SELECT COUNT(*) FROM quizzes) as total_quizzes,
                            (SELECT COUNT(*) FROM quiz_attempts) as total_quiz_attempts,
                            (SELECT SUM(views) FROM lessons) as total_lesson_views";
            $contentStmt = $this->conn->query($contentQuery);
            $analytics['content_stats'] = $contentStmt->fetch();
            
            // Recent activity
            $activityQuery = "SELECT al.*, u.first_name, u.last_name, u.role
                             FROM activity_logs al
                             JOIN users u ON al.user_id = u.id
                             ORDER BY al.created_at DESC
                             LIMIT 20";
            $activityStmt = $this->conn->query($activityQuery);
            $analytics['recent_activity'] = $activityStmt->fetchAll();
            
            return $analytics;
        } catch (PDOException $e) {
            return null;
        }
    }
}
?>