<?php
// File: /config/config.php
// Database Configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'rays_of_grace_elearning');
define('DB_USER', 'root');
define('DB_PASS', '');

// Application Configuration
define('BASE_URL', 'http://localhost/rays-of-grace');
define('SITE_NAME', 'Rays of Grace E-Learning Platform');
define('ROOT_PATH', dirname(__DIR__));

// Session Configuration
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 0); // Set to 1 in production with HTTPS

// File Upload Configuration
define('MAX_FILE_SIZE', 10485760); // 10MB
define('ALLOWED_EXTENSIONS', ['pdf', 'doc', 'docx', 'ppt', 'pptx', 'xls', 'xlsx', 'mp4', 'jpg', 'jpeg', 'png']);
define('UPLOAD_PATH', ROOT_PATH . '/public/uploads/');

// Subscription Plans
define('FREE_TRIAL_DAYS', 60); // 2 months
define('SUBSCRIPTION_PLANS', [
    'monthly' => 15000, // UGX
    'termly' => 40000,
    'yearly' => 120000
]);

// Error Reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Timezone
date_default_timezone_set('Africa/Kampala');

// Start Session
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>