<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: admin.php');
    exit;
}

// One-time schema fix: allow NULL student_id for walk-in students
try { $pdo->exec("ALTER TABLE sit_in_history MODIFY student_id INT NULL DEFAULT NULL"); } catch (Exception $e) {}

// ── Handle POST actions ──────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Post announcement
    if (isset($_POST['add_announcement'])) {
        $content = trim($_POST['content'] ?? '');
        $pdo->prepare("INSERT INTO announcements (admin_name, content) VALUES (?, ?)")
            ->execute([$_SESSION['admin_username'], $content ?: null]);
        header('Location: admin_dashboard.php?page=home&msg=announced'); exit;
    }

    // Delete announcement
    if (isset($_POST['delete_announcement'])) {
        $pdo->prepare("DELETE FROM announcements WHERE id = ?")->execute([(int)$_POST['ann_id']]);
        header('Location: admin_dashboard.php?page=home&msg=ann_deleted'); exit;
    }

    // Add student
    if (isset($_POST['add_student'])) {
        $id_num = trim($_POST['id_number'] ?? '');
        $ln     = trim($_POST['lastname']  ?? '');
        $fn     = trim($_POST['firstname'] ?? '');
        $mn     = trim($_POST['middlename']?? '');
        $course = trim($_POST['course']    ?? '');
        $year   = (int)($_POST['year_level'] ?? 1);
        $email  = trim($_POST['email']     ?? '');
        $pw     = password_hash(trim($_POST['password'] ?? 'Password123'), PASSWORD_DEFAULT);
        try {
            $pdo->prepare("INSERT INTO students (id_number,lastname,firstname,middlename,course,year_level,email,password,session) VALUES (?,?,?,?,?,?,?,?,30)")
                ->execute([$id_num,$ln,$fn,$mn,$course,$year,$email,$pw]);
            header('Location: admin_dashboard.php?page=students&msg=added'); exit;
        } catch (PDOException $e) {
            header('Location: admin_dashboard.php?page=students&msg=add_err'); exit;
        }
    }

    // Edit student
    if (isset($_POST['edit_student'])) {
        $id      = (int)$_POST['student_id'];
        $ln      = trim($_POST['lastname']   ?? '');
        $fn      = trim($_POST['firstname']  ?? '');
        $mn      = trim($_POST['middlename'] ?? '');
        $course  = trim($_POST['course']     ?? '');
        $year    = (int)($_POST['year_level'] ?? 1);
        $email   = trim($_POST['email']      ?? '');
        $session = max(0, min(30, (int)($_POST['session'] ?? 0)));
        $pdo->prepare("UPDATE students SET lastname=?,firstname=?,middlename=?,course=?,year_level=?,email=?,session=? WHERE id=?")
            ->execute([$ln,$fn,$mn,$course,$year,$email,$session,$id]);
        header('Location: admin_dashboard.php?page=students&msg=edited'); exit;
    }

    // Delete student
    if (isset($_POST['delete_student'])) {
        $pdo->prepare("DELETE FROM students WHERE id = ?")->execute([(int)$_POST['student_id']]);
        header('Location: admin_dashboard.php?page=students&msg=deleted'); exit;
    }

    // Edit session only
    if (isset($_POST['edit_session_only'])) {
        $id      = (int)$_POST['student_id'];
        $session = max(0, min(30, (int)($_POST['session'] ?? 0)));
        $pdo->prepare("UPDATE students SET session = ? WHERE id = ?")->execute([$session, $id]);
        header('Location: admin_dashboard.php?page=sitin&msg=session_updated'); exit;
    }

    // Reset ONE student session
    if (isset($_POST['reset_session'])) {
        $pdo->prepare("UPDATE students SET session = 30 WHERE id = ?")->execute([(int)$_POST['student_id']]);
        header('Location: admin_dashboard.php?page=students&msg=reset'); exit;
    }

    // Reset ALL sessions
    if (isset($_POST['reset_all_sessions'])) {
        $pdo->exec("UPDATE students SET session = 30");
        header('Location: admin_dashboard.php?page=students&msg=all_reset'); exit;
    }

    // Sit-in (registered student OR walk-in)
    if (isset($_POST['do_sitin'])) {
        $id_num  = trim($_POST['id_number']    ?? '');
        $name    = trim($_POST['student_name'] ?? '');
        $purpose = trim($_POST['purpose']      ?? '');
        $lab     = trim($_POST['lab']          ?? '');

        if (!$id_num || !$name || !$purpose || !$lab) {
            header('Location: admin_dashboard.php?page=sitin&msg=sitin_err'); exit;
        }

        try {
            $stu = $pdo->prepare("SELECT * FROM students WHERE id_number = ? LIMIT 1");
            $stu->execute([$id_num]);
            $found = $stu->fetch();

            if ($found) {
                if ($found['session'] <= 0) {
                    header('Location: admin_dashboard.php?page=sitin&msg=no_session'); exit;
                }
                $pdo->prepare("UPDATE students SET session = session - 1 WHERE id = ? AND session > 0")
                    ->execute([$found['id']]);
                $pdo->prepare("INSERT INTO sit_in_history (student_id, id_number, fullname, sit_purpose, laboratory, login_time, date) VALUES (?,?,?,?,?,NOW(),CURDATE())")
                    ->execute([$found['id'], $id_num, $name, $purpose, $lab]);
            } else {
                $pdo->prepare("INSERT INTO sit_in_history (student_id, id_number, fullname, sit_purpose, laboratory, login_time, date) VALUES (NULL,?,?,?,?,NOW(),CURDATE())")
                    ->execute([$id_num, $name, $purpose, $lab]);
            }
        } catch (PDOException $e) {
            error_log('Sit-in error: ' . $e->getMessage());
        }

        header('Location: admin_dashboard.php?page=sitin&msg=sittin'); exit;
    }

    // Logout a sit-in record
    if (isset($_POST['logout_sitin'])) {
        $pdo->prepare("UPDATE sit_in_history SET logout_time = NOW() WHERE id = ? AND logout_time IS NULL")
            ->execute([(int)$_POST['sitin_id']]);
        header('Location: admin_dashboard.php?page=sitin&msg=logout'); exit;
    }

    // Approve reservation
    if (isset($_POST['approve_reservation'])) {
        $rid = (int)$_POST['reservation_id'];
        $pdo->prepare("UPDATE reservations SET status = 'approved' WHERE id = ?")->execute([$rid]);
        // Notify student
        $rv = $pdo->prepare("SELECT * FROM reservations WHERE id = ? LIMIT 1");
        $rv->execute([$rid]);
        $rvRow = $rv->fetch();
        if ($rvRow) {
            $pdo->prepare("INSERT INTO notifications (student_id, message) VALUES (?,?)")
                ->execute([$rvRow['student_id'], "Your reservation for Lab {$rvRow['laboratory']} on {$rvRow['date']} has been APPROVED."]);
        }
        header('Location: admin_dashboard.php?page=reservation&msg=approved'); exit;
    }

    // Reject reservation
    if (isset($_POST['reject_reservation'])) {
        $rid = (int)$_POST['reservation_id'];
        $pdo->prepare("UPDATE reservations SET status = 'rejected' WHERE id = ?")->execute([$rid]);
        $rv = $pdo->prepare("SELECT * FROM reservations WHERE id = ? LIMIT 1");
        $rv->execute([$rid]);
        $rvRow = $rv->fetch();
        if ($rvRow) {
            $pdo->prepare("INSERT INTO notifications (student_id, message) VALUES (?,?)")
                ->execute([$rvRow['student_id'], "Your reservation for Lab {$rvRow['laboratory']} on {$rvRow['date']} has been REJECTED."]);
        }
        header('Location: admin_dashboard.php?page=reservation&msg=rejected'); exit;
    }
}

// ── Fetch data ───────────────────────────────────────────────
$total_students  = (int)$pdo->query("SELECT COUNT(*) FROM students")->fetchColumn();
$currently_sitin = (int)$pdo->query("SELECT COUNT(*) FROM sit_in_history WHERE logout_time IS NULL AND date = CURDATE()")->fetchColumn();
$total_sitin     = (int)$pdo->query("SELECT COUNT(*) FROM sit_in_history")->fetchColumn();

$purpose_rows  = $pdo->query("SELECT sit_purpose, COUNT(*) as cnt FROM sit_in_history GROUP BY sit_purpose ORDER BY cnt DESC LIMIT 8")->fetchAll();
$announcements = $pdo->query("SELECT * FROM announcements ORDER BY created_at DESC")->fetchAll();
$students      = $pdo->query("SELECT * FROM students ORDER BY id_number ASC")->fetchAll();
$current_sitin = $pdo->query("
    SELECT s.*,
           COALESCE(st.session, NULL) as remaining_session,
           COALESCE(st.id, 0) as student_db_id
    FROM sit_in_history s
    LEFT JOIN students st ON s.id_number = st.id_number
    WHERE s.logout_time IS NULL AND s.date = CURDATE()
    ORDER BY s.login_time DESC
")->fetchAll();
$all_sitin    = $pdo->query("SELECT * FROM sit_in_history ORDER BY created_at DESC LIMIT 200")->fetchAll();
$reservations = $pdo->query("
    SELECT r.*, s.firstname, s.lastname
    FROM reservations r
    JOIN students s ON r.student_id = s.id
    ORDER BY r.created_at DESC
")->fetchAll();

$page = $_GET['page'] ?? 'home';

$flash_map = [
    'announced'       => '✅ Announcement posted.',
    'ann_deleted'     => '✅ Announcement deleted.',
    'added'           => '✅ Student added successfully.',
    'add_err'         => '❌ Could not add student. ID or email may already exist.',
    'edited'          => '✅ Student updated.',
    'deleted'         => '✅ Student deleted.',
    'reset'           => '✅ Session reset to 30.',
    'all_reset'       => '✅ All sessions reset to 30.',
    'sittin'          => '✅ Student logged in successfully.',
    'logout'          => '✅ Student logged out.',
    'sitin_err'       => '❌ Please fill in all required fields (ID, Name, Purpose, Lab).',
    'no_session'      => '❌ Student has no remaining sessions.',
    'session_updated' => '✅ Session updated successfully.',
    'approved'        => '✅ Reservation approved.',
    'rejected'        => '✅ Reservation rejected.',
];
$flash_msg  = $flash_map[$_GET['msg'] ?? ''] ?? '';
$flash_type = str_starts_with($flash_msg, '❌') ? 'error' : 'success';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>CCS | Admin Dashboard</title>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet"/>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
/* ── CSS Variables ── */
:root {
  --blue:#1e3a5f;--blue-lt:#eef3f9;--blue-bd:#c5d5e8;--blue-dk:#1e2a38;
  --green:#276749;--green-lt:#f0fff4;--green-bd:#9ae6b4;
  --red:#c53030;--red-lt:#fff5f5;--red-bd:#fed7d7;
  --orange:#b45309;--orange-lt:#fff8f0;--orange-bd:#f6c090;
  --gray-100:#f7f8fa;--gray-200:#e2e6ea;--gray-400:#9aa5b4;--gray-600:#4a5568;--gray-800:#1e2a38;
  --radius:6px;
}
*{box-sizing:border-box;margin:0;padding:0;}
body{font-family:'Plus Jakarta Sans',sans-serif;background:var(--gray-100);color:var(--gray-800);font-size:14px;}
nav{background:var(--blue);height:52px;padding:0 20px;display:flex;align-items:center;justify-content:space-between;position:sticky;top:0;z-index:200;}
.nav-brand{font-size:13.5px;font-weight:700;color:#fff;white-space:nowrap;}
.nav-links{display:flex;align-items:center;gap:1px;flex-wrap:wrap;}
.nav-links a{font-size:12.5px;color:rgba(255,255,255,0.7);text-decoration:none;padding:5px 9px;border-radius:4px;white-space:nowrap;transition:all .15s;}
.nav-links a:hover,.nav-links a.active{color:#fff;background:rgba(255,255,255,0.1);}
.btn-logout-nav{background:#276749 !important;color:#fff !important;font-weight:600 !important;border-radius:4px;padding:5px 13px !important;margin-left:4px;}
.btn-logout-nav:hover{background:#1e4d38 !important;}
.flash{padding:9px 14px;border-radius:6px;font-size:13px;margin-bottom:16px;font-weight:500;}
.flash.success{background:var(--green-lt);border:1px solid var(--green-bd);color:var(--green);}
.flash.error{background:var(--red-lt);border:1px solid var(--red-bd);color:var(--red);}
.page-body{max-width:1280px;margin:0 auto;padding:20px 18px 52px;}
.page-section{display:none;}
.page-section.active{display:block;}
.page-title{font-size:18px;font-weight:700;color:var(--blue);margin-bottom:18px;text-align:center;}
.home-grid{display:grid;grid-template-columns:1fr 1fr;gap:18px;}
.card{background:#fff;border:1px solid var(--gray-200);border-radius:7px;overflow:hidden;margin-bottom:18px;}
.card-head{background:var(--blue);padding:10px 15px;display:flex;align-items:center;justify-content:space-between;}
.card-head h2{color:#fff;font-size:12.5px;font-weight:600;}
.stat-row{padding:16px 18px;display:flex;flex-direction:column;gap:10px;border-bottom:1px solid var(--gray-100);}
.stat-item{display:flex;align-items:center;gap:10px;font-size:13.5px;color:var(--gray-800);}
.stat-icon-box{width:32px;height:32px;border-radius:7px;display:flex;align-items:center;justify-content:center;flex-shrink:0;}
.stat-icon-box svg{width:16px;height:16px;fill:none;stroke-width:2;stroke-linecap:round;stroke-linejoin:round;}
.stat-icon-blue{background:var(--blue-lt);border:1px solid var(--blue-bd);}.stat-icon-blue svg{stroke:var(--blue);}
.stat-icon-green{background:var(--green-lt);border:1px solid var(--green-bd);}.stat-icon-green svg{stroke:var(--green);}
.stat-icon-orange{background:var(--orange-lt);border:1px solid var(--orange-bd);}.stat-icon-orange svg{stroke:var(--orange);}
.chart-wrap{padding:10px 14px 14px;display:flex;justify-content:center;}
.chart-wrap canvas{max-width:270px;}
.ann-form{padding:12px 14px;}
.ann-form textarea{width:100%;padding:8px 10px;border:1px solid var(--gray-200);border-radius:6px;font-family:'Plus Jakarta Sans',sans-serif;font-size:13px;resize:vertical;min-height:76px;outline:none;}
.ann-form textarea:focus{border-color:var(--blue);box-shadow:0 0 0 3px rgba(30,58,95,0.07);}
.btn-submit{padding:7px 18px;border:none;border-radius:6px;background:var(--green);color:#fff;font-size:13px;font-weight:600;font-family:'Plus Jakarta Sans',sans-serif;cursor:pointer;margin-top:7px;}
.btn-submit:hover{background:#1e4d38;}
.ann-posted-title{font-size:14px;font-weight:600;padding:11px 14px 4px;}
.ann-item{padding:9px 14px;border-top:1px solid var(--gray-100);}
.ann-meta{font-size:12px;font-weight:600;color:var(--blue);margin-bottom:4px;}
.ann-content{font-size:13px;color:var(--gray-600);}
.table-wrap{overflow-x:auto;}
table{width:100%;border-collapse:collapse;}
thead th{background:var(--gray-100);color:var(--gray-600);font-size:11px;font-weight:700;padding:9px 11px;text-align:left;border-bottom:1px solid var(--gray-200);white-space:nowrap;text-transform:uppercase;letter-spacing:0.03em;}
tbody tr{border-bottom:1px solid var(--gray-100);transition:background .1s;}
tbody tr:hover{background:#fafbfc;}
tbody td{padding:8px 11px;font-size:13px;color:var(--gray-600);}
.no-data{text-align:center;padding:26px;color:var(--gray-400);font-size:13px;font-style:italic;}
.toolbar{display:flex;align-items:center;gap:9px;margin-bottom:12px;flex-wrap:wrap;}
.toolbar-right{margin-left:auto;display:flex;align-items:center;gap:7px;}
.entries-select{padding:5px 7px;border:1px solid var(--gray-200);border-radius:5px;font-size:13px;font-family:'Plus Jakarta Sans',sans-serif;outline:none;}
.search-input{padding:6px 10px;border:1px solid var(--gray-200);border-radius:5px;font-size:13px;font-family:'Plus Jakarta Sans',sans-serif;width:175px;outline:none;}
.search-input:focus{border-color:var(--blue);}
.btn{padding:6px 14px;border:none;border-radius:5px;font-size:13px;font-weight:600;font-family:'Plus Jakarta Sans',sans-serif;cursor:pointer;transition:all .15s;text-decoration:none;display:inline-flex;align-items:center;gap:4px;}
.btn-blue{background:#2563a8;color:#fff;}.btn-blue:hover{background:#1d4f8a;}
.btn-red{background:var(--red);color:#fff;}.btn-red:hover{background:#9b2c2c;}
.btn-green{background:var(--green);color:#fff;}.btn-green:hover{background:#1e4d38;}
.btn-sm{padding:3px 10px;font-size:12px;}
.modal-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,0.35);z-index:500;align-items:center;justify-content:center;}
.modal-overlay.open{display:flex;}
.modal{background:#fff;border-radius:7px;border:1px solid var(--gray-200);width:100%;max-width:460px;overflow:hidden;}
.modal-head{display:flex;align-items:center;justify-content:space-between;padding:13px 18px;border-bottom:1px solid var(--gray-200);}
.modal-head h3{font-size:14px;font-weight:700;color:var(--gray-800);}
.modal-close{background:none;border:none;font-size:19px;cursor:pointer;color:var(--gray-400);line-height:1;padding:0 3px;}
.modal-close:hover{color:var(--gray-800);}
.modal-body{padding:18px;}
.modal-footer{padding:11px 18px;border-top:1px solid var(--gray-100);display:flex;justify-content:flex-end;gap:7px;}
.field{margin-bottom:12px;}
.field label{display:block;font-size:11.5px;font-weight:600;color:var(--gray-600);margin-bottom:4px;}
.field input,.field select{width:100%;padding:8px 10px;border:1px solid var(--gray-200);border-radius:5px;font-size:13px;font-family:'Plus Jakarta Sans',sans-serif;color:var(--gray-800);outline:none;transition:border-color .15s;}
.field input:focus,.field select:focus{border-color:var(--blue);box-shadow:0 0 0 3px rgba(30,58,95,0.07);}
.field input[readonly]{background:var(--gray-100);color:var(--gray-400);}
.field-row{display:grid;grid-template-columns:1fr 1fr;gap:11px;}
.badge{display:inline-block;padding:2px 9px;border-radius:20px;font-size:11.5px;font-weight:600;}
.badge-pending{background:#fefce8;color:#854d0e;}
.badge-approved{background:var(--green-lt);color:var(--green);}
.badge-rejected{background:var(--red-lt);color:var(--red);}
.page-btn{width:28px;height:28px;border-radius:5px;border:1px solid var(--gray-200);background:#fff;font-size:12.5px;font-family:'Plus Jakarta Sans',sans-serif;color:var(--gray-600);cursor:pointer;display:inline-flex;align-items:center;justify-content:center;transition:all .15s;}
.page-btn:hover{border-color:var(--blue);color:var(--blue);}
.page-btn.active{background:var(--blue);border-color:var(--blue);color:#fff;font-weight:600;}
@media(max-width:900px){.home-grid{grid-template-columns:1fr;}}
@media(max-width:640px){nav{padding:0 12px;}.nav-brand{font-size:12px;}.nav-links a{padding:4px 6px;font-size:11px;}}
</style>
</head>
<body>

<nav>
  <div class="nav-brand">CCS Admin Dashboard</div>
  <div class="nav-links">
    <a href="?page=home"        class="<?= $page==='home'        ?'active':'' ?>">Home</a>
    <a href="#" onclick="openModal('searchModal');return false;">Search</a>
    <a href="?page=students"    class="<?= $page==='students'    ?'active':'' ?>">Students</a>
    <a href="?page=sitin"       class="<?= $page==='sitin'       ?'active':'' ?>" onclick="this.href='?page=sitin'">Sit-in</a>
    <a href="?page=records"     class="<?= $page==='records'     ?'active':'' ?>">Sit-in Records</a>
    <a href="?page=reports"     class="<?= $page==='reports'     ?'active':'' ?>">Reports</a>
    <a href="?page=reservation" class="<?= $page==='reservation' ?'active':'' ?>">Reservations</a>
    <a href="admin_feedback.php">View Student Feedback</a>
    <a href="admin_logout.php" class="btn-logout-nav">Log out</a>
  </div>
</nav>

<div class="page-body">

<?php if ($flash_msg): ?>
  <div class="flash <?= $flash_type ?>"><?= $flash_msg ?></div>
<?php endif; ?>

<!-- ════════════ HOME ════════════ -->
<div id="page-home" class="page-section <?= $page==='home'?'active':'' ?>">
  <div class="home-grid">
    <!-- Stats + Chart -->
    <div>
      <div class="card">
        <div class="card-head">
          <h2>📊 Statistics</h2>
        </div>
        <div class="stat-row">
          <div class="stat-item">
            <div class="stat-icon-box stat-icon-blue">
              <svg viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            </div>
            <span><strong>Students Registered:</strong> <?= $total_students ?></span>
          </div>
          <div class="stat-item">
            <div class="stat-icon-box stat-icon-green">
              <svg viewBox="0 0 24 24"><rect x="2" y="3" width="20" height="14" rx="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>
            </div>
            <span><strong>Currently Sitting In:</strong> <?= $currently_sitin ?></span>
          </div>
          <div class="stat-item">
            <div class="stat-icon-box stat-icon-orange">
              <svg viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/></svg>
            </div>
            <span><strong>Total Sit-in Records:</strong> <?= $total_sitin ?></span>
          </div>
        </div>
        <div class="chart-wrap">
          <canvas id="purposeChart"></canvas>
        </div>
      </div>
    </div>

    <!-- Announcements -->
    <div>
      <div class="card">
        <div class="card-head"><h2>📢 Announcement</h2></div>
        <div class="ann-form">
          <form method="POST">
            <textarea name="content" placeholder="Write a new announcement..."></textarea>
            <button type="submit" name="add_announcement" class="btn-submit">Post Announcement</button>
          </form>
        </div>
        <div class="ann-posted-title">Posted Announcements</div>
        <?php foreach ($announcements as $ann): ?>
        <div class="ann-item">
          <div class="ann-meta">
            <?= htmlspecialchars($ann['admin_name']) ?> | <?= date('M d, Y', strtotime($ann['created_at'])) ?>
            <form method="POST" style="display:inline;float:right;">
              <input type="hidden" name="ann_id" value="<?= $ann['id'] ?>"/>
              <button type="submit" name="delete_announcement" class="btn btn-sm btn-red" onclick="return confirm('Delete this announcement?')">✕</button>
            </form>
          </div>
          <?php if ($ann['content']): ?>
            <div class="ann-content"><?= htmlspecialchars($ann['content']) ?></div>
          <?php endif; ?>
        </div>
        <?php endforeach; ?>
        <?php if (!$announcements): ?>
          <div class="ann-item"><span style="color:var(--gray-400);font-size:13px;">No announcements yet.</span></div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<!-- ════════════ STUDENTS ════════════ -->
<div id="page-students" class="page-section <?= $page==='students'?'active':'' ?>">
  <div class="page-title">Students Information</div>
  <div class="toolbar">
    <button class="btn btn-blue" onclick="openModal('addStudentModal')">+ Add Student</button>
    <form method="POST" style="display:inline;" onsubmit="return confirm('Reset ALL student sessions to 30?')">
      <button type="submit" name="reset_all_sessions" class="btn btn-red">Reset All Sessions</button>
    </form>
    <div class="toolbar-right">
      <select class="entries-select"><option>10</option><option>25</option><option>50</option><option>100</option></select>
      <span style="font-size:13px;color:var(--gray-600);">entries</span>
      <span style="font-size:13px;color:var(--gray-600);margin-left:8px;">Search:</span>
      <input type="text" class="search-input" id="studentSearch" oninput="filterTable('studentTable',this.value)" placeholder=""/>
    </div>
  </div>
  <div class="card">
    <div class="table-wrap">
      <table id="studentTable">
        <thead>
          <tr>
            <th>ID Number</th><th>Name</th><th>Year</th><th>Course</th><th>Sessions Left</th><th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if ($students): foreach ($students as $s): ?>
          <tr>
            <td><?= htmlspecialchars($s['id_number']) ?></td>
            <td><?= htmlspecialchars($s['firstname'].' '.$s['middlename'].' '.$s['lastname']) ?></td>
            <td><?= htmlspecialchars($s['year_level']) ?></td>
            <td><?= htmlspecialchars($s['course']) ?></td>
            <td>
              <span style="font-weight:700;color:<?= $s['session']<=5?'#dc2626':($s['session']<=10?'#ea580c':'#16a34a'); ?>">
                <?= htmlspecialchars($s['session']) ?>
              </span> / 30
            </td>
            <td style="display:flex;gap:5px;flex-wrap:wrap;">
              <button class="btn btn-blue btn-sm"
                onclick="openEditStudent(<?= $s['id'] ?>,'<?= addslashes($s['id_number']) ?>','<?= addslashes($s['firstname']) ?>','<?= addslashes($s['middlename']) ?>','<?= addslashes($s['lastname']) ?>','<?= addslashes($s['course']) ?>','<?= $s['year_level'] ?>','<?= addslashes($s['email']) ?>','<?= (int)$s['session'] ?>')">Edit</button>
              <form method="POST" style="display:inline;">
                <input type="hidden" name="student_id" value="<?= $s['id'] ?>"/>
                <button type="submit" name="delete_student" class="btn btn-red btn-sm" onclick="return confirm('Delete this student?')">Delete</button>
              </form>
            </td>
          </tr>
          <?php endforeach; else: ?>
          <tr><td colspan="6" class="no-data">No students registered yet.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- ════════════ SIT-IN ════════════ -->
<div id="page-sitin" class="page-section <?= $page==='sitin'?'active':'' ?>">
  <div class="page-title">Current Sit-in</div>
  <div class="toolbar">
    <button class="btn btn-blue" onclick="openBlankSitin()">+ Sit In Student</button>
    <div class="toolbar-right">
      <span style="font-size:13px;color:var(--gray-600);">Search:</span>
      <input type="text" class="search-input" oninput="filterTable('sitinTable',this.value)" placeholder=""/>
    </div>
  </div>
  <div class="card">
    <div class="table-wrap">
      <table id="sitinTable">
        <thead>
          <tr><th>ID Number</th><th>Name</th><th>Purpose</th><th>Lab</th><th>Login Time</th><th>Sessions Left</th><th>Status</th><th>Actions</th></tr>
        </thead>
        <tbody>
          <?php if ($current_sitin): foreach ($current_sitin as $si):
            $stuStmt = $pdo->prepare("SELECT id, session FROM students WHERE id_number = ? LIMIT 1");
            $stuStmt->execute([$si['id_number']]);
            $stuRow = $stuStmt->fetch();
            $sessNum = $stuRow ? (int)$stuRow['session'] : null;
            $stuDbId = $stuRow ? (int)$stuRow['id'] : 0;
            $sessColor = $sessNum !== null ? ($sessNum <= 5 ? '#dc2626' : ($sessNum <= 10 ? '#ea580c' : '#16a34a')) : '';
          ?>
          <tr>
            <td><?= htmlspecialchars($si['id_number']) ?></td>
            <td><?= htmlspecialchars($si['fullname']) ?></td>
            <td><?= htmlspecialchars($si['sit_purpose']) ?></td>
            <td><?= htmlspecialchars($si['laboratory']) ?></td>
            <td><?= htmlspecialchars($si['login_time'] ?? '—') ?></td>
            <td>
              <?php if ($sessNum !== null): ?>
                <span style="font-weight:700;color:<?= $sessColor ?>;"><?= $sessNum ?></span>
                <span style="font-size:11px;color:var(--gray-400);"> / 30</span>
              <?php else: ?>
                <span style="color:var(--gray-400);font-size:12px;">Walk-in</span>
              <?php endif; ?>
            </td>
            <td><span class="badge badge-approved">Active</span></td>
            <td style="display:flex;gap:5px;flex-wrap:wrap;">
              <?php if ($stuDbId > 0): ?>
                <button class="btn btn-blue btn-sm"
                  onclick="openEditSession(<?= $stuDbId ?>,'<?= addslashes($si['fullname']) ?>','<?= $sessNum ?>')">
                  Edit Session
                </button>
              <?php endif; ?>
              <form method="POST" style="display:inline;">
                <input type="hidden" name="sitin_id" value="<?= $si['id'] ?>"/>
                <button type="submit" name="logout_sitin" class="btn btn-red btn-sm"
                  onclick="return confirm('Log out this student?')">Log Out</button>
              </form>
            </td>
          </tr>
          <?php endforeach; else: ?>
          <tr><td colspan="8" class="no-data">No students currently sitting in.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
    <div style="padding:10px 14px;font-size:12px;color:var(--gray-400);display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:8px;">
      <span>Showing <?= count($current_sitin) ?> active sit-in<?= count($current_sitin)!==1?'s':'' ?></span>
      <span style="display:flex;align-items:center;gap:12px;">
        <span><span style="color:#16a34a;font-weight:700;">●</span> &gt;10 sessions</span>
        <span><span style="color:#ea580c;font-weight:700;">●</span> 6–10 sessions</span>
        <span><span style="color:#dc2626;font-weight:700;">●</span> ≤5 sessions</span>
      </span>
    </div>
  </div>
</div>

<!-- ════════════ RECORDS ════════════ -->
<div id="page-records" class="page-section <?= $page==='records'?'active':'' ?>">
  <div class="page-title">All Sit-in Records</div>
  <div class="toolbar">
    <div style="display:flex;align-items:center;gap:8px;">
      <select class="entries-select" id="recordsEntries" onchange="paginateRecords()">
        <option value="10">10</option><option value="25">25</option><option value="50">50</option><option value="100">100</option>
      </select>
      <span style="font-size:13px;color:var(--gray-600);">entries per page</span>
    </div>
    <div class="toolbar-right">
      <span style="font-size:13px;color:var(--gray-600);">Search:</span>
      <input type="text" class="search-input" id="recordsSearch" oninput="filterRecords(this.value)" placeholder=""/>
    </div>
  </div>
  <div class="card">
    <div class="table-wrap">
      <table id="recordsTable">
        <thead>
          <tr><th>#</th><th>ID Number</th><th>Name</th><th>Purpose</th><th>Lab</th><th>Login</th><th>Logout</th><th>Date</th><th>Status</th><th>Action</th></tr>
        </thead>
        <tbody id="recordsBody">
          <?php if ($all_sitin): $cnt=0; foreach ($all_sitin as $r): $cnt++;
            $isActive = empty($r['logout_time']); ?>
          <tr>
            <td><?= $cnt ?></td>
            <td><?= htmlspecialchars($r['id_number']) ?></td>
            <td><?= htmlspecialchars($r['fullname']) ?></td>
            <td><?= htmlspecialchars($r['sit_purpose']) ?></td>
            <td><?= htmlspecialchars($r['laboratory']) ?></td>
            <td><?= htmlspecialchars($r['login_time'] ?? '—') ?></td>
            <td><?= htmlspecialchars($r['logout_time'] ?? '—') ?></td>
            <td><?= htmlspecialchars($r['date']) ?></td>
            <td>
              <?php if ($isActive): ?>
                <span class="badge badge-approved">Active</span>
              <?php else: ?>
                <span class="badge" style="background:#f1f5f9;color:#64748b;">Done</span>
              <?php endif; ?>
            </td>
            <td>
              <?php if ($isActive): ?>
                <form method="POST" style="display:inline;">
                  <input type="hidden" name="sitin_id" value="<?= $r['id'] ?>"/>
                  <button type="submit" name="logout_sitin" class="btn btn-red btn-sm"
                    onclick="return confirm('Log out this student?')">Log Out</button>
                </form>
              <?php else: ?>
                <span style="font-size:12px;color:var(--gray-400);">—</span>
              <?php endif; ?>
            </td>
          </tr>
          <?php endforeach; else: ?>
          <tr><td colspan="10" class="no-data">No sit-in records yet.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
    <div style="padding:12px 16px;border-top:1px solid var(--gray-100);display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:8px;">
      <span style="font-size:12.5px;color:var(--gray-400);" id="recordsInfo"></span>
      <div style="display:flex;align-items:center;gap:4px;">
        <button class="page-btn" onclick="goRecordsPage('first')">«</button>
        <button class="page-btn" onclick="goRecordsPage('prev')">‹</button>
        <span id="recordsPageBtns" style="display:flex;gap:4px;"></span>
        <button class="page-btn" onclick="goRecordsPage('next')">›</button>
        <button class="page-btn" onclick="goRecordsPage('last')">»</button>
      </div>
    </div>
  </div>
</div>

<!-- ════════════ REPORTS ════════════ -->
<div id="page-reports" class="page-section <?= $page==='reports'?'active':'' ?>">
  <div class="page-title">Sit-in Reports</div>
  <div class="home-grid">
    <div class="card">
      <div class="card-head"><h2>Purpose Breakdown</h2></div>
      <div class="chart-wrap"><canvas id="reportsChart" style="max-width:300px;"></canvas></div>
    </div>
    <div class="card">
      <div class="card-head"><h2>Summary Table</h2></div>
      <div class="table-wrap">
        <table>
          <thead><tr><th>Purpose</th><th>Count</th></tr></thead>
          <tbody>
            <?php foreach ($purpose_rows as $p): ?>
            <tr><td><?= htmlspecialchars($p['sit_purpose']) ?></td><td><?= $p['cnt'] ?></td></tr>
            <?php endforeach; ?>
            <?php if (!$purpose_rows): ?>
            <tr><td colspan="2" class="no-data">No data yet.</td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<!-- ════════════ RESERVATION ════════════ -->
<div id="page-reservation" class="page-section <?= $page==='reservation'?'active':'' ?>">
  <div class="page-title">Reservations</div>
  <div class="toolbar">
    <div class="toolbar-right">
      <span style="font-size:13px;color:var(--gray-600);">Search:</span>
      <input type="text" class="search-input" oninput="filterTable('reservTable',this.value)" placeholder=""/>
    </div>
  </div>
  <div class="card">
    <div class="table-wrap">
      <table id="reservTable">
        <thead>
          <tr><th>Student</th><th>ID Number</th><th>Purpose</th><th>Lab</th><th>Date</th><th>Time</th><th>Status</th><th>Actions</th></tr>
        </thead>
        <tbody>
          <?php if ($reservations): foreach ($reservations as $rv): ?>
          <tr>
            <td><?= htmlspecialchars($rv['firstname'].' '.$rv['lastname']) ?></td>
            <td><?= htmlspecialchars($rv['id_number']) ?></td>
            <td><?= htmlspecialchars($rv['purpose']) ?></td>
            <td><?= htmlspecialchars($rv['laboratory']) ?></td>
            <td><?= htmlspecialchars($rv['date'] ?? '—') ?></td>
            <td><?= htmlspecialchars($rv['time_in'] ?? '—') ?></td>
            <td><span class="badge badge-<?= $rv['status'] ?>"><?= ucfirst($rv['status']) ?></span></td>
            <td style="display:flex;gap:5px;">
              <?php if ($rv['status'] === 'pending'): ?>
              <form method="POST" style="display:inline;">
                <input type="hidden" name="reservation_id" value="<?= $rv['id'] ?>"/>
                <button type="submit" name="approve_reservation" class="btn btn-green btn-sm">Approve</button>
              </form>
              <form method="POST" style="display:inline;">
                <input type="hidden" name="reservation_id" value="<?= $rv['id'] ?>"/>
                <button type="submit" name="reject_reservation" class="btn btn-red btn-sm" onclick="return confirm('Reject this reservation?')">Reject</button>
              </form>
              <?php else: ?>
                <span style="font-size:12px;color:var(--gray-400);"><?= ucfirst($rv['status']) ?></span>
              <?php endif; ?>
            </td>
          </tr>
          <?php endforeach; else: ?>
          <tr><td colspan="8" class="no-data">No reservations yet.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

</div><!-- end page-body -->

<!-- ══════════════════════════════════════
     MODALS
══════════════════════════════════════ -->

<!-- SEARCH MODAL -->
<div class="modal-overlay" id="searchModal">
  <div class="modal" style="max-width:420px;">
    <div class="modal-head">
      <h3>Search Student</h3>
      <button class="modal-close" onclick="closeModal('searchModal')">×</button>
    </div>
    <div class="modal-body">
      <input type="text" class="search-input" id="globalSearch" placeholder="Search by name or ID..."
             style="width:100%;font-size:14px;padding:9px 12px;"
             oninput="globalSearchFn(this.value)"/>
      <div id="searchResults" style="margin-top:14px;max-height:300px;overflow-y:auto;"></div>
    </div>
  </div>
</div>

<!-- SIT-IN FORM MODAL -->
<div class="modal-overlay" id="sitinModal">
  <div class="modal" style="max-width:480px;">
    <div class="modal-head">
      <h3>Sit In Form</h3>
      <button class="modal-close" onclick="closeSitinModal()">×</button>
    </div>
    <form method="POST">
      <div class="modal-body">
        <input type="hidden" name="student_id" id="sitin_student_id" value="0"/>
        <table style="width:100%;border-collapse:separate;border-spacing:0 10px;">
          <tr>
            <td style="width:38%;font-size:13px;color:#3d607f;font-weight:600;padding-right:12px;white-space:nowrap;">ID Number:</td>
            <td>
              <div style="display:flex;gap:6px;">
                <input type="text" name="id_number" id="sitin_id_number" placeholder="Enter student ID"
                       style="flex:1;padding:8px 11px;border:1px solid var(--blue-bd);border-radius:6px;font-size:13px;font-family:inherit;color:var(--blue-dk);outline:none;"/>
                <button type="button" onclick="lookupStudent()"
                        style="padding:8px 12px;background:var(--blue);color:#fff;border:none;border-radius:6px;font-size:12px;font-weight:700;cursor:pointer;white-space:nowrap;">
                  Look up
                </button>
              </div>
              <div id="sitin_lookup_msg" style="font-size:11.5px;margin-top:4px;display:none;"></div>
            </td>
          </tr>
          <tr>
            <td style="font-size:13px;color:#3d607f;font-weight:600;padding-right:12px;">Student Name:</td>
            <td><input type="text" name="student_name" id="sitin_name" placeholder="Enter full name"
                       style="width:100%;padding:8px 11px;border:1px solid var(--blue-bd);border-radius:6px;font-size:13px;font-family:inherit;color:var(--blue-dk);outline:none;"/></td>
          </tr>
          <tr>
            <td style="font-size:13px;color:#3d607f;font-weight:600;padding-right:12px;">Purpose:</td>
            <td><input type="text" name="purpose" id="sitin_purpose" placeholder="e.g. C Programming" required
                       style="width:100%;padding:8px 11px;border:1px solid var(--blue-bd);border-radius:6px;font-size:13px;font-family:inherit;color:var(--blue-dk);outline:none;"/></td>
          </tr>
          <tr>
            <td style="font-size:13px;color:#3d607f;font-weight:600;padding-right:12px;">Lab:</td>
            <td><input type="text" name="lab" id="sitin_lab" placeholder="e.g. 524" required
                       style="width:100%;padding:8px 11px;border:1px solid var(--blue-bd);border-radius:6px;font-size:13px;font-family:inherit;color:var(--blue-dk);outline:none;"/></td>
          </tr>
          <tr>
            <td style="font-size:13px;color:#3d607f;font-weight:600;padding-right:12px;">Sessions Left:</td>
            <td><input type="text" id="sitin_session" readonly placeholder="Auto-filled for registered students"
                       style="width:100%;padding:8px 11px;border:1px solid var(--blue-bd);border-radius:6px;font-size:13px;background:var(--gray-100);font-family:inherit;color:var(--blue-dk);"/></td>
          </tr>
        </table>
      </div>
      <div class="modal-footer">
        <button type="button" style="padding:8px 20px;border-radius:6px;border:none;background:var(--gray-200);color:var(--gray-800);font-size:13px;font-weight:600;font-family:inherit;cursor:pointer;" onclick="closeSitinModal()">Cancel</button>
        <button type="submit" name="do_sitin" style="padding:8px 20px;border-radius:6px;border:none;background:var(--blue);color:#fff;font-size:13px;font-weight:600;font-family:inherit;cursor:pointer;">Sit In</button>
      </div>
    </form>
  </div>
</div>

<!-- ADD STUDENT MODAL -->
<div class="modal-overlay" id="addStudentModal">
  <div class="modal" style="max-width:520px;">
    <div class="modal-head">
      <h3>Add Student</h3>
      <button class="modal-close" onclick="closeModal('addStudentModal')">×</button>
    </div>
    <form method="POST">
      <div class="modal-body">
        <div class="field-row">
          <div class="field"><label>ID Number *</label><input type="text" name="id_number" required/></div>
          <div class="field"><label>Email *</label><input type="email" name="email" required/></div>
        </div>
        <div class="field-row">
          <div class="field"><label>Last Name *</label><input type="text" name="lastname" required/></div>
          <div class="field"><label>First Name *</label><input type="text" name="firstname" required/></div>
        </div>
        <div class="field"><label>Middle Name</label><input type="text" name="middlename"/></div>
        <div class="field-row">
          <div class="field"><label>Course *</label>
            <select name="course" required>
              <option value="">Select</option>
              <option>BSIT</option><option>BSCS</option><option>BSDA</option><option>ACT</option>
            </select>
          </div>
          <div class="field"><label>Year Level *</label>
            <select name="year_level" required>
              <option value="1">1st Year</option><option value="2">2nd Year</option>
              <option value="3">3rd Year</option><option value="4">4th Year</option>
            </select>
          </div>
        </div>
        <div class="field"><label>Password (default: Password123)</label><input type="text" name="password" placeholder="Password123"/></div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn" style="background:var(--gray-200);color:var(--gray-800);" onclick="closeModal('addStudentModal')">Cancel</button>
        <button type="submit" name="add_student" class="btn btn-blue">Add Student</button>
      </div>
    </form>
  </div>
</div>

<!-- EDIT STUDENT MODAL -->
<div class="modal-overlay" id="editStudentModal">
  <div class="modal" style="max-width:520px;">
    <div class="modal-head">
      <h3>Edit Student</h3>
      <button class="modal-close" onclick="closeModal('editStudentModal')">×</button>
    </div>
    <form method="POST">
      <div class="modal-body">
        <input type="hidden" name="student_id" id="edit_student_id"/>
        <div class="field-row">
          <div class="field"><label>Last Name *</label><input type="text" name="lastname" id="edit_ln" required/></div>
          <div class="field"><label>First Name *</label><input type="text" name="firstname" id="edit_fn" required/></div>
        </div>
        <div class="field"><label>Middle Name</label><input type="text" name="middlename" id="edit_mn"/></div>
        <div class="field"><label>Email *</label><input type="email" name="email" id="edit_email" required/></div>
        <div class="field-row">
          <div class="field"><label>Course *</label>
            <select name="course" id="edit_course" required>
              <option>BSIT</option><option>BSCS</option><option>BSDA</option><option>ACT</option>
            </select>
          </div>
          <div class="field"><label>Year Level *</label>
            <select name="year_level" id="edit_year" required>
              <option value="1">1st Year</option><option value="2">2nd Year</option>
              <option value="3">3rd Year</option><option value="4">4th Year</option>
            </select>
          </div>
        </div>
        <div class="field">
          <label>Remaining Sessions (0–30)</label>
          <input type="number" name="session" id="edit_session" min="0" max="30" required/>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn" style="background:var(--gray-200);color:var(--gray-800);" onclick="closeModal('editStudentModal')">Cancel</button>
        <button type="submit" name="edit_student" class="btn btn-blue">Save Changes</button>
      </div>
    </form>
  </div>
</div>

<!-- EDIT SESSION MODAL -->
<div class="modal-overlay" id="editSessionModal">
  <div class="modal" style="max-width:360px;">
    <div class="modal-head">
      <h3>Edit Session</h3>
      <button class="modal-close" onclick="closeModal('editSessionModal')">×</button>
    </div>
    <form method="POST">
      <div class="modal-body">
        <input type="hidden" name="student_id" id="esess_student_id"/>
        <div style="text-align:center;margin-bottom:16px;">
          <div style="font-size:12px;color:var(--gray-400);margin-bottom:4px;">Student</div>
          <div style="font-size:15px;font-weight:700;color:var(--blue);" id="esess_name"></div>
        </div>
        <div style="display:flex;align-items:center;justify-content:center;gap:12px;margin-bottom:14px;">
          <button type="button" onclick="adjustSession(-1)"
                  style="width:36px;height:36px;border-radius:50%;border:2px solid var(--gray-200);background:#fff;font-size:20px;font-weight:700;cursor:pointer;display:flex;align-items:center;justify-content:center;color:var(--red);">−</button>
          <input type="number" name="session" id="esess_value" min="0" max="30"
                 style="width:80px;text-align:center;padding:10px;border:2px solid var(--blue-bd);border-radius:6px;font-size:22px;font-weight:800;color:var(--blue);font-family:inherit;outline:none;"/>
          <button type="button" onclick="adjustSession(1)"
                  style="width:36px;height:36px;border-radius:50%;border:2px solid var(--gray-200);background:#fff;font-size:20px;font-weight:700;cursor:pointer;display:flex;align-items:center;justify-content:center;color:#16a34a;">+</button>
        </div>
        <div style="text-align:center;font-size:12px;color:var(--gray-400);margin-bottom:8px;">Value between <strong>0</strong> and <strong>30</strong></div>
        <div style="display:flex;justify-content:center;gap:6px;flex-wrap:wrap;">
          <?php foreach([0,5,10,15,20,25,30] as $preset): ?>
          <button type="button" onclick="setSession(<?= $preset ?>)"
                  style="padding:4px 10px;border-radius:20px;border:1.5px solid var(--gray-200);background:#fff;font-size:12px;font-weight:600;cursor:pointer;color:var(--gray-600);"><?= $preset ?></button>
          <?php endforeach; ?>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn" style="background:var(--gray-200);color:var(--gray-800);" onclick="closeModal('editSessionModal')">Cancel</button>
        <button type="submit" name="edit_session_only" class="btn btn-blue">Save Session</button>
      </div>
    </form>
  </div>
</div>

<script>
// ── Student data for JS search ──
const allStudents = <?php echo json_encode(array_map(fn($s) => [
  'id'        => $s['id'],
  'id_number' => $s['id_number'],
  'name'      => $s['firstname'].' '.$s['middlename'].' '.$s['lastname'],
  'course'    => $s['course'],
  'year'      => $s['year_level'],
  'session'   => $s['session'],
], $students)); ?>;

// ── Modal helpers ──
function openModal(id){ document.getElementById(id).classList.add('open'); }
function closeModal(id){ document.getElementById(id).classList.remove('open'); }
document.querySelectorAll('.modal-overlay').forEach(o => {
  o.addEventListener('click', e => { if (e.target === o) o.classList.remove('open'); });
});

// ── Edit student ──
function openEditStudent(id,idnum,fn,mn,ln,course,year,email,session){
  document.getElementById('edit_student_id').value = id;
  document.getElementById('edit_fn').value = fn;
  document.getElementById('edit_mn').value = mn;
  document.getElementById('edit_ln').value = ln;
  document.getElementById('edit_email').value = email;
  document.getElementById('edit_course').value = course;
  document.getElementById('edit_year').value = year;
  document.getElementById('edit_session').value = session;
  openModal('editStudentModal');
}

// ── Edit session ──
function openEditSession(id, name, session){
  document.getElementById('esess_student_id').value = id;
  document.getElementById('esess_name').textContent  = name;
  document.getElementById('esess_value').value       = session;
  openModal('editSessionModal');
}
function adjustSession(delta){
  const input = document.getElementById('esess_value');
  input.value = Math.min(30, Math.max(0, (parseInt(input.value)||0) + delta));
}
function setSession(val){ document.getElementById('esess_value').value = val; }

// ── Sit-in modal ──
function openBlankSitin(){
  document.getElementById('sitin_student_id').value = '0';
  document.getElementById('sitin_id_number').value  = '';
  document.getElementById('sitin_name').value        = '';
  document.getElementById('sitin_session').value     = '';
  document.getElementById('sitin_purpose').value     = '';
  document.getElementById('sitin_lab').value         = '';
  const msg = document.getElementById('sitin_lookup_msg');
  msg.style.display = 'none'; msg.textContent = '';
  openModal('sitinModal');
}
function closeSitinModal(){
  closeModal('sitinModal');
  document.getElementById('sitin_lookup_msg').style.display = 'none';
}
function openSitinFor(id, idnum, name, session){
  document.getElementById('sitin_student_id').value = id;
  document.getElementById('sitin_id_number').value  = idnum;
  document.getElementById('sitin_name').value        = name;
  document.getElementById('sitin_session').value     = session;
  document.getElementById('sitin_purpose').value     = '';
  document.getElementById('sitin_lab').value         = '';
  const msg = document.getElementById('sitin_lookup_msg');
  msg.style.display = 'block'; msg.style.color = '#16a34a';
  msg.textContent = '✅ Registered student found — 1 session will be deducted.';
  closeModal('searchModal');
  openModal('sitinModal');
}
function lookupStudent(){
  const idnum = document.getElementById('sitin_id_number').value.trim();
  const msg   = document.getElementById('sitin_lookup_msg');
  if (!idnum){ msg.style.display='block'; msg.style.color='#dc2626'; msg.textContent='Please enter an ID number.'; return; }
  const found = allStudents.find(s => s.id_number === idnum);
  msg.style.display = 'block';
  if (found) {
    document.getElementById('sitin_student_id').value = found.id;
    document.getElementById('sitin_name').value        = found.name;
    document.getElementById('sitin_session').value     = found.session;
    msg.style.color = '#16a34a';
    msg.textContent = '✅ Registered student — 1 session will be deducted.';
  } else {
    document.getElementById('sitin_student_id').value = '0';
    document.getElementById('sitin_name').value        = '';
    document.getElementById('sitin_session').value     = '';
    msg.style.color = '#ea580c';
    msg.textContent = '⚠️ Not found. Fill name manually — walk-in will be recorded.';
  }
}

// ── Global search ──
function globalSearchFn(q){
  const box = document.getElementById('searchResults');
  if (!q.trim()){ box.innerHTML = ''; return; }
  const res = allStudents.filter(s =>
    s.id_number.toLowerCase().includes(q.toLowerCase()) ||
    s.name.toLowerCase().includes(q.toLowerCase())
  );
  if (!res.length){ box.innerHTML = '<p style="color:#aaa;font-size:13px;">No results.</p>'; return; }
  box.innerHTML = res.map(s => `
    <div style="display:flex;align-items:center;justify-content:space-between;padding:9px 0;border-bottom:1px solid #eee;">
      <div>
        <div style="font-size:13px;font-weight:600;">${s.name}</div>
        <div style="font-size:12px;color:#888;">${s.id_number} · ${s.course} Y${s.year} · ${s.session} sessions</div>
      </div>
      <button class="btn btn-blue btn-sm" onclick="openSitinFor(${s.id},'${s.id_number}','${s.name.replace(/'/g,"\\'")}',${s.session})">Sit In</button>
    </div>
  `).join('');
}

// ── Table filter ──
function filterTable(tableId, q){
  document.querySelectorAll('#'+tableId+' tbody tr').forEach(r => {
    r.style.display = r.textContent.toLowerCase().includes(q.toLowerCase()) ? '' : 'none';
  });
}

// ── Records pagination ──
let recordsCurrentPage = 1;
let recordsSearchQuery = '';
function getRecordsRows(){ return Array.from(document.querySelectorAll('#recordsBody tr')); }
function getFilteredRecordsRows(){
  const q = recordsSearchQuery.toLowerCase();
  return getRecordsRows().filter(r => !q || r.textContent.toLowerCase().includes(q));
}
function renderRecords(){
  const perPage  = parseInt(document.getElementById('recordsEntries').value) || 10;
  const filtered = getFilteredRecordsRows();
  const total    = filtered.length;
  const pages    = Math.max(1, Math.ceil(total / perPage));
  recordsCurrentPage = Math.min(recordsCurrentPage, pages);
  const start = (recordsCurrentPage - 1) * perPage;
  const end   = start + perPage;
  getRecordsRows().forEach(r => r.style.display = 'none');
  filtered.forEach((r, i) => { r.style.display = (i >= start && i < end) ? '' : 'none'; });
  const showing = Math.min(end, total);
  document.getElementById('recordsInfo').textContent = total === 0 ? 'No entries' :
    `Showing ${start + 1} to ${showing} of ${total} entr${total === 1 ? 'y' : 'ies'}`;
  const btns = document.getElementById('recordsPageBtns');
  btns.innerHTML = '';
  const maxPages = Math.min(pages, 10);
  const startPage = Math.max(1, recordsCurrentPage - 4);
  for (let i = startPage; i <= Math.min(startPage + maxPages - 1, pages); i++){
    const b = document.createElement('button');
    b.className = 'page-btn' + (i === recordsCurrentPage ? ' active' : '');
    b.textContent = i;
    b.onclick = () => { recordsCurrentPage = i; renderRecords(); };
    btns.appendChild(b);
  }
}
function goRecordsPage(dir){
  const perPage = parseInt(document.getElementById('recordsEntries').value) || 10;
  const pages   = Math.max(1, Math.ceil(getFilteredRecordsRows().length / perPage));
  if (dir === 'first') recordsCurrentPage = 1;
  else if (dir === 'prev')  recordsCurrentPage = Math.max(1, recordsCurrentPage - 1);
  else if (dir === 'next')  recordsCurrentPage = Math.min(pages, recordsCurrentPage + 1);
  else if (dir === 'last')  recordsCurrentPage = pages;
  renderRecords();
}
function filterRecords(q){ recordsSearchQuery = q; recordsCurrentPage = 1; renderRecords(); }
function paginateRecords(){ recordsCurrentPage = 1; renderRecords(); }
window.addEventListener('load', renderRecords);

// ── Pie Charts ──
const purposeLabels = <?php echo json_encode(array_column($purpose_rows,'sit_purpose') ?: ['No Data']); ?>;
const purposeCounts = <?php echo json_encode(array_column($purpose_rows,'cnt') ?: [1]); ?>;
const chartColors = ['#1e3a5f','#e63946','#f4a261','#2a9d8f','#e9c46a','#264653','#457b9d','#a8dadc'];

function buildChart(canvasId){
  const ctx = document.getElementById(canvasId);
  if (!ctx) return;
  new Chart(ctx, {
    type: 'pie',
    data: {
      labels: purposeLabels.map((l,i) => l + ' (' + purposeCounts[i] + ')'),
      datasets:[{ data: purposeCounts, backgroundColor: chartColors, borderWidth: 2, borderColor: '#fff' }]
    },
    options: {
      plugins:{
        legend:{ position:'top', labels:{ font:{ size:11, family:"'Plus Jakarta Sans', sans-serif" }, padding:10, boxWidth:12 }},
        tooltip:{ callbacks:{ label: c => ' ' + c.label.split(' (')[0] + ': ' + c.raw }}
      },
      responsive:true
    }
  });
}
buildChart('purposeChart');
buildChart('reportsChart');
</script>
</body>
</html>