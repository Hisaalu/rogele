<?php
// File: /config/env.php

define('APP_NAME', getenv('APP_NAME') ?: 'ROGELE');
define('APP_ENV', getenv('APP_ENV') ?: 'production');
define('APP_URL', getenv('APP_URL') ?: 'http://rogele.raysofgrace.ac.ug');

define('MAIL_HOST', getenv('MAIL_HOST') ?: 'mail.privateemail.com');
define('MAIL_PORT', getenv('MAIL_PORT') ?: 587);
define('MAIL_USERNAME', getenv('MAIL_USERNAME') ?: '');
define('MAIL_PASSWORD', getenv('MAIL_PASSWORD') ?: '');
define('MAIL_ENCRYPTION', getenv('MAIL_ENCRYPTION') ?: 'tls');
define('MAIL_FROM_ADDRESS', getenv('MAIL_FROM_ADDRESS') ?: '');
define('MAIL_FROM_NAME', getenv('MAIL_FROM_NAME') ?: 'Rays of Grace');

if (getenv('RENDER')) {
    error_log("Application running on Render in " . APP_ENV . " mode");
}
?>