<?php
session_start();
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: login.php'); exit;
}
require_once 'db.php';
// Check if reservations are enabled
$stmt = $pdo->prepare("SELECT setting_value FROM settings WHERE setting_key = 'reservations_enabled'");
$stmt->execute();
$reservations_enabled = $stmt->fetchColumn() === '1';

$msg = ''; $msg_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reserve'])) {
    // Check if reservations are enabled
    if (!$reservations_enabled) {
        $msg_type = 'error';
    } else {
        // Normal reservation processing
        $purpose = trim($_POST['purpose'] ?? '');
        $lab     = trim($_POST['lab']     ?? '');
        $time_in = trim($_POST['time_in'] ?? '');
        $date    = trim($_POST['date']    ?? '');

        if ($purpose && $lab && $date) {
            // Check if student has remaining sessions
            $sessStmt = $pdo->prepare("SELECT session FROM students WHERE id = ?");
            $sessStmt->execute([$_SESSION['student_id']]);
            $sessRow = $sessStmt->fetch();

            if ($sessRow && $sessRow['session'] <= 0) {
                $msg = 'You have no remaining sessions. Please contact the administrator.';
                $msg_type = 'error';
            } else {
                $pdo->prepare("INSERT INTO reservations (student_id, id_number, purpose, laboratory, time_in, date, status)
                               VALUES (?,?,?,?,?,?,'pending')")
                    ->execute([$_SESSION['student_id'], $_SESSION['id_number'], $purpose, $lab, $time_in ?: null, $date]);
                $msg = 'Reservation submitted! Pending admin approval.';
                $msg_type = 'success';
            }
        } else {
            $msg = 'Please fill in all required fields.';
            $msg_type = 'error';
        }
    }
}

// Refresh session count
$sessRefresh = $pdo->prepare("SELECT session FROM students WHERE id = ?");
$sessRefresh->execute([$_SESSION['student_id']]);
$sessData = $sessRefresh->fetch();
if ($sessData) $_SESSION['session'] = $sessData['session'];

$my_reservations = $pdo->prepare("SELECT * FROM reservations WHERE student_id = ? ORDER BY created_at DESC LIMIT 20");
$my_reservations->execute([$_SESSION['student_id']]);
$reservations = $my_reservations->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"/><meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>CCS | Reservation</title>
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
.nav-brand{font-size:13.5px;font-weight:700;color:#fff;}
.nav-links{display:flex;align-items:center;gap:1px;}
.nav-links a{font-size:13px;color:rgba(255,255,255,0.7);text-decoration:none;padding:6px 10px;border-radius:5px;transition:all .15s;white-space:nowrap;}
.nav-links a:hover,.nav-links a.active{color:#fff;background:rgba(255,255,255,0.1);}
.btn-logout{background:#c53030 !important;color:#fff !important;font-weight:600 !important;border-radius:5px;padding:6px 14px !important;margin-left:6px;}
.btn-logout:hover{background:#9b2c2c !important;}
.btn-closed {background-color: #c53030 !important;color: white !important;cursor: not-allowed;}
.btn-closed:hover {background-color: #9b2c2c !important;}
.page-body{max-width:840px;margin:0 auto;padding:28px 20px 52px;}
.page-title{font-size:19px;font-weight:700;color:#1e3a5f;margin-bottom:20px;text-align:center;}
.alert{padding:10px 14px;border-radius:6px;font-size:13px;margin-bottom:18px;font-weight:500;}
.alert-success{background:#f0fff4;border:1px solid #9ae6b4;color:#276749;}
.alert-error{background:#fff5f5;border:1px solid #fed7d7;color:#c53030;}
.card{background:var(--card-bg);border-radius:8px;border:1px solid var(--border-color);overflow:hidden;margin-bottom:20px;transition:background 0.3s, border-color 0.3s;}
.card-head{background:var(--nav-bg);padding:11px 18px;}
.card-head h2{color:#fff;font-size:13px;font-weight:600;}
.card-body{padding:20px 22px 24px;}
.section-divider{font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:0.07em;color:#1e3a5f;padding-bottom:7px;border-bottom:1px solid #eef0f3;margin-bottom:12px;margin-top:18px;}
.section-divider:first-of-type{margin-top:0;}
.field{margin-bottom:12px;}
.field label{display:block;font-size:11px;font-weight:600;color:#4a5568;margin-bottom:4px;text-transform:uppercase;letter-spacing:0.03em;}
.field input,.field select{width:100%;padding:9px 11px;border:1px solid var(--border-color);border-radius:6px;font-size:13px;font-family:'Plus Jakarta Sans',sans-serif;color:var(--text-color);background:var(--input-bg);outline:none;transition:border-color .15s;}
.field input:focus,.field select:focus{border-color:#1e3a5f;box-shadow:0 0 0 3px rgba(30,58,95,0.08);}
.field input[readonly]{background:#f7f8fa;color:#9aa5b4;cursor:not-allowed;}
.field-row{display:grid;grid-template-columns:1fr 1fr;gap:12px;}
.session-badge{display:inline-flex;align-items:center;gap:6px;background:#eef3f9;border:1px solid #c5d5e8;color:#1e3a5f;border-radius:5px;padding:7px 12px;font-size:13px;font-weight:600;margin-top:3px;}
.session-badge.low{background:#fff5f5;border-color:#fed7d7;color:#c53030;}
.btn-reserve{padding:10px 24px;border:none;border-radius:6px;background:var(--nav-bg);color:#fff;font-size:13.5px;font-weight:600;font-family:'Plus Jakarta Sans',sans-serif;cursor:pointer;transition:background .15s;margin-top:6px;}
.btn-reserve:hover{background:#16304f;}
table{width:100%;border-collapse:collapse;}
thead tr{background:var(--nav-bg);}
thead th{color:#fff;font-size:11px;font-weight:600;padding:9px 13px;text-align:left;text-transform:uppercase;letter-spacing:0.03em;}
tbody tr{border-bottom:1px solid #f0f2f5;transition:background .12s;}
tbody tr:last-child{border-bottom:none;}
tbody tr:hover{background:#fafbfc;}
tbody td{padding:9px 13px;font-size:13px;color:var(--text-color);}
.badge{display:inline-block;padding:2px 9px;border-radius:20px;font-size:11.5px;font-weight:600;}
.badge-pending{background:#fefce8;color:#854d0e;}
.badge-approved{background:#f0fff4;color:#276749;}
.badge-rejected{background:#fff5f5;color:#c53030;}
.no-data{text-align:center;padding:26px;color:#9aa5b4;font-size:13px;font-style:italic;}
@media(max-width:600px){.field-row{grid-template-columns:1fr;}nav{padding:0 14px;}.card-body{padding:16px;}}
</style>
</head>
<body>
<?php include 'header.php'; ?>
<div class="page-body">
  <div class="page-title">Lab Reservation</div>

  <?php if ($msg): ?>
    <div class="alert alert-<?= $msg_type ?>">
      <?= $msg_type === 'success' ? '✅' : '⚠️' ?> <?= htmlspecialchars($msg) ?>
    </div>
  <?php endif; ?>

  <div class="card">
    <div class="card-head"><h2>Reservation Form</h2></div>
    <div class="card-body">
      <form method="POST">
        <?php if (!$reservations_enabled): ?>
    <div style="background:#fff5f5;border:1px solid #fed7d7;color:#c53030;padding:12px 16px;border-radius:6px;margin-bottom:18px;text-align:center;font-weight:600;">
        🚫 Reservations are currently disabled by the administrator.
    </div>
<?php endif; ?>

<form method="POST" <?= $reservations_enabled ? '' : 'style="opacity:0.5;pointer-events:none;"' ?>>
    <!-- Your existing form fields here -->
    <div class="section-divider">Student Details</div>
    <div class="field-row">
        <div class="field">
            <label>ID Number</label>
            <input type="text" value="<?= htmlspecialchars($_SESSION['id_number'] ?? '') ?>" readonly/>
        </div>
        <div class="field">
            <label>Student Name</label>
            <input type="text" value="<?= htmlspecialchars($_SESSION['fullname'] ?? '') ?>" readonly/>
        </div>
    </div>
    
    <div class="section-divider">Reservation Details</div>
    <div class="field-row">
        <div class="field">
            <label>Purpose *</label>
            <input type="text" name="purpose" placeholder="e.g. C Programming, Thesis" required/>
        </div>
        <div class="field">
            <label>Laboratory *</label>
            <input type="text" name="lab" placeholder="e.g. 524, 526" required/>
        </div>
    </div>
<div class="section-divider">Schedule</div>
<div class="field-row">
    <div class="field">
        <label>Preferred Time</label>
        <select name="time_in" required style="width:100%;padding:9px 11px;border:1px solid #d0d7e2;border-radius:6px;font-size:13px;font-family:'Plus Jakarta Sans',sans-serif;color:#1e2a38;background:#fff;outline:none;">
            <option value="">Select Time</option>
            <option value="01:00:00">1:00 PM</option>
            <option value="01:30:00">1:30 PM</option>
            <option value="02:00:00">2:00 PM</option>
            <option value="02:30:00">2:30 PM</option>
            <option value="03:00:00">3:00 PM</option>
            <option value="03:30:00">3:30 PM</option>
            <option value="04:00:00">4:00 PM</option>
            <option value="04:30:00">4:30 PM</option>
            <option value="05:00:00">5:00 PM</option>
            <option value="05:30:00">5:30 PM</option>
        </select>
    </div>
    <div class="field">
        <label>Date *</label>
        <input type="date" name="date" min="<?= date('Y-m-d') ?>" required/>
    </div>
</div>
<div style="display:flex;align-items:center;justify-content:flex-end;margin-top:8px;margin-bottom:4px;">
    <button type="submit" name="reserve" 
            class="btn-reserve <?= $reservations_enabled ? '' : 'btn-closed' ?>" 
            <?= $reservations_enabled ? '' : 'disabled' ?>
            style="margin-left:auto;">
        <?= $reservations_enabled ? 'Submit Reservation' : 'Reservations Closed' ?>
    </button>
</div>

<div class="section-divider">Session Info</div>
<div class="field">
    <label>Remaining Sessions</label>
    <?php $sess = (int)($_SESSION['session'] ?? 0); ?>
    <div class="session-badge <?= $sess <= 5 ? 'low' : '' ?>">
        🖥️ <?= $sess ?> session<?= $sess !== 1 ? 's' : '' ?> remaining
    </div>
</div>
    </div>
  <div class="card">
    <div class="card-head"><h2>My Reservations</h2></div>
    <table>
      <thead>
        <tr><th>Purpose</th><th>Lab</th><th>Date</th><th>Time</th><th>Status</th><th>Submitted</th></tr>
      </thead>
      <tbody>
        <?php if ($reservations): foreach ($reservations as $r): ?>
        <tr>
          <td><?= htmlspecialchars($r['purpose']) ?></td>
          <td><?= htmlspecialchars($r['laboratory']) ?></td>
          <td><?= htmlspecialchars($r['date'] ?? '—') ?></td>
          <td><?= htmlspecialchars($r['time_in'] ?? '—') ?></td>
          <td><span class="badge badge-<?= $r['status'] ?>"><?= ucfirst($r['status']) ?></span></td>
          <td><?= date('M d, Y', strtotime($r['created_at'])) ?></td>
        </tr>
        <?php endforeach; else: ?>
        <tr><td colspan="6" class="no-data">No reservations yet.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
</body>
</html>