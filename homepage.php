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

// Fetch the latest 5 items combined from notifications (excluding feedback logs) and announcements
$query = "
    SELECT message, created_at FROM notifications WHERE student_id = ? AND message NOT LIKE 'Feedback:%'
    UNION
    SELECT content AS message, created_at FROM announcements
    ORDER BY created_at DESC LIMIT 5
";

$notifListStmt = $pdo->prepare($query);
$notifListStmt->execute([$_SESSION['student_id']]);
$recent_notifications = $notifListStmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch unread count for the notification badge (ignoring student feedback logs)
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
<style>
*{box-sizing:border-box;margin:0;padding:0;}
body{font-family:'Plus Jakarta Sans',sans-serif;background:#f7f8fa;color:#1e2a38;min-height:100vh;font-size:14px;}
nav{background:#1e3a5f;height:54px;padding:0 24px;display:flex;align-items:center;justify-content:space-between;position:sticky;top:0;z-index:100;}
.nav-brand{font-size:13.5px;font-weight:700;color:#fff;}
.nav-links{display:flex;align-items:center;gap:1px;}
.nav-links a{font-size:13px;color:rgba(255,255,255,0.7);text-decoration:none;padding:6px 10px;border-radius:5px;transition:all .15s;white-space:nowrap;cursor:pointer;}
.nav-links a:hover,.nav-links a.active{color:#fff;background:rgba(255,255,255,0.1);}

/* --- FIXED BADGE AND NAVIGATION WRAPPER ALIGNMENT --- */
.notif-wrapper{position:relative;display:inline-block;}

/* Flexbox design forces the inline items to stay perfectly centered on a horizontal line row */
.notif-trigger {
    display: inline-flex !important;
    align-items: center !important;
    gap: 6px !important; /* Creates clear horizontal spacing between the word and number */
}

/* Customized pill layout that handles double digits nicely without dropping down */
.notif-badge {
    background-color: #c53030; /* Matches your logout-red color variable */
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

.notif-dropdown{display:none;position:absolute;right:0;top:45px;background:#fff;border-radius:8px;box-shadow:0 4px 12px rgba(0,0,0,0.15);width:280px;z-index:1000;border:1px solid #e2e6ea;padding:10px 0;max-height:300px;overflow-y:auto;}
.notif-header{padding:5px 15px 10px;font-size:12px;font-weight:700;color:#1e3a5f;border-bottom:1px solid #f0f2f5;}
.notif-item{padding:10px 15px;border-bottom:1px solid #f0f2f5;text-decoration:none;display:block;}
.notif-item:hover{background:#f8f9fa;}
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

/* --- DASHBOARD STYLES --- */
.dashboard{max-width:1260px;margin:0 auto;padding:22px 20px;display:grid;grid-template-columns:260px 1fr 280px;gap:18px;align-items:start;}
.card{background:#fff;border-radius:8px;border:1px solid #e2e6ea;overflow:hidden;}
.card-head{background:#1e3a5f;padding:11px 15px;display:flex;align-items:center;gap:7px;}
.card-head h2{color:#fff;font-size:12.5px;font-weight:600;}
.card-head svg{width:14px;height:14px;stroke:rgba(255,255,255,0.7);fill:none;stroke-width:2;stroke-linecap:round;stroke-linejoin:round;}
.student-avatar{display:flex;flex-direction:column;align-items:center;padding:20px 14px 16px;border-bottom:1px solid #f0f2f5;}
.avatar-circle{width:84px;height:84px;border-radius:50%;background:#eef3f9;border:2px solid #c5d5e8;display:flex;align-items:center;justify-content:center;overflow:hidden;}
.avatar-circle img{width:100%;height:100%;object-fit:cover;}
.avatar-circle svg{width:38px;height:38px;stroke:#1e3a5f;fill:none;stroke-width:1.5;stroke-linecap:round;stroke-linejoin:round;}
.student-info-list{padding:10px 14px;}
.info-row{display:flex;align-items:flex-start;gap:9px;padding:7px 0;border-bottom:1px solid #f0f2f5;}
.info-row:last-child{border-bottom:none;}
.info-icon{display:flex;align-items:flex-start;justify-content:center;width:16px;flex-shrink:0;padding-top:2px;}
.info-icon svg{width:12px;height:12px;stroke:#1e3a5f;fill:none;stroke-width:2;stroke-linecap:round;stroke-linejoin:round;}
.info-content{display:flex;flex-direction:column;gap:1px;min-width:0;flex:1;}
.info-label{font-size:10px;font-weight:700;color:#9aa5b4;text-transform:uppercase;letter-spacing:0.05em;}
.info-value{font-size:13px;color:#1e2a38;font-weight:600;word-break:break-all;overflow-wrap:anywhere;}
.session-highlight{color:#276749;font-size:14px;}
.ann-scroll{max-height:440px;overflow-y:auto;}
.ann-item{padding:13px 15px;border-bottom:1px solid #f0f2f5;}
.ann-item:last-child{border-bottom:none;}
.ann-item:hover{background:#fafbfc;}
.ann-meta{font-size:11.5px;font-weight:600;color:#1e3a5f;margin-bottom:6px;}
.ann-bubble{background:#fafbfc;border:1px solid #eef0f3;border-radius:5px;padding:9px 11px;font-size:13px;color:#4a5568;line-height:1.6;}
.ann-empty{font-size:13px;color:#9aa5b4;font-style:italic;}
.rules-scroll{max-height:440px;overflow-y:auto;padding:14px 16px;}
.rules-header{text-align:center;margin-bottom:12px;padding-bottom:10px;border-bottom:1px solid #f0f2f5;}
.rules-header h3{font-size:13px;font-weight:700;color:#1e3a5f;}
.rules-header p{font-size:10.5px;color:#4a5568;margin-top:2px;}
.rules-section-title{font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:0.06em;color:#1e3a5f;margin:12px 0 8px;}
.rules-intro{font-size:13px;color:#4a5568;line-height:1.65;margin-bottom:10px;}
.rules-list{display:flex;flex-direction:column;gap:9px;}
.rule-item{display:flex;gap:9px;font-size:13px;color:#4a5568;line-height:1.6;}
.rule-num{min-width:20px;height:20px;border-radius:50%;background:#eef3f9;color:#1e3a5f;font-size:10.5px;font-weight:700;display:flex;align-items:center;justify-content:center;flex-shrink:0;margin-top:2px;border:1px solid #c5d5e8;}
@media(max-width:960px){.dashboard{grid-template-columns:1fr 1fr;}.dashboard>.card:first-child{grid-column:1/-1;}}
@media(max-width:600px){.dashboard{grid-template-columns:1fr;}nav{padding:0 14px;}}
</style>
</head>
<body>
<nav>
  <div class="nav-brand">CCS Sit-in Monitoring System</div>
  <div class="nav-links">
    <div class="notif-wrapper">
        <a onclick="toggleDropdown()" class="notif-trigger">
            <span>Notifications</span>
            <?php if ($unread_count > 0): ?>
                <span id="notif-badge" class="notif-badge"><?= $unread_count ?></span>
            <?php endif; ?>
        </a>
        <div id="notif-dropdown" class="notif-dropdown">
            <div class="notif-header">Recent Notifications</div>
            <?php if (!empty($recent_notifications)): foreach ($recent_notifications as $row): ?>
                <a href="notifications.php" class="notif-item">
                    <div class="notif-msg"><?= htmlspecialchars($row['message'] ?? 'No message') ?></div>
                    <div class="notif-time"><?= date('M d, h:i A', strtotime($row['created_at'])) ?></div>
                </a>
            <?php endforeach; else: ?>
                <div class="notif-item" style="color:#9aa5b4;text-align:center;">No new notifications</div>
            <?php endif; ?>
            <a href="notifications.php" class="view-all-link">View All</a>
        </div>
    </div>
    
    <a href="homepage.php" class="active">Home</a>
    <a href="profile.php">Edit Profile</a>
    <a href="history.php">History</a>
    <a href="reservation.php">Reservation</a>
    <a href="logout.php" class="btn-logout">Log out</a>
  </div>
</nav>

<div class="dashboard">
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
    </div>
  </div>

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