<?php
session_start();
require_once 'db.php';

// Admin check
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: admin.php');
    exit;
}

$format = $_GET['format'] ?? 'csv';
$type = $_GET['type'] ?? 'all';

// Base query
$query = "SELECT * FROM sit_in_history ORDER BY date DESC, login_time DESC";
$stmt = $pdo->query($query);
$data = $stmt->fetchAll();

if ($format === 'csv') {
    // CSV Export
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="sit_in_report_' . date('Y-m-d') . '.csv"');
    
    $output = fopen('php://output', 'w');
    fputcsv($output, ['ID', 'ID Number', 'Full Name', 'Purpose', 'Laboratory', 'PC Number', 'Login Time', 'Logout Time', 'Date', 'Duration (min)']);
    
    foreach ($data as $row) {
        $duration = '';
        if (!empty($row['login_time']) && !empty($row['logout_time'])) {
            $login = new DateTime($row['login_time']);
            $logout = new DateTime($row['logout_time']);
            $interval = $login->diff($logout);
            $duration = $interval->format('%h h %i min');
        }
        
        fputcsv($output, [
            $row['id'],
            $row['id_number'],
            $row['fullname'],
            $row['sit_purpose'],
            $row['laboratory'],
            $row['pc_number'] ?? '—',
            $row['login_time'] ?? '—',
            $row['logout_time'] ?? '—',
            $row['date'],
            $duration
        ]);
    }
    fclose($output);
    exit;
    
} elseif ($format === 'pdf') {
    // PDF Export using HTML + dompdf (simplified)
    // You'll need to install dompdf via composer for full PDF support
    // For now, we'll redirect to a message
    header('Location: admin_dashboard.php?page=reports&msg=pdf_not_ready');
    exit;
}
?>