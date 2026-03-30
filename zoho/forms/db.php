<?php
$host = "localhost"; 
$dbname = "ilinksm3_foodtruck";
$username = "ilinksm3_foodtruck";
$password = "IlinkFoodTruck";
$charset = "utf8mb4";

$dsn = "mysql:host=$host;dbname=$dbname;charset=$charset";

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $username, $password, $options);
    echo"db connected success";
  
} catch (PDOException $e) {
    echo "Database connection failed: " . $e->getMessage();
    exit;
}
