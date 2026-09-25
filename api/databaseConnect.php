<?php
$servername = "mysql-c4b6f13-sportssim98-2816.g.aivencloud.com";
$username = "avnadmin";
$password = "AVNS_s9w_D_bs4m3e3bGzvGe";
$dbname = "football_db";
$port = 25060; // Aiven's default connection port

// 1. Initialize the mysqli object
$conn = mysqli_init();
if (!$conn) {
    die("mysqli_init failed");
}

// 2. Force SSL encryption (Required by Aiven)
$conn->ssl_set(NULL, NULL, NULL, NULL, NULL);

// 3. Establish the connection using the custom port
$success = $conn->real_connect($servername, $username, $password, $dbname, $port);

// Check connection
if (!$success) {
    die("Connection failed: " . mysqli_connect_error());
}
?>

