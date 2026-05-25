<?php
require_once 'db.php';

echo "<h2>Creating software_uploads table...</h2>";

try {
    // Create the table
    $sql = "CREATE TABLE IF NOT EXISTS software_uploads (
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
    echo "✅ Table 'software_uploads' created successfully!<br>";
    
    // Check if table exists
    $check = $pdo->query("SHOW TABLES LIKE 'software_uploads'");
    if ($check->rowCount() > 0) {
        echo "✅ Table verified: 'software_uploads' exists in database.<br>";
    } else {
        echo "❌ Table verification failed. Please check your database permissions.<br>";
    }
    
    echo "<br><a href='admin_software_upload.php'>Go to Software Upload Page →</a>";
    
} catch (PDOException $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
    echo "<br>Try creating the table manually in phpMyAdmin instead.";
}
?>