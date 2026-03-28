<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Network Connectivity Test</h2>";

$host = getenv('DB_HOST') ?: 'gateway01.eu-central-1.prod.aws.tidbcloud.com';
$port = getenv('DB_PORT') ?: '4000';
$user = getenv('DB_USER') ?: '2VcYykLWVZacLnw.root';
$pass = getenv('DB_PASSWORD') ?: '';

echo "<p><strong>Testing connection to:</strong> $host:$port</p>";

// Test 1: DNS Resolution
echo "<h3>1. DNS Lookup:</h3>";
$ip = gethostbyname($host);
echo "<p>Resolved to: $ip</p>";

// Test 2: Socket Connection
echo "<h3>2. Socket Test:</h3>";
$timeout = 10;
$fp = @fsockopen($host, $port, $errno, $errstr, $timeout);
if ($fp) {
    echo "<p style='color:green'>✓ Socket connection successful</p>";
    fclose($fp);
} else {
    echo "<p style='color:red'>✗ Socket failed: $errstr ($errno)</p>";
}

// Test 3: MySQLi Connection
echo "<h3>3. MySQLi Connection Test:</h3>";
$conn = new mysqli();
$conn->ssl_set(null, null, null, null, null);
$conn->real_connect($host, $user, $pass, null, $port, null, MYSQLI_CLIENT_SSL);

if ($conn->connect_errno) {
    echo "<p style='color:red'>✗ MySQLi connection failed: " . $conn->connect_error . " (Error code: " . $conn->connect_errno . ")</p>";
} else {
    echo "<p style='color:green'>✓ MySQLi connection successful!</p>";
    $result = $conn->query("SHOW STATUS LIKE 'Ssl_cipher'");
    if ($result && $row = $result->fetch_assoc()) {
        echo "<p>SSL Cipher: " . $row['Value'] . "</p>";
    }
    $conn->close();
}

// Test 4: Check if database exists
echo "<h3>4. Database Check:</h3>";
$conn = new mysqli();
$conn->ssl_set(null, null, null, null, null);
$conn->real_connect($host, $user, $pass, null, $port, null, MYSQLI_CLIENT_SSL);

if (!$conn->connect_errno) {
    $result = $conn->query("SHOW DATABASES");
    echo "<p>Available databases:</p><ul>";
    while ($row = $result->fetch_assoc()) {
        echo "<li>" . $row['Database'] . "</li>";
    }
    echo "</ul>";
    $conn->close();
} else {
    echo "<p style='color:red'>Cannot check databases - connection failed</p>";
}
?>