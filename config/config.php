<?php
// File: /config/config.php
$envPath = __DIR__ . '/../.env';
if (file_exists($envPath)) {
    $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if (empty($line) || strpos($line, '#') === 0) continue;
        
        if (strpos($line, '=') !== false) {
            list($name, $value) = explode('=', $line, 2);
            $name = trim($name);
            $value = trim($value, " \t\n\r\0\x0B\"'");
            
            if (getenv($name) === false) {
                putenv("$name=$value");
                $_ENV[$name] = $value;
                $_SERVER[$name] = $value;
            }
        }
    }
}

error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
define('ROOT_PATH', dirname(__DIR__));
ini_set('error_log', ROOT_PATH . '/logs/error.log');

date_default_timezone_set('Africa/Kampala');

define('DB_HOST', getenv('DB_HOST') ?: '127.0.0.1');
define('DB_PORT', getenv('DB_PORT') ?: '4000');
define('DB_NAME', getenv('DB_NAME') ?: '');
define('DB_USER', getenv('DB_USER') ?: '');
define('DB_PASS', getenv('DB_PASS') ?: '');

$appUrl = getenv('APP_URL') ?: (getenv('RENDER') ? 'https://rogele.raysofgrace.ac.ug' : 'http://localhost/rogele-prod');
define('BASE_URL', rtrim($appUrl, '/'));
define('SITE_NAME', getenv('APP_NAME') ?: 'ROGELE');

define('MAX_FILE_SIZE', 10485760);
define('ALLOWED_EXTENSIONS', ['pdf', 'doc', 'docx', 'ppt', 'pptx', 'xls', 'xlsx', 'mp4', 'jpg', 'jpeg', 'png']);
define('UPLOAD_PATH', ROOT_PATH . '/public/uploads/');

define('FREE_TRIAL_DAYS', 30);
define('SUBSCRIPTION_PLANS', [
    'monthly' => 15000,
    'termly'  => 40000,
    'yearly'  => 120000
]);

if (getenv('RENDER')) {
    $sessionSavePath = '/tmp/php_sessions';
    if (!is_dir($sessionSavePath)) {
        mkdir($sessionSavePath, 0777, true);
    }
    ini_set('session.save_path', $sessionSavePath);
}

$host = $_SERVER['HTTP_HOST'] ?? '';
$isProd = (strpos($host, 'raysofgrace.ac.ug') !== false);
$cookieDomain = $isProd ? '.raysofgrace.ac.ug' : ''; 

session_set_cookie_params([
    'path' => '/',
    'domain' => $cookieDomain,
    'secure' => $isProd,
    'httponly' => true,
    'samesite' => 'Lax'
]);

ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', $isProd ? 1 : 0); 
ini_set('session.cookie_samesite', 'Lax');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>