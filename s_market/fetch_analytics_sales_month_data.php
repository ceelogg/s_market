<?php
$servername = "localhost";
$username = "root";
$password = "";
$database = "s_market";

$conn = mysqli_connect($servername, $username, $password, $database);

$salesChartQuery = "SELECT name, SUM(sales) AS total_sales FROM product GROUP BY name ORDER BY total_sales DESC";
$salesChartResult = mysqli_query($conn, $salesChartQuery);

$productNames = [];
$productSales = [];

while ($row = mysqli_fetch_assoc($salesChartResult)) {
    $productNames[] = $row['name'];
    $productSales[] = $row['total_sales'];
}


mysqli_close($conn);
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>S-Market - Analytics</title>
    <link rel="stylesheet" href="dashboard.css">
    <link rel="stylesheet" href="analyticsnav.css">


</head>
<body>

<div class="container">
    <div class="sidebar">
        <div class="logo">
            <img src="logo.png" alt="S-Market Logo">
            <h2>S-Market</h2>
        </div>
        <ul class="nav-links">
            <li class="nav-item"><i class="fas fa-home"></i> Dashboard</li>
            <li class="nav-item"><a href="productnav.php"><i class="fas fa-box"></i> Products</a></li>
            <li class="nav-item active"><i class="fas fa-chart-bar"></i> Analytics</li>
            <li class="nav-item"><i class="fas fa-lightbulb"></i> AI Recommendations</li>
            <li class="nav-item"><i class="fas fa-bullhorn"></i> Marketing</li>
            <li class="nav-item"><i class="fas fa-cog"></i> Settings</li>
        </ul>
    </div>



    <!-- Doughnut Chart -->
    <div class="chart-container">
        <h2 class="chart-title">Top Selling Products (Doughnut Chart)</h2>
        <canvas id="salesDoughnutChart"></canvas>
    </div>

    

</div>
<!-- Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script src="analytics.js"></script>


<script src="scripts.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

</body>
</html>
