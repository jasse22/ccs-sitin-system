<?php
require_once 'db.php';

echo "Creating software_uploads table...<br>";

try {
    // Drop table if exists (start fresh)
    $pdo->exec("DROP TABLE IF EXISTS software_uploads");
    echo "✓ Dropped existing table (if any)<br>";
    
    // Create table
    $sql = "CREATE TABLE software_uploads (
        id INT AUTO_INCREMENT PRIMARY KEY,
        software_name VARCHAR(100) NOT NULL,
        version VARCHAR(50),
        lab_room VARCHAR(20) NOT NULL,
        description TEXT,
        file_name VARCHAR(255) NOT NULL,
        file_path VARCHAR(255) NOT NULL,
        uploaded_by VARCHAR(50),
        uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    
    $pdo->exec($sql);
    echo "✓ Table created successfully<br>";
    
    // Insert sample data
    $insert = "INSERT INTO software_uploads (software_name, version, lab_room, description, file_name, file_path, uploaded_by) VALUES
    ('Visual Studio Code', '1.89.0', '524', 'Code editor', 'VSCode_Setup.exe', 'uploads/software/VSCode_Setup.exe', 'Admin')";
    
    $pdo->exec($insert);
    echo "✓ Sample data inserted<br>";
    
    echo "<br><strong>✅ SUCCESS! Table 'software_uploads' is now ready.</strong><br>";
    echo "<a href='admin_software_upload.php'>Go to admin_software_upload.php →</a>";
    
} catch (PDOException $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
    echo "<br>Try creating the table manually in phpMyAdmin.";
}
?>