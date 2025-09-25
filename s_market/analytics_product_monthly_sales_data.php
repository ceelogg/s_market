<?php
$servername = "localhost";
$username = "root";
$password = "";
$database = "s_market";
$conn = mysqli_connect($servername, $username, $password, $database);


// Fetch sales data
$salesQuery = "SELECT 
        name AS product_name, 
        sales_month, 
        SUM(sales) AS total_sales
    FROM 
        product
    GROUP BY 
        name, sales_month
    ORDER BY 
        sales_month, total_sales DESC";

$result = mysqli_query($conn, $salesQuery);

$months = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
$salesData = [];
$topProducts = [];

// Process the query results
while ($row = mysqli_fetch_assoc($result)) {
    $productName = $row['product_name'];
    $monthString = $row['sales_month'];
    $monthIndex = (int)date('n', strtotime($monthString)) - 1;
    $totalSales = (int)$row['total_sales'];

    if (!isset($salesData[$productName])) {
        $salesData[$productName] = array_fill(0, 12, 0);
    }

    $salesData[$productName][$monthIndex] = $totalSales;

    if (!isset($topProducts[$monthIndex]) || $topProducts[$monthIndex]['total_sales'] < $totalSales) {
        $topProducts[$monthIndex] = [
            'product_name' => $productName,
            'total_sales' => $totalSales
        ];
    }
}


// Chart Data
$productSales = [];
foreach ($salesData as $productName => $sales) {
    $isTopProduct = in_array($productName, array_column($topProducts, 'product_name'));
    $productSales[] = [
        'label' => $productName,
        'data' => $sales,
        'borderColor' => $isTopProduct ? 'rgba(255, 99, 132, 1)' 
        : 'rgba(75, 192, 192, 1)','rgba(82, 60, 64, 0.7)',
        'backgroundColor' => $isTopProduct ? 'rgba(255, 99, 132, 0.2)' 
        : 'rgba(75, 192, 192, 0.2)',
        'tension' => 0.1,
        'borderWidth' => $isTopProduct ? 3 : 1
    ];
}


mysqli_close($conn);

echo json_encode([
    'months' => $months,
    'productSales' => $productSales
]);
?>
