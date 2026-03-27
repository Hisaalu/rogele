<?php
// File: /test-token.php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/models/User.php';

$userModel = new User();

echo "<h1>Token Debug Test</h1>";

// Check if any tokens exist
$db = Database::getInstance();
$conn = $db->getConnection();

$stmt = $conn->prepare("SELECT * FROM password_resets ORDER BY created_at DESC LIMIT 5");
$stmt->execute();
$tokens = $stmt->fetchAll();

echo "<h2>Recent Tokens in Database:</h2>";
if (empty($tokens)) {
    echo "<p>No tokens found in password_resets table</p>";
} else {
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>ID</th><th>User ID</th><th>Token</th><th>Expires At</th><th>Used</th><th>Created At</th></tr>";
    foreach ($tokens as $token) {
        echo "<tr>";
        echo "<td>" . $token['id'] . "</td>";
        echo "<td>" . $token['user_id'] . "</td>";
        echo "<td>" . substr($token['token'], 0, 30) . "...</td>";
        echo "<td>" . $token['expires_at'] . "</td>";
        echo "<td>" . ($token['used'] ? 'Yes' : 'No') . "</td>";
        echo "<td>" . $token['created_at'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
}

// Test token verification with a specific token from URL
if (isset($_GET['token'])) {
    $testToken = $_GET['token'];
    echo "<h2>Testing Token: " . htmlspecialchars($testToken) . "</h2>";
    
    $user = $userModel->getUserByResetToken($testToken);
    
    if ($user) {
        echo "<p style='color: green;'>✅ Token is valid!</p>";
        echo "<pre>";
        print_r($user);
        echo "</pre>";
    } else {
        echo "<p style='color: red;'>❌ Token is invalid or expired</p>";
        
        // Check if token exists in database
        $stmt = $conn->prepare("SELECT * FROM password_resets WHERE token = ?");
        $stmt->execute([$testToken]);
        $tokenRecord = $stmt->fetch();
        
        if ($tokenRecord) {
            echo "<p>Token found in database but:</p>";
            echo "<ul>";
            if (strtotime($tokenRecord['expires_at']) < time()) {
                echo "<li>❌ Token is expired (expired at: " . $tokenRecord['expires_at'] . ")</li>";
            }
            if ($tokenRecord['used'] == 1) {
                echo "<li>❌ Token has already been used</li>";
            }
            echo "</ul>";
        } else {
            echo "<p>Token not found in database</p>";
        }
    }
}
?>