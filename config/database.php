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
            
            // For TiDB Cloud Serverless, we need to force SSL
            // Create DSN with SSL parameters in the connection string
            $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";
            
            // SSL options - FORCE SSL connection
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                // Critical SSL settings
                PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false,  // Set to false for testing
                PDO::MYSQL_ATTR_SSL_KEY => '',
                PDO::MYSQL_ATTR_SSL_CERT => '',
                PDO::MYSQL_ATTR_SSL_CA => '',
            ];
            
            // Try to add CA certificate if available
            if (defined('DB_SSL_CA') && file_exists(DB_SSL_CA)) {
                $options[PDO::MYSQL_ATTR_SSL_CA] = DB_SSL_CA;
            }
            
            $this->connection = new PDO($dsn, $user, $pass, $options);
            
            // Verify SSL is actually being used
            $stmt = $this->connection->query("SHOW STATUS LIKE 'Ssl_cipher'");
            $sslStatus = $stmt->fetch();
            if ($sslStatus && $sslStatus['Value']) {
                error_log("✓ SSL connection established with cipher: " . $sslStatus['Value']);
            } else {
                error_log("⚠ SSL connection may not be active");
            }
            
            error_log("✓ Database connection successful!");
            
        } catch (PDOException $e) {
            error_log("✗ Database connection failed: " . $e->getMessage());
            error_log("Connection details - Host: $host, DB: $dbname, User: $user");
            die("Database connection failed. Please check your configuration.");
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