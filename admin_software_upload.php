<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: admin.php');
    exit;
}

// Handle file upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload_software'])) {
    $name = trim($_POST['software_name'] ?? '');
    $version = trim($_POST['version'] ?? '');
    $lab = trim($_POST['lab_room'] ?? '');
    $desc = trim($_POST['description'] ?? '');
    
    // Handle file upload
    $targetDir = "uploads/software/";
    if (!file_exists($targetDir)) {
        mkdir($targetDir, 0777, true);
    }
    
    $fileName = basename($_FILES["software_file"]["name"]);
    $targetFile = $targetDir . $fileName;
    $fileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));
    
    // Allow certain file formats
    $allowedTypes = array('exe', 'msi', 'zip', 'rar', 'pdf', 'jpg', 'png');
    
    if (!in_array($fileType, $allowedTypes)) {
        $error = "❌ Only EXE, MSI, ZIP, RAR, PDF, JPG, PNG files are allowed.";
    } else {
        if (move_uploaded_file($_FILES["software_file"]["tmp_name"], $targetFile)) {
            // Insert into database
            $stmt = $pdo->prepare("INSERT INTO software_uploads (software_name, version, lab_room, description, file_name, file_path, uploaded_by) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$name, $version, $lab, $desc, $fileName, $targetFile, $_SESSION['admin_username'] ?? 'Admin']);
            $success = "✅ Software uploaded successfully!";
        } else {
            $error = "❌ Sorry, there was an error uploading your file.";
        }
    }
}

// Fetch uploaded software
$uploads = $pdo->query("SELECT * FROM software_uploads ORDER BY uploaded_at DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CCS | Upload Software</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet"/>
    <script src="darkmode.js"></script>
    <style>
        /* ── CSS Variables ── */
        :root {
            --bg-color: #f7f8fa;
            --text-color: #1e2a38;
            --card-bg: #ffffff;
            --nav-bg: #1e3a5f;
            --border-color: #e2e6ea;
            --shadow: 0 4px 12px rgba(0,0,0,0.08);
            --input-bg: #ffffff;
            --input-text: #1e2a38;
            --table-text: #1e2a38;
            --badge-bg: #eef3f9;
            --badge-text: #1e3a5f;
            --hover-bg: #f0f4f9;
        }

        body.dark-mode {
            --bg-color: #1a1f2e;
            --text-color: #e8edf5;
            --card-bg: #242b3d;
            --nav-bg: #141824;
            --border-color: #2e364a;
            --shadow: 0 4px 12px rgba(0,0,0,0.3);
            --input-bg: #2c303a;
            --input-text: #e8edf5;
            --table-text: #e8edf5;
            --badge-bg: #2c303a;
            --badge-text: #7aa2f7;
            --hover-bg: #2a3248;
        }

        /* ── NAVIGATION STYLES ── */
        nav {
            background: var(--nav-bg);
            height: 52px;
            padding: 0 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 200;
            transition: background 0.3s;
        }
        .nav-brand {
            font-size: 13.5px;
            font-weight: 700;
            color: #fff;
            white-space: nowrap;
        }
        .nav-links {
            display: flex;
            align-items: center;
            gap: 1px;
            flex-wrap: wrap;
        }
        .nav-links a {
            font-size: 12.5px;
            color: rgba(255,255,255,0.7);
            text-decoration: none;
            padding: 5px 9px;
            border-radius: 4px;
            white-space: nowrap;
            transition: all .15s;
        }
        .nav-links a:hover, .nav-links a.active {
            color: #fff;
            background: rgba(255,255,255,0.1);
        }
        .btn-logout-nav {
            background: #dc3545 !important;
            color: #fff !important;
            font-weight: 600 !important;
            border-radius: 4px;
            padding: 5px 13px !important;
            margin-left: 4px;
        }
        .btn-logout-nav:hover {
            background: #c82333 !important;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: var(--bg-color);
            color: var(--text-color);
            min-height: 100vh;
            transition: background 0.3s, color 0.3s;
        }
        
        .page-body { max-width: 1000px; margin: 0 auto; padding: 30px 20px 52px; }
        .page-title { text-align: center; margin-bottom: 30px; }
        .page-title h1 { font-size: 24px; font-weight: 700; color: var(--text-color); transition: color 0.3s; }
        .page-title p { font-size: 13px; color: #9aa5b4; margin-top: 4px; }
        .card { background: var(--card-bg); border-radius: 12px; box-shadow: var(--shadow); overflow: hidden; border: 1px solid var(--border-color); transition: background 0.3s, border-color 0.3s, box-shadow 0.3s; }
        .card-head { background: var(--nav-bg); padding: 14px 18px; }
        .card-head h3 { color: #fff; font-size: 14px; font-weight: 600; }
        .form-body { padding: 20px 24px 24px; }
        .field { margin-bottom: 12px; }
        .field label { display: block; font-size: 12px; font-weight: 600; color: var(--text-color); margin-bottom: 4px; transition: color 0.3s; }
        .field input, .field select, .field textarea {
            width: 100%; padding: 8px 10px; border: 1px solid var(--border-color); border-radius: 6px;
            font-size: 13px; font-family: inherit; background: var(--input-bg); color: var(--input-text); outline: none;
            transition: background 0.3s, color 0.3s, border-color 0.3s;
        }
        .field input:focus, .field select:focus, .field textarea:focus {
            border-color: #1e3a5f; box-shadow: 0 0 0 3px rgba(30,58,95,0.07);
        }
        .field textarea { min-height: 60px; resize: vertical; }
        .field-row { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
        .btn-submit {
            padding: 10px 24px; border: none; border-radius: 6px; background: #1e3a5f; color: #fff;
            font-size: 14px; font-weight: 600; cursor: pointer; transition: background 0.2s;
        }
        .btn-submit:hover { background: #16304f; }
        .alert { padding: 12px 16px; border-radius: 6px; margin-bottom: 16px; font-weight: 500; }
        .alert-success { background: #d4edda; color: #155724; border: 1px solid #b7ebc5; }
        .alert-error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .table-wrap { overflow-x: auto; padding: 0; }
        table { width: 100%; border-collapse: collapse; }
        thead th {
            background: #1e3a5f; color: #fff; font-size: 11px; font-weight: 700;
            padding: 12px 16px; text-align: left; text-transform: uppercase; letter-spacing: 0.06em;
        }
        tbody tr { border-bottom: 1px solid var(--border-color); transition: background 0.2s; }
        tbody tr:hover { background: var(--hover-bg); }
        tbody td { padding: 10px 16px; font-size: 13px; color: var(--table-text); transition: color 0.3s; }
        .btn-sm { padding: 3px 10px; border-radius: 4px; border: none; font-size: 11px; font-weight: 600; cursor: pointer; }
        .btn-red { background: #dc3545; color: #fff; }
        .btn-red:hover { background: #c82333; }
        .btn-blue { background: #1e3a5f; color: #fff; }
        .btn-blue:hover { background: #16304f; }
        
        .lab-badge {
            display: inline-block;
            padding: 2px 10px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 500;
            background: var(--badge-bg);
            color: var(--badge-text);
            transition: background 0.3s, color 0.3s;
        }
        
        body.dark-mode .lab-badge {
            background: #2c303a;
            color: #7aa2f7;
            border: 1px solid #4a4d57;
        }
        
        .file-download {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 4px 12px;
            border-radius: 4px;
            background: #1e3a5f;
            color: #fff;
            text-decoration: none;
            font-size: 12px;
            font-weight: 600;
            transition: background 0.2s;
        }
        .file-download:hover { background: #16304f; }
        body.dark-mode .file-download {
            background: #2c303a;
            color: #7aa2f7;
            border: 1px solid #4a4d57;
        }
        body.dark-mode .file-download:hover { background: #3a4050; }
        
        @media (max-width: 640px) {
            .field-row { grid-template-columns: 1fr; }
            nav { padding: 0 12px; }
        }
    </style>
</head>
<body>
<?php include 'admin_header.php'; ?>

<div class="page-body">
    <div class="page-title">
        <h1>📤 Upload Software</h1>
        <p>Upload software installation files for lab computers</p>
    </div>
    
    <?php if (isset($success)): ?>
        <div class="alert alert-success"><?= $success ?></div>
    <?php endif; ?>
    <?php if (isset($error)): ?>
        <div class="alert alert-error"><?= $error ?></div>
    <?php endif; ?>
    
    <div class="card" style="margin-bottom:24px;">
        <div class="card-head"><h3>➕ Upload New Software</h3></div>
        <div class="form-body">
            <form method="POST" enctype="multipart/form-data">
                <div class="field-row">
                    <div class="field">
                        <label>Software Name *</label>
                        <input type="text" name="software_name" placeholder="e.g. Visual Studio Code" required>
                    </div>
                    <div class="field">
                        <label>Version</label>
                        <input type="text" name="version" placeholder="e.g. 1.89.0">
                    </div>
                </div>
                <div class="field-row">
                    <div class="field">
                        <label>Lab Room *</label>
                        <select name="lab_room" required>
                            <option value="">Select Lab</option>
                            <option value="524">Lab 524</option>
                            <option value="526">Lab 526</option>
                            <option value="528">Lab 528</option>
                        </select>
                    </div>
                    <div class="field">
                        <label>Description</label>
                        <input type="text" name="description" placeholder="Short description">
                    </div>
                </div>
                <div class="field">
                    <label>Software File *</label>
                    <input type="file" name="software_file" required>
                    <small style="color:#888;">Allowed: EXE, MSI, ZIP, RAR, PDF, JPG, PNG</small>
                </div>
                <button type="submit" name="upload_software" class="btn-submit">📤 Upload Software</button>
            </form>
        </div>
    </div>
    
    <div class="card">
        <div class="card-head"><h3>📋 Uploaded Software</h3></div>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Software Name</th>
                        <th>Version</th>
                        <th>Lab Room</th>
                        <th>File</th>
                        <th>Uploaded By</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($uploads): foreach ($uploads as $u): ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($u['software_name']) ?></strong></td>
                        <td><?= htmlspecialchars($u['version'] ?? '—') ?></td>
                        <td><span class="lab-badge">📍 Lab <?= htmlspecialchars($u['lab_room']) ?></span></td>
                        <td>
                            <?php if (!empty($u['file_path'])): ?>
                                <a href="<?= htmlspecialchars($u['file_path']) ?>" target="_blank" class="file-download">📥 Download</a>
                            <?php else: ?>
                                <span style="color:#9aa5b4;">—</span>
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($u['uploaded_by'] ?? 'Admin') ?></td>
                        <td><?= date('M d, Y', strtotime($u['uploaded_at'])) ?></td>
                    </tr>
                    <?php endforeach; else: ?>
                    <tr><td colspan="6" style="padding:30px;text-align:center;color:#9aa5b4;font-size:13px;font-style:italic;">No software uploaded yet.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
</body>
</html>