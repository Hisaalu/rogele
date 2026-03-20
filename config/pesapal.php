<?php
// File: /config/pesapal.php

// Pesapal Configuration
define('PESAPAL_CONSUMER_KEY', 'omW9Fn5vvUCLHaud5NbdaPPYon7/KlWM'); 
define('PESAPAL_CONSUMER_SECRET', '984F0Zb/aHSNMzPEr0tTh4znSNc='); 
define('PESAPAL_ENVIRONMENT', 'sandbox'); 

// Callback URLs
define('PESAPAL_CALLBACK_URL', BASE_URL . '/external/pesapal-callback');
define('PESAPAL_IPN_URL', BASE_URL . '/external/pesapal-ipn');

// API URLs
if (PESAPAL_ENVIRONMENT == 'production') {
    define('PESAPAL_API_URL', 'https://www.pesapal.com/api/PostPesapalDirectOrderV4');
    define('PESAPAL_QUERY_URL', 'https://www.pesapal.com/api/QueryPaymentDetails');
    define('PESAPAL_IPN_REGISTER_URL', 'https://www.pesapal.com/api/RegisterIPN');
} else {
    define('PESAPAL_API_URL', 'https://sandbox.pesapal.com/api/PostPesapalDirectOrderV4');
    define('PESAPAL_QUERY_URL', 'https://sandbox.pesapal.com/api/QueryPaymentDetails');
    define('PESAPAL_IPN_REGISTER_URL', 'https://sandbox.pesapal.com/api/RegisterIPN');
}

// Pesapal currency (UGX for Uganda)
define('PESAPAL_CURRENCY', 'UGX');
?>