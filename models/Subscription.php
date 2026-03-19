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
            
            // Apply filters
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
            
            // Order by most recent first
            $sql .= " ORDER BY s.created_at DESC";
            
            // Add pagination
            if ($limit > 0) {
                $sql .= " LIMIT :limit OFFSET :offset";
            }
            
            $stmt = $this->conn->prepare($sql);
            
            // Bind parameters
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
            
            // Debug log
            error_log("getAllSubscriptions with user_id filter returned " . count($results) . " results");
            
            return $results;
            
        } catch (PDOException $e) {
            error_log("Error getting all subscriptions: " . $e->getMessage());
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
            error_log("Error counting subscriptions: " . $e->getMessage());
            return 0;
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
            error_log("Get total revenue error: " . $e->getMessage());
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
            error_log("Get total subscriptions error: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Get subscription statistics for admin dashboard
     */
    public function getSubscriptionStats() {
        try {
            $stats = [];
            
            // Total active subscriptions
            $sql1 = "SELECT COUNT(*) as total FROM subscriptions WHERE status = 'active'";
            $stmt1 = $this->conn->query($sql1);
            $stats['active'] = $stmt1->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
            
            // Total expired subscriptions
            $sql2 = "SELECT COUNT(*) as total FROM subscriptions WHERE status = 'expired'";
            $stmt2 = $this->conn->query($sql2);
            $stats['expired'] = $stmt2->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
            
            // Total pending subscriptions
            $sql3 = "SELECT COUNT(*) as total FROM subscriptions WHERE status = 'pending'";
            $stmt3 = $this->conn->query($sql3);
            $stats['pending'] = $stmt3->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
            
            // Total cancelled
            $sql4 = "SELECT COUNT(*) as total FROM subscriptions WHERE status = 'cancelled'";
            $stmt4 = $this->conn->query($sql4);
            $stats['cancelled'] = $stmt4->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
            
            // Total revenue from all subscriptions
            $sql5 = "SELECT SUM(amount) as total FROM subscriptions WHERE status = 'active' OR status = 'expired'";
            $stmt5 = $this->conn->query($sql5);
            $stats['total_revenue'] = $stmt5->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
            
            // Revenue this month
            $sql6 = "SELECT SUM(amount) as total FROM subscriptions 
                    WHERE MONTH(created_at) = MONTH(CURRENT_DATE()) 
                    AND YEAR(created_at) = YEAR(CURRENT_DATE())
                    AND (status = 'active' OR status = 'expired')";
            $stmt6 = $this->conn->query($sql6);
            $stats['monthly_revenue'] = $stmt6->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
            
            // Plan distribution
            $sql7 = "SELECT plan_type, COUNT(*) as count, SUM(amount) as total 
                    FROM subscriptions 
                    WHERE status = 'active' 
                    GROUP BY plan_type";
            $stmt7 = $this->conn->query($sql7);
            $stats['plan_distribution'] = $stmt7->fetchAll(PDO::FETCH_ASSOC);
            
            // Recent subscriptions (last 30 days)
            $sql8 = "SELECT COUNT(*) as total FROM subscriptions 
                    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
            $stmt8 = $this->conn->query($sql8);
            $stats['recent_30days'] = $stmt8->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
            
            return $stats;
            
        } catch (PDOException $e) {
            error_log("Error getting subscription stats: " . $e->getMessage());
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
            error_log("Error getting subscription by ID: " . $e->getMessage());
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
            error_log("Error updating subscription status: " . $e->getMessage());
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
            error_log("Error getting expiring subscriptions: " . $e->getMessage());
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
            error_log("Error getting current subscription: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Calculate prorated upgrade price
     */
    public function calculateUpgradePrice($currentPlan, $newPlan, $currentSubscription) {
        try {
            // Get plan prices from settings
            $settingsModel = new Settings();
            $settings = $settingsModel->getSubscriptionSettings();
            
            $prices = [
                'monthly' => $settings['monthly_price'] ?? 15000,
                'termly' => $settings['termly_price'] ?? 40000,
                'yearly' => $settings['yearly_price'] ?? 120000
            ];
            
            $currentPrice = $prices[$currentPlan] ?? 0;
            $newPrice = $prices[$newPlan] ?? 0;
            
            // Calculate remaining days
            $endDate = new DateTime($currentSubscription['end_date']);
            $now = new DateTime();
            $daysRemaining = $now->diff($endDate)->days;
            
            // Get total days for current plan
            $totalDays = $this->getPlanDays($currentPlan);
            
            // Calculate remaining value (prorated)
            $dailyRate = $currentPrice / $totalDays;
            $remainingValue = $dailyRate * $daysRemaining;
            
            // Price to pay = new plan price - remaining value
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
            error_log("Error calculating upgrade price: " . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Failed to calculate upgrade price'
            ];
        }
    }

    /**
     * Get total days for a plan
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
     * Process subscription upgrade - CORRECTED for your table structure
     */
    public function upgradeSubscription($userId, $fromPlan, $toPlan, $paymentDetails) {
        try {
            // Begin transaction
            $this->conn->beginTransaction();
            
            // Get current subscription
            $currentSubscription = $this->getCurrentSubscription($userId);
            
            if (!$currentSubscription) {
                throw new Exception('No active subscription found');
            }
            
            // Calculate upgrade price
            $priceCalc = $this->calculateUpgradePrice($fromPlan, $toPlan, $currentSubscription);
            
            if (!$priceCalc['success']) {
                throw new Exception('Failed to calculate upgrade price');
            }
            
            // Calculate new end date
            $newEndDate = $this->calculateNewEndDate($currentSubscription['end_date'], $toPlan);
            
            // Update current subscription to 'expired' status (or 'cancelled')
            $updateSql = "UPDATE subscriptions 
                        SET status = 'expired',
                            upgraded_to = :to_plan
                        WHERE id = :subscription_id";
            
            $updateStmt = $this->conn->prepare($updateSql);
            $updateStmt->bindValue(':subscription_id', $currentSubscription['id'], PDO::PARAM_INT);
            $updateStmt->bindValue(':to_plan', $toPlan);
            $updateStmt->execute();
            
            // Create new subscription - MATCHING YOUR TABLE STRUCTURE
            $insertSql = "INSERT INTO subscriptions (
                            user_id, 
                            plan_type, 
                            amount,
                            start_date, 
                            end_date, 
                            payment_method,
                            transaction_id,
                            status,
                            auto_renew,
                            created_at,
                            is_upgrade,
                            upgraded_from,
                            original_subscription_id
                        ) VALUES (
                            :user_id,
                            :plan_type,
                            :amount,
                            NOW(),
                            :end_date,
                            :payment_method,
                            :transaction_id,
                            'active',
                            :auto_renew,
                            NOW(),
                            1,
                            :upgraded_from,
                            :original_subscription_id
                        )";
            
            $insertStmt = $this->conn->prepare($insertSql);
            $insertStmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
            $insertStmt->bindValue(':plan_type', $toPlan);
            $insertStmt->bindValue(':amount', $priceCalc['upgrade_price']);
            $insertStmt->bindValue(':end_date', $newEndDate);
            $insertStmt->bindValue(':payment_method', $paymentDetails['method'] ?? 'mobile_money');
            $insertStmt->bindValue(':transaction_id', $paymentDetails['transaction_id'] ?? ('UPG_' . time()));
            $insertStmt->bindValue(':auto_renew', 1, PDO::PARAM_INT);
            $insertStmt->bindValue(':upgraded_from', $fromPlan);
            $insertStmt->bindValue(':original_subscription_id', $currentSubscription['id'], PDO::PARAM_INT);
            $insertStmt->execute();
            
            $newSubscriptionId = $this->conn->lastInsertId();
            
            // Record payment in payment_history table
            $this->recordUpgradePayment($userId, $fromPlan, $toPlan, $priceCalc['upgrade_price'], $paymentDetails, $newSubscriptionId);
            
            // Commit transaction
            $this->conn->commit();
            
            return [
                'success' => true,
                'message' => 'Successfully upgraded to ' . ucfirst($toPlan) . ' plan',
                'new_subscription_id' => $newSubscriptionId,
                'upgrade_price' => $priceCalc['upgrade_price'],
                'new_end_date' => $newEndDate
            ];
            
        } catch (Exception $e) {
            $this->conn->rollBack();
            error_log("Error upgrading subscription: " . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Calculate new end date after upgrade
     */
    private function calculateNewEndDate($currentEndDate, $newPlan) {
        $endDate = new DateTime($currentEndDate);
        $now = new DateTime();
        
        // Get days for new plan
        $planDays = $this->getPlanDays($newPlan);
        
        // If current end date is in the future, add plan days to current end date
        if ($endDate > $now) {
            $endDate->modify("+{$planDays} days");
        } else {
            // If expired, start from now
            $endDate = $now->modify("+{$planDays} days");
        }
        
        return $endDate->format('Y-m-d H:i:s');
    }

    /**
     * Record upgrade payment - UPDATED to ensure all fields are saved
     */
    private function recordUpgradePayment($userId, $fromPlan, $toPlan, $amount, $paymentDetails, $subscriptionId) {
        try {
            // First, check if payment_history table exists and get its structure
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
            $stmt->bindValue(':payment_method', $paymentDetails['method'] ?? 'mobile_money');
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
            error_log("Error recording upgrade payment: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get subscription settings
     */
    public function getSubscriptionSettings() {
        try {
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
            error_log("Error getting user subscription history: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get upgrade details for a specific subscription
     */
    public function getUpgradeDetails($subscriptionId) {
        try {
            $sql = "SELECT * FROM subscriptions WHERE id = :id";
            $stmt = $this->conn->prepare($sql);
            $stmt->bindValue(':id', $subscriptionId, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting upgrade details: " . $e->getMessage());
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
            
            // Debug log
            error_log("getPaymentForSubscription for ID $subscriptionId returned " . count($results) . " records");
            
            return $results; // Always return an array
            
        } catch (PDOException $e) {
            error_log("Error getting payment details: " . $e->getMessage());
            return []; // Return empty array on error
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
            error_log("Error getting payment history: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get combined subscription and payment history
     */
    public function getCombinedHistory($userId) {
        try {
            // Get subscriptions
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
            
            // Get payment history
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
            
            // Merge and sort by date
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

}
?>