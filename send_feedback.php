<?php
require_once 'db.php';

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
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="darkmode.js"></script>
    <style>
        :root {
            --bg-color: #f7f8fa;
            --text-color: #1e2a38;
            --card-bg: #ffffff;
            --nav-bg: #1e3a5f;
            --border-color: #e2e6ea;
            --shadow: 0 8px 30px rgba(0,0,0,0.08);
            --input-bg: #fafbfc;
            --input-border: #d0d7e2;
            --hover-bg: #f8f9fa;
            --icon-bg: #eef3f9;
            --icon-color: #1e3a5f;
        }

        body.dark-mode {
            --bg-color: #1a1f2e;
            --text-color: #e8edf5;
            --card-bg: #242b3d;
            --nav-bg: #141824;
            --border-color: #2e364a;
            --shadow: 0 8px 30px rgba(0,0,0,0.4);
            --input-bg: #242b3d;
            --input-border: #2e364a;
            --hover-bg: #2a3248;
            --icon-bg: #2a3248;
            --icon-color: #e8edf5;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: var(--bg-color);
            color: var(--text-color);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            transition: background 0.3s, color 0.3s;
        }
        
        .navbar {
            width: 100%;
            background: var(--nav-bg);
            padding: 14px 30px;
            display: flex;
            justify-content: center;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 100;
            transition: background 0.3s;
        }
        .navbar span {
            color: #fff;
            font-size: 15px;
            font-weight: 700;
            letter-spacing: 0.5px;
        }
        
        .container {
            flex: 1;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 40px 20px;
            width: 100%;
        }
        
        .feedback-card {
            background: var(--card-bg);
            width: 100%;
            max-width: 560px;
            padding: 40px 36px;
            border-radius: 16px;
            box-shadow: var(--shadow);
            border: 1px solid var(--border-color);
            transition: background 0.3s, border-color 0.3s;
        }
        
        .feedback-header {
            text-align: center;
            margin-bottom: 28px;
        }
        
        .icon-circle {
            width: 60px;
            height: 60px;
            background: var(--icon-bg);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 16px auto;
            transition: background 0.3s;
        }
        .icon-circle svg {
            width: 28px;
            height: 28px;
            stroke: var(--icon-color);
            fill: none;
            stroke-width: 1.5;
            stroke-linecap: round;
            stroke-linejoin: round;
            transition: stroke 0.3s;
        }
        
        h2 {
            color: var(--text-color);
            font-size: 22px;
            font-weight: 700;
            margin-bottom: 6px;
            transition: color 0.3s;
        }
        
        .subtitle {
            color: #8e9bb3;
            font-size: 14px;
            line-height: 1.6;
        }
        
        .textarea-container {
            position: relative;
            margin-bottom: 6px;
        }
        
        textarea {
            width: 100%;
            height: 150px;
            padding: 14px 16px;
            border: 1.5px solid var(--input-border);
            border-radius: 10px;
            font-size: 14px;
            font-family: 'Plus Jakarta Sans', sans-serif;
            color: var(--text-color);
            resize: none;
            outline: none;
            transition: border-color 0.2s, box-shadow 0.2s, background 0.3s;
            background: var(--input-bg);
        }
        textarea:focus {
            border-color: #1e3a5f;
            box-shadow: 0 0 0 4px rgba(30, 58, 95, 0.06);
        }
        textarea::placeholder {
            color: #9aa5b4;
        }
        
        .char-count {
            text-align: right;
            font-size: 12px;
            color: #8e9bb3;
            margin-top: 4px;
            font-weight: 500;
        }
        
        .button-group {
            display: flex;
            gap: 10px;
            margin-top: 22px;
        }
        
        .btn-submit {
            flex: 1;
            padding: 12px 20px;
            border: none;
            border-radius: 8px;
            background: #1e3a5f;
            color: #fff;
            font-size: 14px;
            font-weight: 600;
            font-family: 'Plus Jakarta Sans', sans-serif;
            cursor: pointer;
            transition: all 0.2s;
        }
        .btn-submit:hover {
            background: #16304f;
            transform: translateY(-1px);
        }
        .btn-submit:active {
            transform: scale(0.98);
        }
        
        .btn-cancel {
            padding: 12px 24px;
            border: 1.5px solid var(--input-border);
            border-radius: 8px;
            background: transparent;
            color: var(--text-color);
            font-size: 14px;
            font-weight: 600;
            font-family: 'Plus Jakarta Sans', sans-serif;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s;
        }
        .btn-cancel:hover {
            border-color: #1e3a5f;
            color: #1e3a5f;
            background: var(--hover-bg);
        }
        
        @media (max-width: 500px) {
            .feedback-card { padding: 28px 20px; }
            .button-group { flex-direction: column; }
            .btn-cancel { width: 100%; }
        }
    </style>
</head>
<body>

<div class="navbar">
    <span>CCS Sit-in Monitoring System</span>
</div>

<div class="container">
    <div class="feedback-card">
        <div class="feedback-header">
            <div class="icon-circle">
                <svg viewBox="0 0 24 24"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
            </div>
            <h2>Submit Feedback</h2>
            <p class="subtitle">Help us improve your laboratory experience. Share your thoughts or report any workstation issues.</p>
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