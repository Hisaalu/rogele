<?php
// File: /config/env.php

require_once __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;

// Load environment variables from .env file ONLY if it exists
$envFile = __DIR__ . '/../.env';
if (file_exists($envFile)) {
    try {
        $dotenv = Dotenv::createImmutable(__DIR__ . '/../');
        $dotenv->load();
        error_log("Loaded .env file successfully");
    } catch (Exception $e) {
        error_log("Error loading .env file: " . $e->getMessage());
    }
} else {
    error_log("No .env file found - using environment variables from system");
}

// Define constants with fallback values
// Check both $_ENV and getenv() for maximum compatibility
define('APP_NAME', $_ENV['APP_NAME'] ?? getenv('APP_NAME') ?? 'ROGELE');
define('APP_ENV', $_ENV['APP_ENV'] ?? getenv('APP_ENV') ?? 'local');
define('APP_URL', $_ENV['APP_URL'] ?? getenv('APP_URL') ?? 'http://localhost/rays-of-grace');

// Mail configuration constants
define('MAIL_HOST', $_ENV['MAIL_HOST'] ?? getenv('MAIL_HOST') ?? 'mail.privateemail.com');
define('MAIL_PORT', $_ENV['MAIL_PORT'] ?? getenv('MAIL_PORT') ?? 587);
define('MAIL_USERNAME', $_ENV['MAIL_USERNAME'] ?? getenv('MAIL_USERNAME') ?? '');
define('MAIL_PASSWORD', $_ENV['MAIL_PASSWORD'] ?? getenv('MAIL_PASSWORD') ?? '');
define('MAIL_ENCRYPTION', $_ENV['MAIL_ENCRYPTION'] ?? getenv('MAIL_ENCRYPTION') ?? 'tls');
define('MAIL_FROM_ADDRESS', $_ENV['MAIL_FROM_ADDRESS'] ?? getenv('MAIL_FROM_ADDRESS') ?? '');
define('MAIL_FROM_NAME', $_ENV['MAIL_FROM_NAME'] ?? getenv('MAIL_FROM_NAME') ?? 'Rays of Grace');

// Log configuration status (helpful for debugging)
if (APP_ENV === 'development' || APP_ENV === 'local') {
    error_log("Application running in " . APP_ENV . " mode");
    error_log("Mail configured with host: " . MAIL_HOST);
}