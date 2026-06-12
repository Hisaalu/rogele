<?php
// File: /controllers/AdminSubscriptionController.php 
require_once __DIR__ . '/../models/Subscription.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Settings.php';

class AdminSubscriptionController {
    private $subscriptionModel;
    private $userModel;
    private $settingsModel;
    private $itemsPerPage = 20;
    
    public function __construct() {
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
            $this->redirect(BASE_URL . '/login');
        }
        
        $this->subscriptionModel = new Subscription();
        $this->userModel = new User();
        $this->settingsModel = new Settings();
    }
    
    // ==================== HELPER METHODS ====================
    private function redirect($url) {
        header('Location: ' . $url);
        exit;
    }
    
    private function redirectWithError($message, $url) {
        $_SESSION['error'] = $message;
        $this->redirect($url);
    }
    
    private function redirectWithSuccess($message, $url) {
        $_SESSION['success'] = $message;
        $this->redirect($url);
    }
    
    private function isPostRequest() {
        return $_SERVER['REQUEST_METHOD'] === 'POST';
    }
    
    private function getPaginationParams() {
        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $offset = ($page - 1) * $this->itemsPerPage;
        return ['page' => $page, 'offset' => $offset, 'limit' => $this->itemsPerPage];
    }
    
    private function getFilters() {
        return [
            'status' => $_GET['status'] ?? '',
            'plan_type' => $_GET['plan_type'] ?? '',
            'search' => $_GET['search'] ?? '',
            'date_from' => $_GET['date_from'] ?? '',
            'date_to' => $_GET['date_to'] ?? ''
        ];
    }
    
    private function validateSubscriptionExists($subscription, $id) {
        if (!$subscription) {
            $this->redirectWithError('Subscription not found', BASE_URL . '/admin/subscriptions');
        }
    }
    
    // ==================== PUBLIC METHODS ====================
    public function index() {
        $hideFooter = true;
        
        $pagination = $this->getPaginationParams();
        $filters = $this->getFilters();
        
        $subscriptions = $this->subscriptionModel->getAllSubscriptions($filters, $pagination['limit'], $pagination['offset']);
        $totalSubscriptions = $this->subscriptionModel->countAllSubscriptions($filters);
        $totalPages = ceil($totalSubscriptions / $this->itemsPerPage);
        $stats = $this->subscriptionModel->getSubscriptionStats();
        $users = $this->userModel->getAllUsers(null, 100, 0);
        
        require_once __DIR__ . '/../views/admin/subscriptions/index.php';
    }

    public function view($id) {
        $hideFooter = true;
        
        $subscription = $this->subscriptionModel->getSubscriptionById($id);
        $this->validateSubscriptionExists($subscription, $id);
        
        $filters = ['user_id' => $subscription['user_id']];
        $userHistory = $this->subscriptionModel->getAllSubscriptions($filters, 0, 0);
        $paymentHistory = $this->subscriptionModel->getPaymentForSubscription($id);
        
        if (!is_array($paymentHistory)) {
            $paymentHistory = [];
        }
        
        require_once __DIR__ . '/../views/admin/subscriptions/view.php';
    }
    
    public function updateStatus() {
        if (!$this->isPostRequest()) {
            $this->redirect(BASE_URL . '/admin/subscriptions');
        }
        
        $subscriptionId = (int)($_POST['subscription_id'] ?? 0);
        $status = $_POST['status'] ?? '';
        
        if (!$subscriptionId || !$status) {
            $this->redirectWithError('Invalid request', BASE_URL . '/admin/subscriptions');
        }
        
        $result = $this->subscriptionModel->updateSubscriptionStatus($subscriptionId, $status);
        
        if ($result['success']) {
            $this->redirectWithSuccess("Subscription #{$subscriptionId} has been updated", BASE_URL . '/admin/subscriptions');
        } else {
            $this->redirectWithError($result['error'], BASE_URL . '/admin/subscriptions');
        }
    }
    
    public function cancel($id) {
        $result = $this->subscriptionModel->cancelSubscription($id);
        
        if ($result['success']) {
            $this->redirectWithSuccess("Subscription #{$id} has been cancelled", BASE_URL . '/admin/subscriptions');
        } else {
            $this->redirectWithError($result['error'], BASE_URL . '/admin/subscriptions');
        }
    }
    
    public function export() {
        $filters = $this->getFilters();
        $subscriptions = $this->subscriptionModel->getAllSubscriptions($filters, 0, 0);
        
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="subscriptions_' . date('Y-m-d') . '.csv"');
        
        $output = fopen('php://output', 'w');
        
        $headers = ['ID', 'User', 'Email', 'Plan', 'Amount', 'Start Date', 'End Date', 'Status', 'Payment Method', 'Transaction ID', 'Is Upgrade', 'Created At'];
        fputcsv($output, $headers);
        
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
    
    public function reports() {
        $hideFooter = true;
        
        $stats = $this->subscriptionModel->getSubscriptionStats();
        $expiring = $this->subscriptionModel->getExpiringSubscriptions(30);
        $revenueByMonth = $this->getRevenueByMonth();
        
        require_once __DIR__ . '/../views/admin/subscriptions/reports.php';
    }
    
    private function getRevenueByMonth() {
        try {
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
            return [];
        }
    }
}
?>