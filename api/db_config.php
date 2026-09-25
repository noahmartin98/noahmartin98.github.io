<?php
$servername = "://aivencloud.com";
$username = "avnadmin";
$password = "AVNS_s9w_D_bs4m3e3bGzvGe";
$dbname = "football_db";
$port = 25060;

try {
    // 1. Establish connection via PDO using explicit SSL flags (required by Aiven)
    $dsn = "mysql:host=$servername;dbname=$dbname;port=$port;charset=utf8mb4";
    $options = [
        PDO::MYSQL_ATTR_SSL_CA => true, // Enables SSL encryption 
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, // Throws clean exceptions
        PDO::ATTR_TIMEOUT => 5 // Times out quickly if connection drops
    ];

    $pdo = new PDO($dsn, $username, $password, $options);

    // 2. BACKWARD COMPATIBILITY BRIDGE: 
    // Since your other 12+ files expect a standard MySQLi object called '$conn', 
    // we build a fake $conn object wrapper so you don't have to rewrite your other files!
    class ServerlessBridge {
        private $pdo;
        public function __construct($pdo) { $this->pdo = $pdo; }
        public function query($sql) {
            $stmt = $this->pdo->query($sql);
            return $stmt;
        }
        // If your code uses $conn->prepare()
        public function prepare($sql) {
            return $this->pdo->prepare($sql);
        }
    }
    
    $conn = new ServerlessBridge($pdo);

} catch (PDOException $e) {
    http_response_code(500);
    die(json_encode([
        "error" => "Database connection failed",
        "message" => $e->getMessage()
    ]));
}
?>
