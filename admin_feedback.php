<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: admin.php');
    exit;
}

// Fetch all feedback from students with student details
$stmt = $pdo->query("
    SELECT f.*, s.firstname, s.lastname, s.id_number, s.course, s.year_level, s.profile_photo 
    FROM feedback f 
    JOIN students s ON f.student_id = s.id 
    ORDER BY f.created_at DESC
");
$feedback = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CCS | Student Feedback</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="darkmode.js"></script>
    <style>
        /* ── CSS Variables ── */
        :root {
            --bg-color: #f7f8fa;
            --text-color: #1e2a38;
            --card-bg: #ffffff;
            --nav-bg: #1e3a5f;
            --border-color: #e2e6ea;
            --shadow: 0 4px 12px rgba(0,0,0,0.08);
            --hover-bg: #f0f4f9;
            --table-header-bg: #1e3a5f;
            --table-header-text: #ffffff;
            --table-row-bg: #ffffff;
            --table-row-alt: #fafbfc;
            --table-text: #1e2a38;
            --table-border: #eef0f3;
        }

        body.dark-mode {
            --bg-color: #1a1f2e;
            --text-color: #e8edf5;
            --card-bg: #242b3d;
            --nav-bg: #141824;
            --border-color: #2e364a;
            --shadow: 0 4px 12px rgba(0,0,0,0.3);
            --hover-bg: #2a3145;
            --table-header-bg: #1e293b;
            --table-header-text: #e8edf5;
            --table-row-bg: #242b3d;
            --table-row-alt: #1a1f2e;
            --table-text: #e8edf5;
            --table-border: #2e364a;
        }

        /* ── NAVIGATION STYLES ── */
        nav {
            background: var(--nav-bg);
            height: 52px;
            padding: 0 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 200;
            transition: background 0.3s;
        }
        .nav-brand {
            font-size: 13.5px;
            font-weight: 700;
            color: #fff;
            white-space: nowrap;
        }
        .nav-links {
            display: flex;
            align-items: center;
            gap: 1px;
            flex-wrap: wrap;
        }
        .nav-links a {
            font-size: 12.5px;
            color: rgba(255,255,255,0.7);
            text-decoration: none;
            padding: 5px 9px;
            border-radius: 4px;
            white-space: nowrap;
            transition: all .15s;
        }
        .nav-links a:hover, .nav-links a.active {
            color: #fff;
            background: rgba(255,255,255,0.1);
        }
        .btn-logout-nav {
            background: #dc3545 !important;
            color: #fff !important;
            font-weight: 600 !important;
            border-radius: 4px;
            padding: 5px 13px !important;
            margin-left: 4px;
        }
        .btn-logout-nav:hover {
            background: #c82333 !important;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: var(--bg-color);
            color: var(--text-color);
            min-height: 100vh;
            transition: background 0.3s, color 0.3s;
        }
        
        .page-body {
            max-width: 1100px;
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
        
        .testimonial-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            padding: 20px;
        }
        
        .testimonial-card {
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 10px;
            padding: 20px;
            transition: all 0.3s ease;
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            gap: 12px;
        }
        .testimonial-card:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow);
        }
        
        .testimonial-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid var(--nav-bg);
            flex-shrink: 0;
        }
        .testimonial-avatar-placeholder {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: var(--nav-bg);
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 20px;
            flex-shrink: 0;
        }
        
        .testimonial-header {
            display: flex;
            align-items: center;
            gap: 12px;
            width: 100%;
        }
        
        .testimonial-name {
            font-size: 14px;
            font-weight: 600;
            color: var(--text-color);
        }
        .testimonial-details {
            font-size: 11px;
            color: #9aa5b4;
        }
        
        .testimonial-content {
            font-size: 14px;
            color: var(--text-color);
            line-height: 1.6;
            font-style: italic;
            padding: 10px 0;
            border-top: 1px solid var(--border-color);
            width: 100%;
        }
        
        .testimonial-date {
            font-size: 11px;
            color: #9aa5b4;
            margin-top: 4px;
        }
        
        .testimonial-stats {
            display: flex;
            gap: 15px;
            font-size: 11px;
            color: #9aa5b4;
            margin-top: 4px;
        }
        
        /* Dark mode overrides */
        body.dark-mode .testimonial-card {
            background: #2c303a;
            border-color: #4a4d57;
        }
        body.dark-mode .testimonial-name {
            color: #e8edf5;
        }
        body.dark-mode .testimonial-content {
            color: #e8edf5;
        }
        body.dark-mode .testimonial-avatar {
            border-color: #7aa2f7;
        }
        body.dark-mode .testimonial-avatar-placeholder {
            background: #2c303a;
            color: #7aa2f7;
            border: 1px solid #4a4d57;
        }
        
        .no-data {
            text-align: center;
            padding: 60px 20px;
            color: #9aa5b4;
            font-size: 14px;
        }
        .no-data-icon {
            font-size: 48px;
            margin-bottom: 12px;
        }
        
        @media (max-width: 640px) {
            .testimonial-grid {
                grid-template-columns: 1fr;
            }
            nav { padding: 0 12px; }
            .nav-brand { font-size: 12px; }
            .nav-links a { padding: 4px 6px; font-size: 11px; }
        }
    </style>
</head>
<body>
<?php include 'admin_header.php'; ?>

<div class="page-body">
    <div class="page-title">
        <h1>💬 Student Testimonials</h1>
        <p>View feedback and testimonials submitted by students</p>
    </div>
    
    <div class="card">
        <div class="card-head"><h3>📋 All Testimonials (<?= count($feedback) ?>)</h3></div>
        
        <?php if ($feedback): ?>
        <div class="testimonial-grid">
            <?php foreach ($feedback as $f): 
                $photoPath = !empty($f['profile_photo']) ? 'uploads/profiles/' . htmlspecialchars($f['profile_photo']) : null;
                $initials = strtoupper(substr($f['firstname'], 0, 1) . substr($f['lastname'], 0, 1));
            ?>
            <div class="testimonial-card">
                <div class="testimonial-header">
                    <?php if ($photoPath && file_exists($photoPath)): ?>
                        <img src="<?= $photoPath ?>" alt="<?= htmlspecialchars($f['firstname']) ?>" class="testimonial-avatar">
                    <?php else: ?>
                        <div class="testimonial-avatar-placeholder"><?= $initials ?></div>
                    <?php endif; ?>
                    <div>
                        <div class="testimonial-name"><?= htmlspecialchars($f['firstname'] . ' ' . $f['lastname']) ?></div>
                        <div class="testimonial-details">
                            <?= htmlspecialchars($f['id_number'] ?? '') ?> 
                            <?php if (!empty($f['course'])): ?>· <?= htmlspecialchars($f['course']) ?> <?= htmlspecialchars($f['year_level'] ?? '') ?><?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <div class="testimonial-content">
                    "<?= htmlspecialchars($f['message']) ?>"
                </div>
                
                <div class="testimonial-stats">
                    <span>📅 <?= date('M d, Y', strtotime($f['created_at'])) ?></span>
                    <span>🕐 <?= date('h:i A', strtotime($f['created_at'])) ?></span>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <div class="no-data">
            <div class="no-data-icon">📭</div>
            <p>No testimonials submitted yet.</p>
            <p style="font-size:12px;margin-top:8px;">Students can submit testimonials from their dashboard.</p>
        </div>
        <?php endif; ?>
    </div>
</div>

</body>
</html>