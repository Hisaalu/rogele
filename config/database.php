<?php
// File: /config/database.php
require_once __DIR__ . '/config.php';

class Database {
    private static $instance = null;
    private $connection;
    
    private function __construct() {
        try {
            $host = DB_HOST;
            $port = DB_PORT;
            $dbname = DB_NAME;
            $user = DB_USER;
            $pass = DB_PASS;
            
            error_log("Connecting to: $host:$port");
            
            // TiDB Cloud Serverless requires specific DSN format
            // Use the full hostname exactly as provided by TiDB Cloud
            $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";
            
            // SSL options - critical for TiDB Cloud
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_TIMEOUT => 30,  // Increase timeout
                PDO::ATTR_PERSISTENT => false,
                // SSL settings - force SSL but don't verify cert for now
                PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false,
                PDO::MYSQL_ATTR_SSL_KEY => '',
                PDO::MYSQL_ATTR_SSL_CERT => '',
                PDO::MYSQL_ATTR_SSL_CA => '',
            ];
            
            // Create connection with longer timeout
            $this->connection = new PDO($dsn, $user, $pass, $options);
            
            // Test the connection
            $stmt = $this->connection->query("SELECT 1 as test");
            $result = $stmt->fetch();
            
            if ($result && $result['test'] == 1) {
                error_log("✓ Database connection successful!");
            }
            
            // Check SSL status
            $stmt = $this->connection->query("SHOW STATUS LIKE 'Ssl_cipher'");
            $sslStatus = $stmt->fetch();
            if ($sslStatus && $sslStatus['Value']) {
                error_log("✓ SSL connection established with cipher: " . $sslStatus['Value']);
            }
            
        } catch (PDOException $e) {
            $errorCode = $e->getCode();
            $errorMsg = $e->getMessage();
            error_log("✗ Database connection failed: Code: $errorCode, Message: $errorMsg");
            error_log("Connection details - Host: $host:$port, DB: $dbname, User: $user");
            
            // Provide more helpful error messages
            if ($errorCode == 2002) {
                error_log("TIP: Connection timeout - Check if the host and port are correct and the IP is allowlisted");
            } elseif ($errorCode == 1045) {
                error_log("TIP: Access denied - Check username and password");
            } elseif ($errorCode == 1049) {
                error_log("TIP: Database not found - Check database name");
            }
            
            die("Database connection failed. Please try again later.");
        }
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function getConnection() {
        return $this->connection;
    }
    
    private function __clone() {}
    
    public function __wakeup() {}
}
?>