<?php
require_once 'db.php';

try {
    // Create the software table
    $sql = "
    CREATE TABLE IF NOT EXISTS software (
        id INT AUTO_INCREMENT PRIMARY KEY,
        software_name VARCHAR(100) NOT NULL,
        version VARCHAR(50),
        lab_room VARCHAR(20) NOT NULL,
        description TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    );
    ";
    
    $pdo->exec($sql);
    echo "✅ Software table created successfully!<br>";
    
    // Insert sample data
    $insertSql = "
    INSERT INTO software (software_name, version, lab_room, description) VALUES
    ('Visual Studio Code', '1.89.0', '524', 'Code editing. Redefined.'),
    ('Python', '3.11.4', '524', 'Programming language'),
    ('MySQL Workbench', '8.0.35', '526', 'Database management'),
    ('Eclipse IDE', '2024-03', '526', 'Java development environment'),
    ('Android Studio', '2023.3', '528', 'Android app development'),
    ('Git', '2.45.0', '524', 'Version control system'),
    ('Node.js', '20.11.0', '526', 'JavaScript runtime'),
    ('Postman', '11.0.0', '528', 'API development tool'),
    ('Docker Desktop', '4.27.0', '528', 'Container platform'),
    ('XAMPP', '8.2.12', '524', 'Apache + PHP + MySQL');
    ";
    
    $pdo->exec($insertSql);
    echo "✅ Sample software data inserted successfully!<br>";
    echo "<br><a href='admin_software.php'>Go to Admin Software Page →</a>";
    
} catch (PDOException $e) {
    echo "❌ Error: " . $e->getMessage();
}
?>