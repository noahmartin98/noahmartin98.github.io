<!DOCTYPE html>
<html>
<head>
<link rel="stylesheet" href="../mystyle.css">
</head>
<body>

<?php
require 'databaseConnect.php';

$gameID = $_GET['gameID'] ?? '538';
?>

<nav class="navbar">
    <ul class="nav-links">
        <li><a href="../home.html">Home</a></li>
        <li><a href="/api/passingLeaders.php">Passing Leaders</a></li>
        <li><a href="/api/rushingLeaders.php">Rushing Leaders</a></li>
        <li><a href="/api/receivingLeaders.php">Receiving Leaders</a></li>
		<li><a href="/api/defLeaders.php">Defensive Leaders</a></li>
        <li><a href="/api/bracket.php">Playoff Brackets</a></li>
    </ul>
</nav>

<?php
  $sql = "SELECT awayTeam.team_name AS awayName, homeTeam.team_name AS homeName
from game
JOIN team_statline t1 ON game.game_id = t1.game_id AND t1.home_away = 'Away'
JOIN team_statline t2 ON game.game_id = t2.game_id AND t2.home_away = 'Home'
JOIN team awayTeam ON awayTeam.team_id = t1.team_id
JOIN team homeTeam ON homeTeam.team_id = t2.team_id
WHERE game.game_id = 538;";
$result = $conn->query($sql);

$awayName = '';
$homeName = '';

if ($result->num_rows > 0) {
    // output data of each row
    while($row = $result->fetch_assoc()) {
        $awayName = $row["awayName"];
        $homeName = $row["homeName"];
    }
} else {
    echo "0 results";
}
?>

<div class="header-container">

    <h1><?php echo $awayName . " @ " . $homeName; ?></h1>

    <h2>Box Score</h2>

</div>
        

<?php


?>


</body>
</html>
