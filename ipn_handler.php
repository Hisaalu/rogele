<?php
// File: ipn_handler.php - Improved version with better debugging

// Enable error logging
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Log function
function ipn_log($message) {
    $logDir = __DIR__ . '/logs';
    if (!file_exists($logDir)) {
        mkdir($logDir, 0777, true);
    }
    $logFile = $logDir . '/ipn_handler.log';
    file_put_contents($logFile, date('Y-m-d H:i:s') . ' - ' . $message . "\n", FILE_APPEND);
}

ipn_log("========== IPN REQUEST RECEIVED ==========");
ipn_log("GET: " . json_encode($_GET));
ipn_log("POST: " . json_encode($_POST));

// Include required files
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/pesapal.php';
require_once __DIR__ . '/lib/Pesapal.php';

try {
    $db = Database::getInstance();
    $pdo = $db->getConnection();
    ipn_log("Database connected successfully");
} catch (Exception $e) {
    ipn_log("Database connection FAILED: " . $e->getMessage());
    http_response_code(200);
    echo "Database error: " . $e->getMessage();
    exit;
}

// Get parameters
$orderTrackingId = $_GET['OrderTrackingId'] ?? $_GET['order_tracking_id'] ?? null;
$orderMerchantReference = $_GET['OrderMerchantReference'] ?? $_GET['merchant_reference'] ?? null;

ipn_log("OrderTrackingId: " . $orderTrackingId);
ipn_log("OrderMerchantReference: " . $orderMerchantReference);

if (!$orderTrackingId || !$orderMerchantReference) {
    ipn_log("INFO: Missing parameters - test request");
    http_response_code(200);
    echo "IPN handler is ready";
    exit;
}

// Verify payment status with PesaPal
ipn_log("Verifying payment with PesaPal API...");
$pesapal = new Pesapal();
$paymentStatus = $pesapal->queryPaymentStatus($orderTrackingId);

ipn_log("PesaPal Response: " . print_r($paymentStatus, true));

// Check if payment is completed
$isCompleted = false;
$paymentStatusText = '';

if ($paymentStatus['success']) {
    $paymentStatusText = strtoupper($paymentStatus['status']);
    $isCompleted = ($paymentStatusText === 'COMPLETED' || $paymentStatusText === 'SUCCESS');
    ipn_log("Payment status from PesaPal: " . $paymentStatusText);
    ipn_log("Is completed: " . ($isCompleted ? 'YES' : 'NO'));
} else {
    ipn_log("Failed to verify payment with PesaPal: " . ($paymentStatus['message'] ?? 'Unknown error'));
}

if (!$isCompleted) {
    ipn_log("WARNING: Payment NOT completed. Status: " . $paymentStatusText);
    ipn_log("NOT activating subscription");
    http_response_code(200);
    echo "Payment not completed - subscription not activated";
    exit;
}

ipn_log("Payment verified as COMPLETED");

try {
    // Get payment record
    $stmt = $pdo->prepare("SELECT * FROM payments WHERE transaction_id = ?");
    $stmt->execute([$orderMerchantReference]);
    $payment = $stmt->fetch(PDO::FETCH_ASSOC);
    
    ipn_log("Payment record: " . ($payment ? json_encode($payment) : "NOT FOUND"));
    
    if (!$payment) {
        ipn_log("ERROR: Payment record not found for transaction: " . $orderMerchantReference);
        http_response_code(200);
        echo "Payment record not found";
        exit;
    }
    
    ipn_log("Payment found - User ID: " . $payment['user_id'] . ", Current status: " . $payment['status']);
    
    if ($payment['status'] === 'completed') {
        ipn_log("Payment already processed - skipping");
        http_response_code(200);
        echo "Payment already processed";
        exit;
    }
    
    // Update payment status
    $updateStmt = $pdo->prepare("UPDATE payments SET status = 'completed', payment_date = NOW(), payment_gateway_response = ? WHERE transaction_id = ?");
    $updateStmt->execute([json_encode($paymentStatus), $orderMerchantReference]);
    ipn_log("Payment status updated to completed");
    
    // Get plan details
    $planType = $payment['plan_type'] ?? 'monthly';
    $planDays = 30;
    if ($planType === 'termly') $planDays = 90;
    elseif ($planType === 'yearly') $planDays = 365;
    
    $endDate = date('Y-m-d H:i:s', strtotime("+{$planDays} days"));
    ipn_log("Plan: $planType, End date: $endDate");
    
    // Check for existing active subscription
    $subStmt = $pdo->prepare("SELECT id, end_date FROM subscriptions WHERE user_id = ? AND status = 'active' ORDER BY id DESC LIMIT 1");
    $subStmt->execute([$payment['user_id']]);
    $existingSub = $subStmt->fetch(PDO::FETCH_ASSOC);
    
    if ($existingSub) {
        ipn_log("Existing active subscription found: ID " . $existingSub['id']);
        
        // Update existing subscription
        $updateSubStmt = $pdo->prepare("UPDATE subscriptions SET plan_type = ?, amount = ?, end_date = ?, status = 'active', payment_method = 'pesapal', transaction_id = ?, updated_at = NOW() WHERE id = ?");
        $updateSubStmt->execute([$planType, $payment['amount'], $endDate, $orderMerchantReference, $existingSub['id']]);
        ipn_log("Subscription updated: ID " . $existingSub['id']);
    } else {
        ipn_log("No active subscription found - creating new one");
        
        // Create new subscription
        $insertSubStmt = $pdo->prepare("INSERT INTO subscriptions (user_id, plan_type, amount, start_date, end_date, status, payment_method, transaction_id, created_at) VALUES (?, ?, ?, NOW(), ?, 'active', 'pesapal', ?, NOW())");
        $insertSubStmt->execute([$payment['user_id'], $planType, $payment['amount'], $endDate, $orderMerchantReference]);
        $newSubId = $pdo->lastInsertId();
        ipn_log("New subscription created: ID " . $newSubId);
    }
    
    ipn_log("SUCCESS: Subscription activated for user: " . $payment['user_id']);
    http_response_code(200);
    echo "IPN processed successfully - subscription activated";
    
} catch (Exception $e) {
    ipn_log("ERROR processing IPN: " . $e->getMessage());
    ipn_log("Stack trace: " . $e->getTraceAsString());
    http_response_code(200);
    echo "Error: " . $e->getMessage();
}

exit;
?>