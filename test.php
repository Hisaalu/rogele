<?php
// File: /test-auth-flow.php

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Testing Auth Flow - Password Reset</h1>";

// Load required files
require_once __DIR__ . '/config/env.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/models/User.php';
require_once __DIR__ . '/helpers/MailHelper.php';

echo "<h2>1. Environment Variables Check</h2>";
echo "<ul>";
echo "<li>APP_ENV: " . (defined('APP_ENV') ? APP_ENV : 'NOT DEFINED') . "</li>";
echo "<li>APP_URL: " . (defined('APP_URL') ? APP_URL : 'NOT DEFINED') . "</li>";
echo "<li>MAIL_HOST: " . (defined('MAIL_HOST') ? MAIL_HOST : 'NOT DEFINED') . "</li>";
echo "<li>MAIL_PORT: " . (defined('MAIL_PORT') ? MAIL_PORT : 'NOT DEFINED') . "</li>";
echo "<li>MAIL_USERNAME: " . (defined('MAIL_USERNAME') ? MAIL_USERNAME : 'NOT DEFINED') . "</li>";
echo "<li>MAIL_PASSWORD: " . (defined('MAIL_PASSWORD') && MAIL_PASSWORD ? 'SET (length: ' . strlen(MAIL_PASSWORD) . ')' : 'NOT SET') . "</li>";
echo "<li>MAIL_ENCRYPTION: " . (defined('MAIL_ENCRYPTION') ? MAIL_ENCRYPTION : 'NOT DEFINED') . "</li>";
echo "<li>MAIL_FROM_ADDRESS: " . (defined('MAIL_FROM_ADDRESS') ? MAIL_FROM_ADDRESS : 'NOT DEFINED') . "</li>";
echo "</ul>";

echo "<h2>2. MailHelper Test</h2>";
try {
    $mailHelper = new MailHelper();
    $isConfigured = $mailHelper->isConfigured();
    echo "<p>MailHelper configured: " . ($isConfigured ? "✅ YES" : "❌ NO") . "</p>";
    
    if ($isConfigured) {
        $testResult = $mailHelper->testMail();
        echo "<p>SMTP Connection Test: " . ($testResult ? "✅ SUCCESS" : "❌ FAILED") . "</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red'>Error: " . $e->getMessage() . "</p>";
}

echo "<h2>3. Database Connection Test</h2>";
try {
    $db = Database::getInstance();
    $conn = $db->getConnection();
    echo "<p style='color: green'>✅ Database connected successfully</p>";
    
    // Count users
    $stmt = $conn->query("SELECT COUNT(*) as count FROM users");
    $result = $stmt->fetch();
    echo "<p>Total users: " . $result['count'] . "</p>";
    
} catch (Exception $e) {
    echo "<p style='color: red'>❌ Database error: " . $e->getMessage() . "</p>";
}

echo "<h2>4. User Model Test</h2>";
try {
    $userModel = new User();
    $testEmail = "nelson.hisaalu@gmail.com"; // Change this to a valid email in your DB
    $user = $userModel->getByEmail($testEmail);
    
    if ($user) {
        echo "<p style='color: green'>✅ User found: " . $user['email'] . " (" . $user['first_name'] . ")</p>";
        
        echo "<h2>5. Token Creation Test</h2>";
        $token = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', strtotime('+20 minutes'));
        
        $saved = $userModel->saveResetToken($user['id'], $token, $expires);
        echo "<p>Token saved: " . ($saved ? "✅ YES" : "❌ NO") . "</p>";
        
        if ($saved) {
            echo "<p>Token: " . substr($token, 0, 32) . "...</p>";
            echo "<p>Expires: " . $expires . "</p>";
            
            echo "<h2>6. Token Verification Test</h2>";
            $verifiedUser = $userModel->getUserByResetToken($token);
            echo "<p>Token verified: " . ($verifiedUser ? "✅ YES" : "❌ NO") . "</p>";
            
            if ($verifiedUser) {
                echo "<p>Verified user: " . $verifiedUser['email'] . "</p>";
                
                echo "<h2>7. Email Sending Test</h2>";
                $resetLink = "http://localhost:9000/reset-password?token=" . $token;
                echo "<p>Reset link: <a href='" . $resetLink . "' target='_blank'>" . $resetLink . "</a></p>";
                
                $sent = $mailHelper->sendResetEmail($testEmail, $user['first_name'], $resetLink);
                echo "<p>Email sent: " . ($sent ? "✅ YES" : "❌ NO") . "</p>";
                
                if (!$sent) {
                    echo "<p style='color: orange'>Check your error logs for SMTP details</p>";
                }
            }
        }
    } else {
        echo "<p style='color: red'>❌ User not found with email: " . $testEmail . "</p>";
        echo "<p>Please update the test email in the script to a valid user email in your database</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red'>Error: " . $e->getMessage() . "</p>";
}

echo "<h2>8. File Path Check</h2>";
$files = [
    'config/env.php',
    'helpers/MailHelper.php',
    'models/User.php',
    'controllers/AuthController.php'
];

foreach ($files as $file) {
    $path = __DIR__ . '/' . $file;
    echo "<p>" . $file . ": " . (file_exists($path) ? "✅ EXISTS" : "❌ MISSING") . "</p>";
}

echo "<h2>9. PHP Configuration</h2>";
echo "<ul>";
echo "<li>SMTP: " . ini_get('SMTP') . "</li>";
echo "<li>smtp_port: " . ini_get('smtp_port') . "</li>";
echo "<li>sendmail_from: " . ini_get('sendmail_from') . "</li>";
echo "</ul>";
?>