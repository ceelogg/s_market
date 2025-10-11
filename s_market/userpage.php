<?php
session_start();

/* =========================================================
   DB CONNECTION
   ========================================================= */
$servername = "localhost";
$username   = "root";
$password   = "smarket";
$database   = "s_market";

$conn = mysqli_connect($servername, $username, $password, $database);
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

/* =========================================================
   SETTINGS
   ========================================================= */
/**
 * Default margin if branch not matched below (e.g., 0.12 = 12%)
 */
$DEFAULT_MARGIN = 0.12;

/* =========================================================
   HELPERS
   ========================================================= */
function formatCurrency($amount) {
    return '₱' . number_format((float)$amount, 0, '.', ',');
}

function hasUnitCost(mysqli $conn): bool {
    $sql = "SELECT 1
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = 'product'
              AND COLUMN_NAME = 'unit_cost'
            LIMIT 1";
    $res = mysqli_query($conn, $sql);
    return $res && mysqli_num_rows($res) > 0;
}

/** PHP helper: get branch margin (matches your DAX SWITCH) */
function getBranchMargin(string $branch, float $default = 0.12): float {
    if ($branch === "DM Foodmart")  return 0.15;
    if ($branch === "CTA Zandueta") return 0.12;
    return $default;
}

/** SQL helper: CASE expression for margins (matches your DAX SWITCH) */
function branchMarginSQLCase(float $default = 0.12): string {
    // IMPORTANT: Keep branch names exactly as in the DB
    return "CASE
                WHEN branch = 'DM Foodmart'  THEN 0.15
                WHEN branch = 'CTA Zandueta' THEN 0.12
                ELSE $default
            END";
}

function monthLabel(string $ym): string {
    // $ym = '2025-08' -> 'August 2025'
    $dt = DateTime::createFromFormat('Y-m', $ym);
    return $dt ? $dt->format('F Y') : $ym;
}

/* =========================================================
   GET LATEST TWO MONTHS WITH DATA
   ========================================================= */
$monthsSql = "
    SELECT DATE_FORMAT(date_of_sale, '%Y-%m') AS ym
    FROM product
    GROUP BY ym
    ORDER BY ym DESC
    LIMIT 2
";
$monthsRes = mysqli_query($conn, $monthsSql);
$ymList = [];
while ($r = mysqli_fetch_assoc($monthsRes)) {
    $ymList[] = $r['ym'];
}
$latestYM = $ymList[0] ?? null;
$prevYM   = $ymList[1] ?? null;

$latestMonthLabel = $latestYM ? monthLabel($latestYM) : 'Latest Month';
$prevMonthLabel   = $prevYM   ? monthLabel($prevYM)   : 'Previous Month';

/* =========================================================
   BRANCH LIST (distinct)
   ========================================================= */
$branches = [];
$branchRes = mysqli_query($conn, "SELECT DISTINCT branch FROM product ORDER BY branch");
while ($b = mysqli_fetch_assoc($branchRes)) {
    $branches[] = $b['branch'];
}
if (empty($branches)) {
    // fallback if table is empty
    $branches = ['CTA Zandueta', 'DM Foodmart', 'CTA Camp 7', 'BGH - OPD'];
}

/* =========================================================
   PROFIT MODE (with or without unit_cost)
   ========================================================= */
$useRealProfit = hasUnitCost($conn);

/* =========================================================
   AGGREGATION HELPERS
   ========================================================= */
function revenueByMonth(mysqli $conn, string $ym): float {
    $sql = "SELECT COALESCE(SUM(total_sales),0) AS revenue
            FROM product
            WHERE DATE_FORMAT(date_of_sale,'%Y-%m') = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, 's', $ym);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($res);
    return (float)($row['revenue'] ?? 0);
}

function profitByMonth(mysqli $conn, string $ym, bool $useRealProfit, float $defaultMargin): float {
    if ($useRealProfit) {
        // Real profit: SUM((unit_price - unit_cost) * quantity_sold)
        $sql = "SELECT COALESCE(SUM((unit_price - unit_cost) * quantity_sold),0) AS profit
                FROM product
                WHERE DATE_FORMAT(date_of_sale,'%Y-%m') = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, 's', $ym);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($res);
        return (float)($row['profit'] ?? 0);
    } else {
        // Estimated profit using branch-specific margins (Power BI logic)
        $case = branchMarginSQLCase($defaultMargin);
        $sql = "SELECT COALESCE(SUM(total_sales * $case), 0) AS profit
                FROM product
                WHERE DATE_FORMAT(date_of_sale,'%Y-%m') = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, 's', $ym);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($res);
        return (float)($row['profit'] ?? 0);
    }
}

function branchSalesProfitForMonth(mysqli $conn, string $ym, array $branches, bool $useRealProfit, float $defaultMargin): array {
    $data = [];
    // Sales per branch
    $salesSql = "
        SELECT branch, COALESCE(SUM(total_sales),0) AS sales
        FROM product
        WHERE DATE_FORMAT(date_of_sale,'%Y-%m') = ?
        GROUP BY branch
    ";
    $stmt = mysqli_prepare($conn, $salesSql);
    mysqli_stmt_bind_param($stmt, 's', $ym);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $salesMap = [];
    while ($row = mysqli_fetch_assoc($res)) {
        $salesMap[$row['branch']] = (float)$row['sales'];
    }

    // Profit per branch
    if ($useRealProfit) {
        $profitSql = "
            SELECT branch, COALESCE(SUM((unit_price - unit_cost) * quantity_sold),0) AS profit
            FROM product
            WHERE DATE_FORMAT(date_of_sale,'%Y-%m') = ?
            GROUP BY branch
        ";
        $stmt2 = mysqli_prepare($conn, $profitSql);
        mysqli_stmt_bind_param($stmt2, 's', $ym);
        mysqli_stmt_execute($stmt2);
        $res2 = mysqli_stmt_get_result($stmt2);
        $profitMap = [];
        while ($row = mysqli_fetch_assoc($res2)) {
            $profitMap[$row['branch']] = (float)$row['profit'];
        }
    } else {
        // Estimated profit by branch-specific margin (Sales * Margin(branch))
        $profitMap = [];
        foreach ($salesMap as $br => $s) {
            $profitMap[$br] = $s * getBranchMargin($br, $defaultMargin);
        }
    }

    foreach ($branches as $br) {
        $data[$br] = [
            'sales'  => $salesMap[$br]  ?? 0,
            'profit' => $profitMap[$br] ?? 0,
        ];
    }
    return $data;
}

/* =========================================================
   KPI CALCULATIONS
   ========================================================= */
$latestRevenue   = $latestYM ? revenueByMonth($conn, $latestYM) : 0;
$prevRevenue     = $prevYM   ? revenueByMonth($conn, $prevYM)   : 0;

$latestProfit    = $latestYM ? profitByMonth($conn, $latestYM, $useRealProfit, $DEFAULT_MARGIN) : 0;
$prevProfit      = $prevYM   ? profitByMonth($conn, $prevYM,   $useRealProfit, $DEFAULT_MARGIN) : 0;

$revenueChange   = ($prevRevenue > 0) ? (($latestRevenue - $prevRevenue) / $prevRevenue) * 100 : 0;
$profitChange    = ($prevProfit   > 0) ? (($latestProfit  - $prevProfit ) / $prevProfit ) * 100 : 0;

$revenueTrendClass = $revenueChange >= 0 ? 'positive' : 'negative';
$revenueTrendIcon  = $revenueChange >= 0 ? 'fa-arrow-up' : 'fa-arrow-down';

$profitTrendClass  = $profitChange  >= 0 ? 'positive' : 'negative';
$profitTrendIcon   = $profitChange  >= 0 ? 'fa-arrow-up' : 'fa-arrow-down';

/* Profit margin for latest month */
$avgProfitMargin = ($latestRevenue > 0) ? ($latestProfit / $latestRevenue) * 100 : 0;

/* =========================================================
   BRANCH ANALYTICS (latest month) + growth vs previous month
   ========================================================= */
$latestBranchData = $latestYM ? branchSalesProfitForMonth($conn, $latestYM, $branches, $useRealProfit, $DEFAULT_MARGIN) : [];
$prevBranchData   = $prevYM   ? branchSalesProfitForMonth($conn, $prevYM,   $branches, $useRealProfit, $DEFAULT_MARGIN) : [];

$branchAnalytics = [];
foreach ($branches as $br) {
    $salesLatest = $latestBranchData[$br]['sales']  ?? 0;
    $profitLatest= $latestBranchData[$br]['profit'] ?? 0;
    $salesPrev   = $prevBranchData[$br]['sales']    ?? 0;

    $growth = ($salesPrev > 0) ? (($salesLatest - $salesPrev) / $salesPrev) * 100 : 0;

    $branchAnalytics[$br] = [
        'sales'     => $salesLatest,
        'profit'    => $profitLatest,
        'customers' => 0,           // not in table
        'growth'    => $growth
    ];
}

/* Top branch by sales (latest month) */
$topBranch = '';
$topSales  = 0;
foreach ($branchAnalytics as $branch => $data) {
    if ($data['sales'] > $topSales) {
        $topSales  = $data['sales'];
        $topBranch = $branch;
    }
}

/* =========================================================
   CHART DATA: last 6 months by branch (sales & profit)
   ========================================================= */
$chartSql = "
    SELECT
      DATE_FORMAT(date_of_sale, '%Y-%m') AS ym,
      DATE_FORMAT(date_of_sale, '%b')    AS mon,
      branch,
      SUM(total_sales)                   AS sales
    FROM product
    WHERE date_of_sale >= DATE_SUB((SELECT COALESCE(MAX(date_of_sale), CURDATE()) FROM product), INTERVAL 5 MONTH)
    GROUP BY ym, mon, branch
    ORDER BY ym ASC, branch ASC
";
$chartRes = mysqli_query($conn, $chartSql);

/* Build month order and branch-wise sums */
$monthOrder = []; // ['2025-03' => 'Mar', ...]
$salesByMonthBranch = []; // ['2025-03' => ['CTA Zandueta'=>123,...], ...]
while ($row = mysqli_fetch_assoc($chartRes)) {
    $ym  = $row['ym'];
    $mon = $row['mon'];
    $br  = $row['branch'];
    $sales = (float)$row['sales'];

    if (!isset($monthOrder[$ym])) $monthOrder[$ym] = $mon;
    if (!isset($salesByMonthBranch[$ym])) $salesByMonthBranch[$ym] = [];
    $salesByMonthBranch[$ym][$br] = $sales;
}

/* Profit-by-month-branch (using real or estimated) */
$profitByMonthBranch = [];
if ($useRealProfit) {
    $profitChartSql = "
        SELECT
          DATE_FORMAT(date_of_sale, '%Y-%m') AS ym,
          DATE_FORMAT(date_of_sale, '%b')    AS mon,
          branch,
          SUM((unit_price - unit_cost) * quantity_sold) AS profit
        FROM product
        WHERE date_of_sale >= DATE_SUB((SELECT COALESCE(MAX(date_of_sale), CURDATE()) FROM product), INTERVAL 5 MONTH)
        GROUP BY ym, mon, branch
        ORDER BY ym ASC, branch ASC
    ";
    $pRes = mysqli_query($conn, $profitChartSql);
    while ($row = mysqli_fetch_assoc($pRes)) {
        $ym  = $row['ym'];
        $br  = $row['branch'];
        $profit = (float)$row['profit'];
        if (!isset($profitByMonthBranch[$ym])) $profitByMonthBranch[$ym] = [];
        $profitByMonthBranch[$ym][$br] = $profit;
    }
} else {
    // Estimate profit per branch using same branch-specific margins
    foreach ($salesByMonthBranch as $ym => $brMap) {
        $profitByMonthBranch[$ym] = [];
        foreach ($brMap as $br => $s) {
            $profitByMonthBranch[$ym][$br] = $s * getBranchMargin($br, $DEFAULT_MARGIN);
        }
    }
}

/* Normalize to arrays for JS (labels are short month names) */
$monthlySalesData  = []; // [{month:'Mar', 'BranchA': 123, ...}, ...]
$monthlyProfitData = [];

foreach ($monthOrder as $ym => $mon) {
    $rowS = ['month' => $mon];
    $rowP = ['month' => $mon];
    foreach ($branches as $br) {
        $rowS[$br] = isset($salesByMonthBranch[$ym][$br])  ? (float)$salesByMonthBranch[$ym][$br]  : 0;
        $rowP[$br] = isset($profitByMonthBranch[$ym][$br]) ? (float)$profitByMonthBranch[$ym][$br] : 0;
    }
    $monthlySalesData[]  = $rowS;
    $monthlyProfitData[] = $rowP;
}

/* Also build month dropdown (last 6 months, newest first) */
$months = array_map(function($ym) {
    return monthLabel($ym);
}, array_reverse(array_keys($monthOrder)));

$selectedMonth = $months[ count($months)-1 ] ?? ($latestMonthLabel ?? ''); // default to latest label

/* Totals across all branches (latest month) for KPI cards */
$totalRevenue = $latestRevenue;
$totalProfit  = $latestProfit;

mysqli_close($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>S-Market - AI Marketing Decision Modeling System</title>

    <!-- Your styles -->
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
                    <div class="kpi-title">Total Revenue (<?= htmlspecialchars($latestMonthLabel) ?>)</div>
                    <div class="kpi-icon revenue">
                        <i class="fas fa-money-bill-wave"></i>
                    </div>
                </div>
                <div class="kpi-value"><?= formatCurrency($totalRevenue) ?></div>
                <div class="kpi-trend <?= $revenueTrendClass ?>">
                    <i class="fas <?= $revenueTrendIcon ?>"></i>
                    <?= sprintf("%+.1f", $revenueChange) ?>% vs <?= htmlspecialchars($prevMonthLabel) ?>
                </div>
                <div class="kpi-subtitle">Across all branches</div>
            </div>

            <!-- Total Profit Card -->
            <div class="kpi-card profit">
                <div class="kpi-header">
                    <div class="kpi-title">Total Profit (<?= htmlspecialchars($latestMonthLabel) ?>)</div>
                    <div class="kpi-icon profit">
                        <i class="fas fa-chart-pie"></i>
                    </div>
                </div>
                <div class="kpi-value"><?= formatCurrency($totalProfit) ?></div>
                <div class="kpi-trend <?= $profitTrendClass ?>">
                    <i class="fas <?= $profitTrendIcon ?>"></i>
                    <?= sprintf("%+.1f", $profitChange) ?>% vs <?= htmlspecialchars($prevMonthLabel) ?>
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
                <div class="kpi-trend <?= ($avgProfitMargin >= 0 ? 'positive':'negative') ?>">
                    <i class="fas <?= ($avgProfitMargin >= 0 ? 'fa-arrow-up':'fa-arrow-down') ?>"></i>
                    <?= ($avgProfitMargin >= 0 ? 'Up from last month' : 'Down from last month') ?>
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
                <div class="kpi-value"><?= htmlspecialchars($topBranch ?: '—') ?></div>
                <div class="kpi-trend positive">
                    <i class="fas fa-arrow-up"></i>
                    +<?= ($topSales > 0 && $latestRevenue>0) ? number_format(($topSales/$latestRevenue)*100,1) : '0.0' ?>% of revenue
                </div>
                <div class="kpi-subtitle"><?= formatCurrency($topSales) ?> sales (<?= htmlspecialchars($latestMonthLabel) ?>)</div>
            </div>
        </section>

        <!-- Branch Analytics Container -->
        <section class="branch-analytics-container">
            <div class="branch-analytics-header">
                <h2 class="section-title">
                    <i class="fas fa-chart-line icon"></i>
                    Branch Analytics Overview — <?= htmlspecialchars($latestMonthLabel) ?>
                </h2>
            </div>

            <!-- Branch Cards -->
            <div class="branch-cards">
                <?php foreach ($branchAnalytics as $branchName => $data):
                    $sales  = (float)($data['sales']  ?? 0);
                    $profit = (float)($data['profit'] ?? 0);
                    $growth = (float)($data['growth'] ?? 0);

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
                                <span class="stat-value <?= $growth >= 0 ? 'positive' : 'negative' ?>">
                                    <?= number_format($growth, 1) ?>%
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
                    <h3 class="chart-title">Monthly <?= $useRealProfit ? 'Gross Profit' : 'Estimated Profit' ?> per Branch</h3>
                    <div class="chart-container">
                        <canvas id="profitChart"></canvas>
                    </div>
                </div>
            </div>
        </section>
    </main>
</div>

<script src="Profile.js"></script>
<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Data from PHP
    const monthlySalesData  = <?php echo json_encode(array_values($monthlySalesData)); ?>;
    const monthlyProfitData = <?php echo json_encode(array_values($monthlyProfitData)); ?>;
    const branchNames       = <?php echo json_encode(array_values($branches)); ?>;

    // Color palette
    const colors = ['#3498db', '#2ecc71', '#e67e22', '#9b59b6', '#16a085', '#e84393', '#d35400', '#34495e'];

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
                            label: ctx => ctx.dataset.label + ': ₱' + Number(ctx.parsed.y).toLocaleString()
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
                            label: ctx => ctx.dataset.label + ': ₱' + Number(ctx.parsed.y).toLocaleString()
                        }
                    },
                    legend: { position: 'top' }
                }
            }
        });
    }

    // KPI hover effects
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
