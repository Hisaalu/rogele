<?php
// File: /config/pesapal.php

define('PESAPAL_CONSUMER_KEY', 'omW9Fn5vvUCLHaud5NbdaPPYon7/KlWM'); 
define('PESAPAL_CONSUMER_SECRET', '984F0Zb/aHSNMzPEr0tTh4znSNc='); 
define('PESAPAL_ENVIRONMENT', 'sandbox'); 

define('PESAPAL_CALLBACK_URL', BASE_URL . '/external/pesapal-callback');
define('PESAPAL_IPN_URL', BASE_URL . '/external/pesapal-ipn');

if (PESAPAL_ENVIRONMENT == 'production') {
    define('PESAPAL_API_URL', 'https://www.pesapal.com/api/PostPesapalDirectOrderV4');
    define('PESAPAL_QUERY_URL', 'https://www.pesapal.com/api/QueryPaymentDetails');
    define('PESAPAL_IPN_REGISTER_URL', 'https://www.pesapal.com/api/RegisterIPN');
} else {
    define('PESAPAL_API_URL', 'https://sandbox.pesapal.com/api/PostPesapalDirectOrderV4');
    define('PESAPAL_QUERY_URL', 'https://sandbox.pesapal.com/api/QueryPaymentDetails');
    define('PESAPAL_IPN_REGISTER_URL', 'https://sandbox.pesapal.com/api/RegisterIPN');
}

define('PESAPAL_CURRENCY', 'UGX');
?>