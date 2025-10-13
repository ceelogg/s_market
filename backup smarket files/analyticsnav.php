<?php
$servername = "localhost";
$username = "root";
$password = "";
$database = "s_market";

$conn = mysqli_connect($servername, $username, $password, $database);
mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>S-Market - Analytics</title>
    <link rel="stylesheet" href="AnalyticsCSStesing.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
    <!-- Mobile Menu Toggle -->
    <button class="mobile-menu-toggle" id="mobileMenuToggle">
        <i class="fas fa-bars"></i>
    </button>

    <!-- Mobile Overlay -->
    <div class="mobile-overlay" id="mobileOverlay"></div>

    <div class="analytics-container">
        <!-- Sidebar Navigation -->   

        <div class="sidebar" id="sidebar">
            <div class="logo">
                <img src="logo.png" alt="S-Market Logo">
            <h2>S-Market</h2>
    </div>
    <ul class="nav-links">
        <li class="nav-item"><a href="userpage.php"><i class="fas fa-home"></i> <span>Dashboard</span></a></li>
        <li class="nav-item"><a href="productnav.php"><i class="fas fa-box"></i> <span>Products</span></a></li>
        <li class="nav-item active"><a href="analyticsnav.php"><i class="fas fa-chart-bar"></i> <span>Analytics</span></a></li>
        <li class="nav-item"><a href="AiRecnav.php"><i class="fas fa-lightbulb"></i> <span>AI Recommendations</span></a></li>
        <li class="nav-item"><a href="#"><i class="fas fa-bullhorn"></i> <span>Marketing</span></a></li>
        <li class="nav-item"><a href="#"><i class="fas fa-cog"></i> <span>Settings</span></a></li>
    </ul>
</div>

        <!-- Main Content Area -->
        <div class="main-content">
            <!-- Page Header -->
            <div class="page-header">
                <h1><i class="fas fa-chart-line"></i> Analytics Dashboard</h1>
                <div class="header-controls">
                    <div class="date-filter">
                        <span>Period:</span>
                        <select>
                            <option>Last 7 days</option>
                            <option selected>Last 30 days</option>
                            <option>Last 90 days</option>
                            <option>Year to date</option>
                        </select>
                    </div>
                    <button class="export-btn">
                        <i class="fas fa-download"></i> Export Report
                    </button>
                </div>
            </div>

            <!-- Quick Stats -->
            <div class="quick-stats">
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                    <div class="stat-info">
                        <h3>Total Sales</h3>
                        <div class="stat-value">$30.19K</div>
                        <div class="stat-trend positive">+12.5%</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-box"></i>
                    </div>
                    <div class="stat-info">
                        <h3>Items Sold</h3>
                        <div class="stat-value">1,143</div>
                        <div class="stat-trend positive">+8.3%</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-money-bill-wave"></i>
                    </div>
                    <div class="stat-info">
                        <h3>Capital</h3>
                        <div class="stat-value">$247.24K</div>
                        <div class="stat-trend negative">-2.1%</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fas fa-chart-pie"></i>
                    </div>
                    <div class="stat-info">
                        <h3>Profit</h3>
                        <div class="stat-value">$30.19K</div>
                        <div class="stat-trend positive">+15.2%</div>
                    </div>
                </div>
            </div>

            <!-- Power BI Report Container -->
            <div class="powerbi-section">
                <div class="section-header">
                    <h2>Interactive Sales Analytics</h2>
                    <div class="section-actions">
                        <button class="refresh-btn">
                            <i class="fas fa-sync-alt"></i> Refresh
                        </button>
                    </div>
                </div>
                <div class="powerbi-container">
                    <iframe 
                        src="https://app.powerbi.com/reportEmbed?reportId=8a970f37-63b7-49bb-b876-a0a2d44006f9&autoAuth=true&ctid=230efac0-932f-4bb5-ab75-fbd736b468f9&navContentPaneEnabled=false&filterPaneEnabled=false" 
                        allowFullScreen="true">
                    </iframe>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Mobile sidebar functionality
        const mobileMenuToggle = document.getElementById('mobileMenuToggle');
        const sidebar = document.getElementById('sidebar');
        const sidebarClose = document.getElementById('sidebarClose');
        const mobileOverlay = document.getElementById('mobileOverlay');

        function toggleSidebar() {
            sidebar.classList.toggle('mobile-open');
            mobileOverlay.classList.toggle('active');
            document.body.classList.toggle('sidebar-open');
        }

        mobileMenuToggle.addEventListener('click', toggleSidebar);
        sidebarClose.addEventListener('click', toggleSidebar);
        mobileOverlay.addEventListener('click', toggleSidebar);

        // Close sidebar when clicking on nav links (mobile)
        document.querySelectorAll('.nav-item a').forEach(link => {
            link.addEventListener('click', () => {
                if (window.innerWidth <= 768) {
                    toggleSidebar();
                }
            });
        });

        // Handle window resize
        window.addEventListener('resize', () => {
            if (window.innerWidth > 768) {
                sidebar.classList.remove('mobile-open');
                mobileOverlay.classList.remove('active');
                document.body.classList.remove('sidebar-open');
            }
        });
    </script>
</body>
</html>