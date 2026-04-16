<?php
echo "<h1>Testing File Upload</h1>";

$targetDir = __DIR__ . '/public/uploads/lessons/';

if (!file_exists($targetDir)) {
    mkdir($targetDir, 0777, true);
    echo "Created directory: $targetDir<br>";
}

echo "Directory exists: " . (file_exists($targetDir) ? "YES" : "NO") . "<br>";
echo "Directory writable: " . (is_writable($targetDir) ? "YES" : "NO") . "<br>";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['test_file'])) {
    $file = $_FILES['test_file'];
    $targetFile = $targetDir . time() . '_' . basename($file['name']);
    
    if (move_uploaded_file($file['tmp_name'], $targetFile)) {
        echo "<p style='color: green'>File uploaded successfully: " . basename($targetFile) . "</p>";
    } else {
        echo "<p style='color: red'>Failed to upload file. Error code: " . $file['error'] . "</p>";
    }
}
?>

<form method="POST" enctype="multipart/form-data">
    <input type="file" name="test_file" required>
    <button type="submit">Test Upload</button>
</form>