<?php
// File: ipn_handler.php 
error_reporting(E_ALL);
ini_set('display_errors', 1); 
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/logs/php_errors.log');

function ipn_log($message) {
    $logDir = __DIR__ . '/logs';
    if (!file_exists($logDir)) {
        mkdir($logDir, 0777, true);
    }
    $logFile = $logDir . '/ipn_handler.log';
    file_put_contents($logFile, date('Y-m-d H:i:s') . ' - ' . $message . "\n", FILE_APPEND | LOCK_EX);
}

ipn_log("=========================================");
ipn_log("IPN Handler Started");
ipn_log("GET: " . json_encode($_GET));
ipn_log("POST: " . json_encode($_POST));

if (isset($_GET['view_logs'])) {
    $logFile = __DIR__ . '/logs/ipn_handler.log';
    if (file_exists($logFile)) {
        header('Content-Type: text/plain');
        echo file_get_contents($logFile);
    } else {
        echo "No log file found yet.";
    }
    exit;
}

if (isset($_GET['test'])) {
    echo "IPN Handler is working";
    exit;
}

ipn_log("Loading required files...");
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/pesapal.php';
require_once __DIR__ . '/lib/Pesapal.php';

$orderTrackingId = $_GET['OrderTrackingId'] ?? $_GET['order_tracking_id'] ?? null;
$orderMerchantReference = $_GET['OrderMerchantReference'] ?? $_GET['merchant_reference'] ?? null;

ipn_log("OrderTrackingId: " . $orderTrackingId);
ipn_log("OrderMerchantReference: " . $orderMerchantReference);

if (!$orderTrackingId || !$orderMerchantReference) {
    ipn_log("Missing parameters - exiting");
    http_response_code(200);
    echo "Missing parameters";
    exit;
}

ipn_log("Verifying payment with PesaPal API...");

try {
    $pesapal = new Pesapal();
    $paymentStatus = $pesapal->queryPaymentStatus($orderTrackingId);
    ipn_log("PesaPal response: " . print_r($paymentStatus, true));
} catch (Exception $e) {
    ipn_log("Error calling PesaPal: " . $e->getMessage());
    http_response_code(200);
    echo "Error verifying payment";
    exit;
}

$isCompleted = false;
if ($paymentStatus['success'] && isset($paymentStatus['status'])) {
    $status = strtoupper($paymentStatus['status']);
    $isCompleted = ($status === 'COMPLETED' || $status === 'SUCCESS');
    ipn_log("Payment status: $status, Is completed: " . ($isCompleted ? 'YES' : 'NO'));
}

if (!$isCompleted) {
    ipn_log("Payment not completed - subscription NOT activated");
    http_response_code(200);
    echo "Payment not completed";
    exit;
}

ipn_log("Payment is COMPLETED! Processing subscription...");

try {
    $db = Database::getInstance();
    $pdo = $db->getConnection();
    ipn_log("Database connected");
} catch (Exception $e) {
    ipn_log("Database connection failed: " . $e->getMessage());
    http_response_code(200);
    echo "Database error";
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT * FROM payments WHERE transaction_id = ?");
    $stmt->execute([$orderMerchantReference]);
    $payment = $stmt->fetch(PDO::FETCH_ASSOC);
    ipn_log("Payment record: " . json_encode($payment));
    
    if (!$payment) {
        ipn_log("Payment record NOT FOUND for transaction: " . $orderMerchantReference);
        http_response_code(200);
        echo "Payment record not found";
        exit;
    }
    
    if ($payment['status'] === 'completed') {
        ipn_log("Payment already processed - skipping");
        http_response_code(200);
        echo "Already processed";
        exit;
    }
    
    $updateStmt = $pdo->prepare("UPDATE payments SET status = 'completed', payment_date = NOW() WHERE transaction_id = ?");
    $updateStmt->execute([$orderMerchantReference]);
    ipn_log("Payment status updated to completed");
    
    $planType = $payment['plan_type'] ?? 'monthly';
    $planDays = $planType === 'monthly' ? 30 : ($planType === 'termly' ? 90 : 365);
    $endDate = date('Y-m-d H:i:s', strtotime("+{$planDays} days"));
    ipn_log("Plan: $planType, End date: $endDate");
    
    $subStmt = $pdo->prepare("SELECT id FROM subscriptions WHERE user_id = ? AND status = 'active' ORDER BY id DESC LIMIT 1");
    $subStmt->execute([$payment['user_id']]);
    $existingSub = $subStmt->fetch(PDO::FETCH_ASSOC);
    
    if ($existingSub) {
        $updateSubStmt = $pdo->prepare("UPDATE subscriptions SET plan_type = ?, amount = ?, end_date = ?, status = 'active', payment_method = 'pesapal', transaction_id = ?, updated_at = NOW() WHERE id = ?");
        $updateSubStmt->execute([$planType, $payment['amount'], $endDate, $orderMerchantReference, $existingSub['id']]);
        ipn_log("Subscription updated: ID " . $existingSub['id']);
    } else {
        $insertSubStmt = $pdo->prepare("INSERT INTO subscriptions (user_id, plan_type, amount, start_date, end_date, status, payment_method, transaction_id, created_at) VALUES (?, ?, ?, NOW(), ?, 'active', 'pesapal', ?, NOW())");
        $insertSubStmt->execute([$payment['user_id'], $planType, $payment['amount'], $endDate, $orderMerchantReference]);
        ipn_log("New subscription created for user: " . $payment['user_id']);
    }
    
    ipn_log("SUCCESS! Subscription activated for user: " . $payment['user_id']);
    http_response_code(200);
    echo "IPN processed successfully";
    
} catch (Exception $e) {
    ipn_log("ERROR: " . $e->getMessage());
    ipn_log("Stack trace: " . $e->getTraceAsString());
    http_response_code(200);
    echo "Error: " . $e->getMessage();
}
?>