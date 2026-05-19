<?php
require_once 'db.php';

// Fetch unread count for current student
$unreadStmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE student_id = ? AND is_read = 0");
$unreadStmt->execute([$_SESSION['student_id']]);
$unread_count = $unreadStmt->fetchColumn();

// Fetch recent notifications (last 5)
$notifStmt = $pdo->prepare("
    SELECT message, created_at FROM notifications WHERE student_id = ? AND message NOT LIKE 'Feedback:%'
    UNION
    SELECT content AS message, created_at FROM announcements
    ORDER BY created_at DESC LIMIT 5
");
$notifStmt->execute([$_SESSION['student_id']]);
$recent_notifications = $notifStmt->fetchAll();
?>

<div class="notif-wrapper" style="position:relative;display:inline-block;">
    <a onclick="toggleNotifDropdown()" style="display:inline-flex;align-items:center;gap:4px;cursor:pointer;color:rgba(255,255,255,0.7);text-decoration:none;font-size:13px;padding:6px 10px;border-radius:5px;transition:all .15s;position:relative;">
        <!-- Bell Icon SVG with Badge -->
        <div style="position:relative;display:inline-block;">
            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
                <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
            </svg>
            <?php if ($unread_count > 0): ?>
                <span id="notif-badge" style="position:absolute;top:-6px;right:-8px;background-color:#dc3545;color:white;font-size:10px;font-weight:700;padding:1px 5px;border-radius:50%;min-width:18px;height:18px;text-align:center;line-height:18px;box-shadow:0 2px 4px rgba(220,53,69,0.3);">
                    <?= $unread_count ?>
                </span>
            <?php endif; ?>
        </div>
        <span style="margin-left:4px;">Notifications</span>
    </a>
    <div id="notif-dropdown" style="display:none;position:absolute;right:0;top:100%;background:#fff;border-radius:8px;box-shadow:0 4px 12px rgba(0,0,0,0.15);width:300px;z-index:1000;border:1px solid #e2e6ea;margin-top:4px;max-height:400px;overflow-y:auto;">
        <div style="padding:10px 15px;font-size:12px;font-weight:700;color:#1e3a5f;border-bottom:1px solid #f0f2f5;">Recent Notifications</div>
        <?php if (!empty($recent_notifications)): ?>
            <?php foreach ($recent_notifications as $row): ?>
                <a href="notifications.php" style="display:block;padding:10px 15px;border-bottom:1px solid #f0f2f5;text-decoration:none;transition:background 0.2s;" onmouseover="this.style.background='#f8f9fa'" onmouseout="this.style.background=''">
                    <div style="font-size:12px;color:#1e2a38;font-weight:500;line-height:1.4;"><?= htmlspecialchars($row['message'] ?? 'No message') ?></div>
                    <div style="font-size:10px;color:#9aa5b4;margin-top:4px;"><?= date('M d, h:i A', strtotime($row['created_at'])) ?></div>
                </a>
            <?php endforeach; ?>
            <a href="notifications.php" style="display:block;text-align:center;padding:8px;font-size:12px;color:#1e3a5f;font-weight:700;text-decoration:none;">View All</a>
        <?php else: ?>
            <div style="padding:20px;text-align:center;color:#9aa5b4;font-size:12px;">No new notifications</div>
        <?php endif; ?>
    </div>
</div>

<script>
function toggleNotifDropdown() {
    var dropdown = document.getElementById('notif-dropdown');
    if (dropdown.style.display === 'block') {
        dropdown.style.display = 'none';
    } else {
        dropdown.style.display = 'block';
        // Mark as read when opened
        fetch('mark_read.php');
    }
}

// Close dropdown when clicking outside
document.addEventListener('click', function(event) {
    if (!event.target.closest('.notif-wrapper')) {
        var dropdown = document.getElementById('notif-dropdown');
        if (dropdown.style.display === 'block') {
            dropdown.style.display = 'none';
        }
    }
});
</script>