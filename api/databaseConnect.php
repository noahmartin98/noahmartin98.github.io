<?php
$uri = "mysql://avnadmin:AVNS_s9w_D_bs4m3e3bGzvGe@://aivencloud.com";
$fields = parse_url($uri);

$host = $fields["host"];
$port = $fields["port"];
$username = $fields["user"];
$password = $fields["pass"];
$dbname = "football_db"; // Your target database name

// 1. Initialize mysqli
$conn = mysqli_init();

if (!$conn) {
    die("mysqli_init failed");
}

// 2. Set the SSL certificate path (Required by Aiven)
// Replace with the absolute path to your downloaded ca.pem file
$conn->ssl_set(NULL, NULL, "/absolute/path/to/ca.pem", NULL, NULL);

// 3. Connect to the database
$success = $conn->real_connect($host, $username, $password, $dbname, $port, NULL, MYSQLI_CLIENT_SSL);

if (!$success) {
    die("Connection failed: " . mysqli_connect_error());
}

echo "Connected successfully to Aiven MySQL using mysqli!";
?>
