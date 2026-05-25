<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: admin.php');
    exit;
}

// ── FETCH DATA ───────────────────────────────────────────────

// 1. Historical data for pattern analysis
$historicalStmt = $pdo->query("
    SELECT 
        DAYOFWEEK(login_time) as day_of_week,
        HOUR(login_time) as hour_of_day,
        laboratory,
        COUNT(*) as usage_count
    FROM sit_in_history
    WHERE login_time IS NOT NULL
    GROUP BY day_of_week, hour_of_day, laboratory
    ORDER BY usage_count DESC
");
$historicalData = $historicalStmt->fetchAll();

// 2. Peak hours analysis
$peakHoursStmt = $pdo->query("
    SELECT 
        HOUR(login_time) as hour,
        COUNT(*) as count
    FROM sit_in_history
    WHERE login_time IS NOT NULL
    GROUP BY HOUR(login_time)
    ORDER BY count DESC
    LIMIT 5
");
$peakHours = $peakHoursStmt->fetchAll();

// 3. Lab popularity
$labPopularityStmt = $pdo->query("
    SELECT 
        laboratory,
        COUNT(*) as count
    FROM sit_in_history
    GROUP BY laboratory
    ORDER BY count DESC
");
$labPopularity = $labPopularityStmt->fetchAll();

// 4. Day of week analysis
$dayOfWeekStmt = $pdo->query("
    SELECT 
        DAYNAME(login_time) as day_name,
        COUNT(*) as count
    FROM sit_in_history
    WHERE login_time IS NOT NULL
    GROUP BY DAYNAME(login_time)
    ORDER BY FIELD(DAYNAME(login_time), 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday')
");
$dayOfWeekData = $dayOfWeekStmt->fetchAll();

// 5. Today's count
$todayStmt = $pdo->query("SELECT COUNT(*) as today_count FROM sit_in_history WHERE DATE(login_time) = CURDATE()");
$todayCount = $todayStmt->fetchColumn();

// 6. Total sessions
$totalSessions = $pdo->query("SELECT COUNT(*) FROM sit_in_history")->fetchColumn();

// ── AI LOGIC ──────────────────────────────────────────────────

$recommendations = [];

// 1. Peak hours recommendation
if (!empty($peakHours)) {
    $bestHour = $peakHours[0]['hour'];
    $recommendations[] = [
        'type' => 'peak_hours',
        'message' => "📊 Peak usage is around <strong>{$bestHour}:00</strong>. Consider scheduling maintenance during off-peak hours.",
        'icon' => '🕐',
        'priority' => 'high'
    ];
}

// 2. Lab usage recommendation
if (!empty($labPopularity)) {
    $mostPopularLab = $labPopularity[0]['laboratory'];
    $leastPopularLab = end($labPopularity)['laboratory'];
    $recommendations[] = [
        'type' => 'lab_usage',
        'message' => "🔬 Lab <strong>{$mostPopularLab}</strong> is the most popular. Lab <strong>{$leastPopularLab}</strong> is underutilized. Consider redistributing resources.",
        'icon' => '📊',
        'priority' => 'medium'
    ];
}

// 3. Predictive forecasting (based on day of week)
if (!empty($dayOfWeekData)) {
    $busiestDay = $dayOfWeekData[0]['day_name'];
    $recommendations[] = [
        'type' => 'predictive',
        'message' => "📈 <strong>{$busiestDay}</strong> is typically the busiest day based on historical data. Plan for increased capacity on {$busiestDay}s.",
        'icon' => '🔮',
        'priority' => 'medium'
    ];
}

// 4. Automated scheduling suggestion
if (!empty($peakHours) && !empty($dayOfWeekData)) {
    $bestHour = $peakHours[0]['hour'];
    $busiestDay = $dayOfWeekData[0]['day_name'];
    $recommendations[] = [
        'type' => 'scheduling',
        'message' => "📅 Optimal maintenance window: <strong>{$busiestDay} at {$bestHour}:00</strong>. Schedule lab maintenance during this time.",
        'icon' => '📅',
        'priority' => 'high'
    ];
}

// 5. Daily summary
$recommendations[] = [
    'type' => 'daily_summary',
    'message' => "📅 Today's sit-in count: <strong>{$todayCount}</strong> sessions.",
    'icon' => '📋',
    'priority' => 'low'
];

// 6. General suggestion
if ($totalSessions > 0) {
    $recommendations[] = [
        'type' => 'general',
        'message' => "🎯 Total of <strong>{$totalSessions}</strong> sit-in sessions recorded. Keep up the good work!",
        'icon' => '🎯',
        'priority' => 'low'
    ];
}

// 7. Predictive forecast (future peak prediction)
if (!empty($historicalData)) {
    // Simple prediction: average usage by hour
    $hourlyAvg = [];
    foreach ($historicalData as $data) {
        $hour = $data['hour_of_day'];
        if (!isset($hourlyAvg[$hour])) {
            $hourlyAvg[$hour] = 0;
        }
        $hourlyAvg[$hour] += $data['usage_count'];
    }
    arsort($hourlyAvg);
    $predictedPeakHour = array_key_first($hourlyAvg);
    
    if ($predictedPeakHour !== null) {
        $recommendations[] = [
            'type' => 'forecast',
            'message' => "🔮 Predicted peak hour: <strong>{$predictedPeakHour}:00</strong> based on historical patterns. Prepare for increased traffic.",
            'icon' => '⏰',
            'priority' => 'high'
        ];
    }
}

// Sort recommendations by priority
usort($recommendations, function($a, $b) {
    $priorityOrder = ['high' => 0, 'medium' => 1, 'low' => 2];
    return $priorityOrder[$a['priority']] - $priorityOrder[$b['priority']];
});
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CCS | AI Recommendations</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="darkmode.js"></script>
    <style>
        :root {
            --bg-color: #f7f8fa;
            --text-color: #1e2a38;
            --card-bg: #ffffff;
            --nav-bg: #1e3a5f;
            --border-color: #e2e6ea;
            --shadow: 0 4px 12px rgba(0,0,0,0.08);
            --hover-bg: #f0f4f9;
        }

        body.dark-mode {
            --bg-color: #1a1f2e;
            --text-color: #e8edf5;
            --card-bg: #242b3d;
            --nav-bg: #141824;
            --border-color: #2e364a;
            --shadow: 0 4px 12px rgba(0,0,0,0.3);
            --hover-bg: #2a3145;
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

        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: var(--bg-color);
            color: var(--text-color);
            min-height: 100vh;
            transition: background 0.3s, color 0.3s;
        }
        
        .page-body {
            max-width: 1100px;
            margin: 0 auto;
            padding: 30px 20px 52px;
        }
        
        .page-title {
            text-align: center;
            margin-bottom: 30px;
        }
        .page-title h1 {
            font-size: 24px;
            font-weight: 700;
            color: var(--text-color);
            transition: color 0.3s;
        }
        .page-title p {
            font-size: 13px;
            color: #9aa5b4;
            margin-top: 4px;
        }
        
        .card {
            background: var(--card-bg);
            border-radius: 12px;
            box-shadow: var(--shadow);
            overflow: hidden;
            border: 1px solid var(--border-color);
            transition: background 0.3s, border-color 0.3s, box-shadow 0.3s;
        }
        .card-head {
            background: var(--nav-bg);
            padding: 14px 18px;
        }
        .card-head h3 {
            color: #fff;
            font-size: 14px;
            font-weight: 600;
        }
        
        .recommendations-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 16px;
            padding: 20px;
        }
        
        .priority-badge {
            display: inline-block;
            padding: 2px 10px;
            border-radius: 12px;
            font-size: 10px;
            font-weight: 600;
            text-transform: uppercase;
            margin-top: 4px;
        }
        .priority-high {
            background: #fee2e2;
            color: #dc2626;
        }
        .priority-medium {
            background: #fef3c7;
            color: #d97706;
        }
        .priority-low {
            background: #dbeafe;
            color: #2563eb;
        }
        
        body.dark-mode .priority-high {
            background: #2c303a;
            color: #ef4444;
            border: 1px solid #4a4d57;
        }
        body.dark-mode .priority-medium {
            background: #2c303a;
            color: #f59e0b;
            border: 1px solid #4a4d57;
        }
        body.dark-mode .priority-low {
            background: #2c303a;
            color: #3b82f6;
            border: 1px solid #4a4d57;
        }

        .recommendation-card {
            background: #f8f9fa;
            border: 1px solid var(--border-color);
            border-radius: 10px;
            padding: 16px;
            transition: all 0.3s ease;
            display: flex;
            align-items: flex-start;
            gap: 12px;
        }
        .recommendation-card:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow);
        }
        
        .recommendation-icon {
            font-size: 28px;
            flex-shrink: 0;
            width: 50px;
            height: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #eef3f9;
            border-radius: 50%;
            border: 1px solid #c5d5e8;
        }
        
        .recommendation-content {
            flex: 1;
        }
        .recommendation-title {
            font-size: 14px;
            font-weight: 600;
            color: var(--text-color);
            margin-bottom: 4px;
        }
        .recommendation-message {
            font-size: 13px;
            color: #4a5568;
            line-height: 1.6;
        }
        
        body.dark-mode .recommendation-card {
            background: #2c303a;
            border-color: #4a4d57;
        }
        body.dark-mode .recommendation-icon {
            background: #2c303a;
            border-color: #4a4d57;
        }
        body.dark-mode .recommendation-title {
            color: #e8edf5;
        }
        body.dark-mode .recommendation-message {
            color: #a0a8b8;
        }
        
        .insight-section {
            padding: 20px;
        }
        .insight-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid var(--border-color);
        }
        .insight-row:last-child {
            border-bottom: none;
        }
        .insight-label {
            color: #4a5568;
            font-size: 13px;
        }
        .insight-value {
            font-weight: 600;
            color: var(--text-color);
            font-size: 13px;
        }
        body.dark-mode .insight-label {
            color: #a0a8b8;
        }
        body.dark-mode .insight-value {
            color: #e8edf5;
        }
        
        .no-data {
            text-align: center;
            padding: 40px;
            color: #9aa5b4;
            font-size: 14px;
        }
        
        @media (max-width: 640px) {
            .recommendations-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
<?php include 'admin_header.php'; ?>

<div class="page-body">
    <div class="page-title">
        <h1>🤖 AI Recommendations</h1>
        <p>Intelligent suggestions based on sit-in data patterns</p>
    </div>
    
    <div class="card" style="margin-bottom:24px;">
        <div class="card-head"><h3>💡 Recommendations</h3></div>
        <div class="recommendations-grid">
            <?php foreach ($recommendations as $rec): ?>
            <div class="recommendation-card">
                <div class="recommendation-icon"><?= $rec['icon'] ?></div>
                <div class="recommendation-content">
                    <div class="recommendation-title"><?= ucfirst(str_replace('_', ' ', $rec['type'])) ?></div>
                    <div class="recommendation-message"><?= $rec['message'] ?></div>
                    <div class="priority-badge priority-<?= $rec['priority'] ?>"><?= $rec['priority'] ?></div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    
    <div class="card">
        <div class="card-head"><h3>📊 Data Insights</h3></div>
        <div class="insight-section">
            <h4 style="color:var(--text-color);margin-bottom:12px;">Peak Hours</h4>
            <?php if ($peakHours): ?>
                <?php foreach ($peakHours as $index => $ph): ?>
                <div class="insight-row">
                    <span class="insight-label">#<?= $index + 1 ?> — <?= $ph['hour'] ?>:00</span>
                    <span class="insight-value"><?= $ph['count'] ?> sessions</span>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="no-data">No peak hour data available yet.</div>
            <?php endif; ?>
            
            <h4 style="color:var(--text-color);margin:16px 0 12px;">Lab Popularity</h4>
            <?php if ($labPopularity): ?>
                <?php foreach ($labPopularity as $lp): ?>
                <div class="insight-row">
                    <span class="insight-label">Lab <?= htmlspecialchars($lp['laboratory']) ?></span>
                    <span class="insight-value"><?= $lp['count'] ?> sessions</span>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="no-data">No lab popularity data available yet.</div>
            <?php endif; ?>

            <h4 style="color:var(--text-color);margin:16px 0 12px;">Busiest Days</h4>
            <?php if ($dayOfWeekData): ?>
                <?php foreach ($dayOfWeekData as $d): ?>
                <div class="insight-row">
                    <span class="insight-label"><?= htmlspecialchars($d['day_name']) ?></span>
                    <span class="insight-value"><?= $d['count'] ?> sessions</span>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="no-data">No day-of-week data available yet.</div>
            <?php endif; ?>
        </div>
    </div>
</div>

</body>
</html>