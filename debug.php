<?php
// File: test_upload.php

// Create directory with absolute path
$targetDir = __DIR__ . '/public/uploads/test/';

echo "<h2>Debug Info:</h2>";
echo "Script path: " . __FILE__ . "<br>";
echo "Target directory: " . $targetDir . "<br>";
echo "Directory exists: " . (file_exists($targetDir) ? "YES" : "NO") . "<br>";

if (!file_exists($targetDir)) {
    echo "Creating directory...<br>";
    $created = mkdir($targetDir, 0777, true);
    echo "Directory created: " . ($created ? "YES" : "NO") . "<br>";
}

echo "Is writable: " . (is_writable($targetDir) ? "YES" : "NO") . "<br>";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    echo "<h2>Upload Results:</h2>";
    
    for ($i = 0; $i < count($_FILES['file']['name']); $i++) {
        echo "<p>Processing: " . $_FILES['file']['name'][$i] . "</p>";
        echo "Temp file: " . $_FILES['file']['tmp_name'][$i] . "<br>";
        echo "Error code: " . $_FILES['file']['error'][$i] . "<br>";
        
        if ($_FILES['file']['error'][$i] === UPLOAD_ERR_OK) {
            $fileName = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', basename($_FILES['file']['name'][$i]));
            $targetFile = $targetDir . $fileName;
            echo "Target file: " . $targetFile . "<br>";
            
            if (move_uploaded_file($_FILES['file']['tmp_name'][$i], $targetFile)) {
                echo "<p style='color:green'>✓ Uploaded: " . $_FILES['file']['name'][$i] . "</p>";
                chmod($targetFile, 0666);
            } else {
                echo "<p style='color:red'>✗ Failed to move file. Check directory permissions.</p>";
                echo "Last error: " . error_get_last()['message'] . "<br>";
            }
        } else {
            $errors = [
                1 => 'File too large (php.ini)',
                2 => 'File too large (form)',
                3 => 'File partially uploaded',
                4 => 'No file uploaded',
                6 => 'Missing temp folder',
                7 => 'Failed to write to disk',
                8 => 'PHP extension stopped upload'
            ];
            echo "<p style='color:red'>Error: " . ($errors[$_FILES['file']['error'][$i]] ?? 'Unknown error') . "</p>";
        }
    }
}
?>

<h2>Test Upload Form</h2>
<form method="POST" enctype="multipart/form-data">
    <input type="file" name="file[]" multiple>
    <button type="submit">Upload</button>
</form>