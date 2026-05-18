<?php
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $student_id = $_POST['student_id'];
    $message = $_POST['message'];
    
    // Insert into DB
    $stmt = $pdo->prepare("INSERT INTO notifications (student_id, message, is_read) VALUES (?, ?, 0)");
    $stmt->execute([$student_id, $message]);
    echo "Notification sent successfully!";
}
?>

<form method="POST">
    <input type="number" name="student_id" placeholder="Student ID" required>
    <textarea name="message" placeholder="Enter message here..." required></textarea>
    <button type="submit">Send Notification</button>
</form>