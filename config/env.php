<?php
// File: /config/env.php

require_once __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;

// Load environment variables
$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

// Define constants for easy access
define('APP_NAME', $_ENV['APP_NAME'] ?? 'ROGELE');
define('APP_ENV', $_ENV['APP_ENV'] ?? 'local');
define('APP_URL', $_ENV['APP_URL'] ?? 'http://localhost/rays-of-grace');

// Mail configuration constants
define('MAIL_HOST', $_ENV['MAIL_HOST'] ?? 'mail.privateemail.com');
define('MAIL_PORT', $_ENV['MAIL_PORT'] ?? 587);
define('MAIL_USERNAME', $_ENV['MAIL_USERNAME'] ?? '');
define('MAIL_PASSWORD', $_ENV['MAIL_PASSWORD'] ?? '');
define('MAIL_ENCRYPTION', $_ENV['MAIL_ENCRYPTION'] ?? 'tls');
define('MAIL_FROM_ADDRESS', $_ENV['MAIL_FROM_ADDRESS'] ?? '');
define('MAIL_FROM_NAME', $_ENV['MAIL_FROM_NAME'] ?? 'Rays of Grace');