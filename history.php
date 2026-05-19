<?php
session_start();
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: login.php'); exit;
}
require_once 'db.php';

$history = $pdo->prepare("SELECT * FROM sit_in_history WHERE student_id = ? ORDER BY date DESC, login_time DESC");
$history->execute([$_SESSION['student_id']]);
$rows = $history->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"/><meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>CCS | Sit-in History</title>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet"/>
<style>
*{box-sizing:border-box;margin:0;padding:0;}
body{font-family:'Plus Jakarta Sans',sans-serif;background:#f7f8fa;color:#1e2a38;min-height:100vh;font-size:14px;}
nav{background:#1e3a5f;height:54px;padding:0 24px;display:flex;align-items:center;justify-content:space-between;position:sticky;top:0;z-index:100;}
.nav-brand{font-size:13.5px;font-weight:700;color:#fff;}
.nav-links{display:flex;align-items:center;gap:1px;}
.nav-links a{font-size:13px;color:rgba(255,255,255,0.7);text-decoration:none;padding:6px 10px;border-radius:5px;transition:all .15s;white-space:nowrap;}
.nav-links a:hover,.nav-links a.active{color:#fff;background:rgba(255,255,255,0.1);}
.btn-logout{background:#dc3545 !important;color:#fff !important;font-weight:600 !important;border-radius:5px;padding:6px 14px !important;margin-left:6px;}
.btn-logout:hover{background:#c82333 !important;}
.page-body{max-width:1200px;margin:0 auto;padding:28px 20px 52px;}
.page-title{font-size:22px;font-weight:800;color:#1e3a5f;margin-bottom:24px;text-align:center;}
.page-title span{display:block;font-size:13px;font-weight:400;color:#9aa5b4;margin-top:4px;}
.toolbar{display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;flex-wrap:wrap;gap:10px;background:#fff;padding:12px 16px;border-radius:8px;border:1px solid #e2e6ea;}
.toolbar-left{display:flex;align-items:center;gap:10px;font-size:13px;color:#4a5568;flex-wrap:wrap;}
.toolbar-left select{padding:5px 10px;border:1px solid #d0d7e2;border-radius:6px;font-size:13px;font-family:'Plus Jakarta Sans',sans-serif;outline:none;background:#fff;font-weight:500;}
.toolbar-right{display:flex;align-items:center;gap:8px;font-size:13px;color:#4a5568;}
.toolbar-right input{padding:6px 12px;border:1px solid #d0d7e2;border-radius:6px;font-size:13px;font-family:'Plus Jakarta Sans',sans-serif;outline:none;width:200px;transition:border-color 0.2s;}
.toolbar-right input:focus{border-color:#1e3a5f;}
.card{background:#fff;border-radius:12px;box-shadow:0 4px 12px rgba(0,0,0,0.08);overflow:hidden;border:none;}
.table-wrap{overflow-x:hidden;}
table{width:100%;border-collapse:collapse;table-layout:fixed;}
thead tr{background:linear-gradient(135deg, #1e3a5f, #2a5a8a);color:#fff;}
thead th{padding:14px 16px;text-align:left;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:0.06em;border-right:1px solid rgba(255,255,255,0.05);}
thead th:last-child{border-right:none;}
tbody tr{border-bottom:1px solid #eef0f3;transition:all 0.2s;cursor:default;}
tbody tr:nth-child(even){background:#fafbfc;}
tbody tr:hover{background:#f0f4f9;transform:scale(1.002);}
tbody td{padding:12px 16px;font-size:13px;color:#4a5568;}
.no-data{text-align:center;padding:40px;color:#9aa5b4;font-size:14px;font-style:italic;}
.badge{display:inline-flex;align-items:center;gap:4px;padding:4px 14px;border-radius:20px;font-size:11px;font-weight:600;}
.badge-active{background:#d4edda;color:#155724;border:1px solid #b7ebc5;}
.badge-done{background:#f1f5f9;color:#64748b;border:1px solid #e2e8f0;}
.badge-active span,.badge-done span{width:8px;height:8px;border-radius:50%;display:inline-block;}
.badge-active span{background:#28a745;}
.badge-done span{background:#94a3b8;}
.btn-feedback{padding:4px 12px;border-radius:4px;font-size:11px;font-weight:500;cursor:pointer;border:none;background:#1e3a5f;color:#fff;text-decoration:none;display:inline-flex;align-items:center;gap:4px;transition:all 0.2s;}
.btn-feedback:hover{background:#16304f;transform:translateY(-1px);}
.table-footer{display:flex;align-items:center;justify-content:space-between;padding:14px 18px;border-top:1px solid #eef0f3;background:#fafbfc;flex-wrap:wrap;gap:8px;}
.table-footer span{font-size:12.5px;color:#4a5568;font-weight:500;}
.pagination{display:flex;align-items:center;gap:3px;}
.page-btn{border:none;background:transparent;color:#4a5568;cursor:pointer;padding:4px 8px;font-size:14px;transition:all 0.2s;}
.page-btn:hover{color:#1e3a5f;}
.page-btn.active{background:#1e3a5f;color:#fff;border-radius:4px;padding:4px 10px;}
@media(max-width:700px){table{font-size:12px;}thead th,tbody td{padding:8px 10px;}nav{padding:0 14px;}.toolbar{flex-direction:column;align-items:stretch;}.toolbar-right input{width:100%;}}
</style>
</head>
<body>
  <nav>
  <div class="nav-brand">CCS Sit-in Monitoring System</div>
  <div class="nav-links">
    <?php include 'notif_dropdown.php'; ?>
    <a href="homepage.php">Home</a>
    <a href="profile.php">Edit Profile</a>
    <a href="history.php" class="active">History</a>
    <a href="reservation.php">Reservation</a>
    <a href="logout.php" class="btn-logout">Log out</a>
  </div>
</nav>
<div class="page-body">
  <div class="page-title">
    📋 Sit-in History
    <span>Complete record of all your laboratory sit-in sessions</span>
  </div>
  
  <div class="toolbar">
    <div class="toolbar-left">
      <span>Show</span>
      <select id="entriesSelect" onchange="renderPage()">
        <option value="10">10</option>
        <option value="25">25</option>
        <option value="50">50</option>
        <option value="100">100</option>
      </select>
      <span>entries</span>
    </div>
    <div class="toolbar-right">
      <span>🔍 Search:</span>
      <input type="text" id="searchInput" placeholder="Type to filter..." oninput="filterTable()"/>
    </div>
  </div>
  
  <div class="card">
    <div class="table-wrap">
      <table id="historyTable">
        <thead>
          <tr>
            <th>#</th>
            <th>ID Number</th>
            <th>Name</th>
            <th>Purpose</th>
            <th>Laboratory</th>
            <th>Login</th>
            <th>Logout</th>
            <th>Duration</th>
            <th>Date</th>
            <th>Status</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody id="historyBody">
          <?php if ($rows): $i=0; foreach ($rows as $r): $i++;
            $duration = '';
            if (!empty($r['login_time']) && !empty($r['logout_time'])) {
                $login = new DateTime($r['login_time']);
                $logout = new DateTime($r['logout_time']);
                $interval = $login->diff($logout);
                $duration = $interval->format('%h h %i min');
            }
            $isActive = empty($r['logout_time']);
          ?>
          <tr>
            <td style="font-weight:600;text-align:center;"><?= $i ?></td>
            <td style="font-weight:600;font-family:monospace;color:#1e3a5f;"><?= htmlspecialchars($r['id_number']) ?></td>
            <td style="font-weight:500;color:#1e2a38;"><?= htmlspecialchars($r['fullname']) ?></td>
            <td>
              <span style="display:inline-block;background:#e8edf5;color:#1e3a5f;padding:3px 12px;border-radius:14px;font-size:12px;font-weight:500;"><?= htmlspecialchars($r['sit_purpose']) ?></span>
            </td>
            <td>
              <span style="display:inline-block;background:#e8edf5;padding:3px 10px;border-radius:6px;font-size:12px;"><?= htmlspecialchars($r['laboratory']) ?></span>
            </td>
            <td style="font-family:monospace;font-weight:500;white-space:nowrap;">
              <?php if (!empty($r['login_time'])): ?>
                <?= date('h:i A', strtotime($r['login_time'])) ?>
              <?php else: ?>
                <span style="color:#c0c8d4;">—</span>
              <?php endif; ?>
            </td>
            <td style="font-family:monospace;font-weight:500;white-space:nowrap;">
              <?php if (!empty($r['logout_time'])): ?>
                <?= date('h:i A', strtotime($r['logout_time'])) ?>
              <?php else: ?>
                <span style="color:#c0c8d4;">—</span>
              <?php endif; ?>
            </td>
            <td style="font-family:monospace;font-weight:500;white-space:nowrap;">
              <?php if ($duration): ?>
                <?= $duration ?>
              <?php else: ?>
                <span style="color:#c0c8d4;">—</span>
              <?php endif; ?>
            </td>
            <td style="font-family:monospace;font-weight:500;white-space:nowrap;">
              <?php if (!empty($r['date'])): ?>
                <?= htmlspecialchars($r['date']) ?>
              <?php else: ?>
                <span style="color:#c0c8d4;">—</span>
              <?php endif; ?>
            </td>
            <td>
              <?php if ($isActive): ?>
                <span class="badge badge-active"><span></span> Active</span>
              <?php else: ?>
                <span class="badge badge-done"><span></span> Done</span>
              <?php endif; ?>
            </td>
            <td>
              <a href="send_feedback.php?sitin_id=<?= $r['id'] ?>" class="btn-feedback">
                ✏️ Give Feedback
              </a>
            </td>
          </tr>
          <?php endforeach; else: ?>
          <tr><td colspan="11" class="no-data">📭 No sit-in history found yet.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
    <div class="table-footer">
      <span id="tableInfo">Showing <?= count($rows) ?> entr<?= count($rows)===1?'y':'ies' ?></span>
      <div class="pagination" id="paginationWrap"></div>
    </div>
  </div>
</div>

<script>
let currentPage = 1;
let filteredRows = [];

function getAllRows() {
  return Array.from(document.querySelectorAll('#historyBody tr'));
}

function filterTable() {
  const q = document.getElementById('searchInput').value.toLowerCase();
  filteredRows = getAllRows().filter(r => r.textContent.toLowerCase().includes(q));
  currentPage = 1;
  renderPage();
}

function renderPage() {
  const perPage = parseInt(document.getElementById('entriesSelect').value) || 10;
  const allRows = getAllRows();
  const q = document.getElementById('searchInput').value.toLowerCase();
  filteredRows = allRows.filter(r => !q || r.textContent.toLowerCase().includes(q));

  const total = filteredRows.length;
  const pages = Math.max(1, Math.ceil(total / perPage));
  currentPage = Math.min(currentPage, pages);
  const start = (currentPage - 1) * perPage;
  const end = start + perPage;

  allRows.forEach(r => r.style.display = 'none');
  filteredRows.forEach((r, i) => { r.style.display = (i >= start && i < end) ? '' : 'none'; });

  const showing = Math.min(end, total);
  document.getElementById('tableInfo').textContent =
    total === 0 ? 'No entries found' :
    `Showing ${start + 1} to ${showing} of ${total} entr${total === 1 ? 'y' : 'ies'}`;

  const wrap = document.getElementById('paginationWrap');
  wrap.innerHTML = '';
  const mkBtn = (label, page, active) => {
    const b = document.createElement('button');
    b.className = 'page-btn' + (active ? ' active' : '');
    b.textContent = label;
    b.onclick = () => { currentPage = page; renderPage(); };
    return b;
  };
  wrap.appendChild(mkBtn('«', 1, false));
  wrap.appendChild(mkBtn('‹', Math.max(1, currentPage - 1), false));
  for (let i = 1; i <= Math.min(pages, 10); i++) wrap.appendChild(mkBtn(i, i, i === currentPage));
  wrap.appendChild(mkBtn('›', Math.min(pages, currentPage + 1), false));
  wrap.appendChild(mkBtn('»', pages, false));
}

window.addEventListener('load', () => { filteredRows = getAllRows(); renderPage(); });
</script>
</body>
</html>