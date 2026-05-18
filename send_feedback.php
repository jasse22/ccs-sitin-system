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
            --primary-hover: #2980b9;
            --logout-red: #e74c3c; /* Red from 'Log out' button */
            --text-dark: #2c3e50;
            --text-muted: #7f8c8d;
            --bg-light: #f4f7f6;
            --border-color: #dcdde1;
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
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.05);
            box-sizing: border-box;
        }

        .feedback-header {
            text-align: center;
            margin-bottom: 25px;
        }

        .icon-circle {
            width: 56px;
            height: 56px;
            background-color: #ebf5fb;
            color: var(--primary-blue);
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 0 auto 15px auto;
        }

        h2 {
            color: var(--nav-bg);
            margin: 0 0 8px 0;
            font-size: 24px;
            font-weight: 600;
        }

        .feedback-header p {
            color: var(--text-muted);
            font-size: 14px;
            line-height: 1.5;
            margin: 0;
        }

        .textarea-container {
            position: relative;
            margin-bottom: 5px;
        }

        textarea {
            width: 100%;
            height: 160px;
            padding: 15px;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            font-size: 15px;
            color: var(--text-dark);
            resize: none;
            box-sizing: border-box;
            outline: none;
            background-color: #fafafa;
            transition: all 0.3s ease;
        }

        textarea:focus {
            border-color: var(--primary-blue);
            background-color: #ffffff;
            box-shadow: 0 0 0 4px rgba(52, 152, 219, 0.1);
        }

        .char-count {
            text-align: right;
            font-size: 12px;
            color: var(--text-muted);
            margin-top: 6px;
            padding-right: 4px;
        }

        .button-group {
            margin-top: 25px;
            display: flex;
            justify-content: flex-end;
            gap: 12px;
        }

        .btn-submit {
            background-color: var(--primary-blue);
            color: white;
            border: none;
            padding: 12px 28px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            font-size: 14px;
            transition: background-color 0.2s, transform 0.1s;
        }

        .btn-submit:hover {
            background-color: var(--primary-hover);
        }

        .btn-submit:active {
            transform: scale(0.98);
        }

        .btn-cancel {
            background-color: transparent;
            color: var(--text-muted);
            border: 1px solid var(--border-color);
            padding: 11px 24px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 600;
            font-size: 14px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s ease;
        }

        .btn-cancel:hover {
            background-color: #f5f6fa;
            color: var(--logout-red);
            border-color: var(--logout-red);
        }
    </style>
</head>
<body>

<div class="navbar">
    <div>CCS Sit-in Monitoring System</div>
</div>

<div class="container">
    <div class="feedback-card">
        
        <div class="feedback-header">
            <div class="icon-circle">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>
            </div>
            <h2>Submit Feedback</h2>
            <p>Help us improve your laboratory experience. Share your thoughts or report any workstation issues.</p>
        </div>

        <form action="save_feedback.php" method="POST">
            <input type="hidden" name="sitin_id" value="<?php echo htmlspecialchars($sitin_id); ?>">
            
            <div class="textarea-container">
                <textarea name="feedback" id="feedbackText" maxlength="500" placeholder="Write your feedback here..." required></textarea>
                <div class="char-count"><span id="currentChar">0</span>/500</div>
            </div>
            
            <div class="button-group">
                <button type="submit" class="btn-submit">Submit Feedback</button>
                <a href="history.php" class="btn-cancel">Cancel</a>
            </div>
        </form>
    </div>
</div>

<script>
    document.getElementById('feedbackText').addEventListener('input', function() {
        document.getElementById('currentChar').textContent = this.value.length;
    });
</script>

</body>
</html>