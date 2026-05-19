<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: login.php'); exit;
}

$stmt = $pdo->prepare("
    SELECT n.message, n.created_at, 
           CONCAT(s.firstname, ' ', s.lastname) AS student_name
    FROM notifications n
    JOIN students s ON n.student_id = s.id
    WHERE n.message LIKE 'Feedback:%'
    ORDER BY n.created_at DESC
");
$stmt->execute();
$testimonials = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CCS | Testimonials</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet"/>
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
        body{font-family:'Plus Jakarta Sans',sans-serif;background:var(--bg-color);color:var(--text-color);min-height:100vh;transition:background 0.3s, color 0.3s;}
        nav{background:var(--nav-bg);height:54px;padding:0 24px;display:flex;align-items:center;justify-content:space-between;position:sticky;top:0;z-index:100;transition:background 0.3s;}
        .nav-brand{font-size:13.5px;font-weight:700;color:#fff;}
        .nav-links{display:flex;align-items:center;gap:1px;}
        .nav-links a{font-size:13px;color:rgba(255,255,255,0.7);text-decoration:none;padding:6px 10px;border-radius:5px;transition:all .15s;white-space:nowrap;}
        .nav-links a:hover,.nav-links a.active{color:#fff;background:rgba(255,255,255,0.1);}
        .btn-logout{background:#c53030 !important;color:#fff !important;font-weight:600 !important;border-radius:5px;padding:6px 14px !important;margin-left:6px;}
        .btn-logout:hover{background:#9b2c2c !important;}
        .container{max-width:800px;margin:0 auto;padding:40px 20px;}
        .page-title{text-align:center;margin-bottom:30px;}
        .page-title h1{font-size:24px;font-weight:700;color:var(--text-color);}
        .page-title p{color:#9aa5b4;font-size:14px;margin-top:4px;}
        .testimonial-grid{display:grid;grid-template-columns:1fr 1fr;gap:20px;}
        .testimonial-card{background:var(--card-bg);border-radius:12px;padding:20px;box-shadow:var(--shadow);border:1px solid var(--border-color);transition:all 0.2s;}
        .testimonial-card:hover{transform:translateY(-2px);}
        .testimonial-header{display:flex;align-items:center;gap:12px;margin-bottom:10px;}
        .avatar{width:40px;height:40px;border-radius:50%;background:var(--nav-bg);color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:16px;}
        .student-name{font-weight:600;color:var(--text-color);}
        .testimonial-date{font-size:11px;color:#9aa5b4;}
        .testimonial-content{font-size:14px;color:var(--text-color);line-height:1.6;font-style:italic;}
        .empty-state{text-align:center;padding:40px;color:#9aa5b4;}
        .back-link{display:block;text-align:center;margin-top:20px;color:var(--text-color);text-decoration:none;font-weight:600;}
        .back-link:hover{text-decoration:underline;}
        @media(max-width:600px){.testimonial-grid{grid-template-columns:1fr;}}
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
        <a href="testimonials.php" class="active">Testimonials</a>
        <button onclick="toggleDarkMode()" style="background:none;border:none;color:rgba(255,255,255,0.7);cursor:pointer;font-size:16px;padding:6px 10px;border-radius:5px;">🌙</button>
        <a href="logout.php" class="btn-logout">Log out</a>
    </div>
</nav>

<div class="container">
    <div class="page-title">
        <h1>💬 Student Testimonials</h1>
        <p>What students are saying about their laboratory experience</p>
    </div>
    
    <?php if ($testimonials): ?>
        <div class="testimonial-grid">
            <?php foreach ($testimonials as $t): ?>
                <div class="testimonial-card">
                    <div class="testimonial-header">
                        <div class="avatar"><?= substr($t['student_name'], 0, 1) ?></div>
                        <div>
                            <div class="student-name"><?= htmlspecialchars($t['student_name']) ?></div>
                            <div class="testimonial-date"><?= date('M d, Y', strtotime($t['created_at'])) ?></div>
                        </div>
                    </div>
                    <div class="testimonial-content">
                        "<?= htmlspecialchars(str_replace("Feedback: ", "", $t['message'])) ?>"
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="empty-state">
            <p>No testimonials yet. Be the first to share your experience!</p>
        </div>
    <?php endif; ?>
    
    <a href="homepage.php" class="back-link">← Back to Dashboard</a>
</div>

<script src="darkmode.js"></script>
</body>
</html>