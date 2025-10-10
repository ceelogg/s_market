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
$capitalQuery = "SELECT SUM(quantity_sold * unit_price) AS total_capital FROM product";
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
    $sold = intval($_POST['quantity_sold']);
    $unitPrice = floatval($_POST['unit_price']);
    $totalSales = floatval($_POST['total_sales']);
    $dateOfSale = mysqli_real_escape_string($conn, $_POST['date_of_sale']);
    $monthOfSale = mysqli_real_escape_string($conn, $_POST['month_of_sale']);

    // FIXED: Corrected variable name from $quantity to $sold
    $sql = "INSERT INTO product 
        (branch, product_type, product_name, quantity_sold, unit_price, total_sales, date_of_sale, month_of_sale) 
        VALUES 
        ('$branch', '$productType', '$productName', $sold, $unitPrice, $totalSales, '$dateOfSale', '$monthOfSale')";

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
$selectedMonth  = isset($_GET['month']) ? $_GET['month'] : 'August 2025';

// Helper
function formatCurrency($amount) {
    return '₱' . number_format((float)$amount, 0, '.', ',');
}

// Calculate new KPI metrics
$totalRevenue = array_sum(array_column($branchAnalytics, 'sales'));
$totalProfit = array_sum(array_column($branchAnalytics, 'profit'));
$avgProfitMargin = $totalRevenue > 0 ? ($totalProfit / $totalRevenue) * 100 : 0;

// Find top performing branch
$topBranch = '';
$topSales = 0;
foreach ($branchAnalytics as $branch => $data) {
    if ($data['sales'] > $topSales) {
        $topSales = $data['sales'];
        $topBranch = $branch;
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>S-Market - AI Marketing Decision Modeling System</title>

    <!-- Your existing styles -->
    <link rel="stylesheet" href="newdashboard.css">
    <link rel="stylesheet" href="Profile.css">
    <link rel="stylesheet" href="upload.css">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>

<div class="container">
    <!-- Sidebar Navigation -->
    <nav class="sidebar">
        <div class="logo">
            <img src="logo.png" alt="S-Market Logo">
            <h2>S-Market</h2>
        </div>
        <ul class="nav-links">
            <li class="nav-item active"><a href="#"><i class="fas fa-home"></i> Dashboard</a></li>
            <li class="nav-item"><a href="productnav.php"><i class="fas fa-box"></i> Products</a></li>
            <li class="nav-item"><a href="analyticsnav.php"><i class="fas fa-chart-bar"></i> Analytics</a></li>
            <li class="nav-item"><a href="AiRecnav.php"><i class="fas fa-lightbulb"></i> AI Recommendations</a></li>
            <li class="nav-item"><a href="#"><i class="fas fa-bullhorn"></i> Marketing</a></li>
            <li class="nav-item"><a href="#"><i class="fas fa-cog"></i> Settings</a></li>
        </ul>
    </nav>

    <!-- Main Content -->
    <main class="main-content">
        <!-- Header -->
        <header class="header">
            <div class="search-bar">
                <i class="fas fa-search"></i>
                <input type="text" placeholder="Search products, reports, or analytics...">
            </div>

            <!-- Profile Menu -->
            <div class="profile-menu">
                <div class="profile-toggle">
                    <img src="Avatar.png" alt="User Avatar" class="avatar">
                    <span class="username">Jhon James</span>
                    <i class="fas fa-chevron-down"></i>
                </div>
                <div class="dropdown">
                    <div class="profile-info">
                        <img src="Avatar.png" alt="User Avatar" class="avatar">
                        <div>
                            <strong>Jhon James</strong>
                            <span class="email">jhon@example.com</span>
                        </div>
                    </div>
                    <hr>
                    <a href="profile.php"><i class="fas fa-id-card"></i> View Profile</a>
                    <a href="settings.php"><i class="fas fa-cog"></i> Settings</a>
                    <a href="logout.php" class="logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
                </div>
            </div>
        </header>

        <!-- KPI Dashboard -->
        <section class="kpi-dashboard">
            <!-- Total Revenue Card -->
            <div class="kpi-card revenue">
                <div class="kpi-header">
                    <div class="kpi-title">Total Revenue</div>
                    <div class="kpi-icon revenue">
                        <i class="fas fa-money-bill-wave"></i>
                    </div>
                </div>
                <div class="kpi-value"><?= formatCurrency($totalRevenue) ?></div>
                <div class="kpi-trend positive">
                    <i class="fas fa-arrow-up"></i>
                    +12.5% vs last month
                </div>
                <div class="kpi-subtitle">Across all branches</div>
            </div>

            <!-- Total Profit Card -->
            <div class="kpi-card profit">
                <div class="kpi-header">
                    <div class="kpi-title">Total Profit</div>
                    <div class="kpi-icon profit">
                        <i class="fas fa-chart-pie"></i>
                    </div>
                </div>
                <div class="kpi-value"><?= formatCurrency($totalProfit) ?></div>
                <div class="kpi-trend positive">
                    <i class="fas fa-arrow-up"></i>
                    +8.3% vs last month
                </div>
                <div class="kpi-subtitle"><?= number_format($avgProfitMargin, 1) ?>% profit margin</div>
            </div>

            <!-- Average Profit Margin Card -->
            <div class="kpi-card margin">
                <div class="kpi-header">
                    <div class="kpi-title">Avg. Profit Margin</div>
                    <div class="kpi-icon margin">
                        <i class="fas fa-percentage"></i>
                    </div>
                </div>
                <div class="kpi-value"><?= number_format($avgProfitMargin, 1) ?>%</div>
                <div class="kpi-trend negative">
                    <i class="fas fa-arrow-down"></i>
                    -2.1% vs last month
                </div>
                <div class="kpi-subtitle">Industry avg: 38.5%</div>
            </div>

            <!-- Top Performing Branch Card -->
            <div class="kpi-card performance">
                <div class="kpi-header">
                    <div class="kpi-title">Top Performing Branch</div>
                    <div class="kpi-icon performance">
                        <i class="fas fa-trophy"></i>
                    </div>
                </div>
                <div class="kpi-value"><?= $topBranch ?></div>
                <div class="kpi-trend positive">
                    <i class="fas fa-arrow-up"></i>
                    +15% above average
                </div>
                <div class="kpi-subtitle"><?= formatCurrency($topSales) ?> sales this month</div>
            </div>
        </section>

        <!-- Branch Analytics Container -->
        <section class="branch-analytics-container">
            <div class="branch-analytics-header">
                <h2 class="section-title">
                    <i class="fas fa-chart-line icon"></i>
                    Branch Analytics Overview
                </h2>
            </div>

            <!-- Branch Cards -->
            <div class="branch-cards">
                <?php foreach ($branchAnalytics as $branchName => $data): 
                    $sales = isset($data['sales']) ? (float)$data['sales'] : 0;
                    $profit = isset($data['profit']) ? (float)$data['profit'] : 0;

                    // Dynamic dot logic
                    if ($profit < 0) {
                        $dotClass = 'red';
                    } elseif ($sales >= 350000) {
                        $dotClass = 'green';
                    } elseif ($sales >= 250000) {
                        $dotClass = 'yellow';
                    } else {
                        $dotClass = 'orange';
                    }

                    $profitClass = ($profit < 0) ? 'negative' : 'positive';
                ?>
                    <div class="branch-card">
                        <div class="branch-card-header">
                            <div class="branch-name"><?= htmlspecialchars($branchName) ?></div>
                            <div class="branch-color <?= $dotClass ?>"></div>
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
                            <div class="stat-row">
                                <span class="stat-label">Growth</span>
                                <span class="stat-value <?= $data['growth'] >= 0 ? 'positive' : 'negative' ?>">
                                    <?= number_format($data['growth'], 1) ?>%
                                </span>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>

        <!-- Sales Report Container -->
        <section class="sales-report-container">
            <div class="chart-header">
                <h2 class="section-title">
                    <i class="fas fa-chart-bar icon"></i>
                    Sales Report
                </h2>
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
        </section>
    </main>
</div>

<!-- Existing JS files -->
<script src="upload.js"></script>
<script src="Profile.js"></script>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Data from PHP
    const monthlySalesData = <?php echo json_encode($monthlySalesData); ?>;
    const monthlyProfitData = <?php echo json_encode($monthlyProfitData); ?>;
    const branchNames = <?php echo json_encode(array_keys($branchAnalytics)); ?>;
    const colors = ['#3498db', '#2ecc71', '#e67e22', '#9b59b6'];

    // Sales chart
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

    // Profit chart
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

    // Add hover effects to KPI cards
    const kpiCards = document.querySelectorAll('.kpi-card');
    kpiCards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-5px)';
        });
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
        });
    });
});

function updateFilters() {
    const month = document.getElementById('monthSelect').value;
    const url = new URL(window.location.href);
    if (month) url.searchParams.set('month', month);
    window.location.href = url.toString();
}
</script>

</body>
</html>