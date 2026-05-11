<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    echo "<h2>Upload Results:</h2>";
    echo "<pre>";
    print_r($_FILES);
    echo "</pre>";
    
    $targetDir = __DIR__ . '/public/uploads/test/';
    if (!file_exists($targetDir)) {
        mkdir($targetDir, 0777, true);
    }
    
    for ($i = 0; $i < count($_FILES['file']['name']); $i++) {
        if ($_FILES['file']['error'][$i] === UPLOAD_ERR_OK) {
            $targetFile = $targetDir . time() . '_' . basename($_FILES['file']['name'][$i]);
            if (move_uploaded_file($_FILES['file']['tmp_name'][$i], $targetFile)) {
                echo "<p style='color:green'>Uploaded: " . $_FILES['file']['name'][$i] . "</p>";
            } else {
                echo "<p style='color:red'>Failed to upload: " . $_FILES['file']['name'][$i] . "</p>";
            }
        } else {
            echo "<p style='color:red'>Error code: " . $_FILES['file']['error'][$i] . " for file: " . $_FILES['file']['name'][$i] . "</p>";
        }
    }
}
?>

<form method="POST" enctype="multipart/form-data">
    <input type="file" name="file[]" multiple>
    <button type="submit">Upload</button>
</form>