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
  $sql = "SELECT awayTeam.team_name AS awayName, awayTeam.abbr AS awayAbbr, homeTeam.team_name AS homeName, homeTeam.abbr AS homeAbbr,
  t1.q1 AS awayQ1, t1.q2 AS awayQ2, t1.q3 AS awayQ3, t1.q4 AS awayQ4, t1.ot AS awayOT, t1.score AS awayF,   
  t2.q1 AS homeQ1, t1.q2 AS homeQ2, t2.q3 AS homeQ3, t2.q4 AS homeQ4, t2.ot AS homeOT, t2.score AS homeF
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
		$awayAbbr = $row["awayAbbr"];
		$homeAbbr = $row["homeAbbr"];
		$awayScores = [$row["awayQ1"], $row["awayQ2"], $row["awayQ3"], $row["awayQ4"], $row["awayOT"], $row["awayF"]];
		$awayScores = [$row["homeQ1"], $row["homeQ2"], $row["homeQ3"], $row["homeQ4"], $row["homeOT"], $row["homeF"]];
    }
} else {
    echo "0 results";
}
?>

<div class="header-container">

    <h1><?php echo $awayName . " @ " . $homeName; ?></h1>

    <h2>Box Score</h2>

<table>
	<tr>
		<th>Team</th>
		<th>1Q</th>
		<th>2Q</th>
		<th>3Q</th>
		<th>4Q</th>
		<th>OT</th>
		<th>F</th>
	</tr>
	<tr>
		<td><?php echo $awayAbbr ?></td>
		<td><?php echo $awayScores[0] ?></td>
		<td><?php echo $awayScores[1] ?></td>
		<td><?php echo $awayScores[2] ?></td>
		<td><?php echo $awayScores[3] ?></td>
		<td><?php echo $awayScores[4] ?></td>
		<td><?php echo $awayScores[5] ?></td>
	</tr>
	<tr>
		<td><?php echo $homeAbbr ?></td>
		<td><?php echo $homeScores[0] ?></td>
		<td><?php echo $homeScores[1] ?></td>
		<td><?php echo $homeScores[2] ?></td>
		<td><?php echo $homeScores[3] ?></td>
		<td><?php echo $homeScores[4] ?></td>
		<td><?php echo $homeScores[5] ?></td>
	</tr>
</table>

</div>
        

<?php


?>


</body>
</html>
