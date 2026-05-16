<?php
session_start();
require_once 'db.php'; // Ensure this points to your database connection file

if (isset($_SESSION['student_id'])) {
    $stmt = $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE student_id = ? AND is_read = 0");
    $stmt->execute([$_SESSION['student_id']]);
}
?>