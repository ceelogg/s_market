<?php
// Get current page to set active state
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!-- Global Sidebar -->
<div class="sidebar">
    <div class="logo">
        <img src="logo.png" alt="S-Market Logo">
        <h2>S-Market</h2>
    </div>
    <ul class="nav-links">
        <li class="nav-item <?php echo $current_page == 'userpage.php' ? 'active' : ''; ?>">
            <a href="userpage.php"><i class="fas fa-home"></i> Dashboard</a>
        </li>
        <li class="nav-item <?php echo $current_page == 'productnav.php' ? 'active' : ''; ?>">
            <a href="productnav.php"><i class="fas fa-box"></i> Products</a>
        </li>
        <li class="nav-item <?php echo $current_page == 'analyticsnav.php' ? 'active' : ''; ?>">
            <a href="analyticsnav.php"><i class="fas fa-chart-bar"></i> Analytics</a>
        </li>
        <li class="nav-item <?php echo $current_page == 'AiRecnav.php' ? 'active' : ''; ?>">
            <a href="AiRecnav.php"><i class="fas fa-lightbulb"></i> AI Recommendations</a>
        </li>
        <li class="nav-item">
            <a href="#"><i class="fas fa-bullhorn"></i> Marketing</a>
        </li>
        <li class="nav-item">
            <a href="#"><i class="fas fa-cog"></i> Settings</a>
        </li>
    </ul>
    
    <div class="sidebar-footer">
        <div>Model: <strong>XGBoost</strong></div>
        <div>Last trained: <span id="lastTrained">—</span></div>
    </div>
</div>