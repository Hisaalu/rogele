<?php
// File: /test-pesapal-debug.php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/pesapal.php';
require_once __DIR__ . '/lib/Pesapal.php';

echo "<h2>Pesapal Debug Test</h2>";

// Check credentials
echo "<h3>1. Credentials Check:</h3>";
if (PESAPAL_CONSUMER_KEY == 'YOUR_CONSUMER_KEY_HERE') {
    echo "<p style='color:red'>❌ Please update your Consumer Key in config/pesapal.php</p>";
} else {
    echo "<p style='color:green'>✅ Consumer Key: " . substr(PESAPAL_CONSUMER_KEY, 0, 15) . "...</p>";
}

if (PESAPAL_CONSUMER_SECRET == 'YOUR_CONSUMER_SECRET_HERE') {
    echo "<p style='color:red'>❌ Please update your Consumer Secret in config/pesapal.php</p>";
} else {
    echo "<p style='color:green'>✅ Consumer Secret: " . substr(PESAPAL_CONSUMER_SECRET, 0, 10) . "...</p>";
}

echo "<h3>2. Environment:</h3>";
echo "<p>Environment: " . PESAPAL_ENVIRONMENT . "</p>";
echo "<p>API URL: " . PESAPAL_API_URL . "</p>";

// Test with minimal data
echo "<h3>3. Testing Payment Submission:</h3>";

$pesapal = new Pesapal();
$reference = 'TEST_' . time();

// Use a valid test phone number format
$phone = '256772123456'; // Valid Uganda format for testing

$paymentData = [
    'amount' => 1000, // Small amount for testing
    'phone' => $phone,
    'email' => 'test@example.com',
    'first_name' => 'Test',
    'last_name' => 'User',
    'reference' => $reference,
    'description' => 'Test Payment'
];

echo "<p>Reference: " . $reference . "</p>";
echo "<p>Amount: UGX 1000</p>";
echo "<p>Phone: " . $phone . "</p>";

// Direct cURL test to see raw response
$params = array(
    'oauth_callback' => PESAPAL_CALLBACK_URL,
    'pesapal_merchant_reference' => $reference,
    'pesapal_transaction_type' => 'MERCHANT',
    'pesapal_total_amount' => 1000,
    'pesapal_currency' => 'UGX',
    'pesapal_description' => 'Test Payment',
    'pesapal_first_name' => 'Test',
    'pesapal_last_name' => 'User',
    'pesapal_email' => 'test@example.com',
    'pesapal_phone_number' => $phone,
    'pesapal_shipping_phone' => $phone,
    'pesapal_shipping_address1' => 'Kampala'
);

echo "<h4>Request Parameters:</h4>";
echo "<pre>";
print_r($params);
echo "</pre>";

// Get OAuth signature
$consumer = new OAuthConsumer(PESAPAL_CONSUMER_KEY, PESAPAL_CONSUMER_SECRET);
$signatureMethod = new OAuthSignatureMethod_HMAC_SHA1();
$req = OAuthRequest::from_consumer_and_token($consumer, null, 'POST', PESAPAL_API_URL, $params);
$req->sign_request($signatureMethod, $consumer, null);
$signature = $req->to_header();

echo "<h4>OAuth Header:</h4>";
echo "<pre>" . htmlspecialchars($signature) . "</pre>";

// Make the request
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, PESAPAL_API_URL);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
curl_setopt($ch, CURLOPT_HTTPHEADER, array($signature, "Content-Type: application/x-www-form-urlencoded"));
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_VERBOSE, true);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$info = curl_getinfo($ch);
curl_close($ch);

echo "<h4>HTTP Response Code:</h4>";
echo "<p>" . $httpCode . "</p>";

echo "<h4>Raw Response:</h4>";
echo "<pre>" . htmlspecialchars($response) . "</pre>";

echo "<h4>Parsed Response:</h4>";
parse_str($response, $result);
echo "<pre>";
print_r($result);
echo "</pre>";

if (isset($result['pesapal_redirect_url'])) {
    echo "<p style='color:green'>✅ Success! Redirect URL: " . $result['pesapal_redirect_url'] . "</p>";
    echo "<p><a href='" . $result['pesapal_redirect_url'] . "' target='_blank'>Go to Pesapal</a></p>";
} else {
    echo "<p style='color:red'>❌ Failed. Response: " . $response . "</p>";
}

// Also try the library method
echo "<h3>4. Library Method Test:</h3>";
$libraryResponse = $pesapal->submitPayment($paymentData);
echo "<pre>";
print_r($libraryResponse);
echo "</pre>";
?>