<?php
// File: setup.php - DELETE AFTER RUNNING ONCE

$dirs = [
    __DIR__ . '/public/uploads/',
    __DIR__ . '/public/uploads/lessons/',
    __DIR__ . '/public/uploads/profiles/'
];

foreach ($dirs as $dir) {
    if (!file_exists($dir)) {
        mkdir($dir, 0777, true);
    }
    chmod($dir, 0777);
}

echo "Setup complete! Delete this file now.";