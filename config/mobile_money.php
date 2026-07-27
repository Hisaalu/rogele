<?php
// File: /config/mobile_money.php

if (!defined('BASE_URL')) {
    $protocol = isset($_ENV['RENDER']) ? 'https://' : (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://');
    $host = $_SERVER['HTTP_HOST'] ?? getenv('RENDER_EXTERNAL_HOSTNAME') ?? 'localhost';
    define('BASE_URL', $protocol . $host);
}

define('MTN_MOMO_ENVIRONMENT', getenv('MTN_MOMO_ENVIRONMENT') ?: 'sandbox');
define('MTN_MOMO_CURRENCY', getenv('MTN_MOMO_CURRENCY') ?: 'UGX');
define('MTN_MOMO_PRIMARY_KEY', getenv('MTN_MOMO_PRIMARY_KEY') ?: '');
define('MTN_MOMO_API_USER', getenv('MTN_MOMO_API_USER') ?: '');
define('MTN_MOMO_API_KEY', getenv('MTN_MOMO_API_KEY') ?: '');
define('MTN_MOMO_TARGET_ENV', getenv('MTN_MOMO_TARGET_ENV') ?: 'sandbox');

define('AIRTEL_MONEY_ENVIRONMENT', getenv('AIRTEL_MONEY_ENVIRONMENT') ?: 'sandbox');
define('AIRTEL_MONEY_CLIENT_ID', getenv('AIRTEL_MONEY_CLIENT_ID') ?: '');
define('AIRTEL_MONEY_CLIENT_SECRET', getenv('AIRTEL_MONEY_CLIENT_SECRET') ?: '');
define('AIRTEL_MONEY_COUNTRY', getenv('AIRTEL_MONEY_COUNTRY') ?: 'UG');
define('AIRTEL_MONEY_CURRENCY', getenv('AIRTEL_MONEY_CURRENCY') ?: 'UGX');

define('MOBILE_MONEY_CALLBACK_URL', BASE_URL . '/external/payment-callback');
define('MOBILE_MONEY_IPN_URL', BASE_URL . '/ipn_handler.php');