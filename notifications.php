<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

// Fetch only this student's notifications, newest first
$stmt = $pdo->prepare("SELECT * FROM notifications WHERE student_id = ? ORDER BY created_at DESC");
$stmt->execute([$_SESSION['student_id']]);
$notifications = $stmt->fetchAll();

// Mark all as read when viewed
$pdo->prepare("UPDATE notifications SET is_read = 1 WHERE student_id = ?")->execute([$_SESSION['student_id']]);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CCS | Notifications</title>
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
            --hover-bg: #f0f2f5;
        }

        body.dark-mode {
            --bg-color: #1a1f2e;
            --text-color: #e8edf5;
            --card-bg: #2c303a;
            --nav-bg: #141824;
            --border-color: #3a4050;
            --shadow: 0 4px 12px rgba(0,0,0,0.6);
            --hover-bg: #333742;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: var(--bg-color);
            color: var(--text-color);
            min-height: 100vh;
            transition: background 0.3s, color 0.3s;
        }
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
        }
        .nav-links a:hover,
        .nav-links a.active {
            color: #fff;
            background: rgba(255,255,255,0.1);
        }
        .btn-logout {
            background: #dc3545 !important;
            color: #fff !important;
            font-weight: 600 !important;
            border-radius: 5px;
            padding: 6px 14px !important;
            margin-left: 6px;
        }
        .btn-logout:hover {
            background: #c82333 !important;
        }
        .page-body {
            max-width: 800px;
            margin: 0 auto;
            padding: 32px 20px 52px;
        }
        .page-title {
            font-size: 22px;
            font-weight: 800;
            color: var(--text-color);
            margin-bottom: 24px;
            text-align: center;
            transition: color 0.3s;
        }
        .page-title span {
            display: block;
            font-size: 13px;
            font-weight: 400;
            color: #8e9bb3;
            margin-top: 4px;
        }
        .card {
            background: var(--card-bg);
            border-radius: 12px;
            box-shadow: var(--shadow);
            overflow: hidden;
            border: none;
            transition: background 0.3s, box-shadow 0.3s;
        }
        .notif-item {
            padding: 16px 20px;
            border-bottom: 1px solid var(--border-color);
            transition: background 0.2s;
        }
        .notif-item:hover {
            background: var(--hover-bg);
        }
        .notif-item:last-child {
            border-bottom: none;
        }
        .notif-message {
            font-size: 14px;
            color: var(--text-color);
            font-weight: 500;
            line-height: 1.5;
            transition: color 0.3s;
        }
        .notif-time {
            font-size: 12px;
            color: #8e9bb3;
            margin-top: 4px;
            transition: color 0.3s;
        }
        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: #8e9bb3;
        }
        .empty-state svg {
            width: 48px;
            height: 48px;
            stroke: #8e9bb3;
            fill: none;
            stroke-width: 1.5;
            margin-bottom: 12px;
        }
        .back-link {
            display: block;
            text-align: center;
            margin-top: 20px;
            color: var(--text-color);
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s;
        }
        .back-link:hover {
            text-decoration: underline;
        }
        @media (max-width: 600px) {
            nav { padding: 0 14px; }
            .notif-item { padding: 14px 16px; }
        }
    </style>
</head>
<body>
<nav>
    <div class="nav-brand">CCS Sit-in Monitoring System</div>
    <div class="nav-links">
        <a href="homepage.php">Home</a>
        <a href="profile.php">Edit Profile</a>
        <a href="history.php">History</a>
        <a href="reservation.php">Reservation</a>
        <a href="notifications.php" class="active">Notifications</a>
        <button onclick="toggleDarkMode()" style="background:none;border:none;color:rgba(255,255,255,0.7);cursor:pointer;font-size:16px;padding:6px 10px;border-radius:5px;transition:all .15s;" onmouseover="this.style.background='rgba(255,255,255,0.1)'" onmouseout="this.style.background='none'">
            🌙
        </button>
        <a href="logout.php" class="btn-logout">Log out</a>
    </div>
</nav>

<div class="page-body">
    <div class="page-title">
        🔔 Notifications
        <span>Stay updated with your reservations and system announcements</span>
    </div>
    
    <div class="card">
        <?php if ($notifications): ?>
            <?php foreach ($notifications as $n): ?>
                <div class="notif-item">
                    <div class="notif-message">
                        <?= htmlspecialchars($n['message']) ?>
                    </div>
                    <div class="notif-time">
                        <?= date('M d, Y g:i A', strtotime($n['created_at'])) ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="empty-state">
                <svg viewBox="0 0 24 24"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
                <p>No notifications yet.</p>
            </div>
        <?php endif; ?>
    </div>
    
    <a href="homepage.php" class="back-link">← Back to Dashboard</a>
</div>
</body>
</html>