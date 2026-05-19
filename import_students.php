<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: admin.php');
    exit;
}

$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['csv_file'])) {
    $file = $_FILES['csv_file']['tmp_name'];
    
    if (($handle = fopen($file, 'r')) !== false) {
        $row = 0;
        $success = 0;
        $errors = 0;
        
        while (($data = fgetcsv($handle, 1000, ',')) !== false) {
            $row++;
            if ($row == 1) continue; // Skip header row
            
            // Expected columns: ID Number, Last Name, First Name, Middle Name, Course, Year Level, Email, Password
            if (count($data) >= 7) {
                $id_number = trim($data[0] ?? '');
                $lastname = trim($data[1] ?? '');
                $firstname = trim($data[2] ?? '');
                $middlename = trim($data[3] ?? '');
                $course = trim($data[4] ?? '');
                $year_level = (int)($data[5] ?? 1);
                $email = trim($data[6] ?? '');
                $password = trim($data[7] ?? 'Password123');
                
                if ($id_number && $lastname && $firstname && $email) {
                    try {
                        $pdo->prepare("INSERT INTO students (id_number, lastname, firstname, middlename, course, year_level, email, password, session) 
                                       VALUES (?, ?, ?, ?, ?, ?, ?, ?, 30)")
                            ->execute([$id_number, $lastname, $firstname, $middlename, $course, $year_level, $email, password_hash($password, PASSWORD_DEFAULT)]);
                        $success++;
                    } catch (PDOException $e) {
                        $errors++;
                    }
                } else {
                    $errors++;
                }
            }
        }
        fclose($handle);
        $message = "Import complete: $success students added, $errors errors.";
        $message_type = $success > 0 ? 'success' : 'error';
    } else {
        $message = 'Could not open the uploaded file.';
        $message_type = 'error';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Import Students</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        *{box-sizing:border-box;margin:0;padding:0;}
        body{font-family:'Plus Jakarta Sans',sans-serif;background:#f7f8fa;color:#1e2a38;padding:20px;}
        .container{max-width:600px;margin:0 auto;}
        .card{background:#fff;border-radius:12px;box-shadow:0 4px 12px rgba(0,0,0,0.08);padding:30px;border:none;}
        h1{text-align:center;color:#1e3a5f;margin-bottom:10px;}
        .sub{text-align:center;color:#9aa5b4;margin-bottom:20px;}
        .alert{padding:12px 16px;border-radius:6px;margin-bottom:16px;font-weight:500;}
        .alert-success{background:#d4edda;color:#155724;border:1px solid #b7ebc5;}
        .alert-error{background:#f8d7da;color:#721c24;border:1px solid #f5c6cb;}
        .field{margin-bottom:16px;}
        .field label{display:block;font-size:13px;font-weight:600;color:#4a5568;margin-bottom:6px;}
        .field input[type="file"]{width:100%;padding:10px;border:2px dashed #d0d7e2;border-radius:8px;font-size:13px;cursor:pointer;}
        .btn{width:100%;padding:12px;border:none;border-radius:6px;background:#1e3a5f;color:#fff;font-size:14px;font-weight:600;cursor:pointer;transition:all 0.2s;}
        .btn:hover{background:#16304f;}
        .back{display:block;text-align:center;margin-top:16px;color:#1e3a5f;text-decoration:none;font-weight:600;}
        .back:hover{text-decoration:underline;}
        .sample{background:#f8f9fa;padding:12px;border-radius:6px;font-size:12px;font-family:monospace;margin-top:10px;}
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <h1>📤 Import Students</h1>
            <p class="sub">Upload a CSV file to add multiple students at once</p>
            
            <?php if ($message): ?>
                <div class="alert alert-<?= $message_type ?>"><?= $message ?></div>
            <?php endif; ?>
            
            <form method="POST" enctype="multipart/form-data">
                <div class="field">
                    <label>CSV File</label>
                    <input type="file" name="csv_file" accept=".csv" required>
                </div>
                <button type="submit" class="btn">Upload and Import</button>
            </form>
            
            <div class="sample">
                <strong>CSV Format:</strong><br>
                id_number,lastname,firstname,middlename,course,year_level,email,password<br>
                <em>Example:</em><br>
                2024-00005,Dela Cruz,Juan,Santos,BSIT,1,juan@uc.edu.ph,Password123
            </div>
            
            <a href="admin_dashboard.php?page=students" class="back">← Back to Students</a>
        </div>
    </div>
</body>
</html>