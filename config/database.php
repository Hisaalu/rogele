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
            
            // Create MySQLi connection
            $this->connection = new mysqli();
            
            // Set SSL options
            $this->connection->ssl_set(null, null, null, null, null);
            
            // Connect with SSL
            $this->connection->real_connect(
                $host,
                $user,
                $pass,
                $dbname,
                $port,
                null,
                MYSQLI_CLIENT_SSL
            );
            
            if ($this->connection->connect_errno) {
                throw new Exception($this->connection->connect_error);
            }
            
            // Set charset
            $this->connection->set_charset('utf8mb4');
            
            // Verify SSL
            $result = $this->connection->query("SHOW STATUS LIKE 'Ssl_cipher'");
            if ($result && $row = $result->fetch_assoc()) {
                error_log("✓ SSL connection established with cipher: " . $row['Value']);
                $result->free();
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
    
    // PDO-compatible wrapper methods
    public function prepare($sql) {
        $stmt = $this->connection->prepare($sql);
        if (!$stmt) {
            return false;
        }
        return new MySQLiStatementWrapper($stmt);
    }
    
    public function query($sql) {
        $result = $this->connection->query($sql);
        if ($result === false) {
            return false;
        }
        return new MySQLiResultWrapper($result);
    }
    
    public function lastInsertId() {
        return $this->connection->insert_id;
    }
    
    private function __clone() {}
    public function __wakeup() {}
}

/**
 * PDO-compatible wrapper for MySQLi statements
 */
class MySQLiStatementWrapper {
    private $stmt;
    private $params = [];
    private $paramTypes = '';
    
    public function __construct($stmt) {
        $this->stmt = $stmt;
    }
    
    public function bindValue($param, $value, $type = null) {
        $param = ltrim($param, ':');
        $this->params[$param] = $value;
        $this->paramTypes .= 's';
        return true;
    }
    
    public function execute($params = null) {
        if ($params !== null) {
            $this->params = [];
            $this->paramTypes = '';
            foreach ($params as $key => $value) {
                $this->params[$key] = $value;
                $this->paramTypes .= 's';
            }
        }
        
        if (!empty($this->params)) {
            $bindParams = [];
            $bindParams[] = &$this->paramTypes;
            foreach ($this->params as &$value) {
                $bindParams[] = &$value;
            }
            call_user_func_array([$this->stmt, 'bind_param'], $bindParams);
        }
        
        return $this->stmt->execute();
    }
    
    public function fetch($mode = null) {
        $result = $this->stmt->get_result();
        if (!$result) {
            return false;
        }
        return $result->fetch_assoc();
    }
    
    public function fetchAll($mode = null) {
        $result = $this->stmt->get_result();
        if (!$result) {
            return [];
        }
        return $result->fetch_all(MYSQLI_ASSOC);
    }
    
    public function rowCount() {
        return $this->stmt->affected_rows;
    }
    
    public function close() {
        $this->stmt->close();
    }
}

/**
 * PDO-compatible wrapper for MySQLi results
 */
class MySQLiResultWrapper {
    private $result;
    
    public function __construct($result) {
        $this->result = $result;
    }
    
    public function fetch($mode = null) {
        return $this->result->fetch_assoc();
    }
    
    public function fetchAll($mode = null) {
        return $this->result->fetch_all(MYSQLI_ASSOC);
    }
    
    public function rowCount() {
        return $this->result->num_rows;
    }
}
?>