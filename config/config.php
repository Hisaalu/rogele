<?php
// File: /config/config.php

$envPath = __DIR__ . '/../.env';
if (file_exists($envPath)) {
    $envVars = parse_ini_file($envPath, false, INI_SCANNER_RAW);
    if ($envVars !== false) {
        foreach ($envVars as $name => $value) {
            if (getenv($name) === false) {
                putenv("{$name}={$value}");
                $_ENV[$name] = $value;
                $_SERVER[$name] = $value;
            }
        }
    }
}

define('ROOT_PATH', dirname(__DIR__));

error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', ROOT_PATH . '/logs/error.log');

date_default_timezone_set('Africa/Kampala');

define('DB_HOST', getenv('DB_HOST') ?: '127.0.0.1');
define('DB_PORT', getenv('DB_PORT') ?: '4000');
define('DB_NAME', getenv('DB_NAME') ?: '');
define('DB_USER', getenv('DB_USER') ?: '');
define('DB_PASS', getenv('DB_PASS') ?: '');

$detectedHost = $_SERVER['HTTP_HOST'] ?? '';
$isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') 
           || ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https';

if (getenv('APP_URL')) {
    $appUrl = getenv('APP_URL');
} elseif ($detectedHost) {
    $scheme = $isHttps ? 'https://' : 'http://';
    $appUrl = $scheme . $detectedHost;
} elseif (getenv('RENDER') && getenv('RENDER_EXTERNAL_URL')) {
    $appUrl = getenv('RENDER_EXTERNAL_URL');
} else {
    $appUrl = 'http://localhost/rogele-pay';
}

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

$isRender = !empty(getenv('RENDER')) || (strpos($detectedHost, 'onrender.com') !== false);
$isSecure = $isHttps || $isRender;

ini_set('session.gc_maxlifetime', 1800);

session_set_cookie_params([
    'lifetime' => 0,
    'path'     => '/',
    'domain'   => '', 
    'secure'   => $isSecure,
    'httponly' => true,
    'samesite' => 'Lax'
]);

require_once __DIR__ . '/DatabaseSessionHandler.php';

try {
    $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8mb4";
    $sessionPdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_PERSISTENT => true
    ]);

    $handler = new DatabaseSessionHandler($sessionPdo);
    session_set_save_handler($handler, true);
} catch (PDOException $e) {
    error_log("Failed to register DatabaseSessionHandler: " . $e->getMessage());
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}