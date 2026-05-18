<?php
session_start();
require_once 'db.php';
if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    header('Location: homepage.php'); exit;
}
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_number = trim($_POST['id_number'] ?? '');
    $password  = $_POST['password'] ?? '';
    if (empty($id_number) || empty($password)) {
        $error = 'Please enter your ID number and password.';
    } else {
        $stmt = $pdo->prepare("SELECT * FROM students WHERE id_number = ? LIMIT 1");
        $stmt->execute([$id_number]);
        $student = $stmt->fetch();
        if ($student && password_verify($password, $student['password'])) {
            session_regenerate_id(true);
            $_SESSION['logged_in']     = true;
            $_SESSION['student_id']    = $student['id'];
            $_SESSION['id_number']     = $student['id_number'];
            $_SESSION['lastname']      = $student['lastname'];
            $_SESSION['firstname']     = $student['firstname'];
            $_SESSION['middlename']    = $student['middlename'];
            $_SESSION['fullname']      = trim($student['firstname'].' '.$student['middlename'].' '.$student['lastname']);
            $_SESSION['course']        = $student['course'];
            $_SESSION['year_level']    = $student['year_level'];
            $_SESSION['email']         = $student['email'];
            $_SESSION['address']       = $student['address'];
            $_SESSION['session']       = $student['session'];
            $_SESSION['profile_photo'] = $student['profile_photo'] ?? null;
            header('Location: homepage.php'); exit;
        } else {
            $error = 'Invalid ID number or password. Please try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"/><meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>CCS | Student Login</title>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet"/>
<style>
*{box-sizing:border-box;margin:0;padding:0;}
body{font-family:'Plus Jakarta Sans',sans-serif;background:#f7f8fa;color:#1e2a38;min-height:100vh;font-size:14px;}
nav{background:#1e3a5f;height:54px;padding:0 24px;display:flex;align-items:center;justify-content:space-between;position:sticky;top:0;z-index:100;}
.nav-brand{display:flex;align-items:center;gap:9px;text-decoration:none;}
.nav-brand img{width:30px;height:30px;border-radius:50%;object-fit:cover;}
.nav-brand-text{font-size:13.5px;font-weight:700;color:#fff;line-height:1.2;}
.nav-brand-sub{font-size:10px;color:rgba(255,255,255,0.45);}
.nav-links{display:flex;align-items:center;gap:1px;}
.nav-links a{font-size:13px;color:rgba(255,255,255,0.7);text-decoration:none;padding:6px 10px;border-radius:5px;transition:all .15s;}
.nav-links a:hover{color:#fff;background:rgba(255,255,255,0.1);}
.btn-register-nav{background:#2f6090;color:#fff !important;font-weight:600 !important;}
.auth-page{min-height:calc(100vh - 54px);display:flex;align-items:center;justify-content:center;padding:36px 16px;}
.login-box{background:#fff;border:1px solid #e2e6ea;border-radius:8px;width:100%;max-width:820px;display:grid;grid-template-columns:290px 1fr;overflow:hidden;box-shadow:0 4px 20px rgba(0,0,0,0.06);}
.login-left{background:#1e3a5f;padding:44px 28px;display:flex;flex-direction:column;align-items:center;justify-content:center;text-align:center;}
.login-left img{width:82px;height:82px;border-radius:50%;border:3px solid rgba(255,255,255,0.25);object-fit:cover;}
.login-left h2{color:#fff;font-size:16px;font-weight:700;margin-top:14px;line-height:1.35;}
.login-left p{color:rgba(255,255,255,0.5);font-size:12px;margin-top:6px;}
.login-left .motto{margin-top:22px;padding-top:16px;border-top:1px solid rgba(255,255,255,0.1);font-size:11px;color:rgba(255,255,255,0.35);line-height:1.9;}
.login-right{padding:40px 36px;}
.login-right h3{font-size:19px;font-weight:700;color:#1e2a38;}
.login-right .sub{font-size:13px;color:#9aa5b4;margin-top:3px;margin-bottom:24px;}
.alert-error{background:#fff5f5;border:1px solid #fed7d7;color:#c53030;padding:10px 13px;border-radius:6px;font-size:13px;margin-bottom:18px;}
.field{margin-bottom:14px;}
.field label{display:block;font-size:11.5px;font-weight:600;color:#4a5568;margin-bottom:5px;text-transform:uppercase;letter-spacing:0.03em;}
.field input{width:100%;padding:9px 12px;border:1px solid #d0d7e2;border-radius:6px;font-size:13.5px;font-family:'Plus Jakarta Sans',sans-serif;color:#1e2a38;background:#fff;outline:none;transition:border-color .15s;}
.field input:focus{border-color:#1e3a5f;box-shadow:0 0 0 3px rgba(30,58,95,0.08);}
.field input::placeholder{color:#b0bac8;}
.extras{display:flex;align-items:center;justify-content:space-between;margin:12px 0 20px;}
.check{display:flex;align-items:center;gap:6px;font-size:13px;color:#4a5568;cursor:pointer;}
.check input{accent-color:#1e3a5f;}
.link{font-size:13px;color:#1e3a5f;text-decoration:none;font-weight:600;}
.link:hover{text-decoration:underline;}
.btn{width:100%;padding:11px;border:none;border-radius:6px;background:#1e3a5f;color:#fff;font-size:13.5px;font-weight:600;font-family:'Plus Jakarta Sans',sans-serif;cursor:pointer;transition:background .15s;}
.btn:hover{background:#16304f;}
.alt-line{text-align:center;margin-top:16px;font-size:13px;color:#9aa5b4;}
.alt-line a{color:#1e3a5f;font-weight:600;text-decoration:none;}
.alt-line a:hover{text-decoration:underline;}
@media(max-width:640px){nav{padding:0 14px;}.login-box{grid-template-columns:1fr;}.login-left{padding:28px 20px;}.login-right{padding:24px 20px;}}
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
    <a href="register.php" class="btn-register-nav">Register</a>
  </div>
</nav>
<div class="auth-page">
  <div class="login-box">
    <div class="login-left">
      <img src="Uclogo.png" alt="UC Logo"/>
      <h2>College of Computer Studies</h2>
      <p>University of Cebu - Main Campus</p>
    </div>
    <div class="login-right">
      <h3>Student Sign In</h3>
      <p class="sub">Enter your student credentials to access your account.</p>
      <?php if ($error): ?>
        <div class="alert-error">⚠️ <?= htmlspecialchars($error) ?></div>
      <?php endif; ?>
      <form method="POST" action="login.php">
        <div class="field">
          <label>ID Number</label>
          <input type="text" name="id_number" 
                 value="<?= htmlspecialchars($_POST['id_number'] ?? '') ?>" required autofocus/>
        </div>
        <div class="field">
          <label>Password</label>
          <input type="password" name="password" required/>
        </div>
        <div class="extras">
          <a href="#" class="link">Forgot password?</a>
        </div>
        <button type="submit" class="btn">Login</button>
      </form>
      <p class="alt-line">Don't have an account? <a href="register.php">Register here</a></p>
    </div>
  </div>
</div>
</body>
</html>