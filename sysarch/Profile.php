<?php
session_start();
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: login.php'); exit;
}
require_once 'db.php';

$success = $_SESSION['profile_success'] ?? '';
$error   = $_SESSION['profile_error']   ?? '';
unset($_SESSION['profile_success'], $_SESSION['profile_error']);

$stmt = $pdo->prepare("SELECT * FROM students WHERE id = ? LIMIT 1");
$stmt->execute([$_SESSION['student_id']]);
$student = $stmt->fetch();

if ($student && isset($student['profile_photo'])) {
    $_SESSION['profile_photo'] = $student['profile_photo'];
}

$photoSrc = (!empty($_SESSION['profile_photo']))
    ? 'uploads/profiles/' . htmlspecialchars($_SESSION['profile_photo'])
    : null;

function val($student, $key, $session_key = null) {
    if ($student && isset($student[$key]) && $student[$key] !== '') return htmlspecialchars($student[$key]);
    $sk = $session_key ?? $key;
    return htmlspecialchars($_SESSION[$sk] ?? '');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"/><meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>CCS | Edit Profile</title>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet"/>
<style>
*{box-sizing:border-box;margin:0;padding:0;}
body{font-family:'Plus Jakarta Sans',sans-serif;background:#f7f8fa;color:#1e2a38;min-height:100vh;font-size:14px;}
nav{background:#1e3a5f;height:54px;padding:0 24px;display:flex;align-items:center;justify-content:space-between;position:sticky;top:0;z-index:100;}
.nav-brand{font-size:13.5px;font-weight:700;color:#fff;}
.nav-links{display:flex;align-items:center;gap:1px;}
.nav-links a{font-size:13px;color:rgba(255,255,255,0.7);text-decoration:none;padding:6px 10px;border-radius:5px;transition:all .15s;white-space:nowrap;}
.nav-links a:hover,.nav-links a.active{color:#fff;background:rgba(255,255,255,0.1);}
.btn-logout{background:#c53030 !important;color:#fff !important;font-weight:600 !important;border-radius:5px;padding:6px 14px !important;margin-left:6px;}
.btn-logout:hover{background:#9b2c2c !important;}
.page-body{max-width:900px;margin:0 auto;padding:28px 20px 60px;}
.page-header{text-align:center;margin-bottom:22px;}
.page-header h1{font-size:20px;font-weight:700;color:#1e3a5f;}
.page-header p{font-size:13px;color:#9aa5b4;margin-top:3px;}
.alert{display:flex;align-items:center;gap:9px;padding:11px 14px;border-radius:6px;font-size:13px;font-weight:500;margin-bottom:18px;}
.alert svg{width:16px;height:16px;flex-shrink:0;fill:none;stroke-width:2;stroke-linecap:round;stroke-linejoin:round;}
.alert-success{background:#f0fff4;border:1px solid #9ae6b4;color:#276749;}
.alert-success svg{stroke:#276749;}
.alert-error{background:#fff5f5;border:1px solid #fed7d7;color:#c53030;}
.alert-error svg{stroke:#c53030;}
.profile-layout{display:grid;grid-template-columns:250px 1fr;gap:18px;align-items:start;}
.card{background:#fff;border-radius:8px;border:1px solid #e2e6ea;overflow:hidden;}
.card-head{background:#1e3a5f;padding:11px 16px;}
.card-head h3{color:#fff;font-size:13px;font-weight:600;}
.card-head p{color:rgba(255,255,255,0.45);font-size:11.5px;margin-top:2px;}
.photo-body{padding:20px 16px 22px;display:flex;flex-direction:column;align-items:center;gap:12px;}
.avatar-wrap{position:relative;width:100px;height:100px;}
.avatar-circle{width:100px;height:100px;border-radius:50%;border:2px solid #c5d5e8;background:#eef3f9;display:flex;align-items:center;justify-content:center;overflow:hidden;}
.avatar-circle img{width:100%;height:100%;object-fit:cover;}
.avatar-circle svg{width:44px;height:44px;stroke:#1e3a5f;fill:none;stroke-width:1.5;stroke-linecap:round;stroke-linejoin:round;}
.avatar-edit{position:absolute;bottom:3px;right:3px;width:26px;height:26px;border-radius:50%;background:#1e3a5f;border:2px solid #fff;display:flex;align-items:center;justify-content:center;cursor:pointer;transition:background .15s;}
.avatar-edit:hover{background:#16304f;}
.avatar-edit svg{width:12px;height:12px;stroke:#fff;fill:none;stroke-width:2;stroke-linecap:round;stroke-linejoin:round;}
.stu-name{font-size:14px;font-weight:700;color:#1e3a5f;text-align:center;line-height:1.35;}
.stu-course{font-size:12px;color:#9aa5b4;text-align:center;margin-top:1px;}
.id-pill{background:#eef3f9;border:1px solid #c5d5e8;border-radius:20px;padding:4px 12px;font-size:12px;font-weight:600;color:#1e3a5f;}
.upload-zone{width:100%;border:1.5px dashed #d0d7e2;border-radius:6px;padding:12px 8px;text-align:center;cursor:pointer;transition:border-color .15s,background .15s;position:relative;}
.upload-zone:hover{border-color:#1e3a5f;background:#eef3f9;}
.upload-zone input[type="file"]{position:absolute;inset:0;opacity:0;cursor:pointer;width:100%;height:100%;}
.uz-icon svg{width:22px;height:22px;stroke:#9aa5b4;fill:none;stroke-width:1.5;stroke-linecap:round;stroke-linejoin:round;}
.uz-label{font-size:12px;color:#4a5568;margin-top:3px;font-weight:600;}
.uz-hint{font-size:11px;color:#9aa5b4;margin-top:1px;}
#photo-selected{font-size:12px;color:#1e3a5f;word-break:break-all;text-align:center;display:none;}
.btn-upload{width:100%;padding:8px;border:none;border-radius:6px;background:#1e3a5f;color:#fff;font-size:13px;font-weight:600;font-family:'Plus Jakarta Sans',sans-serif;cursor:pointer;transition:background .15s;display:flex;align-items:center;justify-content:center;gap:5px;}
.btn-upload:hover{background:#16304f;}
.btn-upload svg{width:13px;height:13px;stroke:#fff;fill:none;stroke-width:2;stroke-linecap:round;stroke-linejoin:round;}
.form-body{padding:22px 24px 26px;}
.section-label{font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:0.08em;color:#1e3a5f;padding-bottom:7px;border-bottom:1px solid #eef0f3;margin-bottom:12px;margin-top:18px;}
.section-label:first-of-type{margin-top:0;}
.field{margin-bottom:12px;}
.field label{display:flex;align-items:center;gap:4px;font-size:11px;font-weight:600;color:#4a5568;margin-bottom:4px;text-transform:uppercase;letter-spacing:0.04em;}
.field input,.field select{width:100%;padding:9px 11px;border:1px solid #d0d7e2;border-radius:6px;font-size:13px;font-family:'Plus Jakarta Sans',sans-serif;color:#1e2a38;background:#fff;outline:none;transition:border-color .15s;}
.field input:focus,.field select:focus{border-color:#1e3a5f;box-shadow:0 0 0 3px rgba(30,58,95,0.07);}
.field input[readonly]{background:#f7f8fa;color:#9aa5b4;cursor:not-allowed;}
.field input::placeholder{color:#b0bac8;}
.field-row{display:grid;grid-template-columns:1fr 1fr;gap:12px;}
.field-row3{display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px;}
.pw-wrap{position:relative;}
.pw-wrap input{padding-right:38px;}
.pw-eye{position:absolute;right:10px;top:50%;transform:translateY(-50%);cursor:pointer;display:flex;align-items:center;color:#9aa5b4;transition:color .15s;}
.pw-eye:hover{color:#1e3a5f;}
.pw-eye svg{width:15px;height:15px;stroke:currentColor;fill:none;stroke-width:2;stroke-linecap:round;stroke-linejoin:round;}
hr.divider{border:none;border-top:1px solid #eef0f3;margin:6px 0 16px;}
.btn-row{display:flex;gap:9px;}
.btn-save{flex:1;padding:10px;border:none;border-radius:6px;background:#1e3a5f;color:#fff;font-size:13.5px;font-weight:600;font-family:'Plus Jakarta Sans',sans-serif;cursor:pointer;transition:background .15s;display:flex;align-items:center;justify-content:center;gap:6px;}
.btn-save:hover{background:#16304f;}
.btn-save svg{width:14px;height:14px;stroke:#fff;fill:none;stroke-width:2;stroke-linecap:round;stroke-linejoin:round;}
.btn-cancel{padding:10px 16px;border-radius:6px;border:1px solid #d0d7e2;background:transparent;color:#4a5568;font-size:13.5px;font-weight:600;font-family:'Plus Jakarta Sans',sans-serif;cursor:pointer;transition:all .15s;}
.btn-cancel:hover{border-color:#1e3a5f;color:#1e3a5f;}
@media(max-width:760px){.profile-layout{grid-template-columns:1fr;}.field-row,.field-row3{grid-template-columns:1fr;}.form-body{padding:18px;}nav{padding:0 14px;}}
</style>
</head>
<body>
<nav>
  <div class="nav-brand">CCS Sit-in Monitoring System</div>
  <div class="nav-links">
    <a href="notifications.php">
        Notifications 
        <span id="notif-badge" style="display:none; background:#e63946; color:white; border-radius:10px; padding:2px 6px; font-size:10px; font-weight:bold;">0</span>
    </a>
    <a href="homepage.php">Home</a>
    <a href="profile.php" class="active">Edit Profile</a>
    <a href="history.php">History</a>
    <a href="reservation.php">Reservation</a>
    <a href="logout.php" class="btn-logout">Log out</a>
  </div>
</nav>

<div class="page-body">
  <div class="page-header">
    <h1>Edit Profile</h1>
    <p>Manage your personal information and account settings</p>
  </div>

  <?php if ($success): ?>
  <div class="alert alert-success">
    <svg viewBox="0 0 24 24"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
    <?= htmlspecialchars($success) ?>
  </div>
  <?php endif; ?>
  <?php if ($error): ?>
  <div class="alert alert-error">
    <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
    <?= htmlspecialchars($error) ?>
  </div>
  <?php endif; ?>

  <div class="profile-layout">
    <!-- PHOTO CARD -->
    <div class="card">
      <div class="card-head"><h3>Profile Photo</h3></div>
      <div class="photo-body">
        <div class="avatar-wrap">
          <div class="avatar-circle">
            <?php if ($photoSrc): ?>
              <img src="<?= $photoSrc ?>" alt="Profile Photo" id="avatarImg"/>
            <?php else: ?>
              <svg viewBox="0 0 24 24" id="avatarIcon"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
              <img src="" alt="" id="avatarImg" style="display:none;width:100%;height:100%;object-fit:cover;border-radius:50%;"/>
            <?php endif; ?>
          </div>
          <label class="avatar-edit" for="photo_file" title="Change photo">
            <svg viewBox="0 0 24 24"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
          </label>
        </div>
        <div class="stu-name"><?= htmlspecialchars($_SESSION['fullname'] ?? 'Student') ?></div>
        <div class="stu-course"><?= htmlspecialchars($_SESSION['course'] ?? '') ?> &bull; Year <?= htmlspecialchars($_SESSION['year_level'] ?? '') ?></div>
        <div class="id-pill">ID: <?= htmlspecialchars($_SESSION['id_number'] ?? '') ?></div>
        <form method="POST" action="update_profile.php" enctype="multipart/form-data" style="width:100%;display:flex;flex-direction:column;gap:10px;">
          <input type="hidden" name="action" value="upload_photo"/>
          <div class="upload-zone" id="uploadZone">
            <div class="uz-icon"><svg viewBox="0 0 24 24"><path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/><circle cx="12" cy="13" r="4"/></svg></div>
            <div class="uz-label">Click or drag photo here</div>
            <div class="uz-hint">JPG, PNG, WEBP · Max 2 MB</div>
            <input type="file" name="profile_photo" id="photo_file" accept="image/*"/>
          </div>
          <div id="photo-selected"></div>
          <button type="submit" class="btn-upload">
            <svg viewBox="0 0 24 24"><polyline points="16 16 12 12 8 16"/><line x1="12" y1="12" x2="12" y2="21"/><path d="M20.39 18.39A5 5 0 0 0 18 9h-1.26A8 8 0 1 0 3 16.3"/></svg>
            Upload Photo
          </button>
        </form>
      </div>
    </div>

    <!-- FORM CARD -->
    <div class="card">
      <div class="card-head">
        <h3>Personal Information</h3>
        <p>Fill in your details then click Save Changes</p>
      </div>
      <div class="form-body">
        <form method="POST" action="update_profile.php">
          <input type="hidden" name="action" value="save_info"/>

          <div class="section-label">Account</div>
          <div class="field">
            <label>ID Number <small style="font-weight:400;text-transform:none;color:#9aa5b4;font-size:11px;">(cannot be changed)</small></label>
            <input type="text" value="<?= val($student,'id_number','id_number') ?>" readonly/>
          </div>

          <div class="section-label">Personal Details</div>
          <div class="field-row3">
            <div class="field">
              <label>Last Name</label>
              <input type="text" name="lastname" value="<?= val($student,'lastname') ?>" placeholder="Last name" required/>
            </div>
            <div class="field">
              <label>First Name</label>
              <input type="text" name="firstname" value="<?= val($student,'firstname') ?>" placeholder="First name" required/>
            </div>
            <div class="field">
              <label>Middle Name</label>
              <input type="text" name="middlename" value="<?= val($student,'middlename') ?>" placeholder="Middle name"/>
            </div>
          </div>
          <div class="field-row">
            <div class="field">
              <label>Email Address</label>
              <input type="email" name="email" value="<?= val($student,'email') ?>" placeholder="your@email.com" required/>
            </div>
            <div class="field">
              <label>Address</label>
              <input type="text" name="address" value="<?= val($student,'address') ?>" placeholder="Current home address"/>
            </div>
          </div>

          <div class="section-label">Academic Information</div>
          <div class="field-row">
            <div class="field">
              <label>Course</label>
              <select name="course" required>
                <option value="">Select course</option>
                <?php foreach(['BSIT','BSCS','BSDA','ACT'] as $c):
                  $cur = $student['course'] ?? $_SESSION['course'] ?? ''; ?>
                  <option value="<?=$c?>" <?=$cur===$c?'selected':''?>><?=$c?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="field">
              <label>Year Level</label>
              <select name="year_level" required>
                <?php $curY=(int)($student['year_level'] ?? $_SESSION['year_level'] ?? 1);
                foreach(['1st Year'=>1,'2nd Year'=>2,'3rd Year'=>3,'4th Year'=>4] as $lbl=>$v): ?>
                  <option value="<?=$v?>" <?=$curY===$v?'selected':''?>><?=$lbl?></option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>

          <div class="section-label">Change Password <span style="font-weight:400;text-transform:none;font-size:11px;color:#9aa5b4;">(leave blank to keep current)</span></div>
          <div class="field-row">
            <div class="field">
              <label>New Password</label>
              <div class="pw-wrap">
                <input type="password" name="new_password" id="pw1" placeholder="Min. 6 characters" autocomplete="new-password"/>
                <span class="pw-eye" onclick="togglePw('pw1','eye1')">
                  <svg id="eye1" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                </span>
              </div>
            </div>
            <div class="field">
              <label>Confirm Password</label>
              <div class="pw-wrap">
                <input type="password" name="confirm_password" id="pw2" placeholder="Repeat new password" autocomplete="new-password"/>
                <span class="pw-eye" onclick="togglePw('pw2','eye2')">
                  <svg id="eye2" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                </span>
              </div>
            </div>
          </div>

          <hr class="divider"/>
          <div class="btn-row">
            <button type="reset" class="btn-cancel">Reset</button>
            <button type="submit" class="btn-save">
              <svg viewBox="0 0 24 24"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
              Save Changes
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<script>
const EYE_OPEN   = '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>';
const EYE_CLOSED = '<path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94"/><path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19"/><line x1="1" y1="1" x2="23" y2="23"/>';
function togglePw(inputId, iconId) {
    const input = document.getElementById(inputId);
    const icon  = document.getElementById(iconId);
    input.type = input.type === 'password' ? 'text' : 'password';
    icon.innerHTML = input.type === 'password' ? EYE_OPEN : EYE_CLOSED;
}
document.getElementById('photo_file').addEventListener('change', function () {
    const file = this.files[0]; if (!file) return;
    const sel = document.getElementById('photo-selected');
    sel.textContent = file.name; sel.style.display = 'block';
    const reader = new FileReader();
    reader.onload = function (e) {
        const img = document.getElementById('avatarImg');
        const icon = document.getElementById('avatarIcon');
        img.src = e.target.result; img.style.display = 'block';
        if (icon) icon.style.display = 'none';
    };
    reader.readAsDataURL(file);
});
const zone = document.getElementById('uploadZone');
zone.addEventListener('dragover', e => { e.preventDefault(); zone.style.borderColor='#1e3a5f'; zone.style.background='#eef3f9'; });
zone.addEventListener('dragleave', () => { zone.style.borderColor=''; zone.style.background=''; });
zone.addEventListener('drop', () => { zone.style.borderColor=''; zone.style.background=''; });
</script>
</body>
</html>