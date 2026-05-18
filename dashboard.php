<?php
require_once 'db.php'; // This gives us $pdo

session_start();
if (!isset($_SESSION['student_id']) || empty($_SESSION['student_id'])) {
    header("Location: login.php");
    exit();
}

$student_id = $_SESSION['student_id'];
$firstname  = $_SESSION['firstname'];
$lastname   = $_SESSION['lastname'];
$course     = $_SESSION['course'];
$year_level = $_SESSION['year_level'];

$msg      = '';
$msg_type = '';

// Handle sit-in log submission (UPDATED to PDO)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['sitin'])) {
    $lab  = trim($_POST['lab_room']);
    $purp = trim($_POST['purpose']);
    
    if ($lab && $purp) {
        $stmt = $pdo->prepare("INSERT INTO sitin_logs (student_id, lab_room, purpose) VALUES (?, ?, ?)");
        $stmt->execute([$student_id, $lab, $purp]);
        $msg      = "Sit-in logged successfully!";
        $msg_type = "success";
    } else {
        $msg      = "Please select a lab room and enter a purpose.";
        $msg_type = "error";
    }
}

// Fetch recent sit-in logs (UPDATED to PDO)
$stmt = $pdo->prepare("SELECT * FROM sitin_logs WHERE student_id = ? ORDER BY time_in DESC LIMIT 10");
$stmt->execute([$student_id]);
$logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

function ordSuffix($n) {
    if ($n == 1) return '1st';
    if ($n == 2) return '2nd';
    if ($n == 3) return '3rd';
    return $n . 'th';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>CCS | Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        /* ... Keep your existing CSS here, and add this at the bottom for the dropdown ... */
        .notif-wrapper { position: relative; display: inline-block; }
        #notif-icon { background: none; border: none; color: white; cursor: pointer; font-size: 0.9rem; position: relative; }
        .dropdown-menu { display: none; position: absolute; right: 0; top: 100%; width: 300px; background: white; border: 1px solid #ccc; box-shadow: 0px 8px 16px rgba(0,0,0,0.2); z-index: 1000; border-radius: 8px; padding: 10px; color: #333; }
        .notif-item { padding: 10px; border-bottom: 1px solid #eee; font-size: 13px; }
        .view-all { text-align: center; padding-top: 10px; }
    </style>
</head>
<body>

<nav>
    <div class="brand">College of Computer Studies Sit-in Monitoring System</div>
    <div class="nav-right">
        <div class="notif-wrapper">
            <button id="notif-icon">
                🔔 Notifications <span id="notif-badge" style="display:none; background:#ff4d4d; color:white; border-radius:50%; padding:2px 6px; font-size:10px; position:absolute; top:-10px; right:-10px;">0</span>
            </button>
            <div id="notif-dropdown" class="dropdown-menu">
                <div id="notif-list">Loading...</div>
                <div class="view-all"><a href="notifications.php">VIEW ALL</a></div>
            </div>
        </div>
        
        <span class="user-name">👤 <?= htmlspecialchars($firstname . ' ' . $lastname) ?></span>
        <a href="logout.php" class="btn-logout">Logout</a>
    </div>
</nav>

<script>
const notifBtn = document.getElementById('notif-icon');
const notifDropdown = document.getElementById('notif-dropdown');
const notifList = document.getElementById('notif-list');

// Toggle Dropdown
notifBtn.addEventListener('click', () => {
    const isVisible = notifDropdown.style.display === 'block';
    notifDropdown.style.display = isVisible ? 'none' : 'block';
    if (!isVisible) {
        fetch('api_notifications.php')
            .then(res => res.text())
            .then(html => { notifList.innerHTML = html; });
    }
});

// Update Badge Logic
function updateBadge() {
    fetch('api_count.php') // Create this tiny file below
        .then(res => res.json())
        .then(data => {
            const badge = document.getElementById('notif-badge');
            if (data.count > 0) {
                badge.textContent = data.count;
                badge.style.display = 'inline';
            } else {
                badge.style.display = 'none';
            }
        });
}
setInterval(updateBadge, 5000);
updateBadge();
</script>
</body>
</html>