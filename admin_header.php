<?php
// Get the current page filename for active class
$current_page = basename($_SERVER['PHP_SELF']);
?>
<nav>
  <div class="nav-brand" style="display:flex;align-items:center;gap:10px;">
    <!-- UC Logo -->
    <img src="uploads/uc-logo.png" alt="UC Logo" style="width:32px;height:32px;border-radius:50%;object-fit:cover;">
    <span>CCS Admin Dashboard</span>
  </div>
  <div class="nav-links">
    <a href="#" onclick="openModal('searchModal');return false;">🔍︎</a>
    <a href="admin_dashboard.php?page=home" class="<?= ($current_page === 'admin_dashboard.php' && !isset($_GET['page'])) || (isset($_GET['page']) && $_GET['page'] === 'home') ? 'active' : '' ?>">Home</a>
    <a href="admin_dashboard.php?page=students" class="<?= isset($_GET['page']) && $_GET['page'] === 'students' ? 'active' : '' ?>">Students</a>
    <a href="admin_dashboard.php?page=sitin" class="<?= isset($_GET['page']) && $_GET['page'] === 'sitin' ? 'active' : '' ?>">Sit-in</a>
    <a href="admin_dashboard.php?page=records" class="<?= isset($_GET['page']) && $_GET['page'] === 'records' ? 'active' : '' ?>">SRecords</a>
    <a href="admin_dashboard.php?page=sessions" class="<?= isset($_GET['page']) && $_GET['page'] === 'sessions' ? 'active' : '' ?>">Sessions</a>
    <a href="admin_dashboard.php?page=reports" class="<?= isset($_GET['page']) && $_GET['page'] === 'reports' ? 'active' : '' ?>">Reports</a>
    <a href="admin_dashboard.php?page=reservation" class="<?= isset($_GET['page']) && $_GET['page'] === 'reservation' ? 'active' : '' ?>">Reservations</a>
    <a href="admin_software.php" class="<?= $current_page === 'admin_software.php' ? 'active' : '' ?>">Manage Software</a>
    <a href="admin_feedback.php" class="<?= $current_page === 'admin_feedback.php' ? 'active' : '' ?>">View Student Feedback</a>
    <a href="admin_software_upload.php">Upload Software</a>
    <a href="admin_ai_recommendations.php" class="<?= $current_page === 'admin_ai_recommendations.php' ? 'active' : '' ?>">AI Recommendations</a>
    <button onclick="toggleDarkMode()" style="background:transparent !important;background-color:transparent !important;border:none !important;padding:6px 10px;cursor:pointer;font-size:18px;color:rgba(255,255,255,0.7);outline:none !important;box-shadow:none !important;border-radius:0 !important;display:flex;align-items:center;justify-content:center;" title="Toggle Dark Mode">
      <span class="moon-icon" style="display:none;">🌙</span>
      <span class="sun-icon">☀️</span>
    </button>
    <a href="admin_logout.php" class="btn-logout-nav" style="background:#dc3545;color:#fff;padding:5px 13px;border-radius:4px;font-size:12.5px;font-weight:600;text-decoration:none;margin-left:4px;">Log out</a>
  </div>
</nav>