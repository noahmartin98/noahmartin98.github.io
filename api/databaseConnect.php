<?php
$servername = "://aivencloud.com";
$username = "avnadmin";
$password = "AVNS_s9w_D_bs4m3e3bGzvGe";
$dbname = "football_db";
$port = 25060;

// FIX: Force resolve the hostname to an IPv4 address to prevent Vercel from timing out
$resolved_ip = gethostbyname($servername);

// 1. Initialize the mysqli object
$conn = mysqli_init();
if (!$conn) {
    die("mysqli_init failed");
}

// 2. Force SSL encryption (Required by Aiven)
$conn->ssl_set(NULL, NULL, NULL, NULL, NULL);

// 3. Establish the connection using the IPv4 address instead of the long text hostname
$success = $conn->real_connect($resolved_ip, $username, $password, $dbname, $port);

// Check connection
if (!$success) {
    die("Connection failed: " . mysqli_connect_error());
}
?>
