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
<title>CCS | History</title>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet"/>
<style>
*{box-sizing:border-box;margin:0;padding:0;}
body{font-family:'Plus Jakarta Sans',sans-serif;background:#f7f8fa;color:#1e2a38;min-height:100vh;font-size:14px;}
nav{background:#1e3a5f;height:54px;padding:0 24px;display:flex;align-items:center;justify-content:space-between;position:sticky;top:0;z-index:100;}
.nav-brand{font-size:13.5px;font-weight:700;color:#fff;}
.nav-links{display:flex;align-items:center;gap:1px;}
.nav-links a{font-size:13px;color:rgba(255,255,255,0.7);text-decoration:none;padding:6px 10px;border-radius:5px;transition:all .15s;white-space:nowrap;}
.nav-links a:hover,.nav-links a.active{color:#fff;background:rgba(255,255,255,0.1);}
.btn-logout{background:#c53030 !important;color:#fff !important;font-weight:600 !important;border-radius:5px;padding:6px 14px !important;margin-left:6px;}
.btn-logout:hover{background:#9b2c2c !important;}
.page-body{max-width:1080px;margin:0 auto;padding:28px 20px 52px;}
.page-title{font-size:19px;font-weight:700;color:#1e3a5f;margin-bottom:20px;text-align:center;}
.toolbar{display:flex;align-items:center;justify-content:space-between;margin-bottom:12px;flex-wrap:wrap;gap:8px;}
.entries-wrap{display:flex;align-items:center;gap:7px;font-size:13px;color:#4a5568;}
.entries-wrap select{padding:5px 9px;border:1px solid #d0d7e2;border-radius:5px;font-family:'Plus Jakarta Sans',sans-serif;font-size:13px;outline:none;}
.search-wrap{display:flex;align-items:center;gap:7px;font-size:13px;color:#4a5568;}
.search-wrap input{padding:6px 11px;border:1px solid #d0d7e2;border-radius:5px;font-family:'Plus Jakarta Sans',sans-serif;font-size:13px;outline:none;width:200px;}
.search-wrap input:focus{border-color:#1e3a5f;box-shadow:0 0 0 3px rgba(30,58,95,0.07);}
.card{background:#fff;border-radius:8px;border:1px solid #e2e6ea;overflow:hidden;}
table{width:100%;border-collapse:collapse;}
thead tr{background:#1e3a5f;}
thead th{color:#fff;font-size:11px;font-weight:600;padding:10px 13px;text-align:left;white-space:nowrap;text-transform:uppercase;letter-spacing:0.03em;}
tbody tr{border-bottom:1px solid #f0f2f5;transition:background .12s;}
tbody tr:last-child{border-bottom:none;}
tbody tr:hover{background:#fafbfc;}
tbody td{padding:10px 13px;font-size:13px;color:#4a5568;}
.no-data{text-align:center;padding:32px;color:#9aa5b4;font-size:13px;font-style:italic;}
.badge{display:inline-block;padding:2px 9px;border-radius:20px;font-size:11.5px;font-weight:600;}
.badge-active{background:#f0fff4;color:#276749;}
.badge-done{background:#f1f5f9;color:#64748b;}
.table-footer{display:flex;align-items:center;justify-content:space-between;padding:11px 15px;border-top:1px solid #f0f2f5;font-size:12px;color:#9aa5b4;flex-wrap:wrap;gap:7px;}
.pagination{display:flex;align-items:center;gap:4px;}
.page-btn{width:28px;height:28px;border-radius:5px;border:1px solid #d0d7e2;background:#fff;font-size:12px;font-family:'Plus Jakarta Sans',sans-serif;color:#4a5568;cursor:pointer;display:flex;align-items:center;justify-content:center;transition:all .15s;text-decoration:none;}
.page-btn:hover{border-color:#1e3a5f;color:#1e3a5f;}
.page-btn.active{background:#1e3a5f;border-color:#1e3a5f;color:#fff;font-weight:600;}
@media(max-width:700px){table{font-size:12px;}thead th,tbody td{padding:8px 10px;}nav{padding:0 14px;}}
</style>
</head>
<body>
<nav>
  <div class="nav-brand">CCS Sit-in Monitoring System</div>
  <div class="nav-links">
   <a href="notifications.php">
        Notifications 
        <span id="notif-badge" style="display:none; background:#e63946; color:white; border-radius:10px; padding:2px 6px; font-size:10px; font-weight:bold;">0</span>
    </a>
    <a href="homepage.php">Home</a>
    <a href="profile.php">Edit Profile</a>
    <a href="history.php" class="active">History</a>
    <a href="reservation.php">Reservation</a>
    <a href="logout.php" class="btn-logout">Log out</a>
  </div>
</nav>
<div class="page-body">
  <div class="page-title">Sit-in History</div>
  <div class="toolbar">
    <div class="entries-wrap">
      Show <select id="entriesSelect" onchange="renderPage()">
        <option value="10">10</option><option value="25">25</option><option value="50">50</option><option value="100">100</option>
      </select> entries
    </div>
    <div class="search-wrap">
      Search: <input type="text" id="searchInput" placeholder="Search records..." oninput="filterTable()"/>
    </div>
  </div>
  <div class="card">
    <table>
      <thead>
        <tr>
          <th>#</th><th>ID Number</th><th>Name</th><th>Purpose</th><th>Laboratory</th><th>Login</th><th>Logout</th><th>Date</th><th>Status</th><th>Action</th>
        </tr>
      </thead>
      <tbody id="historyBody">
        <?php if ($rows): $i=0; foreach ($rows as $r): $i++; ?>
        <tr>
          <td><?= $i ?></td>
          <td><?= htmlspecialchars($r['id_number']) ?></td>
          <td><?= htmlspecialchars($r['fullname']) ?></td>
          <td><?= htmlspecialchars($r['sit_purpose']) ?></td>
          <td><?= htmlspecialchars($r['laboratory']) ?></td>
          <td><?= htmlspecialchars($r['login_time'] ?? '—') ?></td>
          <td><?= htmlspecialchars($r['logout_time'] ?? '—') ?></td>
          <td><?= htmlspecialchars($r['date']) ?></td>
          <td>
            <?php if (empty($r['logout_time'])): ?>
              <span class="badge badge-active">Active</span>
            <?php else: ?>
              <span class="badge badge-done">Done</span>
            <?php endif; ?>  
          </td>
          <td>
        <a href="send_feedback.php?sitin_id=<?= $r['id'] ?>" style="padding: 5px 10px; background-color: #3182ce; color: white; text-decoration: none; border-radius: 4px; font-size: 12px;">
            Give Feedback
        </a>
    </td>
        </tr>
        <?php endforeach; else: ?>
        <tr><td colspan="9" class="no-data">No sit-in history found yet.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
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
    const b = document.createElement(typeof page === 'number' ? 'button' : 'button');
    b.className = 'page-btn' + (active ? ' active' : '');
    b.textContent = label;
    b.onclick = () => { currentPage = page; renderPage(); };
    return b;
  };
  wrap.appendChild(mkBtn('«', 1, false));
  wrap.appendChild(mkBtn('‹', Math.max(1, currentPage - 1), false));
  for (let i = 1; i <= pages; i++) wrap.appendChild(mkBtn(i, i, i === currentPage));
  wrap.appendChild(mkBtn('›', Math.min(pages, currentPage + 1), false));
  wrap.appendChild(mkBtn('»', pages, false));
}

window.addEventListener('load', () => { filteredRows = getAllRows(); renderPage(); });
</script>
</body>
</html>