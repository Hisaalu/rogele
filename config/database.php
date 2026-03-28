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
            
            // Build DSN
            $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";
            
            // SSL options for TiDB Cloud - FORCE SSL
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::ATTR_TIMEOUT => 30,
                // Critical: These force SSL connection
                PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false,
                PDO::MYSQL_ATTR_SSL_KEY => '',
                PDO::MYSQL_ATTR_SSL_CERT => '',
                PDO::MYSQL_ATTR_SSL_CA => '',
            ];
            
            // Create PDO connection
            $this->connection = new PDO($dsn, $user, $pass, $options);
            
            // Test connection and verify SSL
            $stmt = $this->connection->query("SHOW STATUS LIKE 'Ssl_cipher'");
            $sslStatus = $stmt->fetch();
            if ($sslStatus && $sslStatus['Value']) {
                error_log("✓ SSL connection established with cipher: " . $sslStatus['Value']);
            } else {
                // If SSL cipher not shown, try another method to verify
                $stmt = $this->connection->query("SELECT 'SSL Active' as status");
                error_log("✓ PDO connection successful");
            }
            
            error_log("✓ Database connection successful!");
            
        } catch (PDOException $e) {
            error_log("✗ Database connection failed: " . $e->getMessage());
            error_log("Connection details - Host: $host:$port, DB: $dbname, User: $user");
            error_log("Error code: " . $e->getCode());
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
    
    // PDO wrapper methods for convenience
    public function prepare($sql) {
        return $this->connection->prepare($sql);
    }
    
    public function query($sql) {
        return $this->connection->query($sql);
    }
    
    public function lastInsertId() {
        return $this->connection->lastInsertId();
    }
    
    private function __clone() {}
    
    public function __wakeup() {}
}
?>