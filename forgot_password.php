<?php
session_start();
require_once 'db.php';

$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_number = trim($_POST['id_number'] ?? '');
    $email = trim($_POST['email'] ?? '');
    
    if ($id_number && $email) {
        $stmt = $pdo->prepare("SELECT id, firstname FROM students WHERE id_number = ? AND email = ? LIMIT 1");
        $stmt->execute([$id_number, $email]);
        $student = $stmt->fetch();
        
        if ($student) {
            // Generate a temporary password
            $temp_password = substr(str_shuffle('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, 8);
            $hashed = password_hash($temp_password, PASSWORD_DEFAULT);
            
            $pdo->prepare("UPDATE students SET password = ? WHERE id = ?")->execute([$hashed, $student['id']]);
            
            $message = "Password reset successfully! Your temporary password is: <strong>$temp_password</strong><br>Please log in and change your password immediately.";
            $message_type = 'success';
        } else {
            $message = 'ID Number and Email do not match our records.';
            $message_type = 'error';
        }
    } else {
        $message = 'Please fill in both ID Number and Email.';
        $message_type = 'error';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CCS | Forgot Password</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: #f7f8fa;
            color: #1e2a38;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        nav {
            background: #1e3a5f;
            height: 54px;
            padding: 0 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 100;
        }
        .nav-brand {
            display: flex;
            align-items: center;
            gap: 9px;
            text-decoration: none;
        }
        .nav-brand img {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            object-fit: cover;
        }
        .nav-brand-text {
            font-size: 13.5px;
            font-weight: 700;
            color: #fff;
            line-height: 1.2;
        }
        .nav-brand-sub {
            font-size: 10px;
            color: rgba(255,255,255,0.45);
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
        }
        .nav-links a:hover {
            color: #fff;
            background: rgba(255,255,255,0.1);
        }
        .auth-page {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 32px 16px;
        }
        .forgot-box {
            background: #fff;
            border: 1px solid #e2e6ea;
            border-radius: 12px;
            width: 100%;
            max-width: 460px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0,0,0,0.07);
        }
        .forgot-header {
            background: linear-gradient(135deg, #1e3a5f, #2a5a8a);
            padding: 24px 28px;
            text-align: center;
        }
        .forgot-header img {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            border: 2px solid rgba(255,255,255,0.25);
            object-fit: cover;
            margin-bottom: 8px;
        }
        .forgot-header h2 {
            color: #fff;
            font-size: 18px;
            font-weight: 700;
        }
        .forgot-header p {
            color: rgba(255,255,255,0.5);
            font-size: 12px;
            margin-top: 2px;
        }
        .forgot-body {
            padding: 30px;
        }
        .alert {
            padding: 12px 16px;
            border-radius: 8px;
            font-size: 13px;
            margin-bottom: 20px;
            font-weight: 500;
        }
        .alert-error {
            background: #fff5f5;
            border: 1px solid #fed7d7;
            color: #c53030;
        }
        .alert-success {
            background: #f0fff4;
            border: 1px solid #9ae6b4;
            color: #276749;
        }
        .field {
            margin-bottom: 16px;
        }
        .field label {
            display: block;
            font-size: 12px;
            font-weight: 600;
            color: #4a5568;
            margin-bottom: 4px;
            text-transform: uppercase;
            letter-spacing: 0.03em;
        }
        .field input {
            width: 100%;
            padding: 10px 14px;
            border: 1px solid #d0d7e2;
            border-radius: 8px;
            font-size: 13px;
            font-family: 'Plus Jakarta Sans', sans-serif;
            color: #1e2a38;
            background: #fff;
            outline: none;
            transition: border-color .15s;
        }
        .field input:focus {
            border-color: #1e3a5f;
            box-shadow: 0 0 0 3px rgba(30,58,95,0.08);
        }
        .btn {
            width: 100%;
            padding: 11px;
            border: none;
            border-radius: 8px;
            background: #1e3a5f;
            color: #fff;
            font-size: 14px;
            font-weight: 700;
            font-family: 'Plus Jakarta Sans', sans-serif;
            cursor: pointer;
            transition: background .15s;
        }
        .btn:hover {
            background: #16304f;
        }
        .back-link {
            display: block;
            text-align: center;
            margin-top: 16px;
            color: #1e3a5f;
            text-decoration: none;
            font-weight: 600;
        }
        .back-link:hover {
            text-decoration: underline;
        }
        @media (max-width: 600px) {
            nav { padding: 0 14px; }
            .forgot-body { padding: 20px; }
        }
    </style>
</head>
<body>
<nav>
  <a class="nav-brand" href="index.php">
    <img src="Uclogo.png" alt="UC Logo"/>
    <div>
      <div class="nav-brand-text">College of Computer Studies</div>
      <div class="nav-brand-sub">Sit-in Monitoring System</div>
    </div>
  </a>
  <div class="nav-links">
    <a href="index.php">Home</a>
    <a href="#">About</a>
    <a href="login.php" class="btn-login-nav">Login</a>
  </div>
</nav>

<div class="auth-page">
  <div class="forgot-box">
    <div class="forgot-header">
      <img src="Uclogo.png" alt="UC Logo"/>
      <h2>Forgot Password</h2>
      <p>Enter your ID number and email to reset your password</p>
    </div>
    <div class="forgot-body">
      <?php if ($message): ?>
        <div class="alert alert-<?= $message_type ?>">
          <?= $message_type === 'success' ? '✅' : '⚠️' ?> <?= $message ?>
        </div>
      <?php endif; ?>
      
      <form method="POST">
        <div class="field">
          <label>ID Number</label>
          <input type="text" name="id_number" placeholder="Enter your ID number" required>
        </div>
        <div class="field">
          <label>Email</label>
          <input type="email" name="email" placeholder="Enter your email address" required>
        </div>
        <button type="submit" class="btn">Reset Password</button>
      </form>
      <a href="login.php" class="back-link">← Back to Login</a>
    </div>
  </div>
</div>
</body>
</html>