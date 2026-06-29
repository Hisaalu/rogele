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