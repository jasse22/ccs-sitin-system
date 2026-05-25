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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CCS | Login</title>
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
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background 0.3s, color 0.3s;
        }

        .login-container {
            width: 100%;
            max-width: 420px;
            padding: 20px;
        }

        .login-card {
            background: var(--card-bg);
            border-radius: 16px;
            box-shadow: var(--shadow);
            padding: 40px 32px;
            border: 1px solid var(--border-color);
            transition: background 0.3s, border-color 0.3s;
        }

        .login-header {
            text-align: center;
            margin-bottom: 28px;
        }

        .login-header h1 {
            font-size: 24px;
            font-weight: 700;
            color: var(--text-color);
            transition: color 0.3s;
        }

        .login-header p {
            font-size: 13px;
            color: #9aa5b4;
            margin-top: 4px;
        }

        .login-logo {
            display: flex;
            justify-content: center;
            margin-bottom: 16px;
        }

        .login-logo svg {
            width: 64px;
            height: 64px;
            stroke: var(--nav-bg);
            fill: none;
            stroke-width: 1.5;
        }

        .field {
            margin-bottom: 16px;
        }

        .field label {
            display: block;
            font-size: 12px;
            font-weight: 600;
            color: var(--text-color);
            margin-bottom: 4px;
            transition: color 0.3s;
        }

        .field input {
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

        .field input:focus {
            border-color: var(--nav-bg);
            box-shadow: 0 0 0 3px rgba(30,58,95,0.1);
        }

        .field input::placeholder {
            color: #9aa5b4;
        }

        .btn-login {
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
            margin-top: 8px;
        }

        .btn-login:hover {
            background: #16304f;
        }

        .error-msg {
            background: #fee2e2;
            color: #dc2626;
            padding: 10px 14px;
            border-radius: 8px;
            font-size: 13px;
            margin-bottom: 16px;
            border: 1px solid #fecaca;
        }

        body.dark-mode .error-msg {
            background: #2c303a;
            color: #ef4444;
            border-color: #4a4d57;
        }

        .login-footer {
            text-align: center;
            margin-top: 16px;
            font-size: 12px;
            color: #9aa5b4;
        }
    </style>
</head>
<body>

<div class="login-container">
    <div class="login-card">
        <div class="login-header">
            <div class="login-logo">
                <svg viewBox="0 0 24 24">
                    <path d="M12 2L2 7l10 5 10-5-10-5z"/>
                    <path d="M2 17l10 5 10-5"/>
                    <path d="M2 12l10 5 10-5"/>
                </svg>
            </div>
            <h1>CCS Sit-in System</h1>
            <p>Login with your student ID or admin credentials</p>
        </div>

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
            <button type="submit" class="btn-login">Sign In</button>
        </form>

        <div class="login-footer">
            <p>Student: Use your ID number | Admin: Use your username</p>
        </div>
    </div>
</div>

</body>
</html>