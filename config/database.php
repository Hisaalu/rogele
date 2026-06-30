<?php
// File: /config/database.php
require_once __DIR__ . '/config.php';

class Database {
    private static $instance = null;
    private $connection;
    
    private function __construct() {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8mb4";
            $this->connection = new PDO($dsn, DB_USER, DB_PASS);
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->connection->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Database connection failed: " . $e->getMessage());
            error_log("Target DSN: mysql:host=$host;port=$port;dbname=$dbname");
            
            if (ob_get_length()) ob_clean();

            $errorMessage = $e->getMessage();
            $isQuotaExhausted = (strpos($errorMessage, 'usage quota being exhausted') !== false || strpos($errorMessage, '1105') !== false);

            $title = $isQuotaExhausted ? "System Overloaded" : "Temporary Maintenance";
            $message = $isQuotaExhausted 
                ? "Our systems are experiencing an unusually high amount of traffic right now and have temporarily restricted access. Please try again in a few minutes."
                : "We are currently experiencing technical difficulties connecting to our services. Our team has been notified. Please try again shortly.";

            ?>
            <div id="db-error-overlay">
                <div class="db-error-container">
                    <div class="db-logo-text">Rays of Grace</div>
                    <div class="db-logo-sub">E-Learning Environment</div>
                    <h1><?php echo $title; ?></h1>
                    <p><?php echo $message; ?></p>
                    <a href="javascript:window.location.reload();" class="db-btn">Refresh Page</a>
                </div>
            </div>

            <style>
                #db-error-overlay {
                    position: fixed !important;
                    top: 0 !important;
                    left: 0 !important;
                    width: 100vw !important;
                    height: 100vh !important;
                    background-color: rgba(249, 249, 251, 0.98) !important;
                    z-index: 999999 !important;
                    display: flex !important;
                    justify-content: center !important;
                    align-items: center !important;
                    box-sizing: border-box !important;
                    padding: 20px !important;
                }
                .db-error-container {
                    text-align: center !important;
                    max-width: 500px !important;
                    width: 100% !important;
                    background: white !important;
                    padding: 40px !important;
                    border-radius: 12px !important;
                    box-shadow: 0 10px 30px rgba(0,0,0,0.1) !important;
                    border-top: 5px solid #f06724 !important;
                    font-family: "trajan-pro", serif;
                }
                .db-logo-text { font-size: 24px !important; font-weight: bold !important; color: #7f2677 !important; margin-bottom: 5px !important; text-transform: uppercase !important; letter-spacing: 1px !important; }
                .db-logo-sub { font-size: 14px !important; color: #f06724 !important; font-weight: 600 !important; margin-bottom: 30px !important; letter-spacing: 0.5px !important; }
                .db-error-container h1 { font-size: 22px !important; color: #000 !important; margin: 0 0 15px 0 !important; }
                .db-error-container p { font-size: 15px !important; color: #555 !important; line-height: 1.6 !important; margin: 0 0 30px 0 !important; }
                .db-btn { display: inline-block !important; background-color: #7f2677 !important; color: white !important; padding: 12px 28px !important; text-decoration: none !important; font-weight: bold !important; border-radius: 25px !important; transition: background 0.2s !important; font-size: 14px !important; }
                .db-btn:hover { background-color: #7f2677 !important; }
            </style>
            <?php
            exit;
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