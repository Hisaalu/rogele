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
            
            // Use MySQLi with forced SSL
            $this->connection = mysqli_init();
            
            // Set SSL options - critical for TiDB Cloud
            mysqli_ssl_set(
                $this->connection,
                NULL,           // key file
                NULL,           // cert file
                NULL,           // CA certificate (NULL to use default)
                NULL,           // capath
                NULL            // cipher
            );
            
            // Connect with SSL
            if (!mysqli_real_connect(
                $this->connection,
                $host,
                $user,
                $pass,
                $dbname,
                $port,
                NULL,
                MYSQLI_CLIENT_SSL  // This forces SSL
            )) {
                throw new Exception(mysqli_connect_error());
            }
            
            // Set charset
            mysqli_set_charset($this->connection, 'utf8mb4');
            
            // Verify SSL
            $ssl_cipher = mysqli_fetch_assoc(mysqli_query($this->connection, "SHOW STATUS LIKE 'Ssl_cipher'"));
            if ($ssl_cipher && $ssl_cipher['Value']) {
                error_log("✓ SSL connection established with cipher: " . $ssl_cipher['Value']);
            }
            
            error_log("✓ Database connection successful!");
            
        } catch (Exception $e) {
            error_log("✗ Database connection failed: " . $e->getMessage());
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