<?php
// File: ipn_handler.php - Place this in your root directory (same as index.php)

// Disable error display for production
error_reporting(0);
ini_set('display_errors', 0);

// Log function
function ipn_log($message) {
    $logDir = __DIR__ . '/logs';
    if (!file_exists($logDir)) {
        mkdir($logDir, 0777, true);
    }
    $logFile = $logDir . '/ipn_handler.log';
    file_put_contents($logFile, date('Y-m-d H:i:s') . ' - ' . $message . "\n", FILE_APPEND);
}

ipn_log("========== IPN REQUEST RECEIVED ==========");
ipn_log("GET: " . json_encode($_GET));
ipn_log("POST: " . json_encode($_POST));

// Load environment variables from .env file if it exists
if (file_exists(__DIR__ . '/.env')) {
    $lines = file(__DIR__ . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        $parts = explode('=', $line, 2);
        if (count($parts) == 2) {
            $key = trim($parts[0]);
            $value = trim($parts[1], '"\'');
            putenv("$key=$value");
            $_ENV[$key] = $value;
        }
    }
    ipn_log(".env file loaded");
}

// Try to get database configuration from multiple sources
$dbHost = getenv('DB_HOST') ?: getenv('RENDER_DB_HOST') ?: 'localhost';
$dbName = getenv('DB_NAME') ?: getenv('RENDER_DB_NAME') ?: 'rogele_db';
$dbUser = getenv('DB_USER') ?: getenv('RENDER_DB_USER') ?: 'root';
$dbPass = getenv('DB_PASS') ?: getenv('RENDER_DB_PASSWORD') ?: '';

ipn_log("DB Config - Host: $dbHost, Database: $dbName, User: $dbUser");

// If on Render, try to get from internal database URL
if (getenv('RENDER')) {
    $databaseUrl = getenv('DATABASE_URL');
    if ($databaseUrl) {
        ipn_log("DATABASE_URL found, parsing...");
        // Parse DATABASE_URL (PostgreSQL format: postgresql://user:pass@host:port/db)
        if (preg_match('/postgresql:\/\/([^:]+):([^@]+)@([^:]+):(\d+)\/(.+)/', $databaseUrl, $matches)) {
            $dbUser = $matches[1];
            $dbPass = $matches[2];
            $dbHost = $matches[3];
            $dbName = $matches[5];
            ipn_log("Parsed PostgreSQL connection details");
        }
        // For MySQL: mysql://user:pass@host:port/db
        elseif (preg_match('/mysql:\/\/([^:]+):([^@]+)@([^:]+):(\d+)\/(.+)/', $databaseUrl, $matches)) {
            $dbUser = $matches[1];
            $dbPass = $matches[2];
            $dbHost = $matches[3];
            $dbName = $matches[5];
            ipn_log("Parsed MySQL connection details");
        }
    }
}

ipn_log("Final DB Config - Host: $dbHost, Database: $dbName, User: $dbUser");

// Try to connect to database
try {
    // Try MySQL first
    $pdo = new PDO("mysql:host=$dbHost;dbname=$dbName;charset=utf8mb4", $dbUser, $dbPass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    ipn_log("MySQL database connected successfully");
} catch (PDOException $e) {
    ipn_log("MySQL connection failed: " . $e->getMessage());
    
    // Try PostgreSQL if MySQL fails
    try {
        $pdo = new PDO("pgsql:host=$dbHost;dbname=$dbName", $dbUser, $dbPass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        ipn_log("PostgreSQL database connected successfully");
    } catch (PDOException $e2) {
        ipn_log("All database connections failed: " . $e2->getMessage());
        http_response_code(200);
        echo "Database error: " . $e2->getMessage();
        exit;
    }
}

// Get parameters from PesaPal
$orderTrackingId = $_GET['OrderTrackingId'] ?? $_GET['order_tracking_id'] ?? null;
$orderMerchantReference = $_GET['OrderMerchantReference'] ?? $_GET['merchant_reference'] ?? null;
$orderNotificationType = $_GET['OrderNotificationType'] ?? null;

ipn_log("OrderTrackingId: " . $orderTrackingId);
ipn_log("OrderMerchantReference: " . $orderMerchantReference);
ipn_log("OrderNotificationType: " . $orderNotificationType);

if (!$orderTrackingId || !$orderMerchantReference) {
    ipn_log("ERROR: Missing required parameters");
    http_response_code(200);
    echo "Missing parameters";
    exit;
}

try {
    // First, get the payment record from your database
    $stmt = $pdo->prepare("SELECT * FROM payments WHERE transaction_id = ?");
    $stmt->execute([$orderMerchantReference]);
    $payment = $stmt->fetch(PDO::FETCH_ASSOC);
    
    ipn_log("Payment record found: " . json_encode($payment));
    
    if ($payment) {
        if ($payment['status'] !== 'completed') {
            // Update payment status to completed
            $updateStmt = $pdo->prepare("UPDATE payments SET status = 'completed', payment_date = NOW() WHERE transaction_id = ?");
            $updateStmt->execute([$orderMerchantReference]);
            ipn_log("Payment status updated to completed");
            
            // Determine plan type and days
            $planType = $payment['plan_type'] ?? 'monthly';
            $planDays = $planType === 'monthly' ? 30 : ($planType === 'termly' ? 90 : 365);
            $endDate = date('Y-m-d H:i:s', strtotime("+{$planDays} days"));
            
            // Check for existing active subscription
            $subStmt = $pdo->prepare("SELECT id FROM subscriptions WHERE user_id = ? AND status = 'active' ORDER BY id DESC LIMIT 1");
            $subStmt->execute([$payment['user_id']]);
            $existingSub = $subStmt->fetch(PDO::FETCH_ASSOC);
            
            if ($existingSub) {
                // Update existing subscription
                $updateSubStmt = $pdo->prepare("UPDATE subscriptions SET plan_type = ?, amount = ?, end_date = ?, status = 'active', payment_method = 'pesapal', transaction_id = ? WHERE id = ?");
                $updateSubStmt->execute([$planType, $payment['amount'], $endDate, $orderMerchantReference, $existingSub['id']]);
                ipn_log("Subscription updated: ID " . $existingSub['id']);
            } else {
                // Create new subscription
                $insertSubStmt = $pdo->prepare("INSERT INTO subscriptions (user_id, plan_type, amount, start_date, end_date, status, payment_method, transaction_id) VALUES (?, ?, ?, NOW(), ?, 'active', 'pesapal', ?)");
                $insertSubStmt->execute([$payment['user_id'], $planType, $payment['amount'], $endDate, $orderMerchantReference]);
                $newSubId = $pdo->lastInsertId();
                ipn_log("New subscription created: ID " . $newSubId);
            }
            
            ipn_log("SUCCESS: Payment and subscription processed");
            http_response_code(200);
            echo "IPN processed successfully";
        } else {
            ipn_log("Payment already completed previously");
            http_response_code(200);
            echo "Payment already processed";
        }
    } else {
        ipn_log("ERROR: No payment record found for reference: " . $orderMerchantReference);
        http_response_code(200);
        echo "Payment record not found";
    }
} catch (Exception $e) {
    ipn_log("ERROR processing IPN: " . $e->getMessage());
    ipn_log("Stack trace: " . $e->getTraceAsString());
    http_response_code(200);
    echo "Error: " . $e->getMessage();
}

exit;
?>