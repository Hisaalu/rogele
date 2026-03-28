<?php
// File: /config/config.php

// Database Configuration - Using environment variables from Render
define('DB_HOST', getenv('DB_HOST') ?: 'gateway01.eu-central-1.prod.aws.tidbcloud.com');
define('DB_PORT', getenv('DB_PORT') ?: '4000');
define('DB_NAME', getenv('DB_NAME') ?: 'ROGELEDB');
define('DB_USER', getenv('DB_USER') ?: '2VcYykLWVZacLnw.root');
define('DB_PASS', getenv('DB_PASSWORD') ?: '');

// Application Configuration
define('BASE_URL', getenv('APP_URL') ?: 'https://rogele.onrender.com');
define('SITE_NAME', getenv('APP_NAME') ?: 'ROGELE');
define('ROOT_PATH', dirname(__DIR__));

// Rest of your config remains the same...
// File Upload Configuration, Subscription Plans, etc.

// Error Reporting - Production settings
error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', ROOT_PATH . '/logs/error.log');

// Timezone
date_default_timezone_set('Africa/Kampala');

// Session Configuration
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 1);
ini_set('session.cookie_samesite', 'Strict');
ini_set('session.gc_maxlifetime', 7200);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (getenv('RENDER')) {
    error_log("Application running on Render in production mode");
}
?>