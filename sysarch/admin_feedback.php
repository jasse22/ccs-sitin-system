<?php
require_once 'db.php'; // Ensure database connection

try {
    $sql = "SELECT CONCAT(s.firstname, ' ', s.lastname) as name, n.message, n.created_at 
            FROM notifications n 
            JOIN students s ON n.student_id = s.id 
            ORDER BY n.created_at DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $feedbacks = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Error fetching feedback: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CCS | Admin Feedback</title>
    <style>
        :root {
            --nav-bg: #1e3a5f;      /* Navy Blue from Pic 2 */
            --primary-blue: #3498db; /* Action Blue from Pic 2 */
            --logout-red: #e74c3c;   /* Log out Red from Pic 2 */
            --table-header: #2c3e50;
            --bg-light: #f4f7f6;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--bg-light);
            margin: 0;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        /* Top Navigation Bar */
        .navbar {
            background-color: var(--nav-bg);
            color: white;
            padding: 15px 50px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .nav-links a {
            color: #bdc3c7;
            text-decoration: none;
            margin-left: 20px;
            font-size: 14px;
            transition: color 0.3s;
        }

        .nav-links a:hover, .nav-links a.active {
            color: white;
        }

        .btn-logout {
            background-color: var(--logout-red);
            color: white !important;
            padding: 8px 15px;
            border-radius: 4px;
        }

        /* Content Area */
        .container {
            padding: 40px 50px;
        }

        .feedback-card {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        }

        h2 {
            text-align: center;
            color: var(--nav-bg);
            margin-bottom: 30px;
        }

        /* Table Styling */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        thead tr {
            background-color: var(--nav-bg);
            color: white;
            text-align: left;
        }

        th, td {
            padding: 15px;
            border-bottom: 1px solid #edf2f7;
            font-size: 14px;
        }

        tbody tr:hover {
            background-color: #f8fafc;
        }

        .student-name {
            font-weight: 600;
            color: #2d3748;
        }

        .date-sent {
            color: #718096;
            font-size: 12px;
        }
    </style>
</head>
<body>

<div class="navbar">
    <div class="logo">CCS Admin Dashboard</div>
    <div class="nav-links">
        <a href="admin_dashboard.php">Home</a>
        <a href="admin_reservations.php">Reservations</a>
        <a href="admin_feedback.php" class="active">View Student Feedback</a>
        <a href="logout.php" class="btn-logout">Log out</a>
    </div>
</div>

<div class="container">
    <div class="feedback-card">
        <h2>Student Feedback Reports</h2>
        <table>
            <thead>
                <tr>
                    <th>STUDENT NAME</th>
                    <th>FEEDBACK</th>
                    <th>DATE SENT</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($feedbacks): ?>
                    <?php foreach ($feedbacks as $row): ?>
                        <tr>
                            <td class="student-name"><?php echo htmlspecialchars($row['name']); ?></td>
                            <td><?php echo htmlspecialchars($row['message']); ?></td>
                            <td class="date-sent"><?php echo htmlspecialchars($row['created_at']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="3" style="text-align:center;">No feedback reported yet.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>