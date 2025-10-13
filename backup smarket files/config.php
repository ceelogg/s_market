<?php
$servername = "localhost";
$username = "root";
$password = "smarket";
$dbname = "s_market";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>