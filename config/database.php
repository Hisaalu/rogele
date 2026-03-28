<?php
// File: /config/database.php
require_once __DIR__ . '/config.php';

class Database {
    private static $instance = null;
    private $connection;
    
    private function __construct() {
        try {
            // Define the port (TiDB Cloud Serverless uses 4000)
            $port = getenv('DB_PORT') ?: '4000';
            
            // Build DSN with port
            $dsn = "mysql:host=" . DB_HOST . ";port=" . $port . ";dbname=" . DB_NAME . ";charset=utf8mb4";
            
            error_log("Connecting to: " . DB_HOST . ":" . $port);
            
            // Prepare SSL options for TiDB Cloud
            $sslOptions = [];
            
            // Check if we're connecting to TiDB Cloud
            if (strpos(DB_HOST, 'tidbcloud.com') !== false || getenv('TIDB_CLOUD') === 'true') {
                $sslOptions = [
                    PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => defined('DB_SSL_VERIFY') ? (DB_SSL_VERIFY === 'true') : false,
                    PDO::MYSQL_ATTR_SSL_CA => defined('DB_SSL_CA') ? DB_SSL_CA : '/etc/ssl/certs/tidb-ca.pem',
                ];
                
                // Required for TiDB Serverless
                $sslOptions[PDO::MYSQL_ATTR_SSL_KEY] = '';
                $sslOptions[PDO::MYSQL_ATTR_SSL_CERT] = '';
                
                error_log("TiDB Cloud detected - enabling SSL connection");
            }
            
            // Merge default options with SSL options
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            ] + $sslOptions;
            
            $this->connection = new PDO(
                $dsn,
                DB_USER,
                DB_PASS,
                $options
            );
            
            // Test the connection with a simple query to verify SSL
            $stmt = $this->connection->query("SHOW STATUS LIKE 'Ssl_cipher'");
            $sslStatus = $stmt->fetch();
            if ($sslStatus && $sslStatus['Value']) {
                error_log("SSL connection established with cipher: " . $sslStatus['Value']);
            }
            
            error_log("Database connection successful!");
            
        } catch (PDOException $e) {
            // Log error and show user-friendly message
            error_log("Database connection failed: " . $e->getMessage());
            error_log("Connection details - Host: " . DB_HOST . ", DB: " . DB_NAME . ", User: " . DB_USER);
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