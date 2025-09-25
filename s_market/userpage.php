<?php
session_start();

/* ---------------------------
   S-Market DB / KPI logic
   --------------------------- */
$servername = "localhost";
$username = "root";
$password = "";
$database = "s_market";

$conn = mysqli_connect($servername, $username, $password, $database);
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

/* --- KPI Queries --- */

// Capital = sum of stock * unit_price
$capitalQuery = "SELECT SUM(product_quantity * unit_price) AS total_capital FROM product";
$capitalResult = mysqli_query($conn, $capitalQuery);
$capitalRow = mysqli_fetch_assoc($capitalResult);
$capital = $capitalRow['total_capital'] ?? 0;

// Product Sales = sum of total_sales
$productSalesQuery = "SELECT SUM(total_sales) AS total_sales FROM product";
$productSalesResult = mysqli_query($conn, $productSalesQuery);
$productSalesRow = mysqli_fetch_assoc($productSalesResult);
$productSales = $productSalesRow['total_sales'] ?? 0;

// Profit = sum of (total_sales - (quantity_sold * unit_price))
$profitQuery = "SELECT SUM(total_sales - (quantity_sold * unit_price)) AS total_profit FROM product";
$profitResult = mysqli_query($conn, $profitQuery);
$profitRow = mysqli_fetch_assoc($profitResult);
$profit = $profitRow['total_profit'] ?? 0;

// Capital Loss (if any product was sold below cost) → this assumes total_sales < (quantity_sold*unit_price)
$capitalLossQuery = "
    SELECT SUM((quantity_sold * unit_price) - total_sales) AS total_loss 
    FROM product 
    WHERE total_sales < (quantity_sold * unit_price)";
$resultLoss = mysqli_query($conn, $capitalLossQuery);
$rowLoss = mysqli_fetch_assoc($resultLoss);
$capitalLoss = $rowLoss['total_loss'] ?? 0;

/* ---------------------------
   Handle Product Insertion
   --------------------------- */
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $branch = mysqli_real_escape_string($conn, $_POST['branch']);
    $productType = mysqli_real_escape_string($conn, $_POST['product_type']);
    $productName = mysqli_real_escape_string($conn, $_POST['product_name']);
    $quantity = intval($_POST['product_quantity']);
    $sold = intval($_POST['quantity_sold']);
    $unitPrice = floatval($_POST['unit_price']);
    $totalSales = floatval($_POST['total_sales']);
    $dateOfSale = mysqli_real_escape_string($conn, $_POST['date_of_sale']);
    $monthOfSale = mysqli_real_escape_string($conn, $_POST['month_of_sale']);

    $sql = "INSERT INTO product 
        (branch, product_type, product_name, product_quantity, quantity_sold, unit_price, total_sales, date_of_sale, month_of_sale) 
        VALUES 
        ('$branch', '$productType', '$productName', $quantity, $sold, $unitPrice, $totalSales, '$dateOfSale', '$monthOfSale')";

    if (mysqli_query($conn, $sql)) {
        $_SESSION['success'] = "✅ Product successfully added!";
    } else {
        $_SESSION['error'] = "❌ Error: " . mysqli_error($conn);
    }

    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

/* Flash messages */
if (isset($_SESSION['success'])) {
    echo "<script>alert('" . addslashes($_SESSION['success']) . "');</script>";
    unset($_SESSION['success']);
}
if (isset($_SESSION['error'])) {
    echo "<script>alert('" . addslashes($_SESSION['error']) . "');</script>";
    unset($_SESSION['error']);
}

mysqli_close($conn);


/* ---------------------------
   Mama Coco analytics data
   --------------------------- */
$branches = [
    'All Branches',
    'CTA Zandueta',
    'DM Foodmart',
    'CTA Camp 7',
    'BGH - OPD'
];

$months = [
    'August 2025',
    'July 2025',
    'June 2025',
    'May 2025',
    'April 2025',
    'March 2025'
];

$branchAnalytics = [
    'CTA Zandueta' => ['sales' => 285000, 'profit' => 95000, 'customers' => 1250, 'growth' => 12.5],
    'DM Foodmart'  => ['sales' => 320000, 'profit' => 112000, 'customers' => 1450, 'growth' => 8.3],
    'CTA Camp 7'   => ['sales' => 195000, 'profit' => 68000, 'customers' => 890, 'growth' => 15.2],
    'BGH - OPD'    => ['sales' => 410000, 'profit' => 145000, 'customers' => 1890, 'growth' => 6.7]
];

$monthlySalesData = [
    ['month' => 'Mar', 'CTA Zandueta' => 260000, 'DM Foodmart' => 295000, 'CTA Camp 7' => 180000, 'BGH - OPD' => 385000],
    ['month' => 'Apr', 'CTA Zandueta' => 275000, 'DM Foodmart' => 310000, 'CTA Camp 7' => 185000, 'BGH - OPD' => 395000],
    ['month' => 'May', 'CTA Zandueta' => 280000, 'DM Foodmart' => 315000, 'CTA Camp 7' => 190000, 'BGH - OPD' => 405000],
    ['month' => 'Jun', 'CTA Zandueta' => 285000, 'DM Foodmart' => 318000, 'CTA Camp 7' => 192000, 'BGH - OPD' => 408000],
    ['month' => 'Jul', 'CTA Zandueta' => 282000, 'DM Foodmart' => 320000, 'CTA Camp 7' => 194000, 'BGH - OPD' => 410000],
    ['month' => 'Aug', 'CTA Zandueta' => 285000, 'DM Foodmart' => 320000, 'CTA Camp 7' => 195000, 'BGH - OPD' => 410000]
];

$monthlyProfitData = [
    ['month' => 'Mar', 'CTA Zandueta' => 88000, 'DM Foodmart' => 103000, 'CTA Camp 7' => 63000, 'BGH - OPD' => 135000],
    ['month' => 'Apr', 'CTA Zandueta' => 92000, 'DM Foodmart' => 108000, 'CTA Camp 7' => 65000, 'BGH - OPD' => 138000],
    ['month' => 'May', 'CTA Zandueta' => 94000, 'DM Foodmart' => 110000, 'CTA Camp 7' => 67000, 'BGH - OPD' => 142000],
    ['month' => 'Jun', 'CTA Zandueta' => 95000, 'DM Foodmart' => 111000, 'CTA Camp 7' => 67500, 'BGH - OPD' => 143000],
    ['month' => 'Jul', 'CTA Zandueta' => 94500, 'DM Foodmart' => 112000, 'CTA Camp 7' => 68000, 'BGH - OPD' => 144000],
    ['month' => 'Aug', 'CTA Zandueta' => 95000, 'DM Foodmart' => 112000, 'CTA Camp 7' => 68000, 'BGH - OPD' => 145000]
];

// Selected filters (safe defaults)
$selectedBranch = isset($_GET['branch']) ? $_GET['branch'] : 'All Branches';
$selectedMonth  = isset($_GET['month']) ? $_GET['month'] : 'August 2025';

// Helper
function formatCurrency($amount) {
    return '₱' . number_format((float)$amount, 0, '.', ',');
}

// Totals (computed from the arrays so they exist)
$totalSales = array_sum(array_column($branchAnalytics, 'sales'));
$totalProfit = array_sum(array_column($branchAnalytics, 'profit'));
$totalBranches = max(0, count($branches) - 1);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>S-Market - AI Marketing Decision Modeling System</title>

    <!-- Your existing styles -->
    <link rel="stylesheet" href="dashboard.css">
    <link rel="stylesheet" href="Profile.css">
    <link rel="stylesheet" href="upload.css">

    <!-- Mama Coco styles (keeps the analytics look consistent) -->
    <link rel="stylesheet" href="maco.css">

    <!-- Font Awesome (kept) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        /* Tiny spacing fix so the analytics section sits nicely under metric cards */
        .analytics-section { margin-top: 1.25rem; }
        .chart-container { height: 260px; }
    </style>
</head>
<body>

<div class="container">

    <div class="sidebar">
        <div class="logo">
            <img src="logo.png" alt="S-Market Logo">
            <h2>S-Market</h2>
        </div>
        <ul class="nav-links">
            <li class="nav-item active"><i class="fas fa-home"></i> Dashboard</li>
            <li class="nav-item"><a href="productnav.php"><i class="fas fa-box"></i> Products</a></li>
            <li class="nav-item"><a href="analyticsnav.php"><i class="fas fa-chart-bar"></i> Analytics</a></li>
            <li class="nav-item"><a href="AiRecnav.php"><i class="fas fa-lightbulb"></i> AI Recommendations</a></li>
            <li class="nav-item"><i class="fas fa-bullhorn"></i> Marketing</li>
            <li class="nav-item"><i class="fas fa-cog"></i> Settings</li>
        </ul>
    </div>

    <div class="main-content">
        <div class="header">
            <!-- Search Bar -->
            <div class="search-bar">
                <i class="fas fa-search"></i>
                <input type="text" placeholder="Search products, or reports...">
            </div>

            <!-- Profile Menu -->
            <div class="profile-menu" id="profileMenu">
                <div class="profile-toggle">
                    <img src="Avatar.png" alt="User Avatar" class="avatar">
                    <span class="username">Jhon James</span>
                    <i class="fas fa-chevron-down"></i>
                </div>
                <div class="dropdown" id="profileDropdown">
                    <div class="profile-info">
                        <img src="Avatar.png" alt="User Avatar" class="avatar">
                        <div>
                            <strong>Jhon James</strong>
                            <p class="email">jhon@example.com</p>
                        </div>
                    </div>
                    <hr>
                    <a href="profile.php"><i class="fas fa-id-card"></i> View Profile</a>
                    <a href="settings.php"><i class="fas fa-cog"></i> Settings</a>
                    <a href="logout.php" class="logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
                </div>
            </div>
        </div>

        <!-- KPI metric cards -->
        <div class="dashboard-grid">
            <div class="metric-card">
                <div class="title">Capital</div>
                <div class="value">₱<?= number_format($capital, 2) ?></div>
            </div>
            <div class="metric-card">
                <div class="title">Product Sales</div>
                <div class="value">₱<?= number_format($productSales, 2) ?></div>
            </div>
            <div class="metric-card">
                <div class="title">Profit</div>
                <div class="value">₱<?= number_format($profit, 2) ?></div>
            </div>
            <div class="metric-card">
                <div class="title">Capital Loss</div>
                <div class="value">₱<?= number_format($capitalLoss, 2) ?></div>
            </div>
        </div>

        <!-- ======= Branch Analytics Container ======= -->
<div class="branch-analytics-container">
    <!-- Header row -->
    <div class="branch-analytics-header">
        <h1 class="section-title">
            <i data-lucide="activity" class="icon"></i>
            Branch Analytics Overview
        </h1>
        <div class="filters">
            <div class="select-wrapper">
                <select id="branchSelect">
                    <option>All Branches</option>
                    <option>CTA Zandueta</option>
                    <option>DM Foodmart</option>
                    <option>CTA Camp 7</option>
                    <option>BGH - OPD</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Branch Cards -->
<div class="branch-cards">
    <?php foreach ($branchAnalytics as $branchName => $data): 
        // Ensure values exist and are numeric
        $sales = isset($data['sales']) ? (float)$data['sales'] : 0;
        $profit = isset($data['profit']) ? (float)$data['profit'] : 0;

        // Dynamic dot logic:
        // - negative profit => red
        // - high sales => green (>= 350k)
        // - medium sales => yellow (>= 250k)
        // - otherwise => orange
        if ($profit < 0) {
            $dotClass = 'red';
        } elseif ($sales >= 350000) {
            $dotClass = 'green';
        } elseif ($sales >= 250000) {
            $dotClass = 'yellow';
        } else {
            $dotClass = 'orange';
        }

        // Profit class for value color
        $profitClass = ($profit < 0) ? 'negative' : 'positive';
    ?>
        <div class="branch-card">
            <div class="branch-card-header">
                <div class="branch-name"><?= htmlspecialchars($branchName) ?></div>
                <div class="branch-color <?= $dotClass ?>" title="<?= htmlspecialchars(ucfirst($dotClass)) ?>"></div>
            </div>

            <div class="branch-stats">
                <div class="stat-row">
                    <span class="stat-label">Sales</span>
                    <span class="stat-value"><?= formatCurrency($sales) ?></span>
                </div>
                <div class="stat-row">
                    <span class="stat-label">Profit</span>
                    <span class="stat-value <?= $profitClass ?>"><?= formatCurrency($profit) ?></span>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>





<!-- Sales Report (separate container) -->
<div class="sales-report-container">
    <div class="chart-header" style="display:flex;justify-content:space-between;align-items:center;">
        <h2 class="section-title"><i data-lucide="calendar" class="icon"></i> Sales Report</h2>
        <div class="select-wrapper">
            <select id="monthSelect" onchange="updateFilters()">
                <?php foreach ($months as $m): ?>
                    <option value="<?= htmlspecialchars($m) ?>" <?= $m == $selectedMonth ? 'selected' : '' ?>>
                        <?= htmlspecialchars($m) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>

    <div class="report-charts">
        <div class="chart-block">
            <h3 class="chart-title">Sales per Month by Branch</h3>
            <div class="chart-container">
                <canvas id="salesChart"></canvas>
            </div>
        </div>

        <div class="chart-block">
            <h3 class="chart-title">Monthly Gross Profit per Branch</h3>
            <div class="chart-container">
                <canvas id="profitChart"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Existing JS files -->
<script src="upload.js"></script>
<script src="Profile.js"></script>

<!-- Chart.js and Lucide icon pack (only once) -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/lucide@0.263.1/dist/lucide.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // lucide icons
    if (window.lucide) lucide.createIcons();

    // Data from PHP
    const monthlySalesData = <?php echo json_encode($monthlySalesData); ?>;
    const monthlyProfitData = <?php echo json_encode($monthlyProfitData); ?>;
    const branchNames = <?php echo json_encode(array_keys($branchAnalytics)); ?>;
    const colors = [
    getComputedStyle(document.documentElement).getPropertyValue('--chart-blue').trim(),
    getComputedStyle(document.documentElement).getPropertyValue('--chart-green').trim(),
    getComputedStyle(document.documentElement).getPropertyValue('--chart-orange').trim(),
    getComputedStyle(document.documentElement).getPropertyValue('--chart-indigo').trim()
];


    // Sales chart (stacked bar per branch over months)
    const salesCanvas = document.getElementById('salesChart');
    if (salesCanvas && window.Chart) {
        const salesCtx = salesCanvas.getContext('2d');
        const salesChart = new Chart(salesCtx, {
            type: 'bar',
            data: {
                labels: monthlySalesData.map(item => item.month),
                datasets: branchNames.map((branch, index) => ({
                    label: branch,
                    data: monthlySalesData.map(item => item[branch] || 0),
                    backgroundColor: colors[index % colors.length],
                    borderRadius: 4
                }))
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: value => '₱' + (value / 1000) + 'K'
                        }
                    }
                },
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: ctx => ctx.dataset.label + ': ₱' + ctx.parsed.y.toLocaleString()
                        }
                    },
                    legend: { position: 'top' }
                }
            }
        });
    }

    // Profit chart (line)
    const profitCanvas = document.getElementById('profitChart');
    if (profitCanvas && window.Chart) {
        const profitCtx = profitCanvas.getContext('2d');
        const profitChart = new Chart(profitCtx, {
            type: 'line',
            data: {
                labels: monthlyProfitData.map(item => item.month),
                datasets: branchNames.map((branch, index) => ({
                    label: branch,
                    data: monthlyProfitData.map(item => item[branch] || 0),
                    borderColor: colors[index % colors.length],
                    backgroundColor: colors[index % colors.length] + '33',
                    tension: 0.35,
                    pointRadius: 4,
                    fill: false
                }))
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: value => '₱' + (value / 1000) + 'K'
                        }
                    }
                },
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: ctx => ctx.dataset.label + ': ₱' + ctx.parsed.y.toLocaleString()
                        }
                    },
                    legend: { position: 'top' }
                }
            }
        });
    }
});

// updateFilters (keeps behavior simple)
function updateFilters() {
    const branch = document.getElementById('branchSelect') ? document.getElementById('branchSelect').value : '';
    const month  = document.getElementById('monthSelect') ? document.getElementById('monthSelect').value : '';
    const url = new URL(window.location.href);
    if (branch) url.searchParams.set('branch', branch);
    if (month) url.searchParams.set('month', month);
    window.location.href = url.toString();
}
</script>

</body>
</html>
