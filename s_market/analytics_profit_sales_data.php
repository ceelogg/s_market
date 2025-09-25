<?php
$servername = "localhost";
$username = "root";
$password = "";
$database = "s_market";

$conn = mysqli_connect($servername, $username, $password, $database);

$profitQuery = "SELECT name, SUM((retail_price * sales) - (original_price * sales)) AS total_profit 
                FROM product GROUP BY name 
                ORDER BY total_profit DESC";

$result = mysqli_query($conn, $profitQuery);

$labels = [];
$profits = [];

while ($row = mysqli_fetch_assoc($result)) {
    $labels[] = $row['name'];
    $profits[] = (float)$row['total_profit'];
}

echo json_encode([
    'labels' => $labels,
    'profit' => $profits
]);

mysqli_close($conn);
?>
