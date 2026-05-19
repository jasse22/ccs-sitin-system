<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: admin.php');
    exit;
}

// Fetch data
$stmt = $pdo->query("SELECT * FROM sit_in_history ORDER BY date DESC, login_time DESC");
$records = $stmt->fetchAll();

// Calculate totals
$total_sessions = count($records);
$total_hours = 0;
foreach ($records as $r) {
    if (!empty($r['login_time']) && !empty($r['logout_time'])) {
        $login = new DateTime($r['login_time']);
        $logout = new DateTime($r['logout_time']);
        $interval = $login->diff($logout);
        $total_hours += $interval->h + ($interval->i / 60);
    }
}

// Build HTML content
$html = '
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>CCS Sit-in Report</title>
    <style>
        * { margin: 0; padding: 0; }
        body { font-family: Arial, sans-serif; color: #1e2a38; padding: 30px; }
        .header { text-align: center; margin-bottom: 30px; border-bottom: 3px solid #1e3a5f; padding-bottom: 15px; }
        .header h1 { color: #1e3a5f; font-size: 24px; }
        .summary { display: flex; justify-content: center; gap: 40px; margin-bottom: 25px; }
        .summary-item { text-align: center; }
        .summary-item .number { font-size: 28px; font-weight: 700; color: #1e3a5f; }
        .summary-item .label { font-size: 12px; color: #9aa5b4; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        thead th { background: #1e3a5f; color: #fff; padding: 10px 12px; text-align: left; font-size: 11px; text-transform: uppercase; }
        tbody td { padding: 8px 12px; font-size: 12px; border-bottom: 1px solid #eef0f3; }
        tbody tr:nth-child(even) { background: #f8f9fa; }
        .footer { text-align: center; margin-top: 30px; font-size: 12px; color: #9aa5b4; border-top: 1px solid #eef0f3; padding-top: 15px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>📊 CCS Sit-in Report</h1>
    </div>

    <div class="summary">
        <div class="summary-item">
            <div class="number">' . $total_sessions . '</div>
            <div class="label">Total Sessions</div>
        </div>
        <div class="summary-item">
            <div class="number">' . round($total_hours, 1) . '</div>
            <div class="label">Total Hours</div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>ID Number</th>
                <th>Name</th>
                <th>Purpose</th>
                <th>Lab</th>
                <th>Login</th>
                <th>Logout</th>
                <th>Duration</th>
                <th>Date</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>';

$cnt = 0;
foreach ($records as $r) {
    $cnt++;
    $duration = '';
    if (!empty($r['login_time']) && !empty($r['logout_time'])) {
        $login = new DateTime($r['login_time']);
        $logout = new DateTime($r['logout_time']);
        $interval = $login->diff($logout);
        $duration = $interval->format('%h h %i min');
    }
    $isActive = empty($r['logout_time']);
    
    $html .= '
            <tr>
                <td>' . $cnt . '</td>
                <td><strong>' . htmlspecialchars($r['id_number']) . '</strong></td>
                <td>' . htmlspecialchars($r['fullname']) . '</td>
                <td>' . htmlspecialchars($r['sit_purpose']) . '</td>
                <td>' . htmlspecialchars($r['laboratory']) . '</td>
                <td>' . ($r['login_time'] ?? '—') . '</td>
                <td>' . ($r['logout_time'] ?? '—') . '</td>
                <td>' . ($duration ?: '—') . '</td>
                <td>' . htmlspecialchars($r['date']) . '</td>
                <td>' . ($isActive ? 'Active' : 'Done') . '</td>
            </tr>';
}

$html .= '
        </tbody>
    </table>

    <div class="footer">
        College of Computer Studies · University of Cebu · Sit-in Monitoring System
    </div>
</body>
</html>';

// Set headers for file download
header('Content-Type: text/html');
header('Content-Disposition: attachment; filename="CCS_Sit-in_Report_' . date('Y-m-d') . '.html"');

echo $html;
?>