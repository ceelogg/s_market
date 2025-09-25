<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "s_market";

$conn = new mysqli($servername, $username, $password, $dbname);



// Get total products
$total_products_query = "SELECT COUNT(*) AS total_products FROM product";
$total_products_result = $conn->query($total_products_query);
$total_products_row = $total_products_result->fetch_assoc();
$total_products = $total_products_row['total_products'] ?? 0;

// Get product count per category
$category_query = "SELECT category, COUNT(*) AS product_count FROM product GROUP BY category";
$category_result = $conn->query($category_query);


// Query to get total sales (sum of all sales)
$total_sales_query = "SELECT SUM(sales) AS total_sales FROM product";
$total_sales_result = $conn->query($total_sales_query);
$total_sales_row = $total_sales_result->fetch_assoc();
$total_sales = $total_sales_row['total_sales'];

// Query to get total revenue (sum of retail price * quantity for each product)
$productSalesQuery = "SELECT SUM(retail_price * sales) AS total_sales FROM product";
$productSalesResult = $conn->query($productSalesQuery);

if ($productSalesResult) {
    $productSalesRow = $productSalesResult->fetch_assoc();
    $productSales = isset($productSalesRow['total_sales']) ? (float)$productSalesRow['total_sales'] : 0;
} else {
    echo "Error: " . $conn->error;
    $productSales = 0;
}

$conn->close();
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>S-Market - Analytics</title>
    <link rel="stylesheet" href="dashboard.css">
    <link rel="stylesheet" href="newairecnav.css">

</head>
<body>

<div class="container">
    <div class="sidebar">
        <div class="logo">
            <img src="logo.png" alt="S-Market Logo">
            <h2>S-Market</h2>
        </div>
        <ul class="nav-links">
            <li class="nav-item"><a href="userpage.php"><i class="fas fa-home"></i> Dashboard</a></li>
            <li class="nav-item"><a href="productnav.php"><i class="fas fa-box"></i> Products</a></li>
            <li class="nav-item"><a href="analyticsnav.php"><i class="fas fa-chart-bar"></i> Analytics</a></li>
            <li class="nav-item active"><i class="fas fa-lightbulb"></i> AI Recommendations</li>
            <li class="nav-item"><i class="fas fa-bullhorn"></i> Marketing</li>
            <li class="nav-item"><i class="fas fa-cog"></i> Settings</li>
        </ul>
    </div>

    <div class="main-content">
        <div class="dashboard-header">
            <h1 class="page-title">

            </h1>
            <div class="time-range">
                <select class="btn btn-outline">
                    <option>Last Month</option>
                    <option selected>This Month</option>
                    <option >Last 90 days</option>
                    <option>This year</option>
                </select>
            </div>
        </div>

        <div class="metrics-grid">
        <!-- Total Products -->
        <div class="metric-card">
            <div class="metric-title"><i class="fas fa-box-open"></i> Total Products</div>
            <div class="metric-value"><?php echo number_format($total_products); ?></div>
            <div class="metric-change positive"><i class="fas fa-arrow-up"></i> 12% from last period</div>
        </div>

        <!-- Total Sales -->
        <div class="metric-card">
            <div class="metric-title"><i class="fas fa-shopping-cart"></i> Total Product Sales</div>
            <div class="metric-value"><?php echo number_format($total_sales); ?></div>
            <div class="metric-change positive"><i class="fas fa-arrow-up"></i> 8% from last period</div>
        </div>

        <!-- Total Revenue -->
        <div class="metric-card">
            <div class="metric-title"><i class="fas fa-money"></i> Total Revenue</div>
            <div class="metric-value">₱<?php echo number_format($productSales, 2); ?></div>
            <div class="metric-change positive"><i class="fas fa-arrow-up"></i> 8% from last period</div>
        </div>
    </div>


        <div class="dashboard-section">
            <div class="section-header">
                <h2 class="section-title">Sales by Category</h2>
                <div class="section-actions">
                    <button class="btn btn-outline"><i class="fas fa-download"></i> Export</button>
                    <button class="btn btn-primary"><i class="fas fa-filter"></i> Filters</button>
                </div>
            </div>
            <div class="chart-container">
                <canvas id="salesByCategoryChart"></canvas>
            </div>
        </div>

        <div class="dashboard-section">
            <div class="section-header">
                <h2 class="section-title">Top Performing Products</h2>
                <div class="section-actions">
                    <select class="btn btn-outline">
                        <option>This month</option>
                        <option selected>Last 3 months</option>
                        <option>This year</option>
                    </select>
                </div>
            </div>
            <div class="chart-container">
                <canvas id="topProductsChart"></canvas>
            </div>
        </div>

        <div class="dashboard-section">
            <div class="section-header">
                <h2 class="section-title">Recent Recommendations</h2>
                <div class="section-actions">
                    <button class="btn btn-outline"><i class="fas fa-sync-alt"></i> Refresh</button>
                </div>
            </div>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Category</th>
                        <th>Recommendation</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Laptop</td>
                        <td>Electronics</td>
                        <td>Increase stock by 30% (high demand)</td>
                        <td><span class="product-badge badge-success">Pending</span></td>
                    </tr>
                   
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="AiRecnav.js"></script>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">


</body>
</html>