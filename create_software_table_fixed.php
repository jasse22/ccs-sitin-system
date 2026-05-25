<?php
require_once 'db.php';

echo "Attempting to create software table...<br>";

// Create table with explicit database name
$sql = "CREATE TABLE IF NOT EXISTS `ccs_sitin`.`software` (
    id INT AUTO_INCREMENT PRIMARY KEY,
    software_name VARCHAR(100) NOT NULL,
    version VARCHAR(50),
    lab_room VARCHAR(20) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

try {
    $pdo->exec($sql);
    echo "✅ Table 'software' created successfully!<br>";
    
    // Insert sample data
    $insert = "INSERT INTO software (software_name, version, lab_room, description) VALUES
    ('Visual Studio Code', '1.89.0', '524', 'Code editing. Redefined.'),
    ('Python', '3.11.4', '524', 'Programming language'),
    ('MySQL Workbench', '8.0.35', '526', 'Database management'),
    ('Eclipse IDE', '2024-03', '526', 'Java development environment'),
    ('Android Studio', '2023.3', '528', 'Android app development'),
    ('Git', '2.45.0', '524', 'Version control system'),
    ('Node.js', '20.11.0', '526', 'JavaScript runtime'),
    ('Postman', '11.0.0', '528', 'API development tool'),
    ('Docker Desktop', '4.27.0', '528', 'Container platform'),
    ('XAMPP', '8.2.12', '524', 'Apache + PHP + MySQL');";
    
    $pdo->exec($insert);
    echo "✅ Sample data inserted successfully!<br>";
    echo "<br><a href='software_availability.php'>Go to Software Availability page →</a>";
    
} catch (PDOException $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
    echo "Try running this SQL directly in phpMyAdmin instead.";
}
?>