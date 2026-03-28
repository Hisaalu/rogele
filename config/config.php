<?php
// File: /config/config.php

// Database Configuration - Using environment variables from Render
define('DB_HOST', getenv('DB_HOST') ?: 'production');
define('DB_NAME', getenv('DB_NAME') ?: 'ROGELEDB');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASSWORD') ?: '');

// TiDB SSL Configuration
define('DB_SSL_CA', getenv('DB_SSL_CA') ?: '/etc/ssl/certs/tidb-ca.pem');
define('DB_SSL_VERIFY', getenv('DB_SSL_VERIFY') ?: 'true');

// Application Configuration - Using environment variables
define('BASE_URL', getenv('APP_URL') ?: 'http://rogele.onrender.com');
define('SITE_NAME', getenv('APP_NAME') ?: 'ROGELE');
define('ROOT_PATH', dirname(__DIR__));

// File Upload Configuration
define('MAX_FILE_SIZE', 10485760); 
define('ALLOWED_EXTENSIONS', ['pdf', 'doc', 'docx', 'ppt', 'pptx', 'xls', 'xlsx', 'mp4', 'jpg', 'jpeg', 'png']);
define('UPLOAD_PATH', ROOT_PATH . '/public/uploads/');

// Subscription Plans
define('FREE_TRIAL_DAYS', 60); 
define('SUBSCRIPTION_PLANS', [
    'monthly' => 15000, 
    'termly' => 40000,
    'yearly' => 120000
]);

// Error Reporting - Production settings
error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', ROOT_PATH . '/logs/error.log');

// Timezone
date_default_timezone_set('Africa/Kampala');

// Session Configuration - Production settings
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 1); // Set to 1 for HTTPS in production
ini_set('session.cookie_samesite', 'Strict');
ini_set('session.gc_maxlifetime', 7200); // 2 hours session lifetime

// Only start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Optional: Log that we're in production mode
if (getenv('RENDER')) {
    error_log("Application running on Render in production mode");
}
?>