<?php
// File: /find-error-log.php
echo "<h2>PHP Error Log Location</h2>";

// Check php.ini settings
echo "<h3>PHP Configuration:</h3>";
echo "<p><strong>error_log path:</strong> " . ini_get('error_log') . "</p>";
echo "<p><strong>log_errors:</strong> " . (ini_get('log_errors') ? 'ON' : 'OFF') . "</p>";
echo "<p><strong>display_errors:</strong> " . (ini_get('display_errors') ? 'ON' : 'OFF') . "</p>";

// Check common XAMPP locations
$commonPaths = [
    'C:/xampp/php/logs/php_error_log',
    'C:/xampp/apache/logs/error.log',
    'C:/xampp/apache/logs/php_error_log',
    'C:/xampp/php/logs/error.log',
];

echo "<h3>Checking common log file locations:</h3>";
foreach ($commonPaths as $path) {
    if (file_exists($path)) {
        $size = filesize($path);
        echo "<p style='color:green'>✅ Found: $path (Size: " . number_format($size) . " bytes)</p>";
        
        // Show last 10 lines
        $content = file($path);
        $lastLines = array_slice($content, -10);
        echo "<pre style='background:#f4f4f4; padding:10px; overflow-x:auto;'>";
        echo "<strong>Last 10 lines:</strong>\n";
        foreach ($lastLines as $line) {
            echo htmlspecialchars($line);
        }
        echo "</pre>";
    } else {
        echo "<p style='color:orange'>⚠️ Not found: $path</p>";
    }
}

// Check if we can write to a custom log
$customLog = __DIR__ . '/custom_error.log';
if (is_writable(__DIR__)) {
    file_put_contents($customLog, date('Y-m-d H:i:s') . " - Log test\n", FILE_APPEND);
    echo "<p style='color:green'>✅ Created custom log: $customLog</p>";
} else {
    echo "<p style='color:red'>❌ Cannot write to directory</p>";
}

// Also check Apache error log location
echo "<h3>Apache Error Log (from Apache config):</h3>";
$apacheConfig = 'C:/xampp/apache/conf/httpd.conf';
if (file_exists($apacheConfig)) {
    $config = file_get_contents($apacheConfig);
    if (preg_match('/ErrorLog\s+"([^"]+)"/', $config, $matches)) {
        echo "<p>Apache ErrorLog: " . $matches[1] . "</p>";
        if (file_exists($matches[1])) {
            echo "<p style='color:green'>✅ File exists</p>";
        }
    } else {
        echo "<p>Could not parse Apache config</p>";
    }
}
?>