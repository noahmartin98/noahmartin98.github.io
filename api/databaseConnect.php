<?php

$host = "mysql-c4b6f13-sportssim98-2816.g.aivencloud.com";
$port = 24353;
$username = "avnadmin";
$password = "AVNS_s9w_D_bs4m3e3bGzvGe";
$dbname = "football_db";

// 1. Initialize mysqli
$conn = mysqli_init();

if (!$conn) {
    die("mysqli_init failed");
}

// 2. FIXED: Dynamically point to the ca.pem file inside your api/ directory
$cert_path = __DIR__ . '/ca.pem';

if (!file_exists($cert_path)) {
    die("SSL Certificate Error: The file ca.pem was not found at: " . $cert_path);
}

$conn->ssl_set(NULL, NULL, $cert_path, NULL, NULL);

// 3. Connect to the database
$success = $conn->real_connect($host, $username, $password, $dbname, $port, NULL, MYSQLI_CLIENT_SSL);

if (!$success) {
    die("Connection failed: " . mysqli_connect_error());
}

echo "Connected successfully to Aiven MySQL using mysqli!";
?>
