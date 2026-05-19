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

.reg-box{background:#fff;border:1px solid #e2e6ea;border-radius:12px;width:100%;max-width:560px;overflow:hidden;box-shadow:0 4px 20px rgba(0,0,0,0.07);}
.reg-header{background:linear-gradient(135deg, #1e3a5f, #2a5a8a);padding:20px 28px;display:flex;align-items:center;gap:14px;}
.reg-header img{width:44px;height:44px;border-radius:50%;border:2px solid rgba(255,255,255,0.25);object-fit:cover;}
.reg-header h2{color:#fff;font-size:18px;font-weight:700;}
.reg-header p{color:rgba(255,255,255,0.5);font-size:12px;margin-top:2px;}
.reg-body{padding:30px 30px 32px;}

.alert{padding:12px 16px;border-radius:8px;font-size:13px;margin-bottom:20px;font-weight:500;}
.alert-error{background:#fff5f5;border:1px solid #fed7d7;color:#c53030;}
.alert-success{background:#f0fff4;border:1px solid #9ae6b4;color:#276749;}

.field{display:flex;flex-direction:column;margin-bottom:14px;}
.field input,.field select{
    padding:10px 14px;
    border:1px solid #d0d7e2;
    border-radius:8px;
    font-size:13px;
    font-family:'Plus Jakarta Sans',sans-serif;
    color:#1e2a38;
    background:#fff;
    outline:none;
    transition:border-color .15s, box-shadow .15s;
    width:100%;
}
.field input:focus,.field select:focus{border-color:#1e3a5f;box-shadow:0 0 0 3px rgba(30,58,95,0.08);}
.field input::placeholder{color:#b0bac8;}
.field label{
    font-size:12px;
    font-weight:500;
    color:#4a5568;
    margin-top:5px;
}
.field label .req{color:#e53e3e;margin-left:2px;}

.field-row{display:grid;grid-template-columns:1fr 1fr;gap:14px;}
.field-row3{display:grid;grid-template-columns:1fr 1fr 1fr;gap:14px;}

.reg-footer{display:flex;gap:10px;margin-top:24px;}
.btn-back{
    padding:10px 20px;
    border-radius:8px;
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
    border-radius:8px;
    background:#1e3a5f;
    color:#fff;
    font-size:14px;
    font-weight:700;
    font-family:'Plus Jakarta Sans',sans-serif;
    cursor:pointer;
    transition:background .15s;
}
.btn-submit:hover{background:#16304f;}

.alt-line{text-align:center;margin-top:14px;font-size:13px;color:#9aa5b4;}
.alt-line a{color:#1e3a5f;font-weight:600;text-decoration:none;}
.alt-line a:hover{text-decoration:underline;}

@media(max-width:600px){nav{padding:0 14px;}.reg-body{padding:20px;}.field-row,.field-row3{grid-template-columns:1fr;}}
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
    <a href="#" onclick="openAboutModal();return false;">About</a>
    <a href="login.php" class="btn-login-nav">Login</a>
  </div>
</nav>

<div class="auth-page">
  <div class="reg-box">
    <div class="reg-header">
      <img src="Uclogo.png" alt="UC Logo"/>
      <div>
        <h2>Create Account</h2>
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

        <!-- Name Fields -->
        <div class="field-row3">
          <div class="field">
            <input type="text" name="lastname" value="<?= htmlspecialchars($_POST['lastname'] ?? '') ?>" placeholder="Last Name" required/>
            <label>Last Name</label>
          </div>
          <div class="field">
            <input type="text" name="firstname" value="<?= htmlspecialchars($_POST['firstname'] ?? '') ?>" placeholder="First Name" required/>
            <label>First Name</label>
          </div>
          <div class="field">
            <input type="text" name="middlename" value="<?= htmlspecialchars($_POST['middlename'] ?? '') ?>" placeholder="Middle Name"/>
            <label>Middle Name</label>
          </div>
        </div>

        <!-- ID Number & Year Level -->
        <div class="field-row">
          <div class="field">
            <input type="text" name="id_number" value="<?= htmlspecialchars($_POST['id_number'] ?? '') ?>" placeholder="ID Number" required/>
            <label>ID Number</label>
          </div>
          <div class="field">
            <select name="year_level" required>
              <option value="">Year Level</option>
              <?php foreach(['1st Year'=>1,'2nd Year'=>2,'3rd Year'=>3,'4th Year'=>4] as $l=>$v): ?>
                <option value="<?=$v?>" <?=($_POST['year_level']??'')==$v?'selected':''?>><?=$l?></option>
              <?php endforeach; ?>
            </select>
            <label>Year Level</label>
          </div>
        </div>

        <!-- Course -->
        <div class="field">
          <select name="course" required>
            <option value="">Select Course</option>
            <?php foreach(['BSIT','BSCS','BSCA'] as $c): ?>
              <option value="<?=$c?>" <?=($_POST['course']??'')===$c?'selected':''?>><?=$c?></option>
            <?php endforeach; ?>
          </select>
          <label>Course</label>
        </div>

        <!-- Password Fields -->
        <div class="field-row">
          <div class="field">
            <input type="password" name="password" placeholder="Password" required/>
            <label>Password</label>
          </div>
          <div class="field">
            <input type="password" name="confirm_password" placeholder="Confirm Password" required/>
            <label>Confirm Password</label>
          </div>
        </div>

        <!-- Email & Address -->
        <div class="field-row">
          <div class="field">
            <input type="email" name="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" placeholder="Email Address" required/>
            <label>Email</label>
          </div>
          <div class="field">
            <input type="text" name="address" value="<?= htmlspecialchars($_POST['address'] ?? '') ?>" placeholder="Current Address"/>
            <label>Address</label>
          </div>
        </div>

        <!-- Buttons -->
        <div class="reg-footer">
          <a href="login.php" class="btn-back">← Back</a>
          <button type="submit" class="btn-submit">Register</button>
        </div>

        <p class="alt-line">Already have an account? <a href="login.php">Sign in</a></p>
      </form>
    </div>
  </div>
</div>
<!-- About Modal -->
<div id="aboutModal" style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.5);z-index:9999;align-items:center;justify-content:center;">
    <div style="background:#fff;border-radius:12px;max-width:500px;width:90%;padding:30px;position:relative;box-shadow:0 4px 20px rgba(0,0,0,0.3);">
        <span onclick="closeAboutModal()" style="position:absolute;top:15px;right:20px;font-size:24px;cursor:pointer;color:#9aa5b4;">&times;</span>
        
        <div style="text-align:center;margin-bottom:20px;">
            <img src="Uclogo.png" alt="UC Logo" style="width:60px;height:60px;border-radius:50%;object-fit:cover;">
            <h2 style="color:#1e3a5f;margin-top:10px;">About CCS Sit-in System</h2>
        </div>
        
        <div style="font-size:14px;color:#4a5568;line-height:1.8;">
            <p><strong>Version:</strong> 1.0.0</p>
            <p><strong>College of Computer Studies</strong></p>
            <p><strong>University of Cebu - Main Campus</strong></p>
            <p style="margin-top:15px;">This Sit-in Monitoring System allows students to reserve laboratory slots, track their sit-in sessions, and receive announcements from the administration.</p>
            <p style="margin-top:10px;font-size:12px;color:#9aa5b4;">&copy; 2026 College of Computer Studies</p>
        </div>
        
        <button onclick="closeAboutModal()" style="display:block;width:100%;padding:10px;margin-top:20px;border:none;border-radius:6px;background:#1e3a5f;color:#fff;font-size:14px;font-weight:600;cursor:pointer;">Close</button>
    </div>
</div>

<script>
function openAboutModal() {
    document.getElementById('aboutModal').style.display = 'flex';
}
function closeAboutModal() {
    document.getElementById('aboutModal').style.display = 'none';
}
// Close modal when clicking outside
document.getElementById('aboutModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeAboutModal();
    }
});
</script>
</body>
</html>