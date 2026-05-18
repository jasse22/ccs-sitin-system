<?php
session_start();
require_once 'db.php';
if (!isset($_SESSION['student_id'])) exit;

$stmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE student_id = ? AND is_read = 0");
$stmt->execute([$_SESSION['student_id']]);
echo json_encode(['count' => $stmt->fetchColumn()]);
?>