<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "s_market";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// SQL query to get total sales per category
$sql = "SELECT category, SUM(sales) AS total_sales FROM product GROUP BY category";
$result = $conn->query($sql);

$categories = [];
$sales = [];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $categories[] = $row['category'];
        $sales[] = $row['total_sales'];
    }
} else {
    echo json_encode(["error" => "No data found"]);
    exit();
}

$conn->close();

echo json_encode([
    "categories" => $categories,
    "sales" => $sales
]);
?>
