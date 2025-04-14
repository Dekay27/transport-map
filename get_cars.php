<?php
// get_cars.php

$host = "localhost";
$user = "root";
$pass = "admin12345";
$dbname = "transport"; // <-- change this

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die(json_encode(['error' => 'Database connection failed']));
}

$sql = "SELECT * FROM cars";
$result = $conn->query($sql);

$cars = [];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $cars[] = $row;
    }
}

header('Content-Type: application/json');
echo json_encode($cars);

$conn->close();
?>