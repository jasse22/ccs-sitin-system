<?php
require_once 'db.php';

// Fetch all sit-in records
$stmt = $pdo->query("SELECT * FROM sit_in_history ORDER BY created_at DESC");
$records = $stmt->fetchAll();

// Calculate total hours
$total_hours = 0;
foreach ($records as $r) {
    if (!empty($r['login_time']) && !empty($r['logout_time'])) {
        $login = new DateTime($r['login_time']);
        $logout = new DateTime($r['logout_time']);
        $interval = $login->diff($logout);
        $total_hours += $interval->h + ($interval->i / 60);
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>CCS Sit-in Report</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 3px solid #1e3a5f; padding-bottom: 15px; }
        .logo { width: 80px; height: 80px; }
        .header h1 { font-size: 24px; color: #1e3a5f; margin: 5px 0; }
        .header h2 { font-size: 18px; color: #1e3a5f; margin: 5px 0; }
        .summary { display: flex; justify-content: center; gap: 40px; margin: 20px 0; }
        .summary-item { text-align: center; }
        .summary-item .number { font-size: 28px; font-weight: bold; color: #1e3a5f; }
        .summary-item .label { font-size: 12px; color: #666; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th { background: #1e3a5f; color: #fff; padding: 10px; text-align: left; font-size: 11px; }
        td { padding: 8px; border-bottom: 1px solid #ddd; font-size: 12px; }
        .status-active { color: #28a745; font-weight: bold; }
        .status-done { color: #6c757d; }
        .footer { text-align: center; margin-top: 30px; font-size: 12px; color: #666; border-top: 1px solid #ddd; padding-top: 15px; }
        @media print {
            .no-print { display: none; }
            body { padding: 0; }
            title { display: none; }
        }
    </style>
</head>
<body>
    <div class="header">
        <img src="uploads/uc-logo.png" alt="UC Logo" style="width:80px;height:80px;" onerror="this.style.display='none'">
        <h1>University of Cebu</h1>
        <h2>College of Computer Studies</h2>
        <p>Sit-in Monitoring System</p>
        <p style="font-size:11px;color:#888;">Generated: <?= date('F d, Y h:i A') ?></p>
    </div>
    
    <div class="summary">
        <div class="summary-item">
            <div class="number"><?= count($records) ?></div>
            <div class="label">Total Sessions</div>
        </div>
        <div class="summary-item">
            <div class="number"><?= round($total_hours, 1) ?></div>
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
                <th>Date</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php $cnt = 1; foreach ($records as $r): 
                $duration = '';
                if (!empty($r['login_time']) && !empty($r['logout_time'])) {
                    $login = new DateTime($r['login_time']);
                    $logout = new DateTime($r['logout_time']);
                    $interval = $login->diff($logout);
                    $duration = $interval->format('%h h %i min');
                }
                $isActive = empty($r['logout_time']);
            ?>
            <tr>
                <td><?= $cnt++ ?></td>
                <td><?= htmlspecialchars($r['id_number']) ?></td>
                <td><?= htmlspecialchars($r['fullname']) ?></td>
                <td><?= htmlspecialchars($r['sit_purpose']) ?></td>
                <td><?= htmlspecialchars($r['laboratory']) ?></td>
                <td><?= htmlspecialchars($r['login_time'] ?? '—') ?></td>
                <td><?= htmlspecialchars($r['logout_time'] ?? '—') ?></td>
                <td><?= htmlspecialchars($r['date'] ?? '—') ?></td>
                <td><?= $isActive ? 'Active' : 'Done' ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    
    <div class="footer">
        College of Computer Studies · University of Cebu · Sit-in Monitoring System
    </div>
</body>
</html>

<script>
// Automatically open print dialog when page loads
window.onload = function() {
    window.print();
};
</script>