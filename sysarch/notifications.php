<?php
// 1. Include your connection file
require_once 'db.php'; 

// 2. Query your notifications table
$stmt = $pdo->query("SELECT * FROM notifications ORDER BY created_at DESC");
$notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<h2>Notifications</h2>
<div class="notification-container">
    <?php if (count($notifications) > 0): ?>
        <ul>
            <?php foreach ($notifications as $row): ?>
                <li>
                    <strong><?php echo htmlspecialchars($row['message']); ?></strong> 
                    <br>
                    <small><?php echo $row['created_at']; ?></small>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <p>No new notifications.</p>
    <?php endif; ?>
</div>

<a href="homepage.php">Back to Dashboard</a>