<?php
header('Content-Type: application/json');
$conn = new mysqli("localhost","root","","s_market");
if($conn->connect_error) exit(json_encode(['error'=>'DB connection failed']));


$sql = "SELECT name, SUM(sales * retail_price) AS revenue 
        FROM product 
        GROUP BY name 
        ORDER BY revenue DESC";
$res = $conn->query($sql);

$data = ['labels'=>[], 'revenue'=>[]];
while($row = $res->fetch_assoc()){
  $data['labels'][]  = $row['name'];
  $data['revenue'][] = (float)$row['revenue'];
}

echo json_encode($data);
$conn->close();
