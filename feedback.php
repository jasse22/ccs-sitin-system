<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

// Handle feedback submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_feedback'])) {
    $message = trim($_POST['message'] ?? '');
    if ($message) {
        $stmt = $pdo->prepare("INSERT INTO feedback (student_id, message) VALUES (?, ?)");
        $stmt->execute([$_SESSION['student_id'], $message]);
        $success = "✅ Thank you! Your testimonial has been submitted.";
    }
}

// Fetch all testimonials with student profile photos
$stmt = $pdo->prepare("
    SELECT f.*, s.firstname, s.lastname, s.profile_photo 
    FROM feedback f 
    JOIN students s ON f.student_id = s.id 
    ORDER BY f.created_at DESC
");
$stmt->execute();
$allFeedback = $stmt->fetchAll();

// Fetch student's own feedback
$stmt2 = $pdo->prepare("SELECT * FROM feedback WHERE student_id = ? ORDER BY created_at DESC");
$stmt2->execute([$_SESSION['student_id']]);
$myFeedback = $stmt2->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CCS | Testimonials</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
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
        }

        body.dark-mode {
            --bg-color: #1a1f2e;
            --text-color: #e8edf5;
            --card-bg: #242b3d;
            --nav-bg: #141824;
            --border-color: #2e364a;
            --shadow: 0 4px 12px rgba(0,0,0,0.3);
            --input-bg: #2c303a;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: var(--bg-color);
            color: var(--text-color);
            min-height: 100vh;
            transition: background 0.3s, color 0.3s;
        }
        nav {
            background: var(--nav-bg);
            height: 54px;
            padding: 0 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 100;
            transition: background 0.3s;
        }
        .nav-brand { font-size: 13.5px; font-weight: 700; color: #fff; }
        .nav-links { display: flex; align-items: center; gap: 1px; }
        .nav-links a {
            font-size: 13px;
            color: rgba(255,255,255,0.7);
            text-decoration: none;
            padding: 6px 10px;
            border-radius: 5px;
            transition: all .15s;
        }
        .nav-links a:hover, .nav-links a.active { color: #fff; background: rgba(255,255,255,0.1); }
        .btn-logout { background: #dc3545 !important; color: #fff !important; border-radius: 5px; padding: 6px 14px !important; }
        .page-body { max-width: 800px; margin: 0 auto; padding: 32px 20px 52px; }
        .page-title { font-size: 22px; font-weight: 800; text-align: center; margin-bottom: 24px; color: var(--text-color); }
        .page-title span { display: block; font-size: 13px; font-weight: 400; color: #9aa5b4; margin-top: 4px; }
        .card { background: var(--card-bg); border-radius: 12px; box-shadow: var(--shadow); overflow: hidden; border: none; transition: background 0.3s; }
        .card-head { background: var(--nav-bg); padding: 14px 18px; }
        .card-head h3 { color: #fff; font-size: 14px; font-weight: 600; }
        .form-body { padding: 20px 24px 24px; }
        .field { margin-bottom: 14px; }
        .field label { display: block; font-size: 13px; font-weight: 600; color: var(--text-color); margin-bottom: 6px; }
        .field textarea {
            width: 100%; padding: 10px 12px; border: 1px solid var(--border-color); border-radius: 6px;
            font-size: 13px; font-family: inherit; min-height: 100px; resize: vertical;
            background: var(--input-bg); color: var(--text-color); outline: none;
        }
        .field textarea:focus { border-color: #1e3a5f; box-shadow: 0 0 0 3px rgba(30,58,95,0.07); }
        .btn-submit {
            padding: 10px 24px; border: none; border-radius: 6px; background: #1e3a5f; color: #fff;
            font-size: 14px; font-weight: 600; cursor: pointer; transition: background 0.2s;
        }
        .btn-submit:hover { background: #16304f; }
        .feedback-item {
            padding: 14px 18px; border-bottom: 1px solid var(--border-color);
            transition: background 0.2s;
        }
        .feedback-item:hover { background: #f8f9fa; }
        .feedback-item:last-child { border-bottom: none; }
        .feedback-message { font-size: 14px; color: var(--text-color); line-height: 1.5; }
        .feedback-author { font-size: 12px; font-weight: 600; color: #1e3a5f; margin-top: 4px; }
        .feedback-date { font-size: 12px; color: #9aa5b4; margin-top: 2px; }
        .alert { padding: 12px 16px; border-radius: 6px; margin-bottom: 16px; font-weight: 500; }
        .alert-success { background: #d4edda; color: #155724; border: 1px solid #b7ebc5; }
        .no-feedback { text-align: center; padding: 30px; color: #9aa5b4; font-size: 14px; }
        .testimonial-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; padding: 16px; }
        .testimonial-card {
            background: #f8f9fa; border-radius: 10px; padding: 16px; border: 1px solid #eef0f3;
            transition: transform 0.2s, box-shadow 0.2s;
            display: flex;
            align-items: flex-start;
            gap: 12px;
        }
        .testimonial-card:hover { transform: translateY(-2px); box-shadow: 0 4px 8px rgba(0,0,0,0.05); }
        .testimonial-avatar {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            object-fit: cover;
            flex-shrink: 0;
            border: 2px solid #1e3a5f;
        }
        .testimonial-avatar-placeholder {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            background: #1e3a5f;
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 18px;
            flex-shrink: 0;
            border: 2px solid #1e3a5f;
        }
        .testimonial-content { flex: 1; }
        .testimonial-quote { font-size: 14px; color: #4a5568; line-height: 1.6; font-style: italic; }
        .testimonial-author { font-size: 12px; font-weight: 600; color: #1e3a5f; margin-top: 6px; }
        .testimonial-date { font-size: 11px; color: #9aa5b4; }
        
        body.dark-mode .testimonial-card {
            background: #2c303a; border-color: #3a4050;
        }
        body.dark-mode .testimonial-quote { color: #e8edf5; }
        body.dark-mode .testimonial-author { color: #7aa2f7; }
        body.dark-mode .testimonial-avatar {
            border-color: #7aa2f7;
        }
        body.dark-mode .testimonial-avatar-placeholder {
            background: #7aa2f7;
            border-color: #7aa2f7;
        }
        
        @media (max-width: 600px) { 
            .testimonial-grid { grid-template-columns: 1fr; }
            nav { padding: 0 14px; }
        }
    </style>
</head>
<body>
<?php include 'header.php'; ?>
<div class="page-body">
    <div class="page-title">
        📝 Testimonials
        <span>Share your experience with the CCS Sit-in Monitoring System</span>
    </div>

    <?php if (isset($success)): ?>
        <div class="alert alert-success"><?= $success ?></div>
    <?php endif; ?>

    <!-- All Testimonials Display -->
    <div class="card" style="margin-bottom:24px;">
        <div class="card-head"><h3>💬 What Students Say</h3></div>
        <div class="testimonial-grid">
            <?php if ($allFeedback): ?>
                <?php foreach ($allFeedback as $f): 
                    // Get profile photo path
                    $photoPath = !empty($f['profile_photo']) ? 'uploads/profiles/' . htmlspecialchars($f['profile_photo']) : null;
                    $initials = strtoupper(substr($f['firstname'], 0, 1) . substr($f['lastname'], 0, 1));
                ?>
                <div class="testimonial-card">
                    <div class="testimonial-avatar">
                        <?php if ($photoPath && file_exists($photoPath)): ?>
                            <img src="<?= $photoPath ?>" alt="<?= htmlspecialchars($f['firstname']) ?>" class="testimonial-avatar">
                        <?php else: ?>
                            <div class="testimonial-avatar-placeholder"><?= $initials ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="testimonial-content">
                        <div class="testimonial-quote">"<?= htmlspecialchars($f['message']) ?>"</div>
                        <div class="testimonial-author">— <?= htmlspecialchars($f['firstname'] . ' ' . $f['lastname']) ?></div>
                        <div class="testimonial-date"><?= date('M d, Y', strtotime($f['created_at'])) ?></div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div style="grid-column:1/-1;text-align:center;padding:30px;color:#9aa5b4;font-size:14px;">
                    📭 No testimonials yet. Be the first to share!
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Submit Feedback Form -->
    <div class="card">
        <div class="card-head"><h3>✏️ Write a Testimonial</h3></div>
        <div class="form-body">
            <form method="POST">
                <div class="field">
                    <label>Your Feedback</label>
                    <textarea name="message" placeholder="Share your thoughts about the system, your experience, or suggestions..." required></textarea>
                </div>
                <button type="submit" name="submit_feedback" class="btn-submit">📤 Submit Testimonial</button>
            </form>
        </div>
    </div>
</div>
</body>
</html>