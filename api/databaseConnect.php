<?php
$servername = "://aivencloud.com";
$username = "avnadmin";
$password = "AVNS_s9w_D_bs4m3e3bGzvGe";
$dbname = "football_db";
$port = 25060;

// Initialize mysqli
$conn = mysqli_init();
if (!$conn) {
    die("mysqli_init failed");
}

// Enable SSL encryption (Required by Aiven)
$conn->ssl_set(NULL, NULL, NULL, NULL, NULL);

// Connect over the precise Aiven host & port
$success = $conn->real_connect($servername, $username, $password, $dbname, $port);

if (!$success) {
    die("Connection failed: " . mysqli_connect_error());
}
?>
