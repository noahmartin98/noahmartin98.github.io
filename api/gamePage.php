<!DOCTYPE html>
<html>
<head>
<link rel="stylesheet" href="../mystyle.css">
</head>
<body>

<?php
require 'databaseConnect.php';

if (isset($_GET['gameid'])) {
    $gameid = $_GET['gameid'];
}
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
 /* $sql = "SELECT awayTeam.team_name AS awayName, awayTeam.abbr AS awayAbbr, homeTeam.team_name AS homeName, homeTeam.abbr AS homeAbbr,
  t1.q1 AS awayQ1, t1.q2 AS awayQ2, t1.q3 AS awayQ3, t1.q4 AS awayQ4, t1.ot AS awayOT, t1.score AS awayF,   
  t2.q1 AS homeQ1, t1.q2 AS homeQ2, t2.q3 AS homeQ3, t2.q4 AS homeQ4, t2.ot AS homeOT, t2.score AS homeF
from game
JOIN team_statline t1 ON game.game_id = t1.game_id AND t1.home_away = 'Away'
JOIN team_statline t2 ON game.game_id = t2.game_id AND t2.home_away = 'Home'
JOIN team awayTeam ON awayTeam.team_id = t1.team_id
JOIN team homeTeam ON homeTeam.team_id = t2.team_id
WHERE game.game_id = $gameid;";*/

$sql = "SELECT 
    g.game_id, g.game_date, g.difficulty, g.week,
    t.team_name,
    t.abbr,
    ts.seed, ts.home_away, ts.team_user,
    ts.q1, ts.q2, ts.q3, ts.q4, ts.ot, ts.score, ts.rush+ts.pass AS total, ts.rush, ts.pass
FROM game g
JOIN team_statline ts ON g.game_id = ts.game_id
JOIN team t ON ts.team_id = t.team_id
WHERE g.game_id = $gameid;";

$result = $conn->query($sql);

$home = [];
$away = [];

while ($row = $result->fetch_assoc()) {
    if ($row["home_away"] === "Home") {
        $home = $row;
    } else {
        $away = $row;
    }
}


/*$result = $conn->query($sql);

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
		$homeScores = [$row["homeQ1"], $row["homeQ2"], $row["homeQ3"], $row["homeQ4"], $row["homeOT"], $row["homeF"]];
    }
} else {
    echo "0 results";
}*/
?>

<div class="header-container">

    <h1><?php echo $away["team_name"] . "   (". $away["seed"] . ") @ " . $home["team_name"] . "   (" . $home["seed"] . ")"; ?></h1>
	<h2><?php echo $away["game_date"] ?></h2>
	<h2><?php echo $away["week"] ?></h2>
	<h3><?php echo "Difficulty: " . $away["difficulty"] ?></h3>

    <h3>Box Score</h3>

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
		<td><?php echo $away["abbr"] . " (" . $away["team_user"] . ") "?></td>
		<td><?php echo $away["q1"] ?></td>
		<td><?php echo $away["q2"] ?></td>
		<td><?php echo $away["q3"] ?></td>
		<td><?php echo $away["q4"] ?></td>
		<td><?php echo $away["ot"] ?></td>
		<td><?php echo $away["score"] ?></td>
	</tr>
	<tr>
		<td><?php echo $home["abbr"] . " (" . $home["team_user"] . ") "?></td>
		<td><?php echo $home["q1"] ?></td>
		<td><?php echo $home["q2"] ?></td>
		<td><?php echo $home["q3"] ?></td>
		<td><?php echo $home["q4"] ?></td>
		<td><?php echo $home["ot"] ?></td>
		<td><?php echo $home["score"] ?></td>
	</tr>
</table>

</div>

<div class="box-score-container">
	<div class="left-side-box">
		<h3>Team Stats</h3>
		<table class="team-stats">
			<tr>
				<th><?php echo $away["abbr"] ?></th>
				<th></th>
				<th><?php echo $home["abbr"] ?></th>
			</tr>
			<tr>
				<td><?php echo $away["total"] ?></td>
				<td>Total Yards</td>
				<td><?php echo $home["total"] ?></td>
			</tr>
			<tr>
				<td><?php echo $away["pass"] ?></td>
				<td>Passing Yards</td>
				<td><?php echo $home["pass"] ?></td>
			</tr>
		</table>
	</div>
	
	<!-- Player Stats -->
	<div class="right-side-box">
		<h3>Player Passing</h3>
		<table>
			<tr>
				<th>Player</th>
				<th>Pos</th>
				<th>Team</th>
				<th>Cmp</th>
				<th>Att</th>
				<th>Yds</th>
				<th>TD</th>
				<th>INT</th>
			</tr>
		
		<?php
		
		$sql = "SELECT p.player_id, p.player_name, pos.pos_abbr, t.abbr, ps.comp, ps.att, ps.yds, ps.td, ps.intr
		    FROM game g
			JOIN pass_statline ps ON g.game_id = ps.game_id
		    JOIN player p ON p.player_id = ps.player_id
		    JOIN team t ON ps.team_id = t.team_id
		    JOIN pos ON ps.pos_id = pos.pos_id
		    WHERE g.game_id = $gameid
		    ORDER BY yds desc;";
		
		$result = $conn->query($sql);
	
		if ($result->num_rows > 0) {
	    // output data of each row
	    while($row = $result->fetch_assoc()) {
	        $playerid = $row["player_id"];
	        echo "<tr>";
	        echo "<td class='link'><a class='leader' href='/api/playerPagePass.php?playerid=" . $playerid . "'>" . $row["player_name"]."</td>";
	        echo "<td>". $row["pos_abbr"]."</td>";
	        echo "<td>". $row["abbr"]."</td>";
	        echo "<td>". $row["comp"]."</td>";
	        echo "<td>". $row["att"]."</td>";
	        echo "<td>". $row["yds"]."</td>";
	        echo "<td>". $row["td"]."</td>";
	        echo "<td>". $row["intr"]."</td>";
	        echo "</tr>";
	    }
	} else {
	    echo "0 results";
	}
		
		
		
		?>
		</table>
	</div>
</div>


</body>
</html>
