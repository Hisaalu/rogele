<?php
// File: /config/pesapal.php

// Auto-detect BASE_URL for Render deployment
if (!defined('BASE_URL')) {
    if (php_sapi_name() === 'cli') {
        define('BASE_URL', getenv('APP_URL') ?: 'https://rogele.raysofgrace.ac.ug');
    } else {
        $protocol = isset($_ENV['RENDER']) ? 'https://' : (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://');
        $host = $_SERVER['HTTP_HOST'] ?? getenv('RENDER_EXTERNAL_HOSTNAME') ?? 'localhost';
        
        if (getenv('RENDER')) {
            $host = getenv('RENDER_EXTERNAL_HOSTNAME') ?: $host;
            define('BASE_URL', 'https://' . $host);
        } else {
            $scriptName = dirname($_SERVER['SCRIPT_NAME'] ?? '');
            $basePath = rtrim($scriptName, '/');
            define('BASE_URL', $protocol . $host . $basePath);
        }
    }
}

// Load environment variables 
$envFile = __DIR__ . '/../.env';
if (file_exists($envFile) && !getenv('RENDER')) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        
        $parts = explode('=', $line, 2);
        if (count($parts) == 2) {
            $key = trim($parts[0]);
            $value = trim($parts[1], '"\'');
            if (!getenv($key)) {
                putenv("$key=$value");
                $_ENV[$key] = $value;
            }
        }
    }
}

// PesaPal v3 Credentials
define('PESAPAL_CONSUMER_KEY', getenv('PESAPAL_CONSUMER_KEY') ?: '');
define('PESAPAL_CONSUMER_SECRET', getenv('PESAPAL_CONSUMER_SECRET') ?: '');
define('PESAPAL_ENVIRONMENT', getenv('PESAPAL_ENVIRONMENT') ?: 'sandbox');
define('PESAPAL_CURRENCY', getenv('PESAPAL_CURRENCY') ?: 'UGX');

// Define callback URLs for Render
if (getenv('RENDER')) {
    $baseUrl = BASE_URL;
    define('PESAPAL_CALLBACK_URL', $baseUrl . '/external/pesapal-callback');
    define('PESAPAL_IPN_URL', $baseUrl . '/ipn_handler.php');
} else {
    define('PESAPAL_CALLBACK_URL', BASE_URL . '/external/pesapal-callback');
    define('PESAPAL_IPN_URL', BASE_URL . '/ipn_handler.php');
}

// V3 API Endpoints
if (PESAPAL_ENVIRONMENT == 'production') {
    define('PESAPAL_API_URL', 'https://pay.pesapal.com/v3/api/Transactions/SubmitOrderRequest');
    define('PESAPAL_QUERY_URL', 'https://pay.pesapal.com/v3/api/QueryPaymentDetails');
    define('PESAPAL_IPN_REGISTER_URL', 'https://pay.pesapal.com/v3/api/URLSetup/RegisterIPN');
    define('PESAPAL_AUTH_URL', 'https://pay.pesapal.com/v3/api/Auth/RequestToken');
} else {
    define('PESAPAL_API_URL', 'https://cybqa.pesapal.com/pesapalv3/api/Transactions/SubmitOrderRequest');
    define('PESAPAL_QUERY_URL', 'https://cybqa.pesapal.com/pesapalv3/api/QueryPaymentDetails');
    define('PESAPAL_IPN_REGISTER_URL', 'https://cybqa.pesapal.com/pesapalv3/api/URLSetup/RegisterIPN');
    define('PESAPAL_AUTH_URL', 'https://cybqa.pesapal.com/pesapalv3/api/Auth/RequestToken');
}

// Debug mode
if (getenv('PESAPAL_DEBUG') === 'true') {
    error_log("[PesaPal Config] Loaded for environment: " . PESAPAL_ENVIRONMENT);
    error_log("[PesaPal Config] BASE_URL: " . BASE_URL);
    error_log("[PesaPal Config] API URL: " . PESAPAL_API_URL);
    error_log("[PesaPal Config] Callback URL: " . PESAPAL_CALLBACK_URL);
    error_log("[PesaPal Config] IPN URL: " . PESAPAL_IPN_URL);
}
?>