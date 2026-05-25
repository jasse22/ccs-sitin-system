<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CCS | Software Availability</title>
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
        }

        body.dark-mode {
            --bg-color: #1a1f2e;
            --text-color: #e8edf5;
            --card-bg: #242b3d;
            --nav-bg: #141824;
            --border-color: #2e364a;
            --shadow: 0 4px 12px rgba(0,0,0,0.3);
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
        .nav-brand { font-size: 13.5px; font-weight: 700; color: #fff; }
        .nav-links { display: flex; align-items: center; gap: 1px; }
        .nav-links a {
            font-size: 13px;
            color: rgba(255,255,255,0.7);
            text-decoration: none;
            padding: 6px 10px;
            border-radius: 5px;
            transition: all .15s;
        }
        .nav-links a:hover, .nav-links a.active { color: #fff; background: rgba(255,255,255,0.1); }
        .btn-logout { background: #dc3545 !important; color: #fff !important; border-radius: 5px; padding: 6px 14px !important; }
        .page-body { max-width: 1000px; margin: 0 auto; padding: 32px 20px 52px; }
        .page-title { text-align: center; margin-bottom: 28px; }
        .page-title h1 { font-size: 24px; font-weight: 800; color: var(--text-color); }
        .page-title p { font-size: 13px; color: #9aa5b4; margin-top: 4px; }
        .card { background: var(--card-bg); border-radius: 12px; box-shadow: var(--shadow); overflow: hidden; border: none; transition: background 0.3s; }
        .card-head { background: var(--nav-bg); padding: 14px 18px; }
        .card-head h3 { color: #fff; font-size: 14px; font-weight: 600; }
        .card-body { padding: 20px; }
        
        .software-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 16px; }
        .software-item {
            background: #f8f9fa; border-radius: 10px; padding: 16px; border: 1px solid #eef0f3;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .software-item:hover { transform: translateY(-2px); box-shadow: 0 4px 8px rgba(0,0,0,0.05); }
        .software-name { font-weight: 600; font-size: 14px; color: #1e3a5f; }
        .software-version { font-size: 12px; color: #4a5568; }
        .software-lab { font-size: 12px; color: #4a5568; margin-top: 4px; }
        .software-lab span { display: inline-block; background: #eef3f9; padding: 2px 10px; border-radius: 12px; font-size: 11px; }
        .software-desc { font-size: 12px; color: #8e9bb3; margin-top: 4px; font-style: italic; }
        
        body.dark-mode .software-item {
            background: #2c303a; border-color: #3a4050;
        }
        body.dark-mode .software-name { color: #7aa2f7; }
        body.dark-mode .software-version { color: #a0a8b8; }
        body.dark-mode .software-lab { color: #a0a8b8; }
        body.dark-mode .software-desc { color: #8e9bb3; }
        
        .lab-section { margin-bottom: 24px; }
        .lab-header { font-size: 18px; font-weight: 700; color: #1e3a5f; margin-bottom: 12px; padding-bottom: 8px; border-bottom: 2px solid #eef0f3; }
        body.dark-mode .lab-header { color: #e8edf5; border-bottom-color: #2e364a; }
        .empty-state { text-align: center; padding: 40px; color: #9aa5b4; font-size: 14px; }
        .empty-state-icon { font-size: 48px; margin-bottom: 12px; }
        
        @media (max-width: 600px) { nav { padding: 0 14px; } }
    </style>
</head>
<body>
<?php include 'header.php'; ?>
<div class="page-body">
    <div class="page-title">
        <h1>💻 Software Availability</h1>
        <p>View available software in each laboratory</p>
    </div>

    <div class="card">
        <div class="card-head"><h3>📋 Available Software</h3></div>
        <div class="card-body">
            <?php
            // Fetch all software grouped by lab room
            $stmt = $pdo->query("SELECT * FROM software ORDER BY lab_room, software_name");
            $allSoftware = $stmt->fetchAll();
            
            // Group software by lab room
            $softwareByLab = [];
            foreach ($allSoftware as $sw) {
                $lab = $sw['lab_room'];
                if (!isset($softwareByLab[$lab])) {
                    $softwareByLab[$lab] = [];
                }
                $softwareByLab[$lab][] = $sw;
            }
            ?>
            
            <?php if ($allSoftware): ?>
                <?php foreach ($softwareByLab as $lab => $softwareList): ?>
                <div class="lab-section">
                    <div class="lab-header">🔬 Lab <?= htmlspecialchars($lab) ?> (<?= count($softwareList) ?> software)</div>
                    <div class="software-grid">
                        <?php foreach ($softwareList as $sw): ?>
                        <div class="software-item">
                            <div class="software-name">📦 <?= htmlspecialchars($sw['software_name']) ?></div>
                            <div class="software-version">Version: <?= htmlspecialchars($sw['version'] ?? '1.0') ?></div>
                            <div class="software-lab"><span>📍 Lab <?= htmlspecialchars($sw['lab_room']) ?></span></div>
                            <?php if (!empty($sw['description'])): ?>
                            <div class="software-desc"><?= htmlspecialchars($sw['description']) ?></div>
                            <?php endif; ?>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="empty-state">
                    <div class="empty-state-icon">🔧</div>
                    <p>No software available yet. Please check back later.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
</body>
</html>