<?php
session_start();
require_once 'db.php';

if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    header('Location: homepage.php'); exit;
}

$error = ''; $success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_number  = trim($_POST['id_number']  ?? '');
    $lastname   = trim($_POST['lastname']   ?? '');
    $firstname  = trim($_POST['firstname']  ?? '');
    $middlename = trim($_POST['middlename'] ?? '');
    $email      = trim($_POST['email']      ?? '');
    $address    = trim($_POST['address']    ?? '');
    $course     = trim($_POST['course']     ?? '');
    $year_level = trim($_POST['year_level'] ?? '');
    $password   = $_POST['password']          ?? '';
    $confirm_pw = $_POST['confirm_password']  ?? '';

    if (!$id_number || !$lastname || !$firstname || !$email || !$course || !$year_level || !$password) {
        $error = 'Please fill in all required fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } elseif ($password !== $confirm_pw) {
        $error = 'Passwords do not match.';
    } else {
        $s = $pdo->prepare("SELECT id FROM students WHERE id_number = ? LIMIT 1");
        $s->execute([$id_number]);
        if ($s->fetch()) {
            $error = 'ID Number is already registered.';
        } else {
            $s = $pdo->prepare("SELECT id FROM students WHERE email = ? LIMIT 1");
            $s->execute([$email]);
            if ($s->fetch()) {
                $error = 'Email address is already registered.';
            } else {
                $pdo->prepare("INSERT INTO students
                    (id_number, lastname, firstname, middlename, course, year_level, email, password, address, session)
                    VALUES (?,?,?,?,?,?,?,?,?,30)")
                    ->execute([
                        $id_number, $lastname, $firstname, $middlename,
                        $course, $year_level, $email,
                        password_hash($password, PASSWORD_DEFAULT),
                        $address
                    ]);
                $success = 'Account created! You can now log in.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"/><meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>CCS | Register</title>
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
.btn-login-nav{border:1px solid rgba(255,255,255,0.2);}

.auth-page{min-height:calc(100vh - 54px);display:flex;align-items:center;justify-content:center;padding:32px 16px;}

.reg-box{background:#fff;border:1px solid #e2e6ea;border-radius:8px;width:100%;max-width:560px;overflow:hidden;box-shadow:0 4px 20px rgba(0,0,0,0.07);}
.reg-header{background:#1e3a5f;padding:18px 28px;display:flex;align-items:center;gap:14px;}
.reg-header img{width:44px;height:44px;border-radius:50%;border:2px solid rgba(255,255,255,0.25);object-fit:cover;}
.reg-header h2{color:#fff;font-size:16px;font-weight:700;}
.reg-header p{color:rgba(255,255,255,0.5);font-size:12px;margin-top:2px;}
.reg-body{padding:28px 28px 32px;}

.alert{padding:11px 14px;border-radius:6px;font-size:13px;margin-bottom:20px;font-weight:500;}
.alert-error{background:#fff5f5;border:1px solid #fed7d7;color:#c53030;}
.alert-success{background:#f0fff4;border:1px solid #9ae6b4;color:#276749;}

/* Field: input on top, label below — exactly like the screenshot */
.field{display:flex;flex-direction:column;margin-bottom:14px;}
.field input,
.field select{
    padding:9px 12px;
    border:1px solid #d0d7e2;
    border-radius:6px;
    font-size:13.5px;
    font-family:'Plus Jakarta Sans',sans-serif;
    color:#1e2a38;
    background:#fff;
    outline:none;
    transition:border-color .15s, box-shadow .15s;
    width:100%;
}
.field input:focus,
.field select:focus{border-color:#1e3a5f;box-shadow:0 0 0 3px rgba(30,58,95,0.08);}
.field input::placeholder{color:#b0bac8;}
/* Label sits BELOW the input */
.field label{
    font-size:12px;
    font-weight:500;
    color:#4a5568;
    margin-top:5px;
}
.field label .req{color:#e53e3e;margin-left:2px;}

.reg-footer{display:flex;gap:10px;margin-top:24px;}
.btn-back{
    padding:10px 18px;
    border-radius:6px;
    background:#8b1a1a;
    border:none;
    color:#fff;
    font-size:13px;
    font-weight:600;
    font-family:'Plus Jakarta Sans',sans-serif;
    cursor:pointer;
    transition:background .15s;
    text-decoration:none;
    display:flex;
    align-items:center;
}
.btn-back:hover{background:#6e1414;}
.btn-submit{
    flex:1;
    padding:11px;
    border:none;
    border-radius:6px;
    background:#1e3a5f;
    color:#fff;
    font-size:13.5px;
    font-weight:700;
    font-family:'Plus Jakarta Sans',sans-serif;
    cursor:pointer;
    transition:background .15s;
}
.btn-submit:hover{background:#16304f;}

.alt-line{text-align:center;margin-top:14px;font-size:13px;color:#9aa5b4;}
.alt-line a{color:#1e3a5f;font-weight:600;text-decoration:none;}
.alt-line a:hover{text-decoration:underline;}

@media(max-width:600px){nav{padding:0 14px;}.reg-body{padding:20px;}}
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
  <div class="reg-box">
    <div class="reg-header">
      <img src="Uclogo.png" alt="UC Logo"/>
      <div>
        <h2>Sign up</h2>
        <p>CCS Sit-in Monitoring System</p>
      </div>
    </div>

    <div class="reg-body">
      <?php if ($error): ?>
        <div class="alert alert-error">⚠️ <?= htmlspecialchars($error) ?></div>
      <?php endif; ?>
      <?php if ($success): ?>
        <div class="alert alert-success">
          ✅ <?= htmlspecialchars($success) ?>
          <a href="login.php" style="color:#276749;font-weight:700;margin-left:4px;">Sign in →</a>
        </div>
      <?php endif; ?>

      <form method="POST" action="register.php">

        <!-- First Name -->
        <div class="field">
          <input type="text" name="firstname"
                 value="<?= htmlspecialchars($_POST['firstname'] ?? '') ?>"
                 placeholder=""/>
          <label>First Name <span class="req"></span></label>
        </div>

        <!-- Middle Name -->
        <div class="field">
          <input type="text" name="middlename"
                 value="<?= htmlspecialchars($_POST['middlename'] ?? '') ?>"
                 placeholder=""/>
          <label>Middle Name</label>
        </div>

        <!-- Last Name -->
        <div class="field">
          <input type="text" name="lastname"
                 value="<?= htmlspecialchars($_POST['lastname'] ?? '') ?>"
                 placeholder=""/>
          <label>Last Name <span class="req"></span></label>
        </div>

        <!-- ID Number -->
        <div class="field">
          <input type="text" name="id_number"
                 value="<?= htmlspecialchars($_POST['id_number'] ?? '') ?>"
                 placeholder=""/>
          <label>ID Number <span class="req"></span></label>
        </div>

        <!-- Year Level -->
        <div class="field">
          <select name="year_level" required>
            <option value="">-- Select Year Level --</option>
            <?php foreach(['1st Year'=>1,'2nd Year'=>2,'3rd Year'=>3,'4th Year'=>4] as $l=>$v): ?>
              <option value="<?=$v?>" <?=($_POST['year_level']??'')==$v?'selected':''?>><?=$l?></option>
            <?php endforeach; ?>
          </select>
          <label>Course Level <span class="req"></span></label>
        </div>

        <!-- Course -->
        <div class="field">
          <select name="course" required>
            <option value="">-- Select Course --</option>
            <?php foreach(['BSIT','BSCS','BSDA','ACT'] as $c): ?>
              <option value="<?=$c?>" <?=($_POST['course']??'')===$c?'selected':''?>><?=$c?></option>
            <?php endforeach; ?>
          </select>
          <label>Course <span class="req"></span></label>
        </div>

        <!-- Password -->
        <div class="field">
          <input type="password" name="password" placeholder=""/>
          <label>Password <span class="req"></span></label>
        </div>

        <!-- Repeat Password -->
        <div class="field">
          <input type="password" name="confirm_password" placeholder=""/>
          <label>Repeat your password <span class="req"></span></label>
        </div>

        <!-- Email -->
        <div class="field">
          <input type="email" name="email"
                 value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                 placeholder=""/>
          <label>Email <span class="req"></span></label>
        </div>

        <!-- Address -->
        <div class="field">
          <input type="text" name="address"
                 value="<?= htmlspecialchars($_POST['address'] ?? '') ?>"
                 placeholder=""/>
          <label>Address</label>
        </div>

        <!-- Buttons -->
        <div class="reg-footer">
          <a href="login.php" class="btn-back">Back</a>
          <button type="submit" class="btn-submit">Register</button>
        </div>

        <p class="alt-line">Already have an account? <a href="login.php">Sign in</a></p>
      </form>
    </div>
  </div>
</div>

</body>
</html>