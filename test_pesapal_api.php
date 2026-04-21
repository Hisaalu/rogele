<?php
// File: test_pesapal_api.php - Test PesaPal API connection

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<pre>";
echo "Testing PesaPal API Connection\n";
echo "==============================\n\n";

// Load config
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
    
    // Test 2: Query a known order (use a recent OrderTrackingId from your logs)
    $orderTrackingId = "35d2ea1f-4900-4ffa-af45-da7952c682bb"; // From your latest log
    echo "Test 2: Querying payment status for: $orderTrackingId\n";
    
    $url = (PESAPAL_ENVIRONMENT == 'production' ? 'https://pay.pesapal.com' : 'https://cybqa.pesapal.com') . 
           '/api/Transactions/GetTransactionStatus?order_tracking_id=' . urlencode($orderTrackingId);
    
    echo "URL: $url\n";
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $token,
        'Accept: application/json'
    ]);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);
    
    echo "HTTP Code: $httpCode\n";
    if ($curlError) {
        echo "CURL Error: $curlError\n";
    }
    echo "Response: " . print_r(json_decode($response, true), true) . "\n";
    
} else {
    echo "✗ Failed to get access token\n";
    echo "Check your consumer key and secret\n";
}

echo "\n</pre>";
?>