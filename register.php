<?php
session_start();
require_once 'db.php';

// If already logged in, redirect
if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    header('Location: homepage.php');
    exit;
}
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header('Location: admin_dashboard.php');
    exit;
}

$error = '';

// Handle registration
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $lastname = trim($_POST['lastname'] ?? '');
    $firstname = trim($_POST['firstname'] ?? '');
    $middlename = trim($_POST['middlename'] ?? '');
    $id_number = trim($_POST['id_number'] ?? '');
    $year_level = (int)($_POST['year_level'] ?? 1);
    $course = trim($_POST['course'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $confirm_password = trim($_POST['confirm_password'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $address = trim($_POST['address'] ?? '');

    if ($password !== $confirm_password) {
        $error = "❌ Passwords do not match.";
    } else {
        try {
            // Check if ID number already exists
            $check = $pdo->prepare("SELECT id FROM students WHERE id_number = ?");
            $check->execute([$id_number]);
            if ($check->fetch()) {
                $error = "❌ ID number already exists.";
            } else {
                $hashed = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO students (id_number, lastname, firstname, middlename, course, year_level, email, address, password, session) VALUES (?,?,?,?,?,?,?,?,?,30)");
                $stmt->execute([$id_number, $lastname, $firstname, $middlename, $course, $year_level, $email, $address, $hashed]);
                $success = "✅ Account created successfully! You can now login.";
            }
        } catch (PDOException $e) {
            $error = "❌ Registration failed: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CCS | Create Account</title>
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
        .btn-login {
            background: #2563a8;
            color: #fff;
            border-radius: 5px;
            padding: 6px 14px;
            font-weight: 600;
        }
        .btn-login:hover {
            background: #1d4f8a;
        }

        .page-body {
            max-width: 600px;
            margin: 0 auto;
            padding: 30px 20px 52px;
        }

        .card {
            background: var(--card-bg);
            border-radius: 12px;
            box-shadow: var(--shadow);
            overflow: hidden;
            border: 1px solid var(--border-color);
            transition: background 0.3s, border-color 0.3s, box-shadow 0.3s;
        }
        .card-head {
            background: var(--nav-bg);
            padding: 20px 24px;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .card-head h2 {
            color: #fff;
            font-size: 18px;
            font-weight: 700;
        }
        .card-head p {
            color: rgba(255,255,255,0.6);
            font-size: 13px;
            margin-top: 2px;
        }
        .card-head .logo-small {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            color: #1e3a5f;
            font-size: 16px;
        }

        .form-body {
            padding: 24px;
        }

        .field-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
        }
        .field-row-3 {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 12px;
        }

        .field {
            margin-bottom: 12px;
        }
        .field label {
            display: block;
            font-size: 12px;
            font-weight: 600;
            color: var(--text-color);
            margin-bottom: 4px;
            transition: color 0.3s;
        }
        .field input, .field select {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid var(--input-border);
            border-radius: 6px;
            font-size: 14px;
            font-family: inherit;
            background: var(--input-bg);
            color: var(--text-color);
            outline: none;
            transition: all 0.3s;
        }
        .field input:focus, .field select:focus {
            border-color: var(--nav-bg);
            box-shadow: 0 0 0 3px rgba(30,58,95,0.1);
        }

        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 600;
            font-family: inherit;
            cursor: pointer;
            transition: all 0.2s;
        }
        .btn-back {
            background: #dc3545;
            color: #fff;
        }
        .btn-back:hover {
            background: #c82333;
        }
        .btn-register {
            background: #1e3a5f;
            color: #fff;
            width: 100%;
        }
        .btn-register:hover {
            background: #16304f;
        }

        .btn-row {
            display: flex;
            gap: 12px;
            margin-top: 8px;
        }

        .alert {
            padding: 10px 14px;
            border-radius: 6px;
            margin-bottom: 16px;
            font-size: 13px;
        }
        .alert-error {
            background: #fee2e2;
            color: #dc2626;
            border: 1px solid #fecaca;
        }
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #b7ebc5;
        }
        body.dark-mode .alert-error {
            background: #2c303a;
            color: #ef4444;
            border-color: #4a4d57;
        }
        body.dark-mode .alert-success {
            background: #2c303a;
            color: #7aa2f7;
            border-color: #4a4d57;
        }

        .login-link {
            text-align: center;
            margin-top: 16px;
            font-size: 13px;
            color: #9aa5b4;
        }
        .login-link a {
            color: #1e3a5f;
            text-decoration: none;
            font-weight: 600;
        }
        .login-link a:hover {
            text-decoration: underline;
        }
        body.dark-mode .login-link a {
            color: #7aa2f7;
        }

        @media (max-width: 600px) {
            .field-row, .field-row-3 {
                grid-template-columns: 1fr;
            }
            nav { padding: 0 14px; }
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
        <a href="index.php">Home</a>
        <a href="#">About</a>
        <a href="#" onclick="openLoginModal()" class="btn-login">Login</a>
        <button onclick="toggleDarkMode()" style="background:none;border:none;color:rgba(255,255,255,0.7);cursor:pointer;font-size:16px;padding:6px 10px;border-radius:5px;">
            🌙
        </button>
    </div>
</nav>

<div class="page-body">
    <div class="card">
        <div class="card-head">
            <div class="logo-small">UC</div>
            <div>
                <h2>Create Account</h2>
                <p>CCS Sit-in Monitoring System</p>
            </div>
        </div>
        <div class="form-body">
            <?php if (isset($error)): ?>
                <div class="alert alert-error"><?= $error ?></div>
            <?php endif; ?>
            <?php if (isset($success)): ?>
                <div class="alert alert-success"><?= $success ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="field-row-3">
                    <div class="field">
                        <label>Last Name</label>
                        <input type="text" name="lastname" placeholder="Last Name" required>
                    </div>
                    <div class="field">
                        <label>First Name</label>
                        <input type="text" name="firstname" placeholder="First Name" required>
                    </div>
                    <div class="field">
                        <label>Middle Name</label>
                        <input type="text" name="middlename" placeholder="Middle Name">
                    </div>
                </div>

                <div class="field-row">
                    <div class="field">
                        <label>ID Number</label>
                        <input type="text" name="id_number" placeholder="ID Number" required>
                    </div>
                    <div class="field">
                        <label>Year Level</label>
                        <select name="year_level">
                            <option value="1">1st Year</option>
                            <option value="2">2nd Year</option>
                            <option value="3">3rd Year</option>
                            <option value="4">4th Year</option>
                        </select>
                    </div>
                </div>

                <div class="field">
                    <label>Course</label>
                    <select name="course" required>
                        <option value="">Select Course</option>
                        <option value="BSIT">BSIT</option>
                        <option value="BSCS">BSCS</option>
                        <option value="BSDA">BSDA</option>
                        <option value="ACT">ACT</option>
                    </select>
                </div>

                <div class="field-row">
                    <div class="field">
                        <label>Password</label>
                        <input type="password" name="password" placeholder="Password" required>
                    </div>
                    <div class="field">
                        <label>Confirm Password</label>
                        <input type="password" name="confirm_password" placeholder="Confirm Password" required>
                    </div>
                </div>

                <div class="field-row">
                    <div class="field">
                        <label>Email Address</label>
                        <input type="email" name="email" placeholder="Email" required>
                    </div>
                    <div class="field">
                        <label>Current Address</label>
                        <input type="text" name="address" placeholder="Address">
                    </div>
                </div>

                <div class="btn-row">
                    <button type="button" class="btn btn-back" onclick="window.location.href='index.php'">← Back</button>
                    <button type="submit" class="btn btn-register">Register</button>
                </div>
            </form>

            <div class="login-link">
                Already have an account? <a href="#" onclick="openLoginModal()">Sign In</a>
            </div>
        </div>
    </div>
</div>

<!-- ── LOGIN MODAL (copied from index.php) ── -->
<div class="modal-overlay" id="loginModal">
    <div class="modal">
        <button class="modal-close" onclick="closeLoginModal()">×</button>
        <h2>Welcome Back</h2>
        <p>Login with your student ID or admin credentials</p>
        
        <form action="index.php" method="POST">
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

<style>
/* ── LOGIN MODAL STYLES ── */
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
body.dark-mode .modal {
    background: #242b3d;
    border-color: #2e364a;
}
body.dark-mode .modal h2 {
    color: #e8edf5;
}
body.dark-mode .modal .field label {
    color: #e8edf5;
}
body.dark-mode .modal .field input {
    background: #2c303a;
    color: #e8edf5;
    border-color: #2e364a;
}
body.dark-mode .modal .btn-submit {
    background: #1a1f2e;
}
body.dark-mode .modal .btn-submit:hover {
    background: #141824;
}
</style>

<script>
function openLoginModal() {
    document.getElementById('loginModal').classList.add('open');
}
function closeLoginModal() {
    document.getElementById('loginModal').classList.remove('open');
}
document.getElementById('loginModal').addEventListener('click', function(e) {
    if (e.target === this) closeLoginModal();
});
</script>

</body>
</html>