<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

// Fetch student's sit-in history
$sessStmt = $pdo->prepare("
    SELECT * FROM sit_in_history 
    WHERE student_id = ? 
    ORDER BY created_at DESC 
    LIMIT 50
");
$sessStmt->execute([$_SESSION['student_id']]);
$sessions = $sessStmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CCS | My Sessions</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="darkmode.js"></script>
    <style>
        :root {
            --bg-color: #f7f8fa;
            --text-color: #1e2a38;
            --card-bg: #ffffff;
            --nav-bg: #1e3a5f;
            --border-color: #e2e6ea;
            --shadow: 0 4px 12px rgba(0,0,0,0.08);
            --input-bg: #ffffff;
            --input-border: #d0d7e2;
            --hover-bg: #f0f4f9;
            --bubble-bg: #fafbfc;
            --bubble-border: #eef0f3;
            --stat-bg: #eef3f9;
            --stat-text: #1e3a5f;
            --badge-bg: #eef3f9;
            --badge-text: #1e3a5f;
            --stat-number: #1e3a5f;
            --stat-label: #4a5568;
            --ann-meta-color: #1e3a5f;
            --ann-bubble-text: #1e2a38;
            --rule-header-color: #1e3a5f;
            --rule-sub-color: #4a5568;
            --rule-section-color: #1e3a5f;
            --rule-text-color: #1e2a38;
            --rule-num-bg: #eef3f9;
            --rule-num-text: #1e3a5f;
            --table-header-bg: #1e3a5f;
            --table-header-text: #ffffff;
            --table-row-bg: #ffffff;
            --table-row-alt: #fafbfc;
            --table-text: #1e2a38;
            --table-border: #eef0f3;
            --table-hover: #f0f4f9;
        }

        body.dark-mode {
            --bg-color: #1a1f2e;
            --text-color: #e8edf5;
            --card-bg: #242b3d;
            --nav-bg: #141824;
            --border-color: #2e364a;
            --shadow: 0 4px 12px rgba(0,0,0,0.4);
            --input-bg: #242b3d;
            --input-border: #2e364a;
            --hover-bg: #2a3248;
            --bubble-bg: #242b3d;
            --bubble-border: #2e364a;
            --stat-bg: #242b3d;
            --stat-text: #e8edf5;
            --badge-bg: #242b3d;
            --badge-text: #e8edf5;
            --stat-number: #f0f6fc;
            --stat-label: #8e9bb3;
            --ann-meta-color: #7aa2f7;
            --ann-bubble-text: #e8edf5;
            --rule-header-color: #e8edf5;
            --rule-sub-color: #a0a8b8;
            --rule-section-color: #7aa2f7;
            --rule-text-color: #e8edf5;
            --rule-num-bg: #2c303a;
            --rule-num-text: #7aa2f7;
            --table-header-bg: #1e293b;
            --table-header-text: #e8edf5;
            --table-row-bg: #242b3d;
            --table-row-alt: #1a1f2e;
            --table-text: #e8edf5;
            --table-border: #2e364a;
            --table-hover: #2a3248;
        }

        /* ── NAVIGATION STYLES ── */
        nav {
            background: var(--nav-bg);
            height: 54px;
            padding: 0 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 100;
            transition: background 0.3s;
        }
        .nav-brand {
            font-size: 13.5px;
            font-weight: 700;
            color: #fff;
        }
        .nav-links {
            display: flex;
            align-items: center;
            gap: 1px;
        }
        .nav-links a {
            font-size: 13px;
            color: rgba(255,255,255,0.7);
            text-decoration: none;
            padding: 6px 10px;
            border-radius: 5px;
            transition: all .15s;
            white-space: nowrap;
            cursor: pointer;
        }
        .nav-links a:hover, .nav-links a.active {
            color: #fff;
            background: rgba(255,255,255,0.1);
        }
        .btn-logout {
            background: #c53030 !important;
            color: #fff !important;
            font-weight: 600 !important;
            border-radius: 5px;
            padding: 6px 14px !important;
            margin-left: 6px;
        }
        .btn-logout:hover {
            background: #9b2c2c !important;
        }

        /* ── DASHBOARD STYLES ── */
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: var(--bg-color);
            color: var(--text-color);
            min-height: 100vh;
            font-size: 14px;
            transition: background 0.3s, color 0.3s;
        }
        
        .page-body {
            max-width: 1000px;
            margin: 0 auto;
            padding: 30px 20px 52px;
        }
        
        .page-title {
            text-align: center;
            margin-bottom: 30px;
        }
        .page-title h1 {
            font-size: 24px;
            font-weight: 700;
            color: var(--text-color);
            transition: color 0.3s;
        }
        .page-title p {
            font-size: 13px;
            color: #9aa5b4;
            margin-top: 4px;
        }
        
        .card {
            background: var(--card-bg);
            border-radius: 12px;
            box-shadow: var(--shadow);
            overflow: hidden;
            border: 1px solid var(--border-color);
            transition: background 0.3s, border-color 0.3s, box-shadow 0.3s;
        }
        
        .table-wrap {
            overflow-x: auto;
            padding: 0;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        thead th {
            background: var(--table-header-bg);
            color: var(--table-header-text);
            font-size: 11px;
            font-weight: 700;
            padding: 12px 16px;
            text-align: left;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            border-bottom: 1px solid var(--table-border);
            transition: background 0.3s, color 0.3s;
        }
        
        tbody tr {
            border-bottom: 1px solid var(--table-border);
            transition: background 0.2s;
            background: var(--table-row-bg);
        }
        tbody tr:nth-child(even) {
            background: var(--table-row-alt);
        }
        tbody tr:hover {
            background: var(--table-hover);
        }
        
        tbody td {
            padding: 10px 16px;
            font-size: 13px;
            color: var(--table-text);
            transition: color 0.3s;
        }
        
        .no-data {
            text-align: center;
            padding: 40px 20px;
            color: #9aa5b4;
            font-size: 14px;
            font-style: italic;
        }
        
        /* Status badges */
        .status-active {
            display: inline-block;
            padding: 2px 12px;
            border-radius: 14px;
            font-size: 12px;
            font-weight: 500;
            background: #d4edda;
            color: #155724;
        }
        .status-done {
            display: inline-block;
            padding: 2px 12px;
            border-radius: 14px;
            font-size: 12px;
            font-weight: 500;
            background: #f1f5f9;
            color: #64748b;
        }
        
        body.dark-mode .status-active {
            background: #2c303a;
            color: #7aa2f7;
            border: 1px solid #4a4d57;
        }
        body.dark-mode .status-done {
            background: #2c303a;
            color: #a0a8b8;
            border: 1px solid #4a4d57;
        }
        
        @media (max-width: 640px) {
            nav { padding: 0 14px; }
            thead th, tbody td { padding: 8px 10px; font-size: 11px; }
        }
    </style>
</head>
<body>
<?php include 'header.php'; ?>

<div class="page-body">
    <div class="page-title">
        <h1>📋 My Sessions</h1>
        <p>Complete record of all your laboratory sit-in sessions</p>
    </div>
    
    <div class="card">
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Time-in</th>
                        <th>Time-out</th>
                        <th>Duration</th>
                        <th>PC No.</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($sessions): foreach ($sessions as $s): 
                        $duration = '';
                        if (!empty($s['login_time']) && !empty($s['logout_time'])) {
                            $login = new DateTime($s['login_time']);
                            $logout = new DateTime($s['logout_time']);
                            $interval = $login->diff($logout);
                            $duration = $interval->format('%h h %i min');
                        }
                        $isActive = empty($s['logout_time']);
                    ?>
                    <tr>
                        <td><?= htmlspecialchars($s['date'] ?? '—') ?></td>
                        <td><?= htmlspecialchars($s['login_time'] ?? '—') ?></td>
                        <td><?= htmlspecialchars($s['logout_time'] ?? '—') ?></td>
                        <td><?= $duration ?: '—' ?></td>
                        <td><?= htmlspecialchars($s['pc_number'] ?? '—') ?></td>
                        <td>
                            <?php if ($isActive): ?>
                                <span class="status-active">Active</span>
                            <?php else: ?>
                                <span class="status-done">Done</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; else: ?>
                    <tr>
                        <td colspan="6" class="no-data">📭 No sessions yet.</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <div style="padding:14px 18px;border-top:1px solid var(--border-color);display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:8px;background:var(--card-bg);transition:background 0.3s;">
            <span style="font-size:12.5px;color:var(--text-color);font-weight:500;">Showing <?= count($sessions) ?> session<?= count($sessions)!==1?'s':'' ?></span>
        </div>
    </div>
</div>
</body>
</html>