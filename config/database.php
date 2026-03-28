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
            
            // Initialize MySQLi
            $this->connection = mysqli_init();
            
            if (!$this->connection) {
                throw new Exception("mysqli_init failed");
            }
            
            // Set SSL options - this forces SSL connection
            mysqli_ssl_set(
                $this->connection,
                null,   // key file
                null,   // cert file
                null,   // ca file (null uses default)
                null,   // capath
                null    // cipher
            );
            
            // Connect with SSL flag
            $connected = mysqli_real_connect(
                $this->connection,
                $host,
                $user,
                $pass,
                $dbname,
                $port,
                null,
                MYSQLI_CLIENT_SSL  // This forces SSL
            );
            
            if (!$connected) {
                throw new Exception(mysqli_connect_error());
            }
            
            // Set charset
            mysqli_set_charset($this->connection, 'utf8mb4');
            
            // Verify SSL connection
            $result = mysqli_query($this->connection, "SHOW STATUS LIKE 'Ssl_cipher'");
            if ($result) {
                $row = mysqli_fetch_assoc($result);
                if ($row && $row['Value']) {
                    error_log("✓ SSL connection established with cipher: " . $row['Value']);
                } else {
                    error_log("⚠ SSL connection status unknown");
                }
                mysqli_free_result($result);
            }
            
            error_log("✓ Database connection successful!");
            
        } catch (Exception $e) {
            error_log("✗ Database connection failed: " . $e->getMessage());
            error_log("Connection details - Host: $host:$port, DB: $dbname, User: $user");
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
    
    // For compatibility with PDO-style code
    public function prepare($sql) {
        return $this->connection->prepare($sql);
    }
    
    public function query($sql) {
        return $this->connection->query($sql);
    }
    
    public function execute($stmt, $params = []) {
        if (!$stmt) return false;
        
        if (empty($params)) {
            return $stmt->execute();
        }
        
        // Build types string (all strings by default)
        $types = str_repeat('s', count($params));
        
        // Prepare parameters for bind_param (by reference)
        $bindParams = array_merge([$types], array_values($params));
        $refs = [];
        foreach ($bindParams as $i => $param) {
            $refs[$i] = &$bindParams[$i];
        }
        
        call_user_func_array([$stmt, 'bind_param'], $refs);
        return $stmt->execute();
    }
    
    public function fetchAll($stmt) {
        $result = $stmt->get_result();
        if ($result) {
            return $result->fetch_all(MYSQLI_ASSOC);
        }
        return [];
    }
    
    public function fetch($stmt) {
        $result = $stmt->get_result();
        if ($result) {
            return $result->fetch_assoc();
        }
        return null;
    }
    
    public function lastInsertId() {
        return $this->connection->insert_id;
    }
    
    public function affectedRows() {
        return $this->connection->affected_rows;
    }
    
    private function __clone() {}
    
    public function __wakeup() {}
}