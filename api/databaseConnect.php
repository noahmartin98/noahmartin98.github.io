<?php
$servername = "://aivencloud.com";
$username = "avnadmin";
$password = "AVNS_s9w_D_bs4m3e3bGzvGe";
$dbname = "football_db";
$port = 25060;

// 1. Initialize the mysqli object safely
$conn = mysqli_init();
if (!$conn) {
    die("mysqli_init failed");
}

// 2. Set options: Timeout if it takes more than 5 seconds so it doesn't freeze Vercel
$conn->options(MYSQLI_OPT_CONNECT_TIMEOUT, 5);

// 3. Enable SSL encryption (Absolutely required by Aiven)
$conn->ssl_set(NULL, NULL, NULL, NULL, NULL);

// 4. Establish the connection over the precise Aiven host & port
$success = @$conn->real_connect($servername, $username, $password, $dbname, $port);

// 5. Catch connection failures cleanly
if (!$success) {
    http_response_code(500);
    echo json_encode([
        "error" => "Database connection failed",
        "details" => mysqli_connect_error(),
        "errno" => mysqli_connect_errno()
    ]);
    exit();
}
?>
