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

    /**
     * Get the database connection
     * 
     * @return PDO The database connection
     */
    public function getConnection() {
        return $this->conn;
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
            return [];
        }
    }
    
    /**
     * Get all subscriptions with optional filters
     */
    public function getAllSubscriptions($filters = [], $limit = 20, $offset = 0) {
        try {
            $sql = "SELECT s.*, 
                        u.first_name, 
                        u.last_name, 
                        u.email,
                        u.role as user_role
                    FROM subscriptions s
                    LEFT JOIN users u ON s.user_id = u.id
                    WHERE 1=1";
            
            $params = [];
            
            if (!empty($filters['status'])) {
                $sql .= " AND s.status = :status";
                $params[':status'] = $filters['status'];
            }
            
            if (!empty($filters['plan_type'])) {
                $sql .= " AND s.plan_type = :plan_type";
                $params[':plan_type'] = $filters['plan_type'];
            }
            
            if (!empty($filters['user_id'])) {
                $sql .= " AND s.user_id = :user_id";
                $params[':user_id'] = $filters['user_id'];
            }
            
            $sql .= " ORDER BY s.created_at DESC";
            
            if ($limit > 0) {
                $sql .= " LIMIT :limit OFFSET :offset";
            }
            
            $stmt = $this->conn->prepare($sql);
            
            foreach ($params as $key => $value) {
                if ($key == ':limit' || $key == ':offset') {
                    $stmt->bindValue($key, $value, PDO::PARAM_INT);
                } else {
                    $stmt->bindValue($key, $value);
                }
            }
            
            if ($limit > 0) {
                $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
                $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            }
            
            $stmt->execute();
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return $results;
            
        } catch (PDOException $e) {
            return [];
        }
    }

    /**
     * Count total subscriptions with filters (for pagination)
     */
    public function countAllSubscriptions($filters = []) {
        try {
            $sql = "SELECT COUNT(*) as total 
                    FROM subscriptions s
                    LEFT JOIN users u ON s.user_id = u.id
                    WHERE 1=1";
            
            $params = [];
            
            if (!empty($filters['status'])) {
                $sql .= " AND s.status = :status";
                $params[':status'] = $filters['status'];
            }
            
            if (!empty($filters['plan_type'])) {
                $sql .= " AND s.plan_type = :plan_type";
                $params[':plan_type'] = $filters['plan_type'];
            }
            
            if (!empty($filters['search'])) {
                $sql .= " AND (u.first_name LIKE :search 
                            OR u.last_name LIKE :search 
                            OR u.email LIKE :search)";
                $params[':search'] = '%' . $filters['search'] . '%';
            }
            
            if (empty($filters['status'])) {
                $sql .= " AND s.status IN ('active', 'expired')";
            }
            
            $stmt = $this->conn->prepare($sql);
            $stmt->execute($params);
            
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ? (int)$result['total'] : 0;
            
        } catch (PDOException $e) {
            return 0;
        }
    }
    
    // Process payment (direct - for testing or non-PesaPal flows)
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
    
    // Check and expire subscriptions
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
            return [];
        }
    }

    /**
     * Get total revenue from all completed payments
     */
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

    /**
     * Get total number of active subscriptions
     */
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

    /**
     * Get subscription statistics for admin dashboard
     */
    public function getSubscriptionStats() {
        try {
            $stats = [];
            
            $sql1 = "SELECT COUNT(*) as total FROM subscriptions WHERE status = 'active'";
            $stmt1 = $this->conn->query($sql1);
            $stats['active'] = $stmt1->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
            
            $sql2 = "SELECT COUNT(*) as total FROM subscriptions WHERE status = 'expired'";
            $stmt2 = $this->conn->query($sql2);
            $stats['expired'] = $stmt2->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
            
            $sql3 = "SELECT COUNT(*) as total FROM subscriptions WHERE status = 'pending'";
            $stmt3 = $this->conn->query($sql3);
            $stats['pending'] = $stmt3->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
            
            $sql4 = "SELECT COUNT(*) as total FROM subscriptions WHERE status = 'cancelled'";
            $stmt4 = $this->conn->query($sql4);
            $stats['cancelled'] = $stmt4->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
            
            $sql5 = "SELECT SUM(amount) as total FROM subscriptions WHERE status = 'active' OR status = 'expired'";
            $stmt5 = $this->conn->query($sql5);
            $stats['total_revenue'] = $stmt5->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
            
            $sql6 = "SELECT SUM(amount) as total FROM subscriptions 
                    WHERE MONTH(created_at) = MONTH(CURRENT_DATE()) 
                    AND YEAR(created_at) = YEAR(CURRENT_DATE())
                    AND (status = 'active' OR status = 'expired')";
            $stmt6 = $this->conn->query($sql6);
            $stats['monthly_revenue'] = $stmt6->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
            
            $sql7 = "SELECT plan_type, COUNT(*) as count, SUM(amount) as total 
                    FROM subscriptions 
                    WHERE status = 'active' 
                    GROUP BY plan_type";
            $stmt7 = $this->conn->query($sql7);
            $stats['plan_distribution'] = $stmt7->fetchAll(PDO::FETCH_ASSOC);
            
            $sql8 = "SELECT COUNT(*) as total FROM subscriptions 
                    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
            $stmt8 = $this->conn->query($sql8);
            $stats['recent_30days'] = $stmt8->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
            
            return $stats;
            
        } catch (PDOException $e) {
            return [];
        }
    }

    /**
     * Get subscription details by ID
     */
    public function getSubscriptionById($subscriptionId) {
        try {
            $sql = "SELECT s.*, 
                        u.first_name, 
                        u.last_name, 
                        u.email,
                        u.phone,
                        u.role as user_role
                    FROM subscriptions s
                    LEFT JOIN users u ON s.user_id = u.id
                    WHERE s.id = :id";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':id', $subscriptionId, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            return null;
        }
    }

    /**
     * Update subscription status (admin)
     */
    public function updateSubscriptionStatus($subscriptionId, $status) {
        try {
            $sql = "UPDATE subscriptions SET status = :status WHERE id = :id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':status', $status);
            $stmt->bindValue(':id', $subscriptionId, PDO::PARAM_INT);
            
            if ($stmt->execute()) {
                return ['success' => true, 'message' => 'Subscription status updated'];
            } else {
                return ['success' => false, 'error' => 'Failed to update status'];
            }
            
        } catch (PDOException $e) {
            return ['success' => false, 'error' => 'Database error'];
        }
    }

    /**
     * Cancel subscription (admin)
     */
    public function cancelSubscription($subscriptionId) {
        return $this->updateSubscriptionStatus($subscriptionId, 'cancelled');
    }

    /**
     * Get expiring subscriptions (next 30 days)
     */
    public function getExpiringSubscriptions($days = 30) {
        try {
            $sql = "SELECT s.*, u.first_name, u.last_name, u.email 
                    FROM subscriptions s
                    LEFT JOIN users u ON s.user_id = u.id
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

    /**
     * Get user's current active subscription
     */
    public function getCurrentSubscription($userId) {
        try {
            $sql = "SELECT * FROM subscriptions 
                    WHERE user_id = :user_id 
                    AND status = 'active' 
                    AND end_date > NOW() 
                    ORDER BY created_at DESC LIMIT 1";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return null;
        }
    }

    /**
     * Calculate prorated upgrade price
     */
    public function calculateUpgradePrice($currentPlan, $newPlan, $currentSubscription) {
        try {
            // Check if Settings class exists, if not use defaults
            if (class_exists('Settings')) {
                $settingsModel = new Settings();
                $settings = $settingsModel->getSubscriptionSettings();
            } else {
                $settings = [];
            }
            
            $prices = [
                'monthly' => $settings['monthly_price'] ?? 15000,
                'termly' => $settings['termly_price'] ?? 40000,
                'yearly' => $settings['yearly_price'] ?? 120000
            ];
            
            $currentPrice = $prices[$currentPlan] ?? 0;
            $newPrice = $prices[$newPlan] ?? 0;
            
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
            return [
                'success' => false,
                'error' => 'Failed to calculate upgrade price'
            ];
        }
    }

    /**
     * Get plan days based on plan type
     */
    private function getPlanDays($planType) {
        switch ($planType) {
            case 'monthly':
                return 30;
            case 'termly':
                return 90;
            case 'yearly':
                return 365;
            default:
                return 30;
        }
    }

    /**
     * Process subscription upgrade
     */
    public function upgradeSubscription($userId, $fromPlan, $toPlan, $paymentDetails) {
        try {
            $this->conn->beginTransaction();
            
            $currentSubscription = $this->getCurrentSubscription($userId);
            
            if (!$currentSubscription) {
                throw new Exception('No active subscription found');
            }
            
            // Get settings with fallback
            if (class_exists('Settings')) {
                $settings = $this->getSubscriptionSettings();
            } else {
                $settings = [];
            }
            
            $prices = [
                'monthly' => $settings['monthly_price'] ?? 15000,
                'termly' => $settings['termly_price'] ?? 40000,
                'yearly' => $settings['yearly_price'] ?? 120000
            ];
            
            $newPrice = $prices[$toPlan] ?? 0;
            
            $planDays = $toPlan === 'monthly' ? 30 : ($toPlan === 'termly' ? 90 : 365);
            $newEndDate = date('Y-m-d H:i:s', strtotime("+{$planDays} days"));
            
            $updateSql = "UPDATE subscriptions SET status = 'expired' WHERE id = :id";
            $updateStmt = $this->conn->prepare($updateSql);
            $updateStmt->bindValue(':id', $currentSubscription['id'], PDO::PARAM_INT);
            $updateStmt->execute();
            
            $insertSql = "INSERT INTO subscriptions (
                            user_id, plan_type, amount, start_date, end_date, 
                            status, payment_method, transaction_id, is_upgrade, 
                            upgraded_from, original_subscription_id, created_at
                        ) VALUES (
                            :user_id, :plan_type, :amount, NOW(), :end_date,
                            'active', :payment_method, :transaction_id, 1,
                            :upgraded_from, :original_subscription_id, NOW()
                        )";
            
            $insertStmt = $this->conn->prepare($insertSql);
            $insertStmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
            $insertStmt->bindValue(':plan_type', $toPlan);
            $insertStmt->bindValue(':amount', $paymentDetails['amount']);
            $insertStmt->bindValue(':end_date', $newEndDate);
            $insertStmt->bindValue(':payment_method', $paymentDetails['method']);
            $insertStmt->bindValue(':transaction_id', $paymentDetails['transaction_id']);
            $insertStmt->bindValue(':upgraded_from', $fromPlan);
            $insertStmt->bindValue(':original_subscription_id', $currentSubscription['id'], PDO::PARAM_INT);
            $insertStmt->execute();
            
            $newSubscriptionId = $this->conn->lastInsertId();
            
            $this->conn->commit();
            
            return [
                'success' => true,
                'message' => 'Successfully upgraded to ' . ucfirst($toPlan) . ' plan',
                'new_subscription_id' => $newSubscriptionId,
                'new_end_date' => $newEndDate
            ];
            
        } catch (Exception $e) {
            $this->conn->rollBack();
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Calculate new end date after upgrade
     */
    private function calculateNewEndDate($currentEndDate, $newPlan) {
        $endDate = new DateTime($currentEndDate);
        $now = new DateTime();
        
        $planDays = $this->getPlanDays($newPlan);
        
        if ($endDate > $now) {
            $endDate->modify("+{$planDays} days");
        } else {
            $endDate = $now->modify("+{$planDays} days");
        }
        
        return $endDate->format('Y-m-d H:i:s');
    }

    /**
     * Record upgrade payment
     */
    private function recordUpgradePayment($userId, $fromPlan, $toPlan, $amount, $paymentDetails, $subscriptionId) {
        try {
            $checkTable = $this->conn->query("SHOW TABLES LIKE 'payment_history'");
            if ($checkTable->rowCount() == 0) {
                error_log("Payment history table does not exist");
                return false;
            }
            
            $sql = "INSERT INTO payment_history (
                        user_id,
                        subscription_id,
                        amount,
                        payment_type,
                        from_plan,
                        to_plan,
                        status,
                        payment_method,
                        transaction_id,
                        payment_data,
                        created_at
                    ) VALUES (
                        :user_id,
                        :subscription_id,
                        :amount,
                        'upgrade',
                        :from_plan,
                        :to_plan,
                        'completed',
                        :payment_method,
                        :transaction_id,
                        :payment_data,
                        NOW()
                    )";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
            $stmt->bindValue(':subscription_id', $subscriptionId, PDO::PARAM_INT);
            $stmt->bindValue(':amount', $amount);
            $stmt->bindValue(':from_plan', $fromPlan);
            $stmt->bindValue(':to_plan', $toPlan);
            $stmt->bindValue(':payment_method', $paymentDetails['method'] ?? 'pesapal');
            $stmt->bindValue(':transaction_id', $paymentDetails['transaction_id'] ?? ('TXN_' . time() . '_' . $userId));
            $stmt->bindValue(':payment_data', json_encode($paymentDetails));
            
            $result = $stmt->execute();
            
            if ($result) {
                error_log("Payment history recorded successfully for user: " . $userId . ", amount: " . $amount);
            } else {
                error_log("Failed to record payment history for user: " . $userId);
            }
            
            return $result;
            
        } catch (PDOException $e) {
            error_log("Error recording payment history: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get subscription settings
     */
    public function getSubscriptionSettings() {
        try {
            // Check if settings table exists
            $checkTable = $this->conn->query("SHOW TABLES LIKE 'settings'");
            if ($checkTable->rowCount() == 0) {
                return [];
            }
            
            $sql = "SELECT * FROM settings WHERE setting_group = 'subscription'";
            $stmt = $this->conn->query($sql);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $settings = [];
            foreach ($results as $row) {
                $settings[$row['setting_key']] = $row['setting_value'];
            }
            
            return $settings;
        } catch (PDOException $e) {
            error_log("Error getting subscription settings: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get subscription history for a user
     */
    public function getUserSubscriptionHistory($userId) {
        try {
            $sql = "SELECT * FROM subscriptions 
                    WHERE user_id = :user_id 
                    ORDER BY created_at DESC";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    /**
     * Get upgrade details for a subscription
     */
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

    /**
     * Get payment history for a subscription
     * 
     * @param int $subscriptionId The subscription ID
     * @return array Array of payment records
     */
    public function getPaymentForSubscription($subscriptionId) {
        try {
            $sql = "SELECT * FROM payment_history 
                    WHERE subscription_id = :subscription_id 
                    ORDER BY created_at DESC";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':subscription_id', $subscriptionId, PDO::PARAM_INT);
            $stmt->execute();
            
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return $results;
            
        } catch (PDOException $e) {
            error_log("Error getting payment for subscription: " . $e->getMessage());
            return []; 
        }
    }

    /**
     * Get user's payment history
     */
    public function getUserPaymentHistory($userId, $limit = 10) {
        try {
            $sql = "SELECT ph.*, s.plan_type 
                    FROM payment_history ph
                    LEFT JOIN subscriptions s ON ph.subscription_id = s.id
                    WHERE ph.user_id = :user_id 
                    ORDER BY ph.created_at DESC 
                    LIMIT :limit";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Error getting user payment history: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get combined subscription and payment history
     */
    public function getCombinedHistory($userId) {
        try {
            $subSql = "SELECT 
                        id,
                        plan_type,
                        amount,
                        created_at,
                        status,
                        'subscription' as history_type,
                        NULL as payment_method,
                        NULL as transaction_id
                    FROM subscriptions 
                    WHERE user_id = :user_id 
                    AND amount IS NOT NULL 
                    AND amount > 0";
            
            $subStmt = $this->conn->prepare($subSql);
            $subStmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
            $subStmt->execute();
            $subscriptions = $subStmt->fetchAll(PDO::FETCH_ASSOC);
            
            $paySql = "SELECT 
                        id,
                        COALESCE(to_plan, 'subscription') as plan_type,
                        amount,
                        created_at,
                        status,
                        'payment' as history_type,
                        payment_method,
                        transaction_id
                    FROM payment_history 
                    WHERE user_id = :user_id";
            
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
            error_log("Error getting combined history: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Create a pending payment record for PesaPal
     * 
     * @param int $userId User ID
     * @param string $planType Plan type (monthly, termly, yearly)
     * @param float $amount Payment amount
     * @param string $paymentMethod Payment method (pesapal)
     * @param string $phoneNumber User's phone number for mobile money
     * @return array Result with success status and payment details
     */
    public function createPendingPayment($userId, $planType, $amount, $paymentMethod, $phoneNumber = null) {
        try {
            $transactionId = 'PESA_' . time() . '_' . $userId . '_' . rand(100, 999);
            
            $subscriptionId = $this->getOrCreatePendingSubscription($userId, $planType, $amount);
            
            if (!$subscriptionId) {
                return ['success' => false, 'error' => 'Failed to create subscription record'];
            }
            
            $sql = "INSERT INTO payments (
                        user_id, 
                        subscription_id, 
                        amount, 
                        payment_method, 
                        transaction_id,
                        phone_number,
                        status,
                        payment_date,
                        created_at
                    ) VALUES (
                        :user_id,
                        :subscription_id,
                        :amount,
                        :payment_method,
                        :transaction_id,
                        :phone_number,
                        'pending',
                        NOW(),
                        NOW()
                    )";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
            $stmt->bindValue(':subscription_id', $subscriptionId, PDO::PARAM_INT);
            $stmt->bindValue(':amount', $amount);
            $stmt->bindValue(':payment_method', $paymentMethod);
            $stmt->bindValue(':transaction_id', $transactionId);
            $stmt->bindValue(':phone_number', $phoneNumber);
            
            if ($stmt->execute()) {
                $paymentId = $this->conn->lastInsertId();
                
                return [
                    'success' => true,
                    'payment_id' => $paymentId,
                    'transaction_id' => $transactionId,
                    'subscription_id' => $subscriptionId
                ];
            } else {
                $error = $stmt->errorInfo();
                return ['success' => false, 'error' => 'Failed to create payment record: ' . $error[2]];
            }
            
        } catch (PDOException $e) {
            error_log("Error creating pending payment: " . $e->getMessage());
            return ['success' => false, 'error' => 'Database error: ' . $e->getMessage()];
        }
    }

    /**
     * Create a pending subscription record
     */
    private function getOrCreatePendingSubscription($userId, $planType, $amount) {
        try {
            $sql = "SELECT id FROM subscriptions 
                    WHERE user_id = :user_id 
                    AND status = 'pending' 
                    ORDER BY created_at DESC LIMIT 1";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
            $stmt->execute();
            $existing = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($existing) {
                return $existing['id'];
            }
            
            $planDays = $this->getPlanDays($planType);
            $endDate = date('Y-m-d H:i:s', strtotime("+{$planDays} days"));
            
            $sql = "INSERT INTO subscriptions (
                        user_id,
                        plan_type,
                        amount,
                        start_date,
                        end_date,
                        status,
                        created_at
                    ) VALUES (
                        :user_id,
                        :plan_type,
                        :amount,
                        NOW(),
                        :end_date,
                        'pending',
                        NOW()
                    )";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
            $stmt->bindValue(':plan_type', $planType);
            $stmt->bindValue(':amount', $amount);
            $stmt->bindValue(':end_date', $endDate);
            $stmt->execute();
            
            return $this->conn->lastInsertId();
            
        } catch (PDOException $e) {
            error_log("Error creating pending subscription: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Update payment status after PesaPal callback
     * 
     * @param string $transactionId The transaction ID
     * @param string $status Payment status (completed, failed, pending)
     * @param array|null $pesapalData Additional PesaPal callback data
     * @return array Result with success status
     */
    public function updatePaymentStatus($transactionId, $status, $pesapalData = null) {
        try {
            $sql = "UPDATE payments 
                    SET status = :status, 
                        payment_date = CASE 
                            WHEN :status = 'completed' THEN NOW() 
                            ELSE payment_date 
                        END,
                        payment_gateway_response = :gateway_response
                    WHERE transaction_id = :transaction_id";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':status', $status);
            $stmt->bindValue(':transaction_id', $transactionId);
            $stmt->bindValue(':gateway_response', $pesapalData ? json_encode($pesapalData) : null);
            $stmt->execute();
            
            if ($status == 'completed') {
                $this->activateSubscriptionForPayment($transactionId);
            }
            
            return ['success' => true];
            
        } catch (PDOException $e) {
            error_log("Error updating payment status: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Activate subscription after successful payment
     */
    private function activateSubscriptionForPayment($transactionId) {
        try {
            $sql = "SELECT user_id, subscription_id FROM payments WHERE transaction_id = :transaction_id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':transaction_id', $transactionId);
            $stmt->execute();
            $payment = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($payment && $payment['subscription_id']) {
                $sql = "UPDATE subscriptions 
                        SET status = 'active', 
                            payment_method = 'pesapal',
                            transaction_id = :transaction_id
                        WHERE id = :subscription_id";
                $stmt = $this->conn->prepare($sql);
                $stmt->bindValue(':subscription_id', $payment['subscription_id'], PDO::PARAM_INT);
                $stmt->bindValue(':transaction_id', $transactionId);
                $stmt->execute();
                
                error_log("Subscription activated for subscription_id: " . $payment['subscription_id']);
            }
            
        } catch (PDOException $e) {
            error_log("Error activating subscription: " . $e->getMessage());
        }
    }

    /**
     * Get payment by transaction ID
     * 
     * @param string $transactionId The transaction ID
     * @return array|null Payment record or null if not found
     */
    public function getPaymentByTransactionId($transactionId) {
        try {
            $sql = "SELECT * FROM payments WHERE transaction_id = :transaction_id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':transaction_id', $transactionId);
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Error getting payment by transaction ID: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Get payment by reference (alias for getPaymentByTransactionId for compatibility)
     */
    public function getPaymentByReference($reference) {
        return $this->getPaymentByTransactionId($reference);
    }

    /**
     * Create subscription after successful payment
     * 
     * @param int $userId User ID
     * @param string $planType Plan type
     * @param float $amount Payment amount
     * @param string $transactionId PesaPal transaction ID
     * @return array Result with success status and subscription details
     */
    public function createSubscription($userId, $planType, $amount, $transactionId) {
        try {
            $this->conn->beginTransaction();
            
            $currentSubscription = $this->getCurrentSubscription($userId);
            
            $planDays = $this->getPlanDays($planType);
            $endDate = date('Y-m-d H:i:s', strtotime("+{$planDays} days"));
            
            $sql = "INSERT INTO subscriptions (
                        user_id,
                        plan_type,
                        amount,
                        start_date,
                        end_date,
                        status,
                        payment_method,
                        transaction_id,
                        is_upgrade,
                        created_at
                    ) VALUES (
                        :user_id,
                        :plan_type,
                        :amount,
                        NOW(),
                        :end_date,
                        'active',
                        'pesapal',
                        :transaction_id,
                        :is_upgrade,
                        NOW()
                    )";
            
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
            $stmt->bindValue(':plan_type', $planType);
            $stmt->bindValue(':amount', $amount);
            $stmt->bindValue(':end_date', $endDate);
            $stmt->bindValue(':transaction_id', $transactionId);
            $stmt->bindValue(':is_upgrade', $currentSubscription ? 1 : 0, PDO::PARAM_INT);
            $stmt->execute();
            
            $newSubscriptionId = $this->conn->lastInsertId();
            
            if ($currentSubscription) {
                $updateSql = "UPDATE subscriptions SET status = 'expired' WHERE id = :id";
                $updateStmt = $this->conn->prepare($updateSql);
                $updateStmt->bindValue(':id', $currentSubscription['id'], PDO::PARAM_INT);
                $updateStmt->execute();
            }
            
            $this->conn->commit();
            
            return [
                'success' => true,
                'subscription_id' => $newSubscriptionId,
                'end_date' => $endDate
            ];
            
        } catch (PDOException $e) {
            $this->conn->rollBack();
            error_log("Error creating subscription: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Get payment by ID
     */
    public function getPaymentById($paymentId) {
        try {
            $sql = "SELECT * FROM payments WHERE id = :id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':id', $paymentId, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting payment by ID: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Get user details by ID (helper method)
     */
    public function getUserById($userId) {
        try {
            $sql = "SELECT * FROM users WHERE id = :id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':id', $userId, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting user by ID: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Get PesaPal payment details for a transaction
     */
    public function getPesaPalPaymentDetails($transactionId) {
        try {
            $sql = "SELECT * FROM payments 
                    WHERE transaction_id = :transaction_id 
                    AND payment_method = 'pesapal'";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':transaction_id', $transactionId);
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting PesaPal payment details: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Create or update subscription after successful payment
     */
    public function createOrUpdateSubscription($userId, $planType, $amount, $transactionId) {
        try {
            $this->conn->beginTransaction();
            
            // First, check if there's an existing active subscription
            $checkSql = "SELECT * FROM subscriptions 
                        WHERE user_id = :user_id 
                        AND status = 'active' 
                        ORDER BY id DESC LIMIT 1";
            $checkStmt = $this->conn->prepare($checkSql);
            $checkStmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
            $checkStmt->execute();
            $existingSubscription = $checkStmt->fetch(PDO::FETCH_ASSOC);
            
            // Calculate new end date
            $planDays = $this->getPlanDays($planType);
            $startDate = date('Y-m-d H:i:s');
            
            if ($existingSubscription) {
                // Extend existing subscription instead of creating new one
                $currentEndDate = new DateTime($existingSubscription['end_date']);
                $now = new DateTime();
                
                if ($currentEndDate > $now) {
                    // Extend from current end date
                    $currentEndDate->modify("+{$planDays} days");
                    $endDate = $currentEndDate->format('Y-m-d H:i:s');
                } else {
                    // Start new from today
                    $endDate = date('Y-m-d H:i:s', strtotime("+{$planDays} days"));
                }
                
                // Update existing subscription
                $sql = "UPDATE subscriptions 
                        SET plan_type = :plan_type,
                            amount = :amount,
                            start_date = :start_date,
                            end_date = :end_date,
                            status = 'active',
                            payment_method = 'pesapal',
                            transaction_id = :transaction_id,
                            updated_at = NOW()
                        WHERE id = :id";
                
                $stmt = $this->conn->prepare($sql);
                $stmt->bindValue(':id', $existingSubscription['id'], PDO::PARAM_INT);
            } else {
                // Create new subscription
                $endDate = date('Y-m-d H:i:s', strtotime("+{$planDays} days"));
                
                $sql = "INSERT INTO subscriptions (
                            user_id, plan_type, amount, start_date, end_date, 
                            status, payment_method, transaction_id, created_at
                        ) VALUES (
                            :user_id, :plan_type, :amount, :start_date, :end_date,
                            'active', 'pesapal', :transaction_id, NOW()
                        )";
                
                $stmt = $this->conn->prepare($sql);
                $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
            }
            
            $stmt->bindValue(':plan_type', $planType);
            $stmt->bindValue(':amount', $amount);
            $stmt->bindValue(':start_date', $startDate);
            $stmt->bindValue(':end_date', $endDate);
            $stmt->bindValue(':transaction_id', $transactionId);
            
            $stmt->execute();
            
            // Update payment record status
            $updatePaymentSql = "UPDATE payments 
                                SET status = 'completed', 
                                    payment_date = NOW()
                                WHERE transaction_id = :transaction_id";
            $updateStmt = $this->conn->prepare($updatePaymentSql);
            $updateStmt->bindValue(':transaction_id', $transactionId);
            $updateStmt->execute();
            
            $this->conn->commit();
            
            return ['success' => true, 'end_date' => $endDate];
            
        } catch (PDOException $e) {
            $this->conn->rollBack();
            error_log("Error creating/updating subscription: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
}
?>