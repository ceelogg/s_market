<?php
header('Content-Type: application/json');

$servername = "localhost";
$username = "root";
$password = "";
$database = "s_market";

$conn = mysqli_connect($servername, $username, $password, $database);

$salesChartQuery = "SELECT name, SUM(sales) AS total_sales FROM product GROUP BY name ORDER BY total_sales DESC";
$salesChartResult = mysqli_query($conn, $salesChartQuery);

$data = [
    'labels' => [],
    'sales' => []
];

while ($row = mysqli_fetch_assoc($salesChartResult)) {
    $data['labels'][] = $row['name'];
    $data['sales'][] = $row['total_sales'];
}

mysqli_close($conn);

echo json_encode($data);
?>
