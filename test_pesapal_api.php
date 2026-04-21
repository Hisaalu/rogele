<?php
// File: test_pesapal_api.php - Updated to use the Pesapal class

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<pre>";
echo "Testing PesaPal API Connection\n";
echo "==============================\n\n";

// Load config and class
require_once __DIR__ . '/config/pesapal.php';
require_once __DIR__ . '/lib/Pesapal.php';

echo "Environment: " . PESAPAL_ENVIRONMENT . "\n";
echo "Consumer Key: " . substr(PESAPAL_CONSUMER_KEY, 0, 10) . "...\n";
echo "API Base URL: " . (PESAPAL_ENVIRONMENT == 'production' ? 'https://pay.pesapal.com' : 'https://cybqa.pesapal.com') . "\n\n";

$pesapal = new Pesapal();

// Test 1: Get Access Token
echo "Test 1: Getting Access Token...\n";
$token = $pesapal->getAccessToken();

if ($token) {
    echo "✓ Access token obtained: " . substr($token, 0, 30) . "...\n\n";
    
    // Test 2: Query payment status using the class method
    $orderTrackingId = "35d2ea1f-4900-4ffa-af45-da7952c682bb"; // Use a recent OrderTrackingId
    echo "Test 2: Querying payment status for: $orderTrackingId\n";
    
    $result = $pesapal->queryPaymentStatus($orderTrackingId);
    
    echo "Result:\n";
    print_r($result);
    
} else {
    echo "✗ Failed to get access token\n";
    echo "Check your consumer key and secret\n";
}

echo "\n</pre>";
?>