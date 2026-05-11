<?php
// File: /config/config.php
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
    define('DB_HOST', getenv('DB_HOST'));
    define('DB_PORT', getenv('DB_PORT') ?: '4000');
    define('DB_NAME', getenv('DB_NAME'));
    define('DB_USER', getenv('DB_USER'));
    define('DB_PASS', getenv('DB_PASS'));
} else {
    define('DB_HOST', $env['DB_HOST'] ?? 'gateway01.eu-central-1.prod.aws.tidbcloud.com');
    define('DB_PORT', $env['DB_PORT'] ?? '4000'); 
    define('DB_NAME', $env['DB_NAME'] ?? 'ROGELEDB');
    define('DB_USER', $env['DB_USER'] ?? '2VcYykLWVZacLnw.root');
    define('DB_PASS', $env['DB_PASS'] ?? ''); 
}

// Application Configuration
if (getenv('RENDER')) {
    define('BASE_URL', getenv('APP_URL') ?: 'https://rogele.raysofgrace.ac.ug');
    define('SITE_NAME', getenv('APP_NAME') ?: 'ROGELE');
} else {
    define('BASE_URL', $env['APP_URL'] ?? 'http://localhost/rogele-prod');
    define('SITE_NAME', $env['APP_NAME'] ?? 'ROGELE');
}

// Define ROOT_PATH once for both environments
define('ROOT_PATH', dirname(__DIR__));
define('MAX_FILE_SIZE', 10485760);
define('ALLOWED_EXTENSIONS', ['pdf', 'doc', 'docx', 'ppt', 'pptx', 'xls', 'xlsx', 'mp4', 'jpg', 'jpeg', 'png']);
define('UPLOAD_PATH', ROOT_PATH . '/public/uploads/');

// Subscription Plans
define('FREE_TRIAL_DAYS', 30);
define('SUBSCRIPTION_PLANS', [
    'monthly' => 15000,
    'termly' => 40000,
    'yearly' => 120000
]);

// Error Reporting - Production settings
error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', ROOT_PATH . '/logs/error.log');

// Timezone
date_default_timezone_set('Africa/Kampala');

// Create session directory if it doesn't exist (for Render)
$sessionSavePath = '/tmp/php_sessions';
if (getenv('RENDER')) {
    if (!is_dir($sessionSavePath)) {
        mkdir($sessionSavePath, 0777, true);
    }
    ini_set('session.save_path', $sessionSavePath);
}

// Set session cookie parameters BEFORE session start
$cookieDomain = '.raysofgrace.ac.ug';

session_set_cookie_params([
    'lifetime' => 7200,
    'path' => '/',
    'domain' => $cookieDomain,
    'secure' => true,
    'httponly' => true,
    'samesite' => 'Lax'
]);

// Set session ini settings
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 1);
ini_set('session.cookie_samesite', 'Lax');
ini_set('session.gc_maxlifetime', 7200);

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>