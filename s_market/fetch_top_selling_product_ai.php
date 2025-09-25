<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "s_market";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch all products (remove the sales >= 10 condition)
$sql = "SELECT name, sales FROM product ORDER BY sales DESC LIMIT 10";
$result = $conn->query($sql);

$products = [];
$sales = [];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $products[] = $row['name'];
        $sales[] = (int)$row['sales'];
    }
} else {
    $products = [];
    $sales = [];
}

$conn->close();

echo json_encode(['products' => $products, 'sales' => $sales]);
?>
