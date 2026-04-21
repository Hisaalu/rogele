<?php
// File: test_pesapal_api.php - Use the new OrderTrackingId

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<pre>";
echo "Testing PesaPal API Connection\n";
echo "==============================\n\n";

require_once __DIR__ . '/config/pesapal.php';
require_once __DIR__ . '/lib/Pesapal.php';

echo "Environment: " . PESAPAL_ENVIRONMENT . "\n\n";

$pesapal = new Pesapal();

// Use the NEW OrderTrackingId from your recent logs
$orderTrackingId = "fe8b2f82-864b-4ed0-b600-da7932222da5";
echo "Testing with OrderTrackingId: $orderTrackingId\n\n";

$result = $pesapal->queryPaymentStatus($orderTrackingId);

echo "Result:\n";
print_r($result);

echo "\n</pre>";
?>