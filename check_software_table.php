<?php
require_once 'db.php';

try {
    $stmt = $pdo->query("SELECT COUNT(*) FROM software");
    $count = $stmt->fetchColumn();
    echo "✅ Software table exists! Found $count records.";
} catch (PDOException $e) {
    echo "❌ Software table does NOT exist.<br>";
    echo "Error: " . $e->getMessage();
    echo "<br><br>Please run the SQL query to create the table.";
}
?>