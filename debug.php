<?php
// File: /session-test.php

require_once __DIR__ . '/config/config.php';

header('Content-Type: text/html');

echo "<h1>Session Test</h1>";
echo "<p>Session ID: " . session_id() . "</p>";
echo "<p>Session save path: " . ini_get('session.save_path') . "</p>";
echo "<p>Cookie domain: " . ini_get('session.cookie_domain') . "</p>";

if (!isset($_SESSION['test_count'])) {
    $_SESSION['test_count'] = 1;
    echo "<p style='color:green'>Session initialized! Count = 1</p>";
} else {
    $_SESSION['test_count']++;
    echo "<p style='color:blue'>Session exists! Count = " . $_SESSION['test_count'] . "</p>";
}

echo "<h2>Full Session Data:</h2>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

echo "<p><a href='session-test.php'>Refresh this page</a> - The count should increase each time.</p>";
echo "<p><a href='" . BASE_URL . "/login'>Go to Login</a></p>";