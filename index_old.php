<?php
session_start();
require_once 'db.php';
if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    header('Location: homepage.php'); exit;
}
$announcements = $pdo->query("SELECT * FROM announcements ORDER BY created_at DESC")->fetchAll();
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
}

body.dark-mode {
    --bg-color: #0f172a;
    --text-color: #e2e8f0;
    --card-bg: #1e293b;
    --nav-bg: #0f172a;
    --border-color: #334155;
    --shadow: 0 4px 12px rgba(0,0,0,0.5);
    --input-bg: #1e293b;
    --input-border: #334155;
    --hover-bg: #1e293b;
}

*{box-sizing:border-box;margin:0;padding:0;}
body{font-family:'Plus Jakarta Sans',sans-serif;background:var(--bg-color);color:var(--text-color);min-height:100vh;font-size:14px;transition:background 0.3s, color 0.3s;}
nav{background:var(--nav-bg);height:54px;padding:0 24px;display:flex;align-items:center;justify-content:space-between;position:sticky;top:0;z-index:100;transition:background 0.3s;}
.nav-brand{display:flex;align-items:center;gap:9px;text-decoration:none;}
.nav-brand img{width:30px;height:30px;border-radius:50%;}
.nav-brand-text{font-size:13.5px;font-weight:700;color:#fff;line-height:1.2;}
.nav-brand-sub{font-size:10px;color:rgba(255,255,255,0.45);}
.nav-links{display:flex;align-items:center;gap:1px;}
.nav-links a{font-size:13px;color:rgba(255,255,255,0.7);text-decoration:none;padding:6px 10px;border-radius:5px;transition:all .15s;}
.nav-links a:hover,.nav-links a.active{color:#fff;background:rgba(255,255,255,0.1);}
.btn-login{border:1px solid rgba(255,255,255,0.25);}
.btn-register{background:#2f6090;color:#fff !important;font-weight:600 !important;}
.btn-register:hover{background:#3a72a8 !important;}
.btn-admin{font-size:12px !important;color:rgba(255,255,255,0.45) !important;border:1px solid rgba(255,255,255,0.12);margin-left:4px;}
.btn-admin:hover{color:#fff !important;background:rgba(255,255,255,0.08) !important;}
.hero{background:#1e3a5f;padding:52px 24px 44px;text-align:center;}
.hero img{width:88px;height:88px;border-radius:50%;border:3px solid rgba(255,255,255,0.25);object-fit:cover;}
.hero h1{color:#fff;font-size:26px;font-weight:800;margin-top:16px;}
.hero p{color:rgba(255,255,255,0.55);font-size:13px;margin-top:6px;}
.hero-pills{display:flex;justify-content:center;gap:6px;margin-top:16px;flex-wrap:wrap;}
.pill{background:rgba(255,255,255,0.08);border:1px solid rgba(255,255,255,0.15);color:rgba(255,255,255,0.7);font-size:11px;padding:3px 11px;border-radius:20px;}
.hero-btns{display:flex;justify-content:center;gap:8px;margin-top:24px;flex-wrap:wrap;}
.btn-hero{padding:10px 26px;border-radius:6px;font-size:13px;font-weight:600;font-family:'Plus Jakarta Sans',sans-serif;cursor:pointer;text-decoration:none;transition:all .15s;border:none;display:inline-block;}
.btn-solid{background:#fff;color:#1e3a5f;}
.btn-solid:hover{background:#e8f0f8;}
.btn-outline-hero{background:transparent;color:#fff;border:1.5px solid rgba(255,255,255,0.35);}
.btn-outline-hero:hover{background:rgba(255,255,255,0.08);}
.home-body{max-width:660px;margin:0 auto;padding:32px 20px 52px;}
.card{background:var(--card-bg);border-radius:12px;border:1px solid var(--border-color);overflow:hidden;box-shadow:var(--shadow);transition:background 0.3s, border-color 0.3s;}
.card-head{background:var(--nav-bg);padding:12px 18px;display:flex;align-items:center;gap:8px;}
.card-head h2{color:#fff;font-size:13px;font-weight:600;}
.card-badge{margin-left:auto;background:rgba(255,255,255,0.18);color:#fff;font-size:11px;font-weight:600;padding:2px 8px;border-radius:20px;}
.ann-list{max-height:420px;overflow-y:auto;}
.ann-item{padding:14px 18px;border-bottom:1px solid #f0f2f5;}
.ann-item:last-child{border-bottom:none;}
.ann-item:hover{background:#fafbfc;}
.ann-meta{display:flex;align-items:center;gap:9px;margin-bottom:7px;}
.ann-dot{width:30px;height:30px;border-radius:50%;background:#eef3f9;border:1px solid #d0dcea;color:#1e3a5f;font-size:11px;font-weight:700;display:flex;align-items:center;justify-content:center;flex-shrink:0;}
.ann-author{font-size:13px;font-weight:600;color:#1e3a5f;}
.ann-date{font-size:11.5px;color:#9aa5b4;margin-left:auto;}
.ann-body{font-size:13px;color:#4a5568;line-height:1.65;padding-left:39px;}
.ann-empty{font-size:13px;color:#9aa5b4;font-style:italic;padding-left:39px;}
footer{background:#1e3a5f;text-align:center;padding:16px;font-size:12px;color:rgba(255,255,255,0.3);}
footer a{color:rgba(255,255,255,0.4);text-decoration:none;}
footer a:hover{color:rgba(255,255,255,0.75);}
@media(max-width:600px){nav{padding:0 14px;}.nav-brand-sub{display:none;}}
</style>
</head>
<body>
<nav>
  <a class="nav-brand" href="index.php">
    <img src="Uclogo.png" alt="UC Logo"/>
    <div>
      <div class="nav-brand-text">College of Computer Studies</div>
      <div class="nav-brand-sub">Sit-in Monitoring System</div>
    </div>
  </a>
  <div class="nav-links">
    <a href="index.php" class="active">Home</a>
    <a href="#" onclick="openAboutModal();return false;">About</a>
    <a href="login.php" class="btn-login">Login</a>
    <a href="register.php" class="btn-register">Register</a>
    <a href="admin.php" class="btn-admin">🔒 Admin</a>
    <button onclick="toggleDarkMode()" style="background:none;border:none;color:rgba(255,255,255,0.7);cursor:pointer;font-size:16px;padding:6px 10px;border-radius:5px;transition:all .15s;" onmouseover="this.style.background='rgba(255,255,255,0.1)'" onmouseout="this.style.background='none'">
        🌙
    </button>
  </div>
</nav>

<div class="hero">
  <img src="Uclogo.png" alt="UC Logo"/>
  <h1>University of Cebu </h1>
  <p> Sit-in Monitoring System</p>
  <div class="hero-btns">
    <a href="login.php" class="btn-hero btn-solid">Login</a>
    <a href="register.php" class="btn-hero btn-outline-hero">Create Account</a>
  </div>
</div>

<div class="home-body">
  <div class="card">
    <div class="card-head">
      <h2>📢 Announcements</h2>
      <span class="card-badge"><?= count($announcements) ?></span>
    </div>
    <div class="ann-list">
      <?php if ($announcements): foreach ($announcements as $ann): ?>
      <div class="ann-item">
        <div class="ann-meta">
          <div class="ann-dot">CA</div>
          <span class="ann-author"><?= htmlspecialchars($ann['admin_name']) ?></span>
          <span class="ann-date"><?= date('M d, Y', strtotime($ann['created_at'])) ?></span>
        </div>
        <?php if (!empty($ann['content'])): ?>
          <div class="ann-body"><?= htmlspecialchars($ann['content']) ?></div>
        <?php else: ?>
          <div class="ann-empty">No content provided for this announcement.</div>
        <?php endif; ?>
      </div>
      <?php endforeach; else: ?>
      <div class="ann-item"><div class="ann-empty" style="padding:16px 0;">No announcements yet.</div></div>
      <?php endif; ?>
    </div>
  </div>
</div>

<footer>
  © 2026 College of Computer Studies · University of Cebu &nbsp;|&nbsp;
  <a href="admin.php">Admin Login</a>
</footer>
<!-- About Modal -->
<div id="aboutModal" style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.5);z-index:9999;align-items:center;justify-content:center;">
    <div style="background:var(--card-bg);border-radius:12px;max-width:500px;width:90%;padding:30px;position:relative;box-shadow:var(--shadow);">
        <span onclick="closeAboutModal()" style="position:absolute;top:15px;right:20px;font-size:24px;cursor:pointer;color:#9aa5b4;">&times;</span>
        <div style="text-align:center;margin-bottom:20px;">
            <img src="Uclogo.png" alt="UC Logo" style="width:60px;height:60px;border-radius:50%;object-fit:cover;">
            <h2 style="color:#1e3a5f;margin-top:10px;">About CCS Sit-in System</h2>
        </div>
        <div style="font-size:14px;color:#4a5568;line-height:1.8;">
            <p><strong>Version:</strong> 1.0.0</p>
            <p><strong>College of Computer Studies</strong></p>
            <p><strong>University of Cebu - Main Campus</strong></p>
            <p style="margin-top:15px;">This Sit-in Monitoring System allows students to reserve laboratory slots, track their sit-in sessions, and receive announcements from the administration.</p>
            <p style="margin-top:10px;font-size:12px;color:#9aa5b4;">&copy; 2026 College of Computer Studies</p>
        </div>
        <button onclick="closeAboutModal()" style="display:block;width:100%;padding:10px;margin-top:20px;border:none;border-radius:6px;background:var(--nav-bg);color:#fff;font-size:14px;font-weight:600;cursor:pointer;">Close</button>
    </div>
</div>

<script>
function openAboutModal() {
    document.getElementById('aboutModal').style.display = 'flex';
}
function closeAboutModal() {
    document.getElementById('aboutModal').style.display = 'none';
}
document.getElementById('aboutModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeAboutModal();
    }
});
</script>
</body>
</html>