<?php
// File: /test-contact-direct.php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'config/config.php';
require_once 'controllers/HomeController.php';

$controller = new HomeController();

// Manually set POST data
$_SERVER['REQUEST_METHOD'] = 'POST';
$_POST['name'] = 'Test User';
$_POST['email'] = 'test@example.com';
$_POST['subject'] = 'Test Subject';
$_POST['message'] = 'Test message content';

// Capture output
ob_start();
$controller->sendContact();
$output = ob_get_clean();

echo "<h2>Debug Output</h2>";
echo "<h3>Raw Output:</h3>";
echo "<pre>" . htmlspecialchars($output) . "</pre>";

echo "<h3>JSON Decode Test:</h3>";
$decoded = json_decode($output, true);
if ($decoded === null) {
    echo "JSON decode error: " . json_last_error_msg() . "<br>";
    echo "First 100 chars: " . substr($output, 0, 100);
} else {
    echo "<pre>";
    print_r($decoded);
    echo "</pre>";
}
?>