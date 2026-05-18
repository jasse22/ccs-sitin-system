<?php
require_once 'db.php'; // Standardized PDO connection

$sitin_id = isset($_GET['sitin_id']) ? $_GET['sitin_id'] : null;

if (!$sitin_id) {
    die("Error: Sit-in session not specified.");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CCS | Submit Feedback</title>
    <style>
        :root {
            --nav-bg: #1e3a5f; /* Dark navy from your header */
            --primary-blue: #3498db; /* Blue from 'Give Feedback' button */
            --logout-red: #e74c3c; /* Red from 'Log out' button */
            --text-dark: #2c3e50;
            --bg-light: #f4f7f6;
        }

        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background-color: var(--bg-light);
            margin: 0;
            display: flex;
            flex-direction: column;
            height: 100vh;
        }

        /* Matches your top navigation bar */
        .navbar {
            background-color: var(--nav-bg);
            color: white;
            padding: 15px 50px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-weight: bold;
        }

        .container {
            flex: 1;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .feedback-card {
            background: white;
            width: 100%;
            max-width: 550px;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            text-align: center;
        }

        h2 {
            color: var(--nav-bg);
            margin-bottom: 25px;
            font-size: 24px;
        }

        textarea {
            width: 100%;
            height: 180px;
            padding: 15px;
            border: 1px solid #dcdde1;
            border-radius: 5px;
            font-size: 14px;
            resize: none;
            box-sizing: border-box;
            outline: none;
            transition: border-color 0.3s;
        }

        textarea:focus {
            border-color: var(--primary-blue);
        }

        .button-group {
            margin-top: 30px;
            display: flex;
            justify-content: center;
            gap: 15px;
        }

        .btn-submit {
            background-color: var(--primary-blue);
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 600;
            font-size: 14px;
        }

        .btn-submit:hover {
            background-color: #2980b9;
        }

        .btn-cancel {
            background-color: transparent;
            color: var(--logout-red);
            border: 1px solid var(--logout-red);
            padding: 11px 30px;
            border-radius: 5px;
            text-decoration: none;
            font-weight: 600;
            font-size: 14px;
            transition: all 0.3s;
        }

        .btn-cancel:hover {
            background-color: var(--logout-red);
            color: white;
        }
    </style>
</head>
<body>

<div class="navbar">
    <div>CCS Sit-in Monitoring System</div>
</div>

<div class="container">
    <div class="feedback-card">
        <h2>Submit Feedback</h2>
        <form action="save_feedback.php" method="POST">
            <input type="hidden" name="sitin_id" value="<?php echo htmlspecialchars($sitin_id); ?>">
            
            <textarea name="feedback" placeholder="Write your feedback here..." required></textarea>
            
            <div class="button-group">
                <button type="submit" class="btn-submit">Submit Feedback</button>
                <a href="history.php" class="btn-cancel">Cancel</a>
            </div>
        </form>
    </div>
</div>

</body>
</html>