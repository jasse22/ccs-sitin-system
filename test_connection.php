<?php
// Test 1: Testing Config.php method
include 'config.php';
if ($conn) {
    echo "<h1>✅ Config.php Connection Success!</h1>";
} else {
    echo "<h1>❌ Config.php Failed</h1>";
}

// Test 2: Testing DB.php method
require 'db.php';
if ($pdo) {
    echo "<h1>✅ DB.php Connection Success!</h1>";
} else {
    echo "<h1>❌ DB.php Failed</h1>";
}
?>