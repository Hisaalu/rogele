<?php
// File: /test-reset-debug.php

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Password Reset Debug Tool</h1>";

// Load environment
echo "<h2>1. Loading Environment Variables</h2>";
require_once __DIR__ . '/config/env.php';

echo "<ul>";
echo "<li>APP_ENV: " . (defined('APP_ENV') ? APP_ENV : 'NOT DEFINED') . "</li>";
echo "<li>APP_URL: " . (defined('APP_URL') ? APP_URL : 'NOT DEFINED') . "</li>";
echo "<li>MAIL_HOST: " . (defined('MAIL_HOST') ? MAIL_HOST : 'NOT DEFINED') . "</li>";
echo "<li>MAIL_PORT: " . (defined('MAIL_PORT') ? MAIL_PORT : 'NOT DEFINED') . "</li>";
echo "<li>MAIL_USERNAME: " . (defined('MAIL_USERNAME') ? MAIL_USERNAME : 'NOT DEFINED') . "</li>";
echo "<li>MAIL_PASSWORD: " . (defined('MAIL_PASSWORD') && MAIL_PASSWORD ? 'SET (length: ' . strlen(MAIL_PASSWORD) . ')' : 'NOT SET') . "</li>";
echo "<li>MAIL_PASSWORD first char: " . (defined('MAIL_PASSWORD') && MAIL_PASSWORD ? substr(MAIL_PASSWORD, 0, 1) : 'N/A') . "</li>";
echo "</ul>";

// Test database connection
echo "<h2>2. Testing Database Connection</h2>";
try {
    require_once __DIR__ . '/config/database.php';
    $db = Database::getInstance();
    $conn = $db->getConnection();
    echo "<p style='color: green'>✅ Database connected successfully</p>";
    
    // Check users table
    $stmt = $conn->query("SELECT COUNT(*) as count FROM users");
    $result = $stmt->fetch();
    echo "<p>Total users in database: " . $result['count'] . "</p>";
    
} catch (Exception $e) {
    echo "<p style='color: red'>❌ Database error: " . $e->getMessage() . "</p>";
}

// Test password reset tokens
echo "<h2>3. Password Reset Tokens</h2>";
try {
    // Check if password_resets table exists
    $stmt = $conn->query("SHOW TABLES LIKE 'password_resets'");
    if ($stmt->rowCount() > 0) {
        echo "<p style='color: green'>✅ password_resets table exists</p>";
        
        // Show recent tokens
        $stmt = $conn->query("SELECT * FROM password_resets ORDER BY created_at DESC LIMIT 5");
        $tokens = $stmt->fetchAll();
        
        if (count($tokens) > 0) {
            echo "<table border='1' cellpadding='5' cellspacing='0'>";
            echo "<tr><th>ID</th><th>User ID</th><th>Token (first 20 chars)</th><th>Expires At</th><th>Used</th><th>Created At</th></tr>";
            foreach ($tokens as $token) {
                echo "<tr>";
                echo "<td>" . $token['id'] . "</td>";
                echo "<td>" . $token['user_id'] . "</td>";
                echo "<td>" . substr($token['token'], 0, 20) . "...</td>";
                echo "<td>" . $token['expires_at'] . "</td>";
                echo "<td>" . ($token['used'] ? 'Yes' : 'No') . "</td>";
                echo "<td>" . $token['created_at'] . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "<p>No password reset tokens found</p>";
        }
    } else {
        echo "<p style='color: red'>❌ password_resets table does not exist. Run this SQL:</p>";
        echo "<pre>
CREATE TABLE IF NOT EXISTS `password_resets` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `user_id` int(11) NOT NULL,
    `token` varchar(255) NOT NULL,
    `expires_at` datetime NOT NULL,
    `used` tinyint(1) DEFAULT 0,
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `token` (`token`),
    KEY `user_id` (`user_id`),
    CONSTRAINT `password_resets_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        </pre>";
    }
} catch (Exception $e) {
    echo "<p style='color: red'>❌ Error checking tokens: " . $e->getMessage() . "</p>";
}

// Test user by email
echo "<h2>4. Test User Lookup</h2>";
if (isset($_POST['test_email'])) {
    $testEmail = $_POST['test_email'];
    echo "<p>Testing email: " . htmlspecialchars($testEmail) . "</p>";
    
    try {
        require_once __DIR__ . '/models/User.php';
        $userModel = new User();
        $user = $userModel->getByEmail($testEmail);
        
        if ($user) {
            echo "<p style='color: green'>✅ User found:</p>";
            echo "<pre>";
            print_r([
                'id' => $user['id'],
                'email' => $user['email'],
                'first_name' => $user['first_name'],
                'last_name' => $user['last_name'],
                'role' => $user['role']
            ]);
            echo "</pre>";
            
            // Test creating a reset token
            echo "<h3>Creating Test Reset Token</h3>";
            $token = bin2hex(random_bytes(32));
            $expires = date('Y-m-d H:i:s', strtotime('+20 minutes'));
            
            $saved = $userModel->saveResetToken($user['id'], $token, $expires);
            
            if ($saved) {
                echo "<p style='color: green'>✅ Test token created successfully!</p>";
                echo "<p><strong>Token:</strong> " . $token . "</p>";
                echo "<p><strong>Reset Link:</strong> " . APP_URL . "/reset-password?token=" . $token . "</p>";
                echo "<p><strong>Expires:</strong> " . $expires . "</p>";
                
                // Test verifying the token
                echo "<h3>Testing Token Verification</h3>";
                $verifiedUser = $userModel->getUserByResetToken($token);
                if ($verifiedUser) {
                    echo "<p style='color: green'>✅ Token verification successful!</p>";
                    echo "<p>User: " . $verifiedUser['email'] . "</p>";
                } else {
                    echo "<p style='color: red'>❌ Token verification failed</p>";
                }
            } else {
                echo "<p style='color: red'>❌ Failed to create test token</p>";
            }
        } else {
            echo "<p style='color: red'>❌ User not found with email: " . htmlspecialchars($testEmail) . "</p>";
        }
    } catch (Exception $e) {
        echo "<p style='color: red'>Error: " . $e->getMessage() . "</p>";
    }
}

// Test email sending
echo "<h2>5. Test Email Sending</h2>";
if (isset($_POST['send_email'])) {
    $testEmail = $_POST['send_email'];
    $testToken = $_POST['test_token'] ?? bin2hex(random_bytes(32));
    
    echo "<p>Sending test email to: " . htmlspecialchars($testEmail) . "</p>";
    
    try {
        require_once __DIR__ . '/helpers/MailHelper.php';
        $mailer = new MailHelper();
        
        $testLink = APP_URL . "/reset-password?token=" . $testToken;
        $sent = $mailer->sendResetEmail($testEmail, 'Test User', $testLink);
        
        if ($sent) {
            echo "<p style='color: green'>✅ Test email sent successfully!</p>";
            echo "<p>Reset link: <a href='" . $testLink . "' target='_blank'>" . $testLink . "</a></p>";
        } else {
            echo "<p style='color: red'>❌ Failed to send test email</p>";
        }
    } catch (Exception $e) {
        echo "<p style='color: red'>Error: " . $e->getMessage() . "</p>";
    }
}

// Test full password reset flow
echo "<h2>6. Test Full Reset Flow</h2>";
if (isset($_POST['full_reset'])) {
    $resetEmail = $_POST['reset_email'];
    $newPassword = $_POST['new_password'];
    
    echo "<p>Testing full reset for: " . htmlspecialchars($resetEmail) . "</p>";
    
    try {
        require_once __DIR__ . '/models/User.php';
        $userModel = new User();
        
        // Get user
        $user = $userModel->getByEmail($resetEmail);
        
        if (!$user) {
            echo "<p style='color: red'>❌ User not found</p>";
        } else {
            // Create token
            $token = bin2hex(random_bytes(32));
            $expires = date('Y-m-d H:i:s', strtotime('+20 minutes'));
            $saved = $userModel->saveResetToken($user['id'], $token, $expires);
            
            if ($saved) {
                echo "<p style='color: green'>✅ Token created: " . substr($token, 0, 20) . "...</p>";
                
                // Verify token
                $verifiedUser = $userModel->getUserByResetToken($token);
                
                if ($verifiedUser) {
                    echo "<p style='color: green'>✅ Token verified</p>";
                    
                    // Update password
                    $result = $userModel->updatePassword($user['id'], $newPassword);
                    
                    if ($result['success']) {
                        echo "<p style='color: green'>✅ Password updated successfully!</p>";
                        
                        // Clear token
                        $userModel->clearResetToken($user['id']);
                        echo "<p>✅ Token cleared</p>";
                        
                        echo "<p>You can now login with your new password: <strong>" . htmlspecialchars($newPassword) . "</strong></p>";
                    } else {
                        echo "<p style='color: red'>❌ Failed to update password: " . ($result['error'] ?? 'Unknown error') . "</p>";
                    }
                } else {
                    echo "<p style='color: red'>❌ Token verification failed</p>";
                }
            } else {
                echo "<p style='color: red'>❌ Failed to create token</p>";
            }
        }
    } catch (Exception $e) {
        echo "<p style='color: red'>Error: " . $e->getMessage() . "</p>";
    }
}

// Test form to look up user
echo "<h2>Test Form</h2>";
?>
<form method="POST" style="margin-bottom: 20px; padding: 15px; border: 1px solid #ccc; border-radius: 5px;">
    <h3>1. Look up user by email</h3>
    <input type="email" name="test_email" placeholder="Enter email" style="padding: 5px; width: 300px;">
    <button type="submit">Look Up User</button>
</form>

<form method="POST" style="margin-bottom: 20px; padding: 15px; border: 1px solid #ccc; border-radius: 5px;">
    <h3>2. Send test email</h3>
    <input type="email" name="send_email" placeholder="Recipient email" style="padding: 5px; width: 300px;">
    <input type="text" name="test_token" placeholder="Token (optional)" style="padding: 5px; width: 300px; margin-top: 5px;">
    <button type="submit">Send Test Email</button>
</form>

<form method="POST" style="margin-bottom: 20px; padding: 15px; border: 1px solid #ccc; border-radius: 5px;">
    <h3>3. Test full password reset</h3>
    <input type="email" name="reset_email" placeholder="User email" required style="padding: 5px; width: 300px;">
    <input type="password" name="new_password" placeholder="New password" required style="padding: 5px; width: 300px; margin-top: 5px;">
    <button type="submit" name="full_reset" value="1">Test Full Reset</button>
</form>

<?php
// Check PHP mail configuration
echo "<h2>7. PHP Mail Configuration</h2>";
echo "<ul>";
echo "<li>SMTP: " . ini_get('SMTP') . "</li>";
echo "<li>smtp_port: " . ini_get('smtp_port') . "</li>";
echo "<li>sendmail_from: " . ini_get('sendmail_from') . "</li>";
echo "<li>sendmail_path: " . ini_get('sendmail_path') . "</li>";
echo "</ul>";

// Check if required files exist
echo "<h2>8. File Check</h2>";
$files = [
    '/config/env.php',
    '/config/database.php',
    '/models/User.php',
    '/helpers/MailHelper.php',
    '/controllers/AuthController.php',
    '/views/auth/reset-password.php'
];

foreach ($files as $file) {
    $fullPath = __DIR__ . $file;
    if (file_exists($fullPath)) {
        echo "<p style='color: green'>✅ " . $file . " exists</p>";
    } else {
        echo "<p style='color: red'>❌ " . $file . " does not exist</p>";
    }
}

// Check if reset-password.php can be accessed
echo "<h2>9. Reset Password Page Test</h2>";
$testToken = bin2hex(random_bytes(32));
$resetUrl = APP_URL . "/reset-password?token=" . $testToken;
echo "<p>Test URL: <a href='" . $resetUrl . "' target='_blank'>" . $resetUrl . "</a></p>";
echo "<p>Click this link to test if the reset password page loads correctly.</p>";
echo "<p><strong>Note:</strong> This token is not saved in the database, so it will show as invalid.</p>";
?><?php
// File: /test-reset-debug.php

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Password Reset Debug Tool</h1>";

// Load environment
echo "<h2>1. Loading Environment Variables</h2>";
require_once __DIR__ . '/config/env.php';

echo "<ul>";
echo "<li>APP_ENV: " . (defined('APP_ENV') ? APP_ENV : 'NOT DEFINED') . "</li>";
echo "<li>APP_URL: " . (defined('APP_URL') ? APP_URL : 'NOT DEFINED') . "</li>";
echo "<li>MAIL_HOST: " . (defined('MAIL_HOST') ? MAIL_HOST : 'NOT DEFINED') . "</li>";
echo "<li>MAIL_PORT: " . (defined('MAIL_PORT') ? MAIL_PORT : 'NOT DEFINED') . "</li>";
echo "<li>MAIL_USERNAME: " . (defined('MAIL_USERNAME') ? MAIL_USERNAME : 'NOT DEFINED') . "</li>";
echo "<li>MAIL_PASSWORD: " . (defined('MAIL_PASSWORD') && MAIL_PASSWORD ? 'SET (length: ' . strlen(MAIL_PASSWORD) . ')' : 'NOT SET') . "</li>";
echo "<li>MAIL_PASSWORD first char: " . (defined('MAIL_PASSWORD') && MAIL_PASSWORD ? substr(MAIL_PASSWORD, 0, 1) : 'N/A') . "</li>";
echo "</ul>";

// Test database connection
echo "<h2>2. Testing Database Connection</h2>";
try {
    require_once __DIR__ . '/config/database.php';
    $db = Database::getInstance();
    $conn = $db->getConnection();
    echo "<p style='color: green'>✅ Database connected successfully</p>";
    
    // Check users table
    $stmt = $conn->query("SELECT COUNT(*) as count FROM users");
    $result = $stmt->fetch();
    echo "<p>Total users in database: " . $result['count'] . "</p>";
    
} catch (Exception $e) {
    echo "<p style='color: red'>❌ Database error: " . $e->getMessage() . "</p>";
}

// Test password reset tokens
echo "<h2>3. Password Reset Tokens</h2>";
try {
    // Check if password_resets table exists
    $stmt = $conn->query("SHOW TABLES LIKE 'password_resets'");
    if ($stmt->rowCount() > 0) {
        echo "<p style='color: green'>✅ password_resets table exists</p>";
        
        // Show recent tokens
        $stmt = $conn->query("SELECT * FROM password_resets ORDER BY created_at DESC LIMIT 5");
        $tokens = $stmt->fetchAll();
        
        if (count($tokens) > 0) {
            echo "<table border='1' cellpadding='5' cellspacing='0'>";
            echo "<tr><th>ID</th><th>User ID</th><th>Token (first 20 chars)</th><th>Expires At</th><th>Used</th><th>Created At</th></tr>";
            foreach ($tokens as $token) {
                echo "<tr>";
                echo "<td>" . $token['id'] . "</td>";
                echo "<td>" . $token['user_id'] . "</td>";
                echo "<td>" . substr($token['token'], 0, 20) . "...</td>";
                echo "<td>" . $token['expires_at'] . "</td>";
                echo "<td>" . ($token['used'] ? 'Yes' : 'No') . "</td>";
                echo "<td>" . $token['created_at'] . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "<p>No password reset tokens found</p>";
        }
    } else {
        echo "<p style='color: red'>❌ password_resets table does not exist. Run this SQL:</p>";
        echo "<pre>
CREATE TABLE IF NOT EXISTS `password_resets` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `user_id` int(11) NOT NULL,
    `token` varchar(255) NOT NULL,
    `expires_at` datetime NOT NULL,
    `used` tinyint(1) DEFAULT 0,
    `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `token` (`token`),
    KEY `user_id` (`user_id`),
    CONSTRAINT `password_resets_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        </pre>";
    }
} catch (Exception $e) {
    echo "<p style='color: red'>❌ Error checking tokens: " . $e->getMessage() . "</p>";
}

// Test user by email
echo "<h2>4. Test User Lookup</h2>";
if (isset($_POST['test_email'])) {
    $testEmail = $_POST['test_email'];
    echo "<p>Testing email: " . htmlspecialchars($testEmail) . "</p>";
    
    try {
        require_once __DIR__ . '/models/User.php';
        $userModel = new User();
        $user = $userModel->getByEmail($testEmail);
        
        if ($user) {
            echo "<p style='color: green'>✅ User found:</p>";
            echo "<pre>";
            print_r([
                'id' => $user['id'],
                'email' => $user['email'],
                'first_name' => $user['first_name'],
                'last_name' => $user['last_name'],
                'role' => $user['role']
            ]);
            echo "</pre>";
            
            // Test creating a reset token
            echo "<h3>Creating Test Reset Token</h3>";
            $token = bin2hex(random_bytes(32));
            $expires = date('Y-m-d H:i:s', strtotime('+20 minutes'));
            
            $saved = $userModel->saveResetToken($user['id'], $token, $expires);
            
            if ($saved) {
                echo "<p style='color: green'>✅ Test token created successfully!</p>";
                echo "<p><strong>Token:</strong> " . $token . "</p>";
                echo "<p><strong>Reset Link:</strong> " . APP_URL . "/reset-password?token=" . $token . "</p>";
                echo "<p><strong>Expires:</strong> " . $expires . "</p>";
                
                // Test verifying the token
                echo "<h3>Testing Token Verification</h3>";
                $verifiedUser = $userModel->getUserByResetToken($token);
                if ($verifiedUser) {
                    echo "<p style='color: green'>✅ Token verification successful!</p>";
                    echo "<p>User: " . $verifiedUser['email'] . "</p>";
                } else {
                    echo "<p style='color: red'>❌ Token verification failed</p>";
                }
            } else {
                echo "<p style='color: red'>❌ Failed to create test token</p>";
            }
        } else {
            echo "<p style='color: red'>❌ User not found with email: " . htmlspecialchars($testEmail) . "</p>";
        }
    } catch (Exception $e) {
        echo "<p style='color: red'>Error: " . $e->getMessage() . "</p>";
    }
}

// Test email sending
echo "<h2>5. Test Email Sending</h2>";
if (isset($_POST['send_email'])) {
    $testEmail = $_POST['send_email'];
    $testToken = $_POST['test_token'] ?? bin2hex(random_bytes(32));
    
    echo "<p>Sending test email to: " . htmlspecialchars($testEmail) . "</p>";
    
    try {
        require_once __DIR__ . '/helpers/MailHelper.php';
        $mailer = new MailHelper();
        
        $testLink = APP_URL . "/reset-password?token=" . $testToken;
        $sent = $mailer->sendResetEmail($testEmail, 'Test User', $testLink);
        
        if ($sent) {
            echo "<p style='color: green'>✅ Test email sent successfully!</p>";
            echo "<p>Reset link: <a href='" . $testLink . "' target='_blank'>" . $testLink . "</a></p>";
        } else {
            echo "<p style='color: red'>❌ Failed to send test email</p>";
        }
    } catch (Exception $e) {
        echo "<p style='color: red'>Error: " . $e->getMessage() . "</p>";
    }
}

// Test full password reset flow
echo "<h2>6. Test Full Reset Flow</h2>";
if (isset($_POST['full_reset'])) {
    $resetEmail = $_POST['reset_email'];
    $newPassword = $_POST['new_password'];
    
    echo "<p>Testing full reset for: " . htmlspecialchars($resetEmail) . "</p>";
    
    try {
        require_once __DIR__ . '/models/User.php';
        $userModel = new User();
        
        // Get user
        $user = $userModel->getByEmail($resetEmail);
        
        if (!$user) {
            echo "<p style='color: red'>❌ User not found</p>";
        } else {
            // Create token
            $token = bin2hex(random_bytes(32));
            $expires = date('Y-m-d H:i:s', strtotime('+20 minutes'));
            $saved = $userModel->saveResetToken($user['id'], $token, $expires);
            
            if ($saved) {
                echo "<p style='color: green'>✅ Token created: " . substr($token, 0, 20) . "...</p>";
                
                // Verify token
                $verifiedUser = $userModel->getUserByResetToken($token);
                
                if ($verifiedUser) {
                    echo "<p style='color: green'>✅ Token verified</p>";
                    
                    // Update password
                    $result = $userModel->updatePassword($user['id'], $newPassword);
                    
                    if ($result['success']) {
                        echo "<p style='color: green'>✅ Password updated successfully!</p>";
                        
                        // Clear token
                        $userModel->clearResetToken($user['id']);
                        echo "<p>✅ Token cleared</p>";
                        
                        echo "<p>You can now login with your new password: <strong>" . htmlspecialchars($newPassword) . "</strong></p>";
                    } else {
                        echo "<p style='color: red'>❌ Failed to update password: " . ($result['error'] ?? 'Unknown error') . "</p>";
                    }
                } else {
                    echo "<p style='color: red'>❌ Token verification failed</p>";
                }
            } else {
                echo "<p style='color: red'>❌ Failed to create token</p>";
            }
        }
    } catch (Exception $e) {
        echo "<p style='color: red'>Error: " . $e->getMessage() . "</p>";
    }
}

// Test form to look up user
echo "<h2>Test Form</h2>";
?>
<form method="POST" style="margin-bottom: 20px; padding: 15px; border: 1px solid #ccc; border-radius: 5px;">
    <h3>1. Look up user by email</h3>
    <input type="email" name="test_email" placeholder="Enter email" style="padding: 5px; width: 300px;">
    <button type="submit">Look Up User</button>
</form>

<form method="POST" style="margin-bottom: 20px; padding: 15px; border: 1px solid #ccc; border-radius: 5px;">
    <h3>2. Send test email</h3>
    <input type="email" name="send_email" placeholder="Recipient email" style="padding: 5px; width: 300px;">
    <input type="text" name="test_token" placeholder="Token (optional)" style="padding: 5px; width: 300px; margin-top: 5px;">
    <button type="submit">Send Test Email</button>
</form>

<form method="POST" style="margin-bottom: 20px; padding: 15px; border: 1px solid #ccc; border-radius: 5px;">
    <h3>3. Test full password reset</h3>
    <input type="email" name="reset_email" placeholder="User email" required style="padding: 5px; width: 300px;">
    <input type="password" name="new_password" placeholder="New password" required style="padding: 5px; width: 300px; margin-top: 5px;">
    <button type="submit" name="full_reset" value="1">Test Full Reset</button>
</form>

<?php
// Check PHP mail configuration
echo "<h2>7. PHP Mail Configuration</h2>";
echo "<ul>";
echo "<li>SMTP: " . ini_get('SMTP') . "</li>";
echo "<li>smtp_port: " . ini_get('smtp_port') . "</li>";
echo "<li>sendmail_from: " . ini_get('sendmail_from') . "</li>";
echo "<li>sendmail_path: " . ini_get('sendmail_path') . "</li>";
echo "</ul>";

// Check if required files exist
echo "<h2>8. File Check</h2>";
$files = [
    '/config/env.php',
    '/config/database.php',
    '/models/User.php',
    '/helpers/MailHelper.php',
    '/controllers/AuthController.php',
    '/views/auth/reset-password.php'
];

foreach ($files as $file) {
    $fullPath = __DIR__ . $file;
    if (file_exists($fullPath)) {
        echo "<p style='color: green'>✅ " . $file . " exists</p>";
    } else {
        echo "<p style='color: red'>❌ " . $file . " does not exist</p>";
    }
}

// Check if reset-password.php can be accessed
echo "<h2>9. Reset Password Page Test</h2>";
$testToken = bin2hex(random_bytes(32));
$resetUrl = APP_URL . "/reset-password?token=" . $testToken;
echo "<p>Test URL: <a href='" . $resetUrl . "' target='_blank'>" . $resetUrl . "</a></p>";
echo "<p>Click this link to test if the reset password page loads correctly.</p>";
echo "<p><strong>Note:</strong> This token is not saved in the database, so it will show as invalid.</p>";
?>