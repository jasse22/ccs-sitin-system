<?php
require_once 'db.php';

try {
    // Create settings table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS settings (
            id INT AUTO_INCREMENT PRIMARY KEY,
            setting_key VARCHAR(50) NOT NULL UNIQUE,
            setting_value VARCHAR(50) NOT NULL,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )
    ");
    
    // Insert default setting
    $pdo->exec("
        INSERT INTO settings (setting_key, setting_value) VALUES ('reservations_enabled', '1')
        ON DUPLICATE KEY UPDATE setting_value = setting_value
    ");
    
    echo "✅ Settings table created successfully!";
} catch (PDOException $e) {
    echo "❌ Error: " . $e->getMessage();
}
?>