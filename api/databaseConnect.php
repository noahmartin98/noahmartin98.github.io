
<?php
$servername = "football-db.cnsw24uw47hg.us-east-2.rds.amazonaws.com";
$username = "admin";
$password = "Almostdone2025";
$dbname = "football-db";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
