<?php
// Turn on all error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Debugging Started</h1>";

// Check 1: Does config.php exist?
if (file_exists('config.php')) {
    echo "✅ config.php found.<br>";
} else {
    echo "❌ config.php missing.<br>";
}

// Check 2: Does db.php exist?
if (file_exists('db.php')) {
    echo "✅ db.php found.<br>";
} else {
    echo "❌ db.php missing.<br>";
}

// Check 3: Attempt connection using db.php (The correct way)
echo "<h3>Testing db.php connection:</h3>";
require_once 'db.php';
if (isset($pdo)) {
    echo "✅ Success: PDO connection is working!";
} else {
    echo "❌ Error: \$pdo variable not found.";
}
?>