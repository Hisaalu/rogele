<?php
// File: /config/config.php
/**
 * Reads the .env file and puts variables into getenv()
 */
$env = [];
$envPath = __DIR__ . '/../.env';

if (file_exists($envPath)) {
    $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        if (strpos($line, '=') !== false) {
            list($name, $value) = explode('=', $line, 2);
            $env[trim($name)] = trim($value);
        }
    }
}

// Database Configuration
if (getenv('RENDER')) {
    // PRODUCTION (Render)
    define('DB_HOST', getenv('DB_HOST'));
    define('DB_PORT', getenv('DB_PORT') ?: '4000');
    define('DB_NAME', getenv('DB_NAME'));
    define('DB_USER', getenv('DB_USER'));
    define('DB_PASS', getenv('DB_PASS'));
} elseif (getenv('DOCKER_ENV')) {
    // DOCKER ENVIRONMENT - Read from .env file
    define('DB_HOST', $env['DB_HOST'] ?? 'mariadb');
    define('DB_PORT', '3306');
    define('DB_NAME', $env['DB_NAME'] ?? 'rogele_db');
    define('DB_USER', $env['DB_USER'] ?? 'nelson');
    define('DB_PASS', $env['DB_PASSWORD'] ?? '');
} else {
    // LOCAL SETTINGS (XAMPP)
    define('DB_HOST', 'localhost');
    define('DB_PORT', '3306');
    define('DB_NAME', 'rays_of_grace_elearning');
    define('DB_USER', 'root');
    define('DB_PASS', '');
}

// BASE_URL Configuration - Auto-detect based on how the site is accessed
if (getenv('RENDER')) {
    define('BASE_URL', getenv('APP_URL') ?: 'https://rogele.raysofgrace.ac.ug');
    define('SITE_NAME', getenv('APP_NAME') ?: 'ROGELE');
} elseif (getenv('DOCKER_ENV')) {
    $is_https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || 
                (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');
    $host = $_SERVER['HTTP_HOST'] ?? '';
    
    if (strpos($host, 'raysofgrace.ac.ug') !== false || strpos($host, 'rogele') !== false) {
        $protocol = $is_https ? 'https' : 'https';
        define('BASE_URL', $protocol . '://' . $host);
    } else {
        define('BASE_URL', 'http://localhost:8080');
    }
    define('SITE_NAME', 'ROGELE');
} else {
    define('BASE_URL', $env['APP_URL'] ?? 'http://localhost/rogele-prod');
    define('SITE_NAME', $env['APP_NAME'] ?? 'ROGELE');
}

define('ROOT_PATH', dirname(__DIR__));

define('MAX_FILE_SIZE', 10485760);
define('ALLOWED_EXTENSIONS', ['pdf', 'doc', 'docx', 'ppt', 'pptx', 'xls', 'xlsx', 'mp4', 'jpg', 'jpeg', 'png']);
define('UPLOAD_PATH', ROOT_PATH . '/public/uploads/');

define('FREE_TRIAL_DAYS', 30);
define('SUBSCRIPTION_PLANS', [
    'monthly' => 15000,
    'termly' => 40000,
    'yearly' => 120000
]);

error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', ROOT_PATH . '/logs/error.log');

date_default_timezone_set('Africa/Kampala');

$is_secure = (getenv('DOCKER_ENV') && strpos(BASE_URL, 'https') === 0) || 
             (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');

ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', $is_secure ? 1 : 0);
ini_set('session.cookie_samesite', 'Strict');
ini_set('session.gc_maxlifetime', 1800);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (getenv('RENDER')) {
    error_log("Application running on Render in production mode");
} elseif (getenv('DOCKER_ENV')) {
    error_log("Application running in Docker container - BASE_URL: " . BASE_URL);
    error_log("DB_HOST: " . DB_HOST);
    error_log("DB_NAME: " . DB_NAME);
    error_log("DB_USER: " . DB_USER);
}
?>