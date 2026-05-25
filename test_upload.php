<?php
require_once 'db.php';

echo "<h2>Testing Upload System</h2>";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['test_upload'])) {
    echo "Form submitted!<br>";
    
    if (isset($_FILES['test_file']) && $_FILES['test_file']['error'] === 0) {
        $fileName = $_FILES['test_file']['name'];
        $tmpName = $_FILES['test_file']['tmp_name'];
        $targetDir = "uploads/software/";
        
        // Create folder if it doesn't exist
        if (!file_exists($targetDir)) {
            mkdir($targetDir, 0777, true);
            echo "📁 Created folder: $targetDir<br>";
        }
        
        $targetFile = $targetDir . $fileName;
        if (move_uploaded_file($tmpName, $targetFile)) {
            echo "✅ File uploaded to: $targetFile<br>";
            
            // Insert into database
            $stmt = $pdo->prepare("INSERT INTO software_uploads (software_name, version, lab_room, description, file_name, file_path, uploaded_by) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $result = $stmt->execute(['Test Software', '1.0', '524', 'Test description', $fileName, $targetFile, 'Admin']);
            
            if ($result) {
                echo "✅ Database entry created!<br>";
                echo "<br><a href='admin_software_upload.php'>Go back to upload page</a>";
            } else {
                echo "❌ Database entry failed.<br>";
                print_r($stmt->errorInfo());
            }
        } else {
            echo "❌ File upload failed. Check folder permissions.<br>";
        }
    } else {
        echo "❌ No file uploaded or upload error: " . $_FILES['test_file']['error'] . "<br>";
    }
}
?>
<form method="POST" enctype="multipart/form-data">
    <input type="file" name="test_file" required>
    <button type="submit" name="test_upload">Test Upload</button>
</form>