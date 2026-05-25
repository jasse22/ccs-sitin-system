<?php
session_start();
require_once 'db.php';

// If already logged in, redirect to appropriate dashboard
if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    header('Location: homepage.php');
    exit;
}
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header('Location: admin_dashboard.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');
    
    if ($username && $password) {
        try {
            // Check if it's a student (using id_number)
            $stmt = $pdo->prepare("SELECT * FROM students WHERE id_number = ? LIMIT 1");
            $stmt->execute([$username]);
            $student = $stmt->fetch();
            
            if ($student && password_verify($password, $student['password'])) {
                // Student login
                $_SESSION['logged_in'] = true;
                $_SESSION['student_id'] = $student['id'];
                $_SESSION['id_number'] = $student['id_number'];
                $_SESSION['firstname'] = $student['firstname'];
                $_SESSION['lastname'] = $student['lastname'];
                $_SESSION['fullname'] = trim($student['firstname'] . ' ' . $student['middlename'] . ' ' . $student['lastname']);
                $_SESSION['course'] = $student['course'];
                $_SESSION['year_level'] = $student['year_level'];
                $_SESSION['email'] = $student['email'];
                $_SESSION['address'] = $student['address'];
                $_SESSION['session'] = $student['session'];
                $_SESSION['profile_photo'] = $student['profile_photo'] ?? null;
                
                header('Location: homepage.php');
                exit;
            }
            
            // Check if it's an admin (using username)
            $stmt = $pdo->prepare("SELECT * FROM admins WHERE username = ? LIMIT 1");
            $stmt->execute([$username]);
            $admin = $stmt->fetch();
            
            if ($admin && password_verify($password, $admin['password'])) {
                // Admin login
                $_SESSION['admin_logged_in'] = true;
                $_SESSION['admin_id'] = $admin['id'];
                $_SESSION['admin_username'] = $admin['username'];
                
                header('Location: admin_dashboard.php');
                exit;
            }
            
            $error = "❌ Invalid username or password.";
            
        } catch (PDOException $e) {
            $error = "❌ Database error: " . $e->getMessage();
        }
    } else {
        $error = "❌ Please fill in all fields.";
    }
}

// Fetch announcements for display
$announcements = $pdo->query("SELECT * FROM announcements ORDER BY created_at DESC LIMIT 5")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CCS | Sit-in Monitoring System</title>
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
            --input-border: #d0d7e2;
        }

        body.dark-mode {
            --bg-color: #1a1f2e;
            --text-color: #e8edf5;
            --card-bg: #242b3d;
            --nav-bg: #141824;
            --border-color: #2e364a;
            --shadow: 0 4px 12px rgba(0,0,0,0.3);
            --input-bg: #2c303a;
            --input-border: #2e364a;
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
        .nav-brand {
            font-size: 13px;
            font-weight: 700;
            color: #fff;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .nav-brand svg {
            width: 20px;
            height: 20px;
            stroke: #fff;
        }
        .nav-links {
            display: flex;
            align-items: center;
            gap: 1px;
        }
        .nav-links a {
            font-size: 13px;
            color: rgba(255,255,255,0.7);
            text-decoration: none;
            padding: 6px 10px;
            border-radius: 5px;
            transition: all .15s;
            white-space: nowrap;
        }
        .nav-links a:hover, .nav-links a.active {
            color: #fff;
            background: rgba(255,255,255,0.1);
        }
        .nav-links .btn-register {
            background: #2563a8;
            color: #fff;
            border-radius: 5px;
            padding: 6px 14px;
            font-weight: 600;
        }
        .nav-links .btn-register:hover {
            background: #1d4f8a;
        }

        .hero {
            background: var(--nav-bg);
            padding: 60px 20px 40px;
            text-align: center;
            transition: background 0.3s;
        }
        .hero-logo {
            margin-bottom: 16px;
        }
        .hero-logo img {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            background: #fff;
            padding: 8px;
        }
        .hero h1 {
            font-size: 28px;
            font-weight: 800;
            color: #fff;
        }
        .hero p {
            font-size: 14px;
            color: rgba(255,255,255,0.7);
            margin-top: 4px;
        }
        .hero-buttons {
            display: flex;
            gap: 12px;
            justify-content: center;
            margin-top: 20px;
            flex-wrap: wrap;
        }
        .hero-buttons button {
            padding: 10px 28px;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 600;
            font-family: inherit;
            cursor: pointer;
            transition: all 0.2s;
        }
        .btn-hero-login {
            background: #fff;
            color: #1e3a5f;
            border: none;
        }
        .btn-hero-login:hover {
            background: #f0f0f0;
            transform: translateY(-1px);
        }
        .btn-hero-create {
            background: transparent;
            color: #fff;
            border: 1px solid rgba(255,255,255,0.4);
        }
        .btn-hero-create:hover {
            background: rgba(255,255,255,0.1);
            transform: translateY(-1px);
        }

        .content {
            max-width: 800px;
            margin: 0 auto;
            padding: 30px 20px 52px;
        }
        .card {
            background: var(--card-bg);
            border-radius: 12px;
            border: 1px solid var(--border-color);
            overflow: hidden;
            box-shadow: var(--shadow);
            transition: background 0.3s, border-color 0.3s;
        }
        .card-head {
            background: var(--nav-bg);
            padding: 12px 16px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .card-head h2 {
            color: #fff;
            font-size: 14px;
            font-weight: 600;
        }
        .card-head svg {
            width: 16px;
            height: 16px;
            stroke: rgba(255,255,255,0.7);
        }
        .ann-item {
            padding: 12px 16px;
            border-bottom: 1px solid var(--border-color);
        }
        .ann-item:last-child {
            border-bottom: none;
        }
        .ann-meta {
            font-size: 12px;
            font-weight: 600;
            color: var(--nav-bg);
            margin-bottom: 4px;
        }
        .ann-meta span {
            font-weight: 400;
            color: #9aa5b4;
            font-size: 11px;
        }
        .ann-content {
            font-size: 13px;
            color: var(--text-color);
            line-height: 1.6;
        }

        /* ── LOGIN MODAL ── */
        .modal-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.5);
            z-index: 500;
            align-items: center;
            justify-content: center;
            backdrop-filter: blur(4px);
        }
        .modal-overlay.open {
            display: flex;
        }
        .modal {
            background: var(--card-bg);
            border-radius: 16px;
            padding: 32px;
            width: 100%;
            max-width: 400px;
            border: 1px solid var(--border-color);
            box-shadow: var(--shadow);
            transition: background 0.3s, border-color 0.3s;
        }
        .modal h2 {
            font-size: 20px;
            font-weight: 700;
            color: var(--text-color);
            margin-bottom: 4px;
            transition: color 0.3s;
        }
        .modal p {
            font-size: 13px;
            color: #9aa5b4;
            margin-bottom: 20px;
        }
        .modal .field {
            margin-bottom: 14px;
        }
        .modal .field label {
            display: block;
            font-size: 12px;
            font-weight: 600;
            color: var(--text-color);
            margin-bottom: 4px;
        }
        .modal .field input {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid var(--input-border);
            border-radius: 8px;
            font-size: 14px;
            font-family: inherit;
            background: var(--input-bg);
            color: var(--text-color);
            outline: none;
            transition: all 0.3s;
        }
        .modal .field input:focus {
            border-color: var(--nav-bg);
            box-shadow: 0 0 0 3px rgba(30,58,95,0.1);
        }
        .modal .btn-submit {
            width: 100%;
            padding: 12px;
            border: none;
            border-radius: 8px;
            background: var(--nav-bg);
            color: #fff;
            font-size: 15px;
            font-weight: 600;
            font-family: inherit;
            cursor: pointer;
            transition: background 0.2s;
            margin-top: 4px;
        }
        .modal .btn-submit:hover {
            background: #16304f;
        }
        .modal .error-msg {
            background: #fee2e2;
            color: #dc2626;
            padding: 10px 14px;
            border-radius: 8px;
            font-size: 13px;
            margin-bottom: 16px;
            border: 1px solid #fecaca;
        }
        body.dark-mode .modal .error-msg {
            background: #2c303a;
            color: #ef4444;
            border-color: #4a4d57;
        }
        .modal-close {
            background: none;
            border: none;
            font-size: 24px;
            cursor: pointer;
            color: #9aa5b4;
            float: right;
            line-height: 1;
        }
        .modal-close:hover {
            color: var(--text-color);
        }

        footer {
            background: var(--nav-bg);
            color: rgba(255,255,255,0.5);
            text-align: center;
            padding: 16px;
            font-size: 12px;
            transition: background 0.3s;
        }
        footer a {
            color: rgba(255,255,255,0.7);
            text-decoration: none;
        }
        footer a:hover {
            color: #fff;
        }
    </style>
</head>
<body>

<nav>
    <div class="nav-brand">
        <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
        College of Computer Studies
    </div>
    <div class="nav-links">
        <a href="#" class="active">Home</a>
        <a href="#">About</a>
        <a href="#" onclick="openModal()" class="btn-login">Login</a>
        <a href="register.php" class="btn-register">Register</a>
        <button onclick="toggleDarkMode()" style="background:none;border:none;color:rgba(255,255,255,0.7);cursor:pointer;font-size:16px;padding:6px 10px;border-radius:5px;">
            🌙
        </button>
    </div>
</nav>

<div class="hero">
    <div class="hero-logo">
        <img src="path/to/logo.png" alt="UC Logo" onerror="this.src='data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22%3E%3Ccircle cx=%2250%22 cy=%2250%22 r=%2245%22 fill=%22%23fff%22/%3E%3Ctext x=%2250%22 y=%2255%22 text-anchor=%22middle%22 font-size=%2230%22 font-weight=%22bold%22 fill=%22%231e3a5f%22%3EUC%3C/text%3E%3C/svg%3E'">
    </div>
    <h1>University of Cebu</h1>
    <p>Sit-in Monitoring System</p>
    <div class="hero-buttons">
        <button class="btn-hero-login" onclick="openModal()">Login</button>
        <button class="btn-hero-create" onclick="window.location.href='register.php'">Create Account</button>
    </div>
</div>

<div class="content">
    <div class="card">
        <div class="card-head">
            <svg viewBox="0 0 24 24"><path d="M22 17H2a3 3 0 0 0 3-3V9a7 7 0 0 1 14 0v5a3 3 0 0 0 3 3z"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
            <h2>Announcements</h2>
        </div>
        <div>
            <?php if ($announcements): foreach ($announcements as $ann): ?>
            <div class="ann-item">
                <div class="ann-meta">
                    <?= htmlspecialchars($ann['admin_name'] ?? 'CCS Admin') ?>
                    <span>| <?= date('M d, Y', strtotime($ann['created_at'])) ?></span>
                </div>
                <div class="ann-content"><?= htmlspecialchars($ann['content'] ?? '') ?></div>
            </div>
            <?php endforeach; else: ?>
            <div style="padding:20px;text-align:center;color:#9aa5b4;font-size:13px;">No announcements yet.</div>
            <?php endif; ?>
        </div>
    </div>
</div>

<footer>
    &copy; 2026 College of Computer Studies · University of Cebu | <a href="admin.php">Admin Login</a>
</footer>

<!-- ── LOGIN MODAL ── -->
<div class="modal-overlay" id="loginModal">
    <div class="modal">
        <button class="modal-close" onclick="closeModal()">×</button>
        <h2>Welcome Back</h2>
        <p>Login with your student ID or admin credentials</p>
        
        <?php if ($error): ?>
            <div class="error-msg"><?= $error ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="field">
                <label>Username / ID Number</label>
                <input type="text" name="username" placeholder="Enter your ID number or username" required>
            </div>
            <div class="field">
                <label>Password</label>
                <input type="password" name="password" placeholder="Enter your password" required>
            </div>
            <button type="submit" class="btn-submit">Sign In</button>
        </form>
    </div>
</div>

<script>
function openModal() {
    document.getElementById('loginModal').classList.add('open');
}
function closeModal() {
    document.getElementById('loginModal').classList.remove('open');
}
// Close modal when clicking outside
document.getElementById('loginModal').addEventListener('click', function(e) {
    if (e.target === this) closeModal();
});
</script>

</body>
</html>