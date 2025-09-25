<?php
$servername = "localhost";
$username = "root";
$password = "";
$database = "s_market";

$conn = mysqli_connect($servername, $username, $password, $database);

// Query to calculate total sales and profit for each product
$salesChartQuery = "SELECT name, SUM(sales) AS total_sales, SUM((retail_price * sales) - (original_price * sales)) AS total_profit 
                     FROM product GROUP BY name 
                     ORDER BY total_sales DESC";

$salesChartResult = mysqli_query($conn, $salesChartQuery);


$productNames = [];
$productSales = [];
$productProfits = [];

while ($row = mysqli_fetch_assoc($salesChartResult)) {
    $productNames[] = $row['name'];
    $productSales[] = $row['total_sales'];
    $productProfits[] = $row['total_profit'];
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
    <link rel="stylesheet" href="analyticscss.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

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
            <li class="nav-item active"><i class="fas fa-chart-bar"></i> Analytics</li>
            <li class="nav-item"><a href="AiRecnav.php"><i class="fas fa-lightbulb"></i> AI Recommendations</a></li>
            <li class="nav-item"><i class="fas fa-bullhorn"></i> Marketing</li>
            <li class="nav-item"><i class="fas fa-cog"></i> Settings</li>
        </ul>
    </div>



    <!--Chart -->
    <div class="chart-row">

    <div class="chart-container">
        <h2 class="chart-title">Top Selling Products</h2>
        <canvas id="salesDoughnutChart"></canvas>
    </div>
    

     <div class="chart-container">
      <h2 class="chart-title">Product Sales Revenue</h2>
      <canvas id="revenueBarChart"></canvas>
    </div>
    
    <div class="chart-container">
  <h2 class="chart-title">Product Profit (₱)</h2>
  <canvas id="profitBarChart"></canvas>
</div>

<div class="chart-container">
    <h2 class="chart-title">Monthly Sales Trends</h2>
        <canvas id="monthlySalesLineChart"></canvas>
    </div>
</div>



</div>

<script src="analytics.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

</body>
</html>
