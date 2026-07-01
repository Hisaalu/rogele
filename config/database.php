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

            $host = explode(':', $host)[0];

            $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";
            
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::ATTR_TIMEOUT => 5,
                PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false,
            ];

            if (getenv('RENDER')) {
                $options[PDO::MYSQL_ATTR_SSL_CA] = '/etc/ssl/certs/ca-certificates.crt';
            } else {
                $options[PDO::MYSQL_ATTR_SSL_CA] = '';
            }

            $this->connection = new PDO($dsn, $user, $pass, $options);
            

        } catch (PDOException $e) {
            error_log("Database connection failed: " . $e->getMessage());
            error_log("Target DSN: mysql:host=$host;port=$port;dbname=$dbname");
            throw $e; 
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
    
    public function prepare($sql) {
        if (!$this->connection) {
            error_log("No active database connection during prepare().");
            return false;
        }
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