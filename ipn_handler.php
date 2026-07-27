<?php
// File: ipn_handler.php 
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/logs/php_errors.log');

function ipn_log($message) {
    $logDir = __DIR__ . '/logs';
    if (!file_exists($logDir)) {
        mkdir($logDir, 0777, true);
    }
    file_put_contents($logDir . '/ipn_handler.log', date('Y-m-d H:i:s') . ' - ' . $message . "\n", FILE_APPEND | LOCK_EX);
}

ipn_log("=========================================");
ipn_log("Mobile Money Webhook Handler Triggered");

$rawPayload = file_get_contents('php://input');
if (empty($rawPayload) && !empty($_POST)) {
    $rawPayload = json_encode($_POST);
}

ipn_log("Payload: " . $rawPayload);

$data = json_decode($rawPayload, true) ?? [];

$transactionId = $data['externalId'] ?? $data['transaction']['id'] ?? $_GET['reference'] ?? $_GET['OrderMerchantReference'] ?? null;
$status = strtoupper($data['status'] ?? $data['transaction']['status'] ?? $_GET['status'] ?? '');

if (!$transactionId) {
    ipn_log("Missing transaction reference.");
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Missing reference']);
    exit;
}

try {
    require_once __DIR__ . '/config/database.php';
    require_once __DIR__ . '/models/Subscription.php';

    $subscriptionModel = new Subscription();
    $payment = $subscriptionModel->getPaymentByTransactionId($transactionId);

    if (!$payment) {
        ipn_log("Payment record not found for reference: " . $transactionId);
        http_response_code(200);
        echo json_encode(['status' => 'ignored', 'message' => 'Payment record not found']);
        exit;
    }

    if ($payment['status'] === 'completed') {
        ipn_log("Payment reference " . $transactionId . " is already completed. Skipping.");
        http_response_code(200);
        echo json_encode(['status' => 'success', 'message' => 'Already processed']);
        exit;
    }

    if (in_array($status, ['SUCCESSFUL', 'SUCCESS', 'COMPLETED', 'PAID', '200'])) {
        $subscriptionModel->updatePaymentStatus($transactionId, 'completed', $data);
        ipn_log("Payment marked completed & subscription activated: " . $transactionId);
    } else if (in_array($status, ['FAILED', 'REJECTED', 'CANCELLED'])) {
        $subscriptionModel->updatePaymentStatus($transactionId, 'failed', $data);
        ipn_log("Payment marked as failed: " . $transactionId);
    } else {
        ipn_log("Unhandled payment status: " . $status);
    }

    http_response_code(200);
    echo json_encode(['status' => 'success']);
} catch (Exception $e) {
    ipn_log("Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Internal server error']);
}