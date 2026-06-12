<?php
// File: /models/Subscription.php
require_once __DIR__ . '/../config/database.php';

class Subscription {
    private $db;
    private $conn;
    private $planDaysCache = ['monthly' => 30, 'termly' => 90, 'yearly' => 365];
    private $subscriptionCache = [];
    private $paymentCache = [];
    private $settingsCache = null;
    
    public function __construct() {
        $this->db = Database::getInstance();
        $this->conn = $this->db->getConnection();
    }
    
    // ==================== HELPER METHODS ====================
    public function getConnection() {
        return $this->conn;
    }
    
    private function getPlanDays($planType) {
        return $this->planDaysCache[$planType] ?? 30;
    }
    
    private function getPlanPrice($planType, $settings = null) {
        if ($settings === null) {
            $settings = $this->getSubscriptionSettings();
        }
        
        $prices = [
            'monthly' => $settings['monthly_price'] ?? 15000,
            'termly' => $settings['termly_price'] ?? 40000,
            'yearly' => $settings['yearly_price'] ?? 120000
        ];
        return $prices[$planType] ?? 0;
    }
    
    private function calculateEndDate($planType, $startDate) {
        $days = $this->getPlanDays($planType);
        return date('Y-m-d H:i:s', strtotime($startDate . " + {$days} days"));
    }
    
    private function executeUpdate($sql, $params, $successMessage, $errorMessage) {
        try {
            $stmt = $this->conn->prepare($sql);
            $result = $stmt->execute($params);
            
            if ($result) {
                return ['success' => true, 'message' => $successMessage];
            }
            return ['success' => false, 'error' => $errorMessage];
        } catch (PDOException $e) {
            return ['success' => false, 'error' => $errorMessage];
        }
    }
    
    private function bindParams($stmt, $params) {
        foreach ($params as $key => $value) {
            $type = is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR;
            $stmt->bindValue($key, $value, $type);
        }
    }

    private function buildWhereClause($filters, &$params) {
        $where = [];
        
        if (!empty($filters['status'])) {
            $where[] = "s.status = :status";
            $params[':status'] = $filters['status'];
        }
        
        if (!empty($filters['plan_type'])) {
            $where[] = "s.plan_type = :plan_type";
            $params[':plan_type'] = $filters['plan_type'];
        }
        
        if (!empty($filters['user_id'])) {
            $where[] = "s.user_id = :user_id";
            $params[':user_id'] = $filters['user_id'];
        }
        
        if (!empty($filters['search'])) {
            $where[] = "(u.first_name LIKE :search OR u.last_name LIKE :search OR u.email LIKE :search)";
            $params[':search'] = '%' . $filters['search'] . '%';
        }
        
        return empty($where) ? '' : ' AND ' . implode(' AND ', $where);
    }
    
    private function settingsTableExists() {
        static $exists = null;
        if ($exists === null) {
            try {
                $checkTable = $this->conn->query("SHOW TABLES LIKE 'settings'");
                $exists = $checkTable->rowCount() > 0;
            } catch (PDOException $e) {
                $exists = false;
            }
        }
        return $exists;
    }
    
    // ==================== SUBSCRIPTION MANAGEMENT ====================
    public function create($userId, $planType, $paymentMethod = null) {
        try {
            $amount = $this->getPlanPrice($planType);
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
            return ['success' => false, 'error' => 'Failed to create subscription'];
        }
    }
    
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
            return null;
        }
    }
    
    public function activate($subscriptionId, $transactionId) {
        return $this->executeUpdate(
            "UPDATE subscriptions SET status = 'active', transaction_id = :transaction_id WHERE id = :id",
            [':transaction_id' => $transactionId, ':id' => $subscriptionId],
            'Subscription activated successfully',
            'Failed to activate subscription'
        );
    }
    
    public function cancel($subscriptionId) {
        return $this->executeUpdate(
            "UPDATE subscriptions SET status = 'cancelled' WHERE id = :id",
            [':id' => $subscriptionId],
            'Subscription cancelled successfully',
            'Failed to cancel subscription'
        );
    }
    
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
            return [];
        }
    }
    
    public function getAllSubscriptions($filters = [], $limit = 20, $offset = 0) {
        try {
            $sql = "SELECT s.*, u.first_name, u.last_name, u.email, u.role as user_role
                    FROM subscriptions s
                    LEFT JOIN users u ON s.user_id = u.id
                    WHERE 1=1";
            
            $params = [];
            $sql .= $this->buildWhereClause($filters, $params);
            $sql .= " ORDER BY s.created_at DESC";
            
            if ($limit > 0) {
                $sql .= " LIMIT :limit OFFSET :offset";
                $params[':limit'] = $limit;
                $params[':offset'] = $offset;
            }
            
            $stmt = $this->conn->prepare($sql);
            $this->bindParams($stmt, $params);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }
    
    public function countAllSubscriptions($filters = []) {
        try {
            $sql = "SELECT COUNT(*) as total FROM subscriptions s LEFT JOIN users u ON s.user_id = u.id WHERE 1=1";
            
            $params = [];
            $sql .= $this->buildWhereClause($filters, $params);
            
            if (empty($filters['status'])) {
                $sql .= " AND s.status IN ('active', 'expired')";
            }
            
            $stmt = $this->conn->prepare($sql);
            $this->bindParams($stmt, $params);
            $stmt->execute();
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ? (int)$result['total'] : 0;
        } catch (PDOException $e) {
            return 0;
        }
    }
    
    public function processPayment($userId, $subscriptionId, $phoneNumber, $amount) {
        try {
            $transactionId = 'TXN_' . time() . '_' . uniqid();
            
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
            
            $this->activate($subscriptionId, $transactionId);
            
            return ['success' => true, 'transaction_id' => $transactionId];
        } catch (PDOException $e) {
            return ['success' => false, 'error' => 'Payment processing failed'];
        }
    }
    
    public function expireSubscriptions() {
        try {
            $query = "UPDATE subscriptions SET status = 'expired' 
                      WHERE status = 'active' AND end_date < NOW()";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            
            return ['success' => true, 'affected' => $stmt->rowCount()];
        } catch (PDOException $e) {
            return ['success' => false, 'error' => 'Failed to expire subscriptions'];
        }
    }
    
    public function getRevenueStats($period = 'month') {
        try {
            if ($period === 'month') {
                $query = "SELECT DATE_FORMAT(created_at, '%Y-%m') as period,
                                 COUNT(*) as subscription_count,
                                 SUM(amount) as total_revenue
                          FROM payments WHERE status = 'completed'
                          GROUP BY DATE_FORMAT(created_at, '%Y-%m')
                          ORDER BY period DESC LIMIT 12";
            } else {
                $query = "SELECT DATE(created_at) as period,
                                 COUNT(*) as subscription_count,
                                 SUM(amount) as total_revenue
                          FROM payments WHERE status = 'completed'
                          AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                          GROUP BY DATE(created_at) ORDER BY period DESC";
            }
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            return [];
        }
    }
    
    public function getTotalRevenue() {
        try {
            $query = "SELECT SUM(amount) as total FROM payments WHERE status = 'completed'";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $result = $stmt->fetch();
            return $result['total'] ?? 0;
        } catch (PDOException $e) {
            return 0;
        }
    }
    
    public function getTotalSubscriptions() {
        try {
            $query = "SELECT COUNT(*) as count FROM subscriptions WHERE status = 'active'";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            $result = $stmt->fetch();
            return $result['count'] ?? 0;
        } catch (PDOException $e) {
            return 0;
        }
    }
    
    public function getSubscriptionStats() {
        try {
            $stats = [];
            $statuses = ['active', 'expired', 'pending', 'cancelled'];
            
            foreach ($statuses as $status) {
                $stmt = $this->conn->prepare("SELECT COUNT(*) as total FROM subscriptions WHERE status = :status");
                $stmt->execute([':status' => $status]);
                $stats[$status] = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
            }
            
            $stmt = $this->conn->prepare("SELECT SUM(amount) as total FROM subscriptions WHERE status IN ('active', 'expired')");
            $stmt->execute();
            $stats['total_revenue'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
            
            $stmt = $this->conn->prepare("SELECT SUM(amount) as total FROM subscriptions 
                    WHERE MONTH(created_at) = MONTH(CURRENT_DATE()) 
                    AND YEAR(created_at) = YEAR(CURRENT_DATE())
                    AND status IN ('active', 'expired')");
            $stmt->execute();
            $stats['monthly_revenue'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
            
            $stmt = $this->conn->prepare("SELECT plan_type, COUNT(*) as count, SUM(amount) as total 
                    FROM subscriptions WHERE status = 'active' GROUP BY plan_type");
            $stmt->execute();
            $stats['plan_distribution'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $stmt = $this->conn->prepare("SELECT COUNT(*) as total FROM subscriptions 
                    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)");
            $stmt->execute();
            $stats['recent_30days'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
            
            return $stats;
        } catch (PDOException $e) {
            return [];
        }
    }
    
    public function getSubscriptionById($subscriptionId) {
        try {
            if (isset($this->subscriptionCache[$subscriptionId])) {
                return $this->subscriptionCache[$subscriptionId];
            }
            
            $sql = "SELECT s.*, u.first_name, u.last_name, u.email, u.phone, u.role as user_role
                    FROM subscriptions s LEFT JOIN users u ON s.user_id = u.id WHERE s.id = :id";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':id', $subscriptionId, PDO::PARAM_INT);
            $stmt->execute();
            
            $this->subscriptionCache[$subscriptionId] = $stmt->fetch(PDO::FETCH_ASSOC);
            return $this->subscriptionCache[$subscriptionId];
        } catch (PDOException $e) {
            return null;
        }
    }
    
    public function updateSubscriptionStatus($subscriptionId, $status) {
        try {
            $stmt = $this->conn->prepare("UPDATE subscriptions SET status = :status WHERE id = :id");
            $stmt->bindValue(':status', $status);
            $stmt->bindValue(':id', $subscriptionId, PDO::PARAM_INT);
            
            if ($stmt->execute()) {
                unset($this->subscriptionCache[$subscriptionId]);
                return ['success' => true, 'message' => 'Subscription status updated'];
            }
            return ['success' => false, 'error' => 'Failed to update status'];
        } catch (PDOException $e) {
            return ['success' => false, 'error' => 'Database error'];
        }
    }
    
    public function cancelSubscription($subscriptionId) {
        return $this->updateSubscriptionStatus($subscriptionId, 'cancelled');
    }
    
    public function getExpiringSubscriptions($days = 30) {
        try {
            $sql = "SELECT s.*, u.first_name, u.last_name, u.email 
                    FROM subscriptions s LEFT JOIN users u ON s.user_id = u.id
                    WHERE s.status = 'active' 
                    AND s.end_date BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL :days DAY)
                    ORDER BY s.end_date ASC";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':days', $days, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }
    
    public function expirePastSubscriptions() {
        try {
            $sql = "UPDATE subscriptions SET status = 'expired', updated_at = NOW() 
                    WHERE status = 'active' AND end_date < NOW()";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            
            $affectedRows = $stmt->rowCount();
            if ($affectedRows > 0) {
                error_log("Expired $affectedRows subscription(s) that passed their end date");
            }
            return $affectedRows;
        } catch (PDOException $e) {
            return 0;
        }
    }
    
    public function getCurrentSubscription($userId) {
        try {
            $expireSql = "UPDATE subscriptions SET status = 'expired' 
                        WHERE user_id = :user_id AND status = 'active' AND end_date < NOW()";
            $expireStmt = $this->conn->prepare($expireSql);
            $expireStmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
            $expireStmt->execute();
            
            $sql = "SELECT * FROM subscriptions 
                    WHERE user_id = :user_id AND status = 'active' AND end_date > NOW()
                    ORDER BY created_at DESC LIMIT 1";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return null;
        }
    }
    
    public function hasActiveSubscription($userId) {
        return !empty($this->getCurrentSubscription($userId));
    }
    
    public function calculateUpgradePrice($currentPlan, $newPlan, $currentSubscription) {
        try {
            $settings = $this->getSubscriptionSettings();
            $currentPrice = $this->getPlanPrice($currentPlan, $settings);
            $newPrice = $this->getPlanPrice($newPlan, $settings);
            
            $endDate = new DateTime($currentSubscription['end_date']);
            $now = new DateTime();
            $daysRemaining = $now->diff($endDate)->days;
            $totalDays = $this->getPlanDays($currentPlan);
            
            $dailyRate = $currentPrice / $totalDays;
            $remainingValue = $dailyRate * $daysRemaining;
            $upgradePrice = max(0, $newPrice - $remainingValue);
            
            return [
                'success' => true,
                'current_price' => $currentPrice,
                'new_price' => $newPrice,
                'days_remaining' => $daysRemaining,
                'total_days' => $totalDays,
                'remaining_value' => round($remainingValue),
                'upgrade_price' => round($upgradePrice),
                'daily_rate' => round($dailyRate, 2)
            ];
        } catch (Exception $e) {
            return ['success' => false, 'error' => 'Failed to calculate upgrade price'];
        }
    }
    
    // ==================== PAYMENT MANAGEMENT ====================
    public function getSubscriptionSettings() {
        if ($this->settingsCache !== null) {
            return $this->settingsCache;
        }
        
        try {
            if (!$this->settingsTableExists()) {
                return [];
            }
            
            $sql = "SELECT * FROM settings WHERE setting_group = 'subscription'";
            $stmt = $this->conn->query($sql);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $this->settingsCache = [];
            foreach ($results as $row) {
                $this->settingsCache[$row['setting_key']] = $row['setting_value'];
            }
            
            return $this->settingsCache;
        } catch (PDOException $e) {
            return [];
        }
    }
    
    public function getUserSubscriptionHistory($userId) {
        try {
            $sql = "SELECT * FROM subscriptions WHERE user_id = :user_id ORDER BY created_at DESC";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }
    
    public function getUpgradeDetails($subscriptionId) {
        try {
            $sql = "SELECT * FROM subscriptions WHERE id = :id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':id', $subscriptionId, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return null;
        }
    }
    
    public function getPaymentForSubscription($subscriptionId) {
        try {
            $sql = "SELECT * FROM payment_history WHERE subscription_id = :subscription_id ORDER BY created_at DESC";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':subscription_id', $subscriptionId, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }
    
    public function getUserPaymentHistory($userId, $limit = 10) {
        try {
            $sql = "SELECT ph.*, s.plan_type 
                    FROM payment_history ph
                    LEFT JOIN subscriptions s ON ph.subscription_id = s.id
                    WHERE ph.user_id = :user_id ORDER BY ph.created_at DESC LIMIT :limit";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }
    
    public function getCombinedHistory($userId) {
        try {
            $subSql = "SELECT id, plan_type, amount, created_at, status, 'subscription' as history_type, 
                              NULL as payment_method, NULL as transaction_id
                       FROM subscriptions WHERE user_id = :user_id AND amount IS NOT NULL AND amount > 0";
            
            $subStmt = $this->conn->prepare($subSql);
            $subStmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
            $subStmt->execute();
            $subscriptions = $subStmt->fetchAll(PDO::FETCH_ASSOC);
            
            $paySql = "SELECT id, COALESCE(to_plan, 'subscription') as plan_type, amount, created_at, status, 
                              'payment' as history_type, payment_method, transaction_id
                       FROM payment_history WHERE user_id = :user_id";
            
            $payStmt = $this->conn->prepare($paySql);
            $payStmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
            $payStmt->execute();
            $payments = $payStmt->fetchAll(PDO::FETCH_ASSOC);
            
            $history = array_merge($subscriptions, $payments);
            usort($history, function($a, $b) {
                return strtotime($b['created_at']) - strtotime($a['created_at']);
            });
            
            return $history;
        } catch (PDOException $e) {
            return [];
        }
    }
    
    public function createPendingPayment($userId, $planType, $amount, $paymentMethod, $phoneNumber = null) {
        try {
            $transactionId = 'PESA_' . time() . '_' . $userId . '_' . rand(100, 999);
            
            $sql = "INSERT INTO payments (user_id, amount, payment_method, transaction_id, phone_number, plan_type, status, payment_date, created_at)
                    VALUES (:user_id, :amount, :payment_method, :transaction_id, :phone_number, :plan_type, 'pending', NOW(), NOW())";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
            $stmt->bindValue(':amount', $amount);
            $stmt->bindValue(':payment_method', $paymentMethod);
            $stmt->bindValue(':transaction_id', $transactionId);
            $stmt->bindValue(':phone_number', $phoneNumber);
            $stmt->bindValue(':plan_type', $planType);
            
            if ($stmt->execute()) {
                return [
                    'success' => true,
                    'payment_id' => $this->conn->lastInsertId(),
                    'transaction_id' => $transactionId
                ];
            }
            
            return ['success' => false, 'error' => 'Failed to create payment record'];
        } catch (PDOException $e) {
            return ['success' => false, 'error' => 'Database error: ' . $e->getMessage()];
        }
    }
    
    public function updatePaymentStatus($transactionId, $status, $pesapalData = null) {
        try {
            $payment = $this->getPaymentByTransactionId($transactionId);
            if (!$payment) {
                return ['success' => false, 'error' => 'Payment not found'];
            }
            
            $sql = "UPDATE payments SET status = :status, 
                        payment_date = CASE WHEN :status = 'completed' THEN NOW() ELSE payment_date END,
                        payment_gateway_response = :gateway_response, updated_at = NOW()
                    WHERE transaction_id = :transaction_id";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':status', $status);
            $stmt->bindValue(':transaction_id', $transactionId);
            $stmt->bindValue(':gateway_response', $pesapalData ? json_encode($pesapalData) : null);
            $stmt->execute();
            
            if ($status == 'completed') {
                $this->createOrUpdateSubscription($payment['user_id'], $payment['plan_type'], $payment['amount'], $transactionId);
            }
            
            return ['success' => true];
        } catch (PDOException $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    public function getPaymentByTransactionId($transactionId) {
        try {
            if (isset($this->paymentCache[$transactionId])) {
                return $this->paymentCache[$transactionId];
            }
            
            $sql = "SELECT * FROM payments WHERE transaction_id = :transaction_id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':transaction_id', $transactionId);
            $stmt->execute();
            
            $this->paymentCache[$transactionId] = $stmt->fetch(PDO::FETCH_ASSOC);
            return $this->paymentCache[$transactionId];
        } catch (PDOException $e) {
            return null;
        }
    }
    
    public function getPaymentByReference($reference) {
        return $this->getPaymentByTransactionId($reference);
    }
    
    public function createOrUpdateSubscription($userId, $planType, $amount, $transactionId) {
        try {
            $this->conn->beginTransaction();
            
            $checkStmt = $this->conn->prepare("SELECT id FROM subscriptions WHERE transaction_id = ?");
            $checkStmt->execute([$transactionId]);
            if ($checkStmt->fetch()) {
                $this->conn->commit();
                return ['success' => true];
            }
            
            $planDays = $this->getPlanDays($planType);
            $startDate = date('Y-m-d H:i:s');
            $endDate = date('Y-m-d H:i:s', strtotime("+{$planDays} days"));
            
            $currentSubscription = $this->getCurrentSubscription($userId);
            
            $sql = "INSERT INTO subscriptions (user_id, plan_type, amount, start_date, end_date, status, payment_method, transaction_id, is_upgrade, created_at)
                    VALUES (:user_id, :plan_type, :amount, :start_date, :end_date, 'active', 'pesapal', :transaction_id, :is_upgrade, NOW())";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
            $stmt->bindValue(':plan_type', $planType);
            $stmt->bindValue(':amount', $amount);
            $stmt->bindValue(':start_date', $startDate);
            $stmt->bindValue(':end_date', $endDate);
            $stmt->bindValue(':transaction_id', $transactionId);
            $stmt->bindValue(':is_upgrade', $currentSubscription ? 1 : 0, PDO::PARAM_INT);
            $stmt->execute();
            
            $newSubscriptionId = $this->conn->lastInsertId();
            
            if ($currentSubscription) {
                $updateStmt = $this->conn->prepare("UPDATE subscriptions SET status = 'expired' WHERE id = :id");
                $updateStmt->bindValue(':id', $currentSubscription['id'], PDO::PARAM_INT);
                $updateStmt->execute();
            }
            
            $this->conn->commit();
            
            return [
                'success' => true,
                'subscription_id' => $newSubscriptionId,
                'start_date' => $startDate,
                'end_date' => $endDate
            ];
        } catch (PDOException $e) {
            $this->conn->rollBack();
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    public function getPaymentById($paymentId) {
        try {
            $sql = "SELECT * FROM payments WHERE id = :id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':id', $paymentId, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return null;
        }
    }
    
    public function getUserById($userId) {
        try {
            $sql = "SELECT * FROM users WHERE id = :id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':id', $userId, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return null;
        }
    }
    
    public function getPesaPalPaymentDetails($transactionId) {
        try {
            $sql = "SELECT * FROM payments WHERE transaction_id = :transaction_id AND payment_method = 'pesapal'";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':transaction_id', $transactionId);
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return null;
        }
    }
}
?>