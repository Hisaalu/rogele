<?php
// File: /test-delete-account.php
require_once 'config/config.php';
session_start();

echo "<h2>Test Delete Account Route</h2>";
echo "<p>Current User ID: " . ($_SESSION['user_id'] ?? 'Not logged in') . "</p>";

// Test POST to the delete-account endpoint
?>
<form method="POST" action="<?php echo BASE_URL; ?>/external/delete-account">
    <input type="password" name="password" placeholder="Password" required>
    <button type="submit">Test Delete Account</button>
</form>

<?php
// Check if we have any response
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    echo "<p>Form submitted to: " . BASE_URL . "/external/delete-account</p>";
}
?>