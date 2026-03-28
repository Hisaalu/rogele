<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>TiDB Cloud Connection Debug</h2>";

$host = getenv('DB_HOST') ?: 'gateway01.eu-central-1.prod.aws.tidbcloud.com';
$port = getenv('DB_PORT') ?: '4000';
$user = getenv('DB_USER') ?: '2VcYykLWVZacLnw.root';
$pass = getenv('DB_PASSWORD') ?: '';
$dbname = getenv('DB_NAME') ?: 'ROGELEDB';

echo "<p>Host: $host:$port</p>";
echo "<p>User: $user</p>";
echo "<p>Database: $dbname</p>";

// Test DNS
echo "<h3>1. DNS Lookup:</h3>";
$ip = gethostbyname($host);
echo "<p>Resolved to: $ip</p>";

// Test socket connection
echo "<h3>2. Socket Test:</h3>";
$fp = @fsockopen($host, $port, $errno, $errstr, 10);
if ($fp) {
    echo "<p style='color:green'>✓ Socket connection successful</p>";
    fclose($fp);
} else {
    echo "<p style='color:red'>✗ Socket failed: $errstr ($errno)</p>";
}

// Test actual database connection
echo "<h3>3. Database Connection:</h3>";
try {
    $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false,
    ]);
    echo "<p style='color:green'>✓ Database connection successful!</p>";
    
    $stmt = $pdo->query("SELECT DATABASE(), VERSION()");
    $row = $stmt->fetch();
    echo "<p>Connected to: " . $row[0] . "</p>";
    echo "<p>Version: " . $row[1] . "</p>";
    
} catch (Exception $e) {
    echo "<p style='color:red'>✗ Connection failed: " . $e->getMessage() . "</p>";
}

// Show all environment variables (without passwords)
echo "<h3>Environment Variables:</h3>";
echo "<pre>";
foreach ($_ENV as $key => $value) {
    if (strpos($key, 'PASSWORD') === false && strpos($key, 'SECRET') === false) {
        echo htmlspecialchars("$key = $value\n");
    } else {
        echo htmlspecialchars("$key = [HIDDEN]\n");
    }
}
echo "</pre>";
?>