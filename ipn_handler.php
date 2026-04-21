<?php
// File: /ipn_handler.php - Completely standalone IPN handler
// This file bypasses all framework authentication

// Enable error logging
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/logs/ipn_errors.log');

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

// Load database configuration from environment
$dbHost = getenv('DB_HOST') ?: 'localhost';
$dbName = getenv('DB_NAME') ?: 'rogele_db';
$dbUser = getenv('DB_USER') ?: 'root';
$dbPass = getenv('DB_PASS') ?: '';

try {
    $pdo = new PDO("mysql:host=$dbHost;dbname=$dbName;charset=utf8mb4", $dbUser, $dbPass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    ipn_log("Database connected");
} catch (Exception $e) {
    ipn_log("Database connection failed: " . $e->getMessage());
    http_response_code(200);
    echo "Database error";
    exit;
}

// Get parameters
$orderTrackingId = $_GET['OrderTrackingId'] ?? $_GET['order_tracking_id'] ?? null;
$orderMerchantReference = $_GET['OrderMerchantReference'] ?? $_GET['merchant_reference'] ?? null;

if (!$orderTrackingId || !$orderMerchantReference) {
    ipn_log("Missing parameters");
    http_response_code(200);
    echo "Missing parameters";
    exit;
}

ipn_log("OrderTrackingId: $orderTrackingId");
ipn_log("MerchantReference: $orderMerchantReference");

// Get payment record
try {
    $stmt = $pdo->prepare("SELECT * FROM payments WHERE transaction_id = ?");
    $stmt->execute([$orderMerchantReference]);
    $payment = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($payment) {
        ipn_log("Found payment for user: " . $payment['user_id'] . ", status: " . $payment['status']);
        
        if ($payment['status'] !== 'completed') {
            // Update payment status
            $updateStmt = $pdo->prepare("UPDATE payments SET status = 'completed', payment_date = NOW() WHERE transaction_id = ?");
            $updateStmt->execute([$orderMerchantReference]);
            ipn_log("Payment status updated to completed");
            
            // Check if subscription exists
            $subStmt = $pdo->prepare("SELECT * FROM subscriptions WHERE user_id = ? AND status = 'active' ORDER BY id DESC LIMIT 1");
            $subStmt->execute([$payment['user_id']]);
            $existingSub = $subStmt->fetch(PDO::FETCH_ASSOC);
            
            $planDays = $payment['plan_type'] === 'monthly' ? 30 : ($payment['plan_type'] === 'termly' ? 90 : 365);
            $endDate = date('Y-m-d H:i:s', strtotime("+{$planDays} days"));
            
            if ($existingSub) {
                // Update existing subscription
                $updateSubStmt = $pdo->prepare("UPDATE subscriptions SET plan_type = ?, amount = ?, end_date = ?, status = 'active', payment_method = 'pesapal', transaction_id = ? WHERE id = ?");
                $updateSubStmt->execute([$payment['plan_type'], $payment['amount'], $endDate, $orderMerchantReference, $existingSub['id']]);
                ipn_log("Subscription updated: ID " . $existingSub['id']);
            } else {
                // Create new subscription
                $insertSubStmt = $pdo->prepare("INSERT INTO subscriptions (user_id, plan_type, amount, start_date, end_date, status, payment_method, transaction_id) VALUES (?, ?, ?, NOW(), ?, 'active', 'pesapal', ?)");
                $insertSubStmt->execute([$payment['user_id'], $payment['plan_type'], $payment['amount'], $endDate, $orderMerchantReference]);
                ipn_log("New subscription created for user: " . $payment['user_id']);
            }
            
            echo "IPN processed successfully";
        } else {
            ipn_log("Payment already completed");
            echo "Payment already processed";
        }
    } else {
        ipn_log("Payment not found for reference: " . $orderMerchantReference);
        echo "Payment not found";
    }
} catch (Exception $e) {
    ipn_log("Error: " . $e->getMessage());
    echo "Error processing IPN";
}

http_response_code(200);
exit;
?>