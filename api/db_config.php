<?php
// Bypassing Vercel's DNS resolution by using the direct Aiven IPv4 address
$servername = "34.148.118.232"; 
$username = "avnadmin";
$password = "AVNS_s9w_D_bs4m3e3bGzvGe";
$dbname = "football_db";
$port = 25060;

try {
    // 1. DSN setup using the direct IPv4 numeric address
    $dsn = "mysql:host=$servername;dbname=$dbname;port=$port;charset=utf8mb4";
    
    // 2. Explicit modern driver flags to fix the PHP 8.5+ deprecation warnings
    $options = [
        PDO::MYSQL_ATTR_SSL_CA => true,
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_TIMEOUT => 5
    ];

    $pdo = new PDO($dsn, $username, $password, $options);

    // 3. BACKWARD COMPATIBILITY BRIDGE:
    // This allows your other files (like passingLeaders.php) to continue using $conn->query()
    if (!class_exists('ServerlessBridge')) {
        class ServerlessBridge {
            private $pdo;
            public function __construct($pdo) { $this->pdo = $pdo; }
            public function query($sql) {
                return $this->pdo->query($sql);
            }
            public function prepare($sql) {
                return $this->pdo->prepare($sql);
            }
        }
    }
    
    $conn = new ServerlessBridge($pdo);

} catch (PDOException $e) {
    // Suppress header issues by delivering a clean JSON response if it falls through
    if (!headers_sent()) {
        header('Content-Type: application/json');
        http_response_code(500);
    }
    die(json_encode([
        "error" => "Database connection failed",
        "message" => $e->getMessage()
    ]));
}
?>
