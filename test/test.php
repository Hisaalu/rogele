<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Network Connectivity Test</h2>";

$host = getenv('DB_HOST') ?: 'gateway01.eu-central-1.prod.aws.tidbcloud.com';
$port = getenv('DB_PORT') ?: '4000';
$user = getenv('DB_USER') ?: '2VcYykLWVZacLnw.root';
$pass = getenv('DB_PASS') ?: '';

echo "<p><strong>Testing connection to:</strong> $host:$port</p>";
echo "<p><strong>User:</strong> $user</p>";
echo "<p><strong>Password:</strong> " . (!empty($pass) ? "✓ SET (length: " . strlen($pass) . ")" : "✗ NOT SET") . "</p>";

// Also check $_ENV
$pass_env = $_ENV['DB_PASS'] ?? '';
echo "<p><strong>Password from \$_ENV:</strong> " . (!empty($pass_env) ? "✓ SET" : "✗ NOT SET") . "</p>";

// List all environment variables (without sensitive values)
echo "<h3>Environment Variables:</h3><ul>";
foreach ($_SERVER as $key => $value) {
    if (strpos($key, 'DB_') === 0) {
        if (strpos($key, 'PASSWORD') === false) {
            echo "<li>$key = $value</li>";
        } else {
            echo "<li>$key = [SET]</li>";
        }
    }
}
echo "</ul>";

// Test DNS
echo "<h3>1. DNS Lookup:</h3>";
$ip = gethostbyname($host);
echo "<p>Resolved to: $ip</p>";

// Test Socket
echo "<h3>2. Socket Test:</h3>";
$timeout = 10;
$fp = @fsockopen($host, $port, $errno, $errstr, $timeout);
if ($fp) {
    echo "<p style='color:green'>✓ Socket connection successful</p>";
    fclose($fp);
} else {
    echo "<p style='color:red'>✗ Socket failed: $errstr ($errno)</p>";
}

// Test MySQLi Connection WITH PASSWORD
echo "<h3>3. MySQLi Connection Test:</h3>";
$conn = new mysqli();
$conn->ssl_set(null, null, null, null, null);

// Make sure we're using the password
if (empty($pass)) {
    echo "<p style='color:red'>✗ Password is empty! Please set DB_PASS in Render environment variables.</p>";
} else {
    @$conn->real_connect($host, $user, $pass, null, $port, null, MYSQLI_CLIENT_SSL);
    
    if ($conn->connect_errno) {
        echo "<p style='color:red'>✗ MySQLi connection failed: " . $conn->connect_error . " (Error code: " . $conn->connect_errno . ")</p>";
        if ($conn->connect_errno == 1045) {
            echo "<p>This means the username or password is incorrect.</p>";
        }
    } else {
        echo "<p style='color:green'>✓ MySQLi connection successful!</p>";
        $result = $conn->query("SHOW STATUS LIKE 'Ssl_cipher'");
        if ($result && $row = $result->fetch_assoc()) {
            echo "<p>SSL Cipher: " . $row['Value'] . "</p>";
        }
        $conn->close();
    }
}
?>