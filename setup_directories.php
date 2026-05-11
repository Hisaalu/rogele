<?php
// File: setup_directories.php - Run ONCE, then DELETE

$directories = [
    __DIR__ . '/public/uploads/',
    __DIR__ . '/public/uploads/lessons/',
    __DIR__ . '/public/uploads/profiles/',
    __DIR__ . '/public/uploads/test/',
    __DIR__ . '/logs/'
];

echo "<h1>Setting up directories...</h1>";

foreach ($directories as $dir) {
    if (!file_exists($dir)) {
        mkdir($dir, 0777, true);
        echo "Created: $dir<br>";
    } else {
        echo "Exists: $dir<br>";
    }
    
    // Try to set permissions
    if (chmod($dir, 0777)) {
        echo "Permissions set for: $dir<br>";
    } else {
        echo "Failed to set permissions for: $dir<br>";
    }
    
    // Verify writable
    if (is_writable($dir)) {
        echo "<span style='color:green'>✓ Writable</span><br>";
    } else {
        echo "<span style='color:red'>✗ NOT writable</span><br>";
    }
    echo "<br>";
}

echo "<p>Setup complete! <strong>DELETE THIS FILE NOW</strong> for security.</p>";