<?php
session_start();
header('Content-Type: application/json');

error_log("=== DEBUG BOOKMARK ===");
error_log("Session user_id: " . ($_SESSION['user_id'] ?? 'none'));
error_log("Request method: " . $_SERVER['REQUEST_METHOD']);
error_log("POST data: " . print_r($_POST, true));

echo json_encode([
    'session_active' => isset($_SESSION['user_id']),
    'user_id' => $_SESSION['user_id'] ?? null,
    'method' => $_SERVER['REQUEST_METHOD'],
    'message' => 'Debug endpoint working'
]);