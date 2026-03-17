<?php
// File: /test-upload.php
require_once __DIR__ . '/config/config.php';

echo "<h1>Test File Upload</h1>";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['test_file'])) {
    $targetDir = __DIR__ . '/public/uploads/test/';
    
    if (!file_exists($targetDir)) {
        mkdir($targetDir, 0777, true);
    }
    
    $fileName = time() . '_' . basename($_FILES['test_file']['name']);
    $targetFile = $targetDir . $fileName;
    
    if (move_uploaded_file($_FILES['test_file']['tmp_name'], $targetFile)) {
        echo "<p style='color: green;'>✅ File uploaded successfully!</p>";
        echo "<p>Saved to: $targetFile</p>";
        echo "<p>Access at: <a href='/rays-of-grace/public/uploads/test/$fileName' target='_blank'>/rays-of-grace/public/uploads/test/$fileName</a></p>";
    } else {
        echo "<p style='color: red;'>❌ Upload failed!</p>";
    }
}
?>

<form method="POST" enctype="multipart/form-data">
    <input type="file" name="test_file" required>
    <button type="submit">Upload Test File</button>
</form>