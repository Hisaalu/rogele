<?php
// File: /models/Subscription.php
require_once __DIR__ . '/../config/database.php';

class Subscription {
    private $db;
    private $conn;
    
    public function __construct() {
        $this->db = Database::getInstance();
        $this->conn = $this->db->getConnection();
    }
    
    // Create subscription
    public function create($userId, $planType, $paymentMethod = null) {
        try {
            $amount = SUBSCRIPTION_PLANS[$planType] ?? 0;
            
            $startDate = date('Y-m-d H:i:s');
            $endDate = $this->calculateEndDate($planType, $startDate);
            
            $query = "INSERT INTO subscriptions (user_id, plan_type, amount, start_date, end_date, payment_method, status) 
                      VALUES (:user_id, :plan_type, :amount, :start_date, :end_date, :payment_method, 'pending')";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute([
                ':user_id' => $userId,
                ':plan_type' => $planType,
                ':amount' => $amount,
                ':start_date' => $startDate,
                ':end_date' => $endDate,
                ':payment_method' => $paymentMethod
            ]);
            
            return ['success' => true, 'subscription_id' => $this->conn->lastInsertId()];
        } catch (PDOException $e) {
            error_log("Subscription creation error: " . $e->getMessage());
            return ['success' => false, 'error' => 'Failed to create subscription'];
        }
    }
    
    // Calculate end date based on plan
    private function calculateEndDate($planType, $startDate) {
        switch ($planType) {
            case 'monthly':
                return date('Y-m-d H:i:s', strtotime($startDate . ' + 30 days'));
            case 'termly':
                return date('Y-m-d H:i:s', strtotime($startDate . ' + 3 months'));
            case 'yearly':
                return date('Y-m-d H:i:s', strtotime($startDate . ' + 1 year'));
            default:
                return $startDate;
        }
    }
    
    // Check subscription status
    public function checkStatus($userId) {
        try {
            $query = "SELECT * FROM subscriptions 
                      WHERE user_id = :user_id 
                      AND status = 'active' 
                      AND end_date > NOW() 
                      ORDER BY end_date DESC 
                      LIMIT 1";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute([':user_id' => $userId]);
            
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log("Check status error: " . $e->getMessage());
            return null;
        }
    }
    
    // Activate subscription
    public function activate($subscriptionId, $transactionId) {
        try {
            $query = "UPDATE subscriptions SET status = 'active', transaction_id = :transaction_id WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $result = $stmt->execute([
                ':transaction_id' => $transactionId,
                ':id' => $subscriptionId
            ]);
            
            if ($result) {
                return ['success' => true, 'message' => 'Subscription activated successfully'];
            }
            return ['success' => false, 'error' => 'Failed to activate subscription'];
        } catch (PDOException $e) {
            error_log("Activate subscription error: " . $e->getMessage());
            return ['success' => false, 'error' => 'Failed to activate subscription'];
        }
    }
    
    // Cancel subscription
    public function cancel($subscriptionId) {
        try {
            $query = "UPDATE subscriptions SET status = 'cancelled' WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $result = $stmt->execute([':id' => $subscriptionId]);
            
            if ($result) {
                return ['success' => true, 'message' => 'Subscription cancelled successfully'];
            }
            return ['success' => false, 'error' => 'Failed to cancel subscription'];
        } catch (PDOException $e) {
            error_log("Cancel subscription error: " . $e->getMessage());
            return ['success' => false, 'error' => 'Failed to cancel subscription'];
        }
    }
    
    // Get payment history
    public function getPaymentHistory($userId) {
        try {
            $query = "SELECT p.*, s.plan_type 
                     FROM payments p
                     JOIN subscriptions s ON p.subscription_id = s.id
                     WHERE p.user_id = :user_id
                     ORDER BY p.created_at DESC
                     LIMIT 10";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute([':user_id' => $userId]);
            
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Payment history error: " . $e->getMessage());
            return [];
        }
    }
    
    // Get all subscriptions (for admin)
    public function getAllSubscriptions($status = null) {
        try {
            $query = "SELECT s.*, u.first_name, u.last_name, u.email 
                     FROM subscriptions s
                     JOIN users u ON s.user_id = u.id";
            
            if ($status) {
                $query .= " WHERE s.status = :status";
            }
            
            $query .= " ORDER BY s.created_at DESC LIMIT 50";
            
            $stmt = $this->conn->prepare($query);
            
            if ($status) {
                $stmt->execute([':status' => $status]);
            } else {
                $stmt->execute();
            }
            
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Get all subscriptions error: " . $e->getMessage());
            return [];
        }
    }
    
    // Process payment
    public function processPayment($userId, $subscriptionId, $phoneNumber, $amount) {
        try {
            // Generate transaction ID
            $transactionId = 'TXN_' . time() . '_' . uniqid();
            
            // Create payment record
            $query = "INSERT INTO payments (user_id, subscription_id, amount, payment_method, phone_number, transaction_id, status, payment_date) 
                      VALUES (:user_id, :subscription_id, :amount, 'mobile_money', :phone_number, :transaction_id, 'completed', NOW())";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute([
                ':user_id' => $userId,
                ':subscription_id' => $subscriptionId,
                ':amount' => $amount,
                ':phone_number' => $phoneNumber,
                ':transaction_id' => $transactionId
            ]);
            
            // Activate subscription
            $this->activate($subscriptionId, $transactionId);
            
            return ['success' => true, 'transaction_id' => $transactionId];
        } catch (PDOException $e) {
            error_log("Payment processing error: " . $e->getMessage());
            return ['success' => false, 'error' => 'Payment processing failed'];
        }
    }
    
    // Check and expire subscriptions
    public function expireSubscriptions() {
        try {
            $query = "UPDATE subscriptions SET status = 'expired' 
                      WHERE status = 'active' AND end_date < NOW()";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            
            return ['success' => true, 'affected' => $stmt->rowCount()];
        } catch (PDOException $e) {
            error_log("Expire subscriptions error: " . $e->getMessage());
            return ['success' => false, 'error' => 'Failed to expire subscriptions'];
        }
    }
    
    // Get subscription revenue stats
    public function getRevenueStats($period = 'month') {
        try {
            if ($period === 'month') {
                $query = "SELECT 
                            DATE_FORMAT(created_at, '%Y-%m') as period,
                            COUNT(*) as subscription_count,
                            SUM(amount) as total_revenue
                          FROM payments
                          WHERE status = 'completed'
                          GROUP BY DATE_FORMAT(created_at, '%Y-%m')
                          ORDER BY period DESC
                          LIMIT 12";
            } else {
                $query = "SELECT 
                            DATE(created_at) as period,
                            COUNT(*) as subscription_count,
                            SUM(amount) as total_revenue
                          FROM payments
                          WHERE status = 'completed'
                          AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                          GROUP BY DATE(created_at)
                          ORDER BY period DESC";
            }
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Revenue stats error: " . $e->getMessage());
            return [];
        }
    }
}
?>