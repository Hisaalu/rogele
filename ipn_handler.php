<?php
// File: ipn_handler.php

// Add at the top of ipn_handler.php for debugging
if (isset($_GET['view_logs'])) {
    $logFile = __DIR__ . '/logs/ipn_handler.log';
    if (file_exists($logFile)) {
        header('Content-Type: text/plain');
        echo file_get_contents($logFile);
    } else {
        echo "No log file found";
    }
    exit;
}

// Disable error display
error_reporting(0);
ini_set('display_errors', 0);

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

// Include your existing database configuration
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/pesapal.php';
require_once __DIR__ . '/lib/Pesapal.php';

try {
    // Use your existing Database class
    $db = Database::getInstance();
    $pdo = $db->getConnection();
    ipn_log("Database connected successfully");
} catch (Exception $e) {
    ipn_log("Database connection failed: " . $e->getMessage());
    http_response_code(200);
    echo "Database error";
    exit;
}

// Get parameters from PesaPal
$orderTrackingId = $_GET['OrderTrackingId'] ?? $_GET['order_tracking_id'] ?? null;
$orderMerchantReference = $_GET['OrderMerchantReference'] ?? $_GET['merchant_reference'] ?? null;
$orderNotificationType = $_GET['OrderNotificationType'] ?? null;

ipn_log("OrderTrackingId: " . $orderTrackingId);
ipn_log("OrderMerchantReference: " . $orderMerchantReference);

if (!$orderTrackingId || !$orderMerchantReference) {
    ipn_log("INFO: Missing parameters - test request");
    http_response_code(200);
    echo "IPN handler is ready";
    exit;
}

// CRITICAL: Verify payment status with PesaPal API
ipn_log("Verifying payment status with PesaPal API...");
$pesapal = new Pesapal();
$paymentStatus = $pesapal->queryPaymentStatus($orderTrackingId);

ipn_log("PesaPal API Response: " . print_r($paymentStatus, true));

// Only process if payment is COMPLETED
if (!$paymentStatus['success'] || strtoupper($paymentStatus['status']) !== 'COMPLETED') {
    ipn_log("WARNING: Payment not completed. Status: " . ($paymentStatus['status'] ?? 'Unknown'));
    ipn_log("NOT activating subscription - payment failed or pending");
    http_response_code(200);
    echo "Payment not completed - subscription not activated";
    exit;
}

ipn_log("Payment verified as COMPLETED by PesaPal API");

try {
    // Get payment record
    $stmt = $pdo->prepare("SELECT * FROM payments WHERE transaction_id = ?");
    $stmt->execute([$orderMerchantReference]);
    $payment = $stmt->fetch(PDO::FETCH_ASSOC);
    
    ipn_log("Payment lookup result: " . ($payment ? "Found" : "Not found"));
    
    if ($payment) {
        ipn_log("Payment found - User ID: " . $payment['user_id'] . ", Current Status: " . $payment['status']);
        
        // Only update if not already completed
        if ($payment['status'] !== 'completed') {
            // Update payment status to completed
            $updateStmt = $pdo->prepare("UPDATE payments SET status = 'completed', payment_date = NOW(), payment_gateway_response = ? WHERE transaction_id = ?");
            $updateStmt->execute([json_encode($paymentStatus), $orderMerchantReference]);
            ipn_log("Payment status updated to completed");
            
            // Determine plan type and days
            $planType = $payment['plan_type'] ?? 'monthly';
            $planDays = $planType === 'monthly' ? 30 : ($planType === 'termly' ? 90 : 365);
            $endDate = date('Y-m-d H:i:s', strtotime("+{$planDays} days"));
            
            // Check for existing active subscription
            $subStmt = $pdo->prepare("SELECT id FROM subscriptions WHERE user_id = ? AND status = 'active' ORDER BY id DESC LIMIT 1");
            $subStmt->execute([$payment['user_id']]);
            $existingSub = $subStmt->fetch(PDO::FETCH_ASSOC);
            
            if ($existingSub) {
                // Update existing subscription
                $updateSubStmt = $pdo->prepare("UPDATE subscriptions SET plan_type = ?, amount = ?, end_date = ?, status = 'active', payment_method = 'pesapal', transaction_id = ?, updated_at = NOW() WHERE id = ?");
                $updateSubStmt->execute([$planType, $payment['amount'], $endDate, $orderMerchantReference, $existingSub['id']]);
                ipn_log("Subscription updated: ID " . $existingSub['id']);
            } else {
                // Create new subscription
                $insertSubStmt = $pdo->prepare("INSERT INTO subscriptions (user_id, plan_type, amount, start_date, end_date, status, payment_method, transaction_id, created_at) VALUES (?, ?, ?, NOW(), ?, 'active', 'pesapal', ?, NOW())");
                $insertSubStmt->execute([$payment['user_id'], $planType, $payment['amount'], $endDate, $orderMerchantReference]);
                $newSubId = $pdo->lastInsertId();
                ipn_log("New subscription created: ID " . $newSubId);
            }
            
            ipn_log("SUCCESS: Payment and subscription activated for user: " . $payment['user_id']);
            http_response_code(200);
            echo "IPN processed successfully - subscription activated";
        } else {
            ipn_log("Payment already completed previously - no action taken");
            http_response_code(200);
            echo "Payment already processed";
        }
    } else {
        ipn_log("ERROR: No payment record found for reference: " . $orderMerchantReference);
        http_response_code(200);
        echo "Payment record not found";
    }
} catch (Exception $e) {
    ipn_log("ERROR processing IPN: " . $e->getMessage());
    http_response_code(200);
    echo "Error: " . $e->getMessage();
}

exit;
?>