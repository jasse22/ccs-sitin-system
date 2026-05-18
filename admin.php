<?php
session_start();
require_once 'db.php';

// Already logged in → go to dashboard
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header('Location: admin_dashboard.php');
    exit;
}

// ── AUTO-HEAL: ensure admin account always exists with a valid hash ──
try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS admins (
        id         INT AUTO_INCREMENT PRIMARY KEY,
        username   VARCHAR(100) NOT NULL UNIQUE,
        password   VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    $check = $pdo->prepare("SELECT id, password FROM admins WHERE username = 'admin' LIMIT 1");
    $check->execute();
    $row = $check->fetch();

    if ($row) {
        if (!password_verify('admin123', $row['password'])) {
            $fixed_hash = password_hash('admin123', PASSWORD_BCRYPT);
            $pdo->prepare("UPDATE admins SET password = ? WHERE username = 'admin'")->execute([$fixed_hash]);
        }
    } else {
        $fixed_hash = password_hash('admin123', PASSWORD_BCRYPT);
        $pdo->prepare("INSERT INTO admins (username, password) VALUES ('admin', ?)")->execute([$fixed_hash]);
    }
} catch (Exception $e) {
    // Silently continue
}

// ── Handle login ──
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $error = 'Please enter your username and password.';
    } else {
        try {
            $stmt = $pdo->prepare("SELECT * FROM admins WHERE username = ? LIMIT 1");
            $stmt->execute([$username]);
            $admin = $stmt->fetch();

            if ($admin && password_verify($password, $admin['password'])) {
                session_regenerate_id(true);
                $_SESSION['admin_logged_in'] = true;
                $_SESSION['admin_id']        = $admin['id'];
                $_SESSION['admin_username']  = $admin['username'];
                header('Location: admin_dashboard.php');
                exit;
            } else {
                $error = 'Invalid username or password.';
            }
        } catch (PDOException $e) {
            $error = 'Database error: ' . htmlspecialchars($e->getMessage());
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"/><meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>CCS | Admin Login</title>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet"/>
<style>
*{box-sizing:border-box;margin:0;padding:0;}
body{font-family:'Plus Jakarta Sans',sans-serif;background:#f7f8fa;min-height:100vh;display:flex;align-items:center;justify-content:center;padding:24px 16px;}
.wrap{width:100%;max-width:400px;}
.header{text-align:center;margin-bottom:24px;}
.header img{width:72px;height:72px;border-radius:50%;border:2px solid #c5d5e8;object-fit:cover;}
.header h1{font-size:18px;font-weight:700;color:#1e3a5f;margin-top:12px;}
.header p{font-size:12px;color:#9aa5b4;margin-top:3px;}
.admin-badge{display:inline-block;background:#1e3a5f;color:#fff;font-size:10px;font-weight:700;letter-spacing:0.08em;text-transform:uppercase;padding:3px 11px;border-radius:20px;margin-top:8px;}
.card{background:#fff;border:1px solid #e2e6ea;border-radius:8px;overflow:hidden;box-shadow:0 4px 20px rgba(0,0,0,0.06);}
.card-top{background:#1e3a5f;padding:14px 22px;}
.card-top h2{color:#fff;font-size:13.5px;font-weight:600;}
.card-top p{color:rgba(255,255,255,0.45);font-size:12px;margin-top:2px;}
.card-body{padding:24px 24px 28px;}
.alert-error{background:#fff5f5;border:1px solid #fed7d7;color:#c53030;padding:10px 13px;border-radius:6px;font-size:13px;margin-bottom:18px;}
.field{margin-bottom:14px;}
.field label{display:block;font-size:11px;font-weight:700;color:#4a5568;margin-bottom:5px;text-transform:uppercase;letter-spacing:0.04em;}
.field input{width:100%;padding:9px 12px;border:1px solid #d0d7e2;border-radius:6px;font-size:13.5px;font-family:'Plus Jakarta Sans',sans-serif;color:#1e2a38;background:#fff;outline:none;transition:border-color .15s;}
.field input:focus{border-color:#1e3a5f;box-shadow:0 0 0 3px rgba(30,58,95,0.08);}
.field input::placeholder{color:#b0bac8;}
.btn{width:100%;padding:11px;border:none;border-radius:6px;background:#1e3a5f;color:#fff;font-size:13.5px;font-weight:600;font-family:'Plus Jakarta Sans',sans-serif;cursor:pointer;transition:background .15s;margin-top:4px;}
.btn:hover{background:#16304f;}
.back-link{text-align:center;margin-top:16px;font-size:13px;color:#9aa5b4;}
.back-link a{color:#1e3a5f;font-weight:600;text-decoration:none;}
.back-link a:hover{text-decoration:underline;}
</style>
</head>
<body>
<div class="wrap">
  <div class="header">
    <img src="Uclogo.png" alt="UC Logo"/>
    <h1>College of Computer Studies</h1>
    <p>Sit-in Monitoring System</p>
    <span class="admin-badge">Admin Portal</span>
  </div>

  <div class="card">
    <div class="card-top">
      <h2>Administrator Login</h2>
      <p>Sign in with your admin credentials</p>
    </div>
    <div class="card-body">
      <?php if ($error): ?>
        <div class="alert-error">⚠️ <?= htmlspecialchars($error) ?></div>
      <?php endif; ?>
      <form method="POST" action="admin.php">
        <div class="field">
          <label>Username</label>
          <input type="text" name="username" placeholder="Enter admin username"
                 value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
                 required autofocus/>
        </div>
        <div class="field">
          <label>Password</label>
          <input type="password" name="password" placeholder="Enter password" required/>
        </div>
        <button type="submit" class="btn">Login as Admin</button>
      </form>
      <p class="back-link">Not an admin? <a href="login.php">Student Login</a></p>
    </div>
  </div>
</div>
</body>
</html>