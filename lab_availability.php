<?php
session_start();
require_once 'db.php';
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: login.php'); exit;
}

$lab = $_GET['lab'] ?? '524';
$computers = $pdo->prepare("SELECT * FROM computers WHERE lab_room = ? ORDER BY pc_number");
$computers->execute([$lab]);
$pcs = $computers->fetchAll();

$available = array_filter($pcs, fn($pc) => $pc['status'] === 'available');
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CCS | Lab Availability</title>
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
        [data-theme="dark"] {
            --bg-color: #1a1a2e;
            --text-color: #e0e0e0;
            --card-bg: #16213e;
            --nav-bg: #0f0f1a;
            --border-color: #2a2a4a;
            --shadow: 0 4px 12px rgba(0,0,0,0.5);
        }
        *{box-sizing:border-box;margin:0;padding:0;}
        body{font-family:'Plus Jakarta Sans',sans-serif;background:var(--bg-color);color:var(--text-color);padding:20px;transition:background 0.3s, color 0.3s;}
        nav{background:var(--nav-bg);height:54px;padding:0 24px;display:flex;align-items:center;justify-content:space-between;position:sticky;top:0;z-index:100;transition:background 0.3s;}
        .nav-brand{font-size:13.5px;font-weight:700;color:#fff;}
        .nav-links{display:flex;align-items:center;gap:1px;}
        .nav-links a{font-size:13px;color:rgba(255,255,255,0.7);text-decoration:none;padding:6px 10px;border-radius:5px;transition:all .15s;white-space:nowrap;}
        .nav-links a:hover,.nav-links a.active{color:#fff;background:rgba(255,255,255,0.1);}
        .container{max-width:800px;margin:0 auto;}
        h1{text-align:center;color:var(--text-color);margin-bottom:20px;}
        .lab-selector{display:flex;gap:10px;justify-content:center;margin-bottom:25px;}
        .lab-btn{padding:8px 20px;border:2px solid var(--border-color);border-radius:6px;background:var(--card-bg);color:var(--text-color);font-weight:600;cursor:pointer;transition:all .15s;text-decoration:none;}
        .lab-btn:hover,.lab-btn.active{background:var(--nav-bg);color:#fff;}
        .stats-bar{display:flex;justify-content:center;gap:30px;margin-bottom:20px;padding:15px;background:var(--card-bg);border-radius:8px;border:1px solid var(--border-color);}
        .stat-item{text-align:center;}
        .stat-number{font-size:24px;font-weight:700;color:var(--text-color);}
        .stat-label{font-size:12px;color:#9aa5b4;}
        .lab-grid{display:grid;grid-template-columns:repeat(5,1fr);gap:12px;margin-top:10px;}
        .pc-card{padding:15px;border-radius:8px;text-align:center;font-weight:600;transition:all .15s;}
        .pc-card:hover{transform:translateY(-2px);box-shadow:0 4px 12px rgba(0,0,0,0.08);}
        .available{background:#d4edda;color:#155724;border:2px solid #28a745;}
        .occupied{background:#f8d7da;color:#721c24;border:2px solid #dc3545;}
        .maintenance{background:#fff3cd;color:#856404;border:2px solid #ffc107;}
        .pc-number{font-size:18px;display:block;}
        .pc-status{font-size:11px;text-transform:uppercase;letter-spacing:0.05em;}
        .legend{display:flex;gap:20px;justify-content:center;margin-top:20px;font-size:12px;}
        .legend-item{display:flex;align-items:center;gap:5px;}
        .legend-dot{width:12px;height:12px;border-radius:50%;display:inline-block;}
        .back-link{display:block;text-align:center;margin-top:25px;color:var(--text-color);text-decoration:none;font-weight:600;}
        .back-link:hover{text-decoration:underline;}
        @media(max-width:600px){.lab-grid{grid-template-columns:repeat(4,1fr);}.stats-bar{gap:15px;flex-wrap:wrap;}}
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
        <button onclick="toggleDarkMode()" style="background:none;border:none;color:rgba(255,255,255,0.7);cursor:pointer;font-size:16px;padding:6px 10px;border-radius:5px;">🌙</button>
        <a href="logout.php" class="btn-logout">Log out</a>
    </div>
</nav>

<div class="container">
    <h1>🖥️ Lab Availability</h1>
    
    <div class="lab-selector">
        <a href="?lab=524" class="lab-btn <?= $lab === '524' ? 'active' : '' ?>">Lab 524</a>
        <a href="?lab=526" class="lab-btn <?= $lab === '526' ? 'active' : '' ?>">Lab 526</a>
        <a href="?lab=528" class="lab-btn <?= $lab === '528' ? 'active' : '' ?>">Lab 528</a>
    </div>
    
    <div class="stats-bar">
        <div class="stat-item">
            <div class="stat-number"><?= count($pcs) ?></div>
            <div class="stat-label">Total PCs</div>
        </div>
        <div class="stat-item">
            <div class="stat-number" style="color:#28a745;"><?= count($available) ?></div>
            <div class="stat-label">Available</div>
        </div>
        <div class="stat-item">
            <div class="stat-number" style="color:#dc3545;"><?= count($pcs) - count($available) ?></div>
            <div class="stat-label">Occupied</div>
        </div>
    </div>
    
    <div class="lab-grid">
        <?php foreach ($pcs as $pc): ?>
            <div class="pc-card <?= $pc['status'] ?>">
                <span class="pc-number">PC <?= $pc['pc_number'] ?></span>
                <span class="pc-status"><?= ucfirst($pc['status']) ?></span>
            </div>
        <?php endforeach; ?>
    </div>
    
    <div class="legend">
        <span class="legend-item"><span class="legend-dot" style="background:#28a745;"></span> Available</span>
        <span class="legend-item"><span class="legend-dot" style="background:#dc3545;"></span> Occupied</span>
        <span class="legend-item"><span class="legend-dot" style="background:#ffc107;"></span> Maintenance</span>
    </div>
    
    <a href="homepage.php" class="back-link">← Back to Dashboard</a>
</div>

<script src="darkmode.js"></script>
</body>
</html>