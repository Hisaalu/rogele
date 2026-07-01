<?php
// File: /config/database.php
require_once __DIR__ . '/config.php';

class Database {
    private static $instance = null;
    private $connection;
    
    private function __construct() {
        $host = '';
        $port = '';
        $dbname = '';

        try {
            $host   = defined('DB_HOST') ? DB_HOST : '';
            $port   = defined('DB_PORT') ? DB_PORT : '4000';
            $dbname = defined('DB_NAME') ? DB_NAME : '';
            $user   = defined('DB_USER') ? DB_USER : '';
            $pass   = defined('DB_PASS') ? DB_PASS : '';

            $host = explode(':', $host)[0];

            $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";
            
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
                PDO::ATTR_TIMEOUT            => 5,
            ];

            if (getenv('RENDER')) {
                $options[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = true;
                $options[PDO::MYSQL_ATTR_SSL_CA] = '/etc/ssl/certs/ca-certificates.crt';
            } else {
                if (file_exists('/etc/ssl/certs/ca-certificates.crt')) {
                    $options[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = true;
                    $options[PDO::MYSQL_ATTR_SSL_CA] = '/etc/ssl/certs/ca-certificates.crt';
                } elseif (file_exists('/etc/pki/tls/certs/ca-bundle.crt')) {
                    $options[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = true;
                    $options[PDO::MYSQL_ATTR_SSL_CA] = '/etc/pki/tls/certs/ca-bundle.crt';
                } else {
                    $options[PDO::MYSQL_ATTR_SSL_CAPATH] = '';
                }
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
    
    public function query($sql, $params = []) {
        $stmt = $this->connection->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }
    
    public function fetch($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        return $stmt->fetch();
    }
    
    public function fetchAll($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        return $stmt->fetchAll();
    }
    
    public function insert($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        return $this->connection->lastInsertId();
    }
    
    public function update($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        return $stmt->rowCount();
    }
    
    public function delete($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        return $stmt->rowCount();
    }
    
    public function beginTransaction() {
        return $this->connection->beginTransaction();
    }
    
    public function commit() {
        return $this->connection->commit();
    }
    
    public function rollback() {
        return $this->connection->rollback();
    }
}
?>