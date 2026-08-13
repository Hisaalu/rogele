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

    private function getPlanPrice($planType, $settings = null) {
        if ($settings === null) {
            $settings = $this->getSubscriptionSettings();
        }
        
        $prices = [
            'monthly' => $settings['monthly_price'] ?? 15000,
            'termly'  => $settings['termly_price'] ?? 40000,
            'yearly'  => $settings['yearly_price'] ?? 120000
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
            if ($stmt->execute($params)) {
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

    // ==================== READ & QUERY METHODS ====================
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
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return null;
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
                    ORDER BY end_date DESC LIMIT 1";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
        } catch (PDOException $e) {
            return null;
        }
    }
    
    public function hasActiveSubscription($userId) {
        return !empty($this->getCurrentSubscription($userId));
    }

    public function getSubscriptionById($subscriptionId) {
        try {
            $this->expirePastSubscriptions();

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

    public function getAllSubscriptions($filters = [], $limit = 20, $offset = 0) {
        try {
            $this->expirePastSubscriptions();

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

    public function getTotalRevenue() {
        $stats = $this->getSubscriptionStats();
        return (float)($stats['total_revenue'] ?? 0);
    }

    public function getTotalSubscriptions() {
        $stats = $this->getSubscriptionStats();
        return (int)($stats['active'] ?? 0);
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

    public function cancel($subscriptionId) {
        return $this->updateSubscriptionStatus($subscriptionId, 'cancelled');
    }

    public function cancelSubscription($subscriptionId) {
        return $this->cancel($subscriptionId);
    }

    public function expireSubscriptions() {
        return $this->expirePastSubscriptions();
    }

    public function expirePastSubscriptions() {
        try {
            $sql = "UPDATE subscriptions 
                    SET status = 'expired' 
                    WHERE status IN ('active', 'pending') 
                    AND end_date IS NOT NULL 
                    AND end_date < NOW()";
                    
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            
            return ['success' => true, 'affected' => $stmt->rowCount()];
        } catch (PDOException $e) {
            error_log("Expire subscriptions error: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function updateSubscriptionStatus($subscriptionId, $status) {
        try {
            $stmt = $this->conn->prepare("UPDATE subscriptions SET status = :status, updated_at = NOW() WHERE id = :id");
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

    // ==================== PAYMENT & HISTORY METHODS ====================
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

    public function getPaymentHistory($userId) {
        return $this->getUserPaymentHistory($userId);
    }

    public function getUserPaymentHistory($userId, $limit = 10) {
        try {
            $sql = "SELECT p.*, s.plan_type 
                    FROM payments p
                    LEFT JOIN subscriptions s ON p.subscription_id = s.id
                    WHERE p.user_id = :user_id 
                    ORDER BY p.created_at DESC LIMIT :limit";
            
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
            $paySql = "SELECT p.id, COALESCE(p.plan_type, 'subscription') as plan_type, p.amount, p.created_at, p.status, 
                              'payment' as history_type, p.payment_method, p.transaction_id
                       FROM payments p WHERE p.user_id = :user_id ORDER BY p.created_at DESC";
            
            $stmt = $this->conn->prepare($paySql);
            $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    public function getUpgradeDetails($subscriptionId) {
        return $this->getSubscriptionById($subscriptionId);
    }
    
    public function getPaymentForSubscription($subscriptionId) {
        try {
            $sql = "SELECT * FROM payments WHERE subscription_id = :subscription_id ORDER BY created_at DESC";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':subscription_id', $subscriptionId, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    public function getPaymentByTransactionId($transactionId) {
        try {
            if (isset($this->paymentCache[$transactionId])) {
                return $this->paymentCache[$transactionId];
            }
            
            $sql = "SELECT * FROM payments WHERE transaction_id = :transaction_id";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([':transaction_id' => $transactionId]);
            
            $this->paymentCache[$transactionId] = $stmt->fetch(PDO::FETCH_ASSOC);
            return $this->paymentCache[$transactionId];
        } catch (PDOException $e) {
            return null;
        }
    }

    public function getPaymentByReference($reference) {
        return $this->getPaymentByTransactionId($reference);
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

    // ==================== CORE PAYMENT EXECUTION ====================
    public function createPendingPayment($userId, $planType, $amount, $paymentMethod, $phoneNumber = null) {
        try {
            $transactionId = 'TXN_' . time() . '_' . $userId . '_' . rand(100, 999);
            
            $sql = "INSERT INTO payments (user_id, amount, payment_method, transaction_id, phone_number, plan_type, status, created_at)
                    VALUES (:user_id, :amount, :payment_method, :transaction_id, :phone_number, :plan_type, 'pending', NOW())";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([
                ':user_id'        => $userId,
                ':amount'         => $amount,
                ':payment_method' => $paymentMethod,
                ':transaction_id' => $transactionId,
                ':phone_number'   => $phoneNumber,
                ':plan_type'      => $planType
            ]);
            
            return [
                'success' => true,
                'payment_id' => $this->conn->lastInsertId(),
                'transaction_id' => $transactionId
            ];
        } catch (PDOException $e) {
            return ['success' => false, 'error' => 'Database error: ' . $e->getMessage()];
        }
    }

    public function processCompletedPayment($transactionId, $status = 'completed', $gatewayData = []) {
        try {
            $this->conn->beginTransaction();

            $stmt = $this->conn->prepare("SELECT * FROM payments WHERE transaction_id = :tx_id FOR UPDATE");
            $stmt->execute([':tx_id' => $transactionId]);
            $payment = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$payment) {
                $this->conn->rollBack();
                return ['success' => false, 'error' => 'Payment record not found'];
            }

            if ($payment['status'] === 'completed' && !empty($payment['subscription_id'])) {
                $this->conn->commit();
                return ['success' => true, 'message' => 'Payment already processed and linked'];
            }

            if ($status !== 'completed') {
                $updatePay = $this->conn->prepare("
                    UPDATE payments 
                    SET status = :status, 
                        payment_gateway_response = :resp, 
                        updated_at = NOW() 
                    WHERE id = :id
                ");
                $updatePay->execute([
                    ':status' => $status,
                    ':resp'   => !empty($gatewayData) ? json_encode($gatewayData) : null,
                    ':id'     => $payment['id']
                ]);
                $this->conn->commit();
                return ['success' => true, 'message' => 'Payment status updated to ' . $status];
            }

            $userId   = $payment['user_id'];
            $planType = $payment['plan_type'];
            $days     = $this->getPlanDays($planType);

            $activeSub = $this->getCurrentSubscription($userId);
            $startDate = date('Y-m-d H:i:s');
            
            $baseDate = ($activeSub && strtotime($activeSub['end_date']) > time()) 
                ? $activeSub['end_date'] 
                : $startDate;

            $endDate = date('Y-m-d H:i:s', strtotime($baseDate . " + {$days} days"));

            $subStmt = $this->conn->prepare("
                INSERT INTO subscriptions (user_id, plan_type, amount, start_date, end_date, status, payment_method, transaction_id, created_at)
                VALUES (:user_id, :plan_type, :amount, :start_date, :end_date, 'active', :payment_method, :transaction_id, NOW())
            ");
            $subStmt->execute([
                ':user_id'        => $userId,
                ':plan_type'      => $planType,
                ':amount'         => $payment['amount'],
                ':start_date'     => $startDate,
                ':end_date'       => $endDate,
                ':payment_method' => $payment['payment_method'],
                ':transaction_id' => $transactionId
            ]);
            
            $newSubscriptionId = $this->conn->lastInsertId();

            if ($activeSub) {
                $expireOld = $this->conn->prepare("UPDATE subscriptions SET status = 'expired' WHERE id = :id");
                $expireOld->execute([':id' => $activeSub['id']]);
            }

            $updatePay = $this->conn->prepare("
                UPDATE payments 
                SET status = 'completed', 
                    subscription_id = :sub_id, 
                    payment_date = NOW(),
                    payment_gateway_response = :resp,
                    updated_at = NOW() 
                WHERE id = :id
            ");
            $updatePay->execute([
                ':sub_id' => $newSubscriptionId,
                ':resp'   => !empty($gatewayData) ? json_encode($gatewayData) : null,
                ':id'     => $payment['id']
            ]);

            $this->conn->commit();

            require_once __DIR__ . '/Notification.php';
            Notification::create(
                'subscriber', 
                'New Subscription Activated', 
                'A new subscriber activated their plan.', 
                BASE_URL . '/admin/subscriptions/view/' . $newSubscriptionId
            );

            return [
                'success'         => true,
                'subscription_id' => $newSubscriptionId,
                'payment_id'      => $payment['id']
            ];

        } catch (Exception $e) {
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }
            error_log("Payment Process Exception: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
}
?>