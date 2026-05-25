<?php
session_start();
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: login.php'); exit;
}
require_once 'db.php';

// Refresh student data from DB
$stmt = $pdo->prepare("SELECT * FROM students WHERE id = ? LIMIT 1");
$stmt->execute([$_SESSION['student_id']]);
$student = $stmt->fetch();
if ($student) {
    $_SESSION['id_number']     = $student['id_number'];
    $_SESSION['firstname']     = $student['firstname'];
    $_SESSION['lastname']      = $student['lastname'];
    $_SESSION['middlename']    = $student['middlename'];
    $_SESSION['fullname']      = trim($student['firstname'].' '.$student['middlename'].' '.$student['lastname']);
    $_SESSION['course']        = $student['course'];
    $_SESSION['year_level']    = $student['year_level'];
    $_SESSION['email']         = $student['email'];
    $_SESSION['address']       = $student['address'];
    $_SESSION['session']       = $student['session'];
    $_SESSION['profile_photo'] = $student['profile_photo'] ?? null;
}

// Fetch notifications and announcements
$query = "
    SELECT message, created_at FROM notifications WHERE student_id = ? AND message NOT LIKE 'Feedback:%'
    UNION
    SELECT content AS message, created_at FROM announcements
    ORDER BY created_at DESC LIMIT 5
";
$notifListStmt = $pdo->prepare($query);
$notifListStmt->execute([$_SESSION['student_id']]);
$recent_notifications = $notifListStmt->fetchAll(PDO::FETCH_ASSOC);

$unreadStmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE student_id = ? AND is_read = 0 AND message NOT LIKE 'Feedback:%'");
$unreadStmt->execute([$_SESSION['student_id']]);
$unread_count = $unreadStmt->fetchColumn();

$announcements = $pdo->query("SELECT * FROM announcements ORDER BY created_at DESC")->fetchAll();
$photoSrc = (!empty($_SESSION['profile_photo']))
    ? 'uploads/profiles/'.htmlspecialchars($_SESSION['profile_photo'])
    : null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"/><meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>CCS | Home</title>
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
}

/* ── GLOBAL FIX: Force all text to be readable in dark mode ── */
body.dark-mode .card h2 {
    color: #ffffff !important;
}

body.dark-mode .card-head h2 {
    color: #ffffff !important;
}

body.dark-mode .info-label {
    color: #a0a8b8 !important;
}

body.dark-mode .info-value {
    color: #e8edf5 !important;
}

body.dark-mode .student-avatar .info-value {
    color: #e8edf5 !important;
}

body.dark-mode .stat-box .label {
    color: #a0a8b8 !important;
}

body.dark-mode .stat-box .number {
    color: #e8edf5 !important;
}

body.dark-mode .stat-box .sub-text {
    color: #a0a8b8 !important;
}

body.dark-mode .badge-pill {
    background: #2c303a !important;
    color: #7aa2f7 !important;
    border: 1px solid #4a4d57 !important;
}

body.dark-mode .stat-box {
    background: #2c303a !important;
    border-left-color: #7aa2f7 !important;
}

body.dark-mode .session-highlight {
    color: #7aa2f7 !important;
}

body.dark-mode .student-info-list .info-row {
    border-bottom-color: #2e364a !important;
}

body.dark-mode .info-icon svg {
    stroke: #7aa2f7 !important;
}

body.dark-mode .student-avatar .avatar-circle {
    background: #2c303a !important;
    border-color: #4a4d57 !important;
}

body.dark-mode .student-avatar .avatar-circle svg {
    stroke: #7aa2f7 !important;
}

body.dark-mode .card-head svg {
    stroke: #a0a8b8 !important;
}

body.dark-mode .stat-box[style*="border-left-color: #d4a017"] {
    background: #2c303a !important;
}

body.dark-mode .stat-box[style*="border-left-color: #d4a017"] .number {
    color: #f0c027 !important;
}

/* ── ANNOUNCEMENTS FIX ── */
body.dark-mode .ann-meta {
    color: var(--ann-meta-color) !important;
}

body.dark-mode .ann-bubble {
    background: #2c303a !important;
    border-color: #4a4d57 !important;
    color: var(--ann-bubble-text) !important;
}

body.dark-mode .ann-item {
    border-bottom-color: #2e364a !important;
}

body.dark-mode .ann-empty {
    color: #a0a8b8 !important;
}

/* ── RULES AND REGULATIONS FIX ── */
body.dark-mode .rules-header h3 {
    color: var(--rule-header-color) !important;
}

body.dark-mode .rules-header p {
    color: var(--rule-sub-color) !important;
}

body.dark-mode .rules-section-title {
    color: var(--rule-section-color) !important;
}

body.dark-mode .rules-intro {
    color: var(--rule-text-color) !important;
}

body.dark-mode .rule-item {
    color: var(--rule-text-color) !important;
}

body.dark-mode .rule-num {
    background: var(--rule-num-bg) !important;
    color: var(--rule-num-text) !important;
    border-color: #4a4d57 !important;
}

body.dark-mode .rules-header {
    border-bottom-color: #2e364a !important;
}

/* ── SOFTWARE AVAILABILITY FIX ── */
body.dark-mode .software-item {
    background: #2c303a !important;
    border-color: #4a4d57 !important;
}

body.dark-mode .software-item .software-name {
    color: #7aa2f7 !important;
}

body.dark-mode .software-item .software-version {
    color: #a0a8b8 !important;
}

body.dark-mode .software-item .software-lab {
    color: #a0a8b8 !important;
}

body.dark-mode .software-item .software-desc {
    color: #8e9bb3 !important;
}

body.dark-mode .lab-header {
    color: #e8edf5 !important;
    border-bottom-color: #2e364a !important;
}

body.dark-mode .software-grid .software-item {
    background: #2c303a !important;
    border-color: #4a4d57 !important;
}

body.dark-mode .software-grid .software-item div {
    color: #e8edf5 !important;
}

*{box-sizing:border-box;margin:0;padding:0;}
body{font-family:'Plus Jakarta Sans',sans-serif;background:var(--bg-color);color:var(--text-color);min-height:100vh;font-size:14px;transition:background 0.3s, color 0.3s;}
nav{background:var(--nav-bg);height:54px;padding:0 24px;display:flex;align-items:center;justify-content:space-between;position:sticky;top:0;z-index:100;transition:background 0.3s;}
.nav-brand{font-size:13.5px;font-weight:700;color:#fff;}
.nav-links{display:flex;align-items:center;gap:1px;}
.nav-links a{font-size:13px;color:rgba(255,255,255,0.7);text-decoration:none;padding:6px 10px;border-radius:5px;transition:all .15s;white-space:nowrap;cursor:pointer;}
.nav-links a:hover,.nav-links a.active{color:#fff;background:rgba(255,255,255,0.1);}

/* ── NOTIFICATION DROPDOWN ── */
.notif-wrapper{position:relative;display:inline-block;}
.notif-trigger {
    display: inline-flex !important;
    align-items: center !important;
    gap: 6px !important;
}
.notif-badge {
    background-color: #c53030;
    color: white;
    font-size: 11px;
    font-weight: 700;
    padding: 2px 6px;
    border-radius: 10px;
    line-height: 1;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}
.notif-dropdown{display:none;position:absolute;right:0;top:45px;background:var(--card-bg);border-radius:8px;box-shadow:var(--shadow);width:280px;z-index:1000;border:1px solid var(--border-color);padding:10px 0;max-height:300px;overflow-y:auto;}
.notif-header{padding:5px 15px 10px;font-size:12px;font-weight:700;color:#1e3a5f;border-bottom:1px solid var(--border-color);}
.notif-item{padding:10px 15px;border-bottom:1px solid var(--border-color);text-decoration:none;display:block;}
.notif-item:hover{background:var(--hover-bg);}
.notif-msg{
    font-size:12px;
    color:#1e2a38;
    font-weight:500;
    line-height: 1.4;
    word-wrap: break-word;
    white-space: normal;
}
.notif-time{font-size:10px;color:#9aa5b4;margin-top:4px;}
.view-all-link{text-align:center;font-size:12px;color:#1e3a5f;font-weight:700;margin-top:5px;display:block;text-decoration:none;}

.btn-logout{background:#c53030 !important;color:#fff !important;font-weight:600 !important;border-radius:5px;padding:6px 14px !important;margin-left:6px;}
.btn-logout:hover{background:#9b2c2c !important;}

/* ── DASHBOARD STYLES ── */
.dashboard{max-width:1260px;margin:0 auto;padding:22px 20px;display:grid;grid-template-columns:260px 1fr 280px;gap:18px;align-items:start;}
.card{background:var(--card-bg);border-radius:12px;border:1px solid var(--border-color);overflow:hidden;box-shadow:var(--shadow);transition:background 0.3s, border-color 0.3s;}
.card-head{background:var(--nav-bg);padding:11px 15px;display:flex;align-items:center;gap:7px;}
.card-head h2{color:#fff;font-size:12.5px;font-weight:600;}
.card-head svg{width:14px;height:14px;stroke:rgba(255,255,255,0.7);fill:none;stroke-width:2;stroke-linecap:round;stroke-linejoin:round;}
.student-avatar{display:flex;flex-direction:column;align-items:center;padding:20px 14px 16px;border-bottom:1px solid var(--border-color);}
.avatar-circle{width:84px;height:84px;border-radius:50%;background:#eef3f9;border:2px solid #c5d5e8;display:flex;align-items:center;justify-content:center;overflow:hidden;}
.avatar-circle img{width:100%;height:100%;object-fit:cover;}
.avatar-circle svg{width:38px;height:38px;stroke:#1e3a5f;fill:none;stroke-width:1.5;stroke-linecap:round;stroke-linejoin:round;}
.student-info-list{padding:10px 14px;}
.info-row{display:flex;align-items:flex-start;gap:9px;padding:7px 0;border-bottom:1px solid var(--border-color);}
.info-row:last-child{border-bottom:none;}
.info-icon{display:flex;align-items:flex-start;justify-content:center;width:16px;flex-shrink:0;padding-top:2px;}
.info-icon svg{width:12px;height:12px;stroke:#1e3a5f;fill:none;stroke-width:2;stroke-linecap:round;stroke-linejoin:round;}
.info-content{display:flex;flex-direction:column;gap:1px;min-width:0;flex:1;}
.info-label{font-size:10px;font-weight:700;color:#9aa5b4;text-transform:uppercase;letter-spacing:0.05em;}
.info-value{font-size:13px;color:var(--text-color);font-weight:600;word-break:break-all;overflow-wrap:anywhere;}
.session-highlight{color:#276749;font-size:14px;}

/* ── ANNOUNCEMENTS ── */
.ann-scroll{max-height:440px;overflow-y:auto;}
.ann-item{padding:13px 15px;border-bottom:1px solid var(--border-color);}
.ann-item:last-child{border-bottom:none;}
.ann-item:hover{background:var(--hover-bg);}
.ann-meta{font-size:11.5px;font-weight:600;color:var(--ann-meta-color);margin-bottom:6px;transition:color 0.3s;}
.ann-bubble{background:var(--bubble-bg);border:1px solid var(--bubble-border);border-radius:5px;padding:9px 11px;font-size:13px;color:var(--ann-bubble-text);line-height:1.6;transition:color 0.3s, background 0.3s;}
.ann-empty{font-size:13px;color:#9aa5b4;font-style:italic;}

/* ── RULES ── */
.rules-scroll{max-height:440px;overflow-y:auto;padding:14px 16px;}
.rules-header{text-align:center;margin-bottom:12px;padding-bottom:10px;border-bottom:1px solid var(--border-color);}
.rules-header h3{font-size:13px;font-weight:700;color:var(--rule-header-color);transition:color 0.3s;}
.rules-header p{font-size:10.5px;color:var(--rule-sub-color);margin-top:2px;transition:color 0.3s;}
.rules-section-title{font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:0.06em;color:var(--rule-section-color);margin:12px 0 8px;transition:color 0.3s;}
.rules-intro{font-size:13px;color:var(--rule-text-color);line-height:1.65;margin-bottom:10px;transition:color 0.3s;}
.rules-list{display:flex;flex-direction:column;gap:9px;}
.rule-item{display:flex;gap:9px;font-size:13px;color:var(--rule-text-color);line-height:1.6;transition:color 0.3s;}
.rule-num{min-width:20px;height:20px;border-radius:50%;background:var(--rule-num-bg);color:var(--rule-num-text);font-size:10.5px;font-weight:700;display:flex;align-items:center;justify-content:center;flex-shrink:0;margin-top:2px;border:1px solid #c5d5e8;transition:color 0.3s, background 0.3s;}

/* ── SIT-IN SUMMARY STATS ── */
.stat-box {
    background: var(--stat-bg);
    border-radius: 8px;
    padding: 10px 12px;
    border-left: 3px solid var(--nav-bg);
    transition: background 0.3s, border-color 0.3s;
}
.stat-box .label {
    font-size: 10px;
    font-weight: 500;
    color: var(--stat-label);
    text-transform: uppercase;
    letter-spacing: 0.05em;
}
.stat-box .number {
    font-size: 18px;
    font-weight: 700;
    color: var(--stat-number);
}
.stat-box .sub-text {
    font-size: 12px;
    font-weight: 400;
    color: var(--stat-label);
}

/* ── BADGE STYLES ── */
.badge-pill {
    display: inline-block;
    background: var(--badge-bg);
    color: var(--badge-text);
    padding: 3px 12px;
    border-radius: 14px;
    font-size: 12px;
    font-weight: 500;
    transition: background 0.3s, color 0.3s;
}

/* ── MEDIA QUERIES ── */
@media(max-width:960px){.dashboard{grid-template-columns:1fr 1fr;}.dashboard>.card:first-child{grid-column:1/-1;}}
@media(max-width:600px){.dashboard{grid-template-columns:1fr;}nav{padding:0 14px;}}
</style>
</head>
<body>
<?php include 'header.php'; ?>
<div class="dashboard">
  <!-- LEFT CARD: Student Information -->
  <div class="card">
    <div class="card-head">
      <svg viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
      <h2>Student Information</h2>
    </div>
    <div class="student-avatar">
      <div class="avatar-circle">
        <?php if ($photoSrc): ?>
          <img src="<?= $photoSrc ?>" alt="Profile"/>
        <?php else: ?>
          <svg viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
        <?php endif; ?>
      </div>
    </div>
    <div class="student-info-list">
      <div class="info-row">
        <span class="info-icon"><svg viewBox="0 0 24 24"><rect x="2" y="5" width="20" height="14" rx="2"/><circle cx="8.5" cy="10" r="2.5"/></svg></span>
        <div class="info-content"><span class="info-label">ID Number</span><span class="info-value"><?= htmlspecialchars($_SESSION['id_number'] ?? '') ?></span></div>
      </div>
      <div class="info-row">
        <span class="info-icon"><svg viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg></span>
        <div class="info-content"><span class="info-label">Full Name</span><span class="info-value"><?= htmlspecialchars($_SESSION['fullname'] ?? '') ?></span></div>
      </div>
      <div class="info-row">
        <span class="info-icon"><svg viewBox="0 0 24 24"><path d="M22 10v6M2 10l10-5 10 5-10 5z"/></svg></span>
        <div class="info-content"><span class="info-label">Course</span><span class="info-value"><?= htmlspecialchars($_SESSION['course'] ?? '') ?></span></div>
      </div>
      <div class="info-row">
        <span class="info-icon"><svg viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="3" y1="10" x2="21" y2="10"/></svg></span>
        <div class="info-content"><span class="info-label">Year Level</span><span class="info-value"><?= htmlspecialchars($_SESSION['year_level'] ?? '') ?></span></div>
      </div>
      <div class="info-row">
        <span class="info-icon"><svg viewBox="0 0 24 24"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg></span>
        <div class="info-content"><span class="info-label">Email</span><span class="info-value"><?= htmlspecialchars($_SESSION['email'] ?? '') ?></span></div>
      </div>
      <div class="info-row">
        <span class="info-icon"><svg viewBox="0 0 24 24"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg></span>
        <div class="info-content"><span class="info-label">Address</span><span class="info-value"><?= htmlspecialchars($_SESSION['address'] ?? '') ?></span></div>
      </div>
      <div class="info-row">
        <span class="info-icon"><svg viewBox="0 0 24 24"><rect x="2" y="3" width="20" height="14" rx="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg></span>
        <div class="info-content">
          <span class="info-label">Remaining Sessions</span>
          <span class="info-value session-highlight"><?= htmlspecialchars($_SESSION['session'] ?? '0') ?> / 30</span>
        </div>
      </div>
      
      <!-- ENHANCED SIT-IN SUMMARY -->
      <div style="border-top:2px solid var(--border-color);margin-top:14px;padding-top:14px;">
        <div style="display:flex;align-items:center;gap:8px;margin-bottom:10px;">
          <span style="font-size:11px;font-weight:700;color:#1e3a5f;text-transform:uppercase;letter-spacing:0.05em;">📊 Sit-in Summary</span>
          <span style="font-size:9px;color:#9aa5b4;background:#f0f2f5;padding:2px 8px;border-radius:10px;">Live</span>
        </div>
        
        <?php
        // Calculate stats from sit_in_history
        $statsStmt = $pdo->prepare("
            SELECT 
                COUNT(*) as total_sessions,
                SUM(TIMESTAMPDIFF(MINUTE, login_time, logout_time)) as total_minutes,
                MAX(TIMESTAMPDIFF(MINUTE, login_time, logout_time)) as longest_minutes,
                MIN(TIMESTAMPDIFF(MINUTE, login_time, logout_time)) as shortest_minutes
            FROM sit_in_history 
            WHERE student_id = ? AND logout_time IS NOT NULL
        ");
        $statsStmt->execute([$_SESSION['student_id']]);
        $stats = $statsStmt->fetch();
        
        $total_sessions = $stats['total_sessions'] ?? 0;
        $total_minutes = $stats['total_minutes'] ?? 0;
        $total_hours = floor($total_minutes / 60);
        $total_remaining_minutes = $total_minutes % 60;
        
        $avg_minutes = $total_sessions > 0 ? round($total_minutes / $total_sessions, 0) : 0;
        $avg_hours = floor($avg_minutes / 60);
        $avg_remaining_minutes = $avg_minutes % 60;
        
        $longest_minutes = $stats['longest_minutes'] ?? 0;
        $longest_hours = floor($longest_minutes / 60);
        $longest_remaining_minutes = $longest_minutes % 60;
        
        $shortest_minutes = $stats['shortest_minutes'] ?? 0;
        $shortest_hours = floor($shortest_minutes / 60);
        $shortest_remaining_minutes = $shortest_minutes % 60;
        ?>
        
        <!-- Stats Grid with Icons -->
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;">
          <!-- Total Hours -->
          <div class="stat-box">
            <div style="display:flex;align-items:center;gap:6px;margin-bottom:2px;">
              <span style="font-size:13px;">🕐</span>
              <span class="label">Total Hours</span>
            </div>
            <div class="number">
              <?= $total_hours ?><span class="sub-text">h</span>
              <?php if ($total_remaining_minutes > 0): ?>
                <span class="sub-text"> <?= $total_remaining_minutes ?>m</span>
              <?php endif; ?>
            </div>
          </div>
          
          <!-- Sessions -->
          <div class="stat-box">
            <div style="display:flex;align-items:center;gap:6px;margin-bottom:2px;">
              <span style="font-size:13px;">📋</span>
              <span class="label">Sessions</span>
            </div>
            <div class="number">
              <?= $total_sessions ?>
            </div>
          </div>
          
          <!-- Average Duration -->
          <div class="stat-box" style="border-left-color: #2a6f97;">
            <div style="display:flex;align-items:center;gap:6px;margin-bottom:2px;">
              <span style="font-size:13px;">📊</span>
              <span class="label">Avg Duration</span>
            </div>
            <div class="number">
              <?php if ($avg_hours > 0): ?>
                <?= $avg_hours ?><span class="sub-text">h</span>
              <?php endif; ?>
              <span class="sub-text"> <?= $avg_remaining_minutes ?>m</span>
            </div>
          </div>
          
          <!-- Longest Session -->
          <div class="stat-box" style="border-left-color: #c75b2a;">
            <div style="display:flex;align-items:center;gap:6px;margin-bottom:2px;">
              <span style="font-size:13px;">🏆</span>
              <span class="label">Longest</span>
            </div>
            <div class="number">
              <?php if ($longest_hours > 0): ?>
                <?= $longest_hours ?><span class="sub-text">h</span>
              <?php endif; ?>
              <span class="sub-text"> <?= $longest_remaining_minutes ?>m</span>
            </div>
          </div>
          
          <!-- Shortest Session -->
          <div class="stat-box" style="border-left-color: #2a8f2a;">
            <div style="display:flex;align-items:center;gap:6px;margin-bottom:2px;">
              <span style="font-size:13px;">⏱️</span>
              <span class="label">Shortest</span>
            </div>
            <div class="number">
              <?php if ($shortest_hours > 0): ?>
                <?= $shortest_hours ?><span class="sub-text">h</span>
              <?php endif; ?>
              <span class="sub-text"> <?= $shortest_remaining_minutes ?>m</span>
            </div>
          </div>
          
          <!-- Remaining Sessions -->
          <div style="background:linear-gradient(135deg, #fef9e7, #fdf3d1);padding:10px 12px;border-radius:8px;border-left:3px solid #d4a017;grid-column:1/3;">
            <div style="display:flex;align-items:center;justify-content:space-between;">
              <div style="display:flex;align-items:center;gap:6px;">
                <span style="font-size:13px;">💻</span>
                <span class="label">Remaining Sessions</span>
              </div>
              <div class="number" style="color:#d4a017;">
                <?= htmlspecialchars($_SESSION['session'] ?? '0') ?> / 30
              </div>
            </div>
            <!-- Progress bar -->
            <div style="margin-top:6px;height:4px;background:#e9ecef;border-radius:4px;overflow:hidden;">
              <div style="height:100%;background:linear-gradient(90deg, #d4a017, #f0c027);width:<?= min(100, (($_SESSION['session'] ?? 0) / 30) * 100) ?>%;border-radius:4px;"></div>
            </div>
          </div>
        </div>
        
        <!-- Small note for empty data -->
        <?php if ($total_sessions == 0): ?>
          <div style="text-align:center;padding:8px;font-size:12px;color:#9aa5b4;background:#f8f9fa;border-radius:6px;margin-top:8px;">
            No sit-in history yet. Start using the lab to build your stats!
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- MIDDLE CARD: Announcements -->
  <div class="card">
    <div class="card-head">
      <svg viewBox="0 0 24 24"><path d="M22 17H2a3 3 0 0 0 3-3V9a7 7 0 0 1 14 0v5a3 3 0 0 0 3 3z"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
      <h2>Announcements</h2>
    </div>
    <div class="ann-scroll">
      <?php if ($announcements): foreach ($announcements as $ann): ?>
      <div class="ann-item">
        <div class="ann-meta"><?= htmlspecialchars($ann['admin_name'] ?? 'CCS Admin') ?> &nbsp;|&nbsp; <?= date('M d, Y', strtotime($ann['created_at'])) ?></div>
        <?php if (!empty($ann['content'])): ?>
          <div class="ann-bubble"><?= htmlspecialchars($ann['content']) ?></div>
        <?php else: ?>
          <div class="ann-empty">No content provided.</div>
        <?php endif; ?>
      </div>
      <?php endforeach; else: ?>
      <div class="ann-item"><div class="ann-empty" style="padding:16px;">No announcements yet.</div></div>
      <?php endif; ?>
    </div>
  </div>

  <!-- RIGHT CARD: Rules and Regulations -->
  <div class="card">
    <div class="card-head">
      <svg viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
      <h2>Rules and Regulations</h2>
    </div>
    <div class="rules-scroll">
      <div class="rules-header">
        <h3>University of Cebu</h3>
        <p>COLLEGE OF COMPUTER STUDIES</p>
      </div>
      <div class="rules-section-title">Laboratory Rules and Regulations</div>
      <p class="rules-intro">To maintain a productive and orderly laboratory environment, please observe the following rules:</p>
      <div class="rules-list">
        <div class="rule-item"><span class="rule-num">1</span><span>Maintain silence, proper decorum, and discipline inside the laboratory. Mobile phones must be switched to silent mode.</span></div>
        <div class="rule-item"><span class="rule-num">2</span><span>Games are not allowed inside the lab. This includes computer-related games, card games, and other games that may disturb operations.</span></div>
        <div class="rule-item"><span class="rule-num">3</span><span>Internet surfing is allowed only with instructor permission. Downloading and installing software are strictly prohibited.</span></div>
        <div class="rule-item"><span class="rule-num">4</span><span>Eating, drinking, and smoking inside the laboratory are strictly prohibited.</span></div>
        <div class="rule-item"><span class="rule-num">5</span><span>Students are responsible for keeping their workstations clean and orderly at all times.</span></div>
        <div class="rule-item"><span class="rule-num">6</span><span>Any damage to laboratory equipment due to negligence shall be the responsibility of the student concerned.</span></div>
        <div class="rule-item"><span class="rule-num">7</span><span>Only authorized personnel are allowed to install or remove software and hardware components.</span></div>
        <div class="rule-item"><span class="rule-num">8</span><span>Students must log out and properly shut down computers after each use.</span></div>
      </div>
    </div>
  </div>

</div>

<script>
function toggleDropdown() {
    var dropdown = document.getElementById('notif-dropdown');
    var badge = document.getElementById('notif-badge');
    if (dropdown.style.display === 'block') {
        dropdown.style.display = 'none';
    } else {
        dropdown.style.display = 'block';
        if (badge) {
            badge.style.display = 'none';
        }
        fetch('mark_read.php');
    }
}

window.onclick = function(event) {
    if (!event.target.matches('.notif-wrapper a') && !event.target.matches('.notif-wrapper a *')) {
        var dropdown = document.getElementById('notif-dropdown');
        if (dropdown.style.display === 'block') {
            dropdown.style.display = "none";
        }
    }
}
</script>
</body>
</html>