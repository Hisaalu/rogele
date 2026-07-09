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

define('DB_HOST', getenv('DB_HOST') ?: ($env['DB_HOST'] ?? ''));
define('DB_PORT', getenv('DB_PORT') ?: ($env['DB_PORT'] ?? ''));
define('DB_NAME', getenv('DB_NAME') ?: ($env['DB_NAME'] ?? ''));
define('DB_USER', getenv('DB_USER') ?: ($env['DB_USER'] ?? ''));
define('DB_PASS', getenv('DB_PASSWORD') ?: ($env['DB_PASSWORD'] ?? ''));

define('BASE_URL', getenv('APP_URL') ?: 'https://elearn.raysofgrace.ac.ug');
define('SITE_NAME', getenv('APP_NAME') ?: 'ROGELE');

define('ROOT_PATH', dirname(__DIR__));
define('MAX_FILE_SIZE', 104857300);
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

$cookieDomain = '.raysofgrace.ac.ug';

session_set_cookie_params([
    'path' => '/',
    'domain' => $cookieDomain,
    'secure' => true,
    'httponly' => true,
    'samesite' => 'Lax'
]);

ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 1);
ini_set('session.cookie_samesite', 'Lax');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
