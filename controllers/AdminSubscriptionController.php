<?php
// File: /controllers/AdminSubscriptionController.php
require_once __DIR__ . '/../models/Subscription.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Settings.php';

class AdminSubscriptionController {
    private $subscriptionModel;
    private $userModel;
    private $settingsModel;
    
    public function __construct() {
        // Check if user is logged in and is admin
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
            header('Location: ' . BASE_URL . '/login');
            exit;
        }
        
        $this->subscriptionModel = new Subscription();
        $this->userModel = new User();
        $this->settingsModel = new Settings();
    }
    
    /**
     * List all subscriptions
     */
    public function index() {
        $hideFooter = true;
        
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = 20;
        $offset = ($page - 1) * $limit;
        
        // Get filters from request
        $filters = [
            'status' => $_GET['status'] ?? '',
            'plan_type' => $_GET['plan_type'] ?? '',
            'search' => $_GET['search'] ?? '',
            'date_from' => $_GET['date_from'] ?? '',
            'date_to' => $_GET['date_to'] ?? ''
        ];
        
        // Get subscriptions
        $subscriptions = $this->subscriptionModel->getAllSubscriptions($filters, $limit, $offset);
        
        // Get total count for pagination
        $totalSubscriptions = $this->subscriptionModel->countAllSubscriptions($filters);
        $totalPages = ceil($totalSubscriptions / $limit);
        
        // Get statistics
        $stats = $this->subscriptionModel->getSubscriptionStats();
        
        // Get all users for filter dropdown
        $users = $this->userModel->getAllUsers(null, 100, 0);
        
        require_once __DIR__ . '/../views/admin/subscriptions/index.php';
    }
    
    /**
     * View subscription details
     */
    public function view($id) {
        $hideFooter = true;
        
        // Get subscription details
        $subscription = $this->subscriptionModel->getSubscriptionById($id);
        
        if (!$subscription) {
            $_SESSION['error'] = 'Subscription not found';
            header('Location: ' . BASE_URL . '/admin/subscriptions');
            exit;
        }
        
        // Get user's subscription history (ALL subscriptions for this user)
        // Make sure this method exists and returns all subscriptions for the user
        $filters = ['user_id' => $subscription['user_id']];
        $userHistory = $this->subscriptionModel->getAllSubscriptions($filters, 0, 0);
        
        // Debug: Check if userHistory is being fetched
        error_log("User History count: " . count($userHistory));
        
        // Get payment history for this specific subscription
        $paymentHistory = $this->subscriptionModel->getPaymentForSubscription($id);
        
        // If paymentHistory is not an array, make it an array
        if (!is_array($paymentHistory)) {
            $paymentHistory = $paymentHistory ? [$paymentHistory] : [];
        }
        
        // Pass all variables to the view
        require_once __DIR__ . '/../views/admin/subscriptions/view.php';
    }
    
    /**
     * Update subscription status
     */
    public function updateStatus() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . BASE_URL . '/admin/subscriptions');
            exit;
        }
        
        $subscriptionId = $_POST['subscription_id'] ?? 0;
        $status = $_POST['status'] ?? '';
        $action = $_POST['action'] ?? '';
        
        if (!$subscriptionId || !$status) {
            $_SESSION['error'] = 'Invalid request';
            header('Location: ' . BASE_URL . '/admin/subscriptions');
            exit;
        }
        
        $result = $this->subscriptionModel->updateSubscriptionStatus($subscriptionId, $status);
        
        if ($result['success']) {
            $_SESSION['success'] = "Subscription #{$subscriptionId} has been " . $action;
        } else {
            $_SESSION['error'] = $result['error'];
        }
        
        header('Location: ' . BASE_URL . '/admin/subscriptions');
        exit;
    }
    
    /**
     * Cancel subscription
     */
    public function cancel($id) {
        $result = $this->subscriptionModel->cancelSubscription($id);
        
        if ($result['success']) {
            $_SESSION['success'] = "Subscription #{$id} has been cancelled";
        } else {
            $_SESSION['error'] = $result['error'];
        }
        
        header('Location: ' . BASE_URL . '/admin/subscriptions');
        exit;
    }
    
    /**
     * Export subscriptions to CSV
     */
    public function export() {
        $filters = [
            'status' => $_GET['status'] ?? '',
            'plan_type' => $_GET['plan_type'] ?? '',
            'date_from' => $_GET['date_from'] ?? '',
            'date_to' => $_GET['date_to'] ?? ''
        ];
        
        $subscriptions = $this->subscriptionModel->getAllSubscriptions($filters, 0, 0);
        
        // Set headers for CSV download
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="subscriptions_' . date('Y-m-d') . '.csv"');
        
        $output = fopen('php://output', 'w');
        
        // Add CSV headers
        fputcsv($output, [
            'ID', 'User', 'Email', 'Plan', 'Amount', 'Start Date', 
            'End Date', 'Status', 'Payment Method', 'Transaction ID', 
            'Is Upgrade', 'Created At'
        ]);
        
        // Add data rows
        foreach ($subscriptions as $sub) {
            fputcsv($output, [
                $sub['id'],
                $sub['first_name'] . ' ' . $sub['last_name'],
                $sub['email'],
                ucfirst($sub['plan_type']),
                $sub['amount'],
                date('Y-m-d', strtotime($sub['start_date'])),
                date('Y-m-d', strtotime($sub['end_date'])),
                $sub['status'],
                $sub['payment_method'],
                $sub['transaction_id'],
                $sub['is_upgrade'] ? 'Yes' : 'No',
                date('Y-m-d', strtotime($sub['created_at']))
            ]);
        }
        
        fclose($output);
        exit;
    }
    
    /**
     * Subscription reports
     */
    public function reports() {
        $hideFooter = true;
        
        $stats = $this->subscriptionModel->getSubscriptionStats();
        
        // Get expiring subscriptions
        $expiring = $this->subscriptionModel->getExpiringSubscriptions(30);
        
        // Get revenue by month for chart
        $revenueByMonth = $this->getRevenueByMonth();
        
        require_once __DIR__ . '/../views/admin/subscriptions/reports.php';
    }
    
    /**
     * Get revenue by month for charts
     */
    private function getRevenueByMonth() {
        try {
            // Get the connection from the subscription model
            $conn = $this->subscriptionModel->getConnection();
            
            $sql = "SELECT 
                        DATE_FORMAT(created_at, '%Y-%m') as month,
                        COUNT(*) as count,
                        SUM(amount) as revenue
                    FROM subscriptions 
                    WHERE status IN ('active', 'expired')
                    AND created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
                    GROUP BY DATE_FORMAT(created_at, '%Y-%m')
                    ORDER BY month DESC";
            
            $stmt = $conn->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            error_log("Error getting revenue by month: " . $e->getMessage());
            return [];
        }
    }
}