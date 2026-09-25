<?php
$uri = "mysql://avnadmin:AVNS_s9w_D_bs4m3e3bGzvGe@mysql-c4b6f13-sportssim98-2816.g.aivencloud.com:24353/defaultdb?ssl-mode=REQUIRED";
$fields = parse_url($uri);

// FIXED: Use the correct, standardized array keys from parse_url()
$dsn = "mysql:";
$dsn .= "host=" . $fields["host"];
$dsn .= ";port=" . $fields["port"];
$dsn .= ";dbname=football_db"; // Your target database name
$dsn .= ";sslmode=verify-ca;sslrootcert=/absolute/path/to/ca.pem";

$username = $fields["user"];
$password = $fields["pass"];

try {
    $pdo = new PDO($dsn, $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "Connected successfully to Aiven MySQL!";
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}
?>
