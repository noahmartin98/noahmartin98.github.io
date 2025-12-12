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

// Team stats query
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

// Player passing stats query 
$sql = "SELECT p.player_id, p.player_name, pos.pos_abbr, t.abbr, ps.comp, ps.att, ps.yds, ps.td, ps.intr
		    FROM game g
			JOIN pass_statline ps ON g.game_id = ps.game_id
		    JOIN player p ON p.player_id = ps.player_id
		    JOIN team t ON ps.team_id = t.team_id
		    JOIN pos ON ps.pos_id = pos.pos_id
		    WHERE g.game_id = $gameid
		    ORDER BY yds desc;";
		
		$result = $conn->query($sql);
$playerPass = [];

while ($row = $result->fetch_assoc()) {
    $playerPass[] = $row;
}

// Player rushing stats query 
$sql = "SELECT p.player_id, p.player_name, pos.pos_abbr, t.abbr, rs.att, rs.yds, rs.td
		    FROM game g
			JOIN rush_statline rs ON g.game_id = rs.game_id
		    JOIN player p ON p.player_id = rs.player_id
		    JOIN team t ON rs.team_id = t.team_id
		    JOIN pos ON rs.pos_id = pos.pos_id
		    WHERE g.game_id = $gameid
		    ORDER BY yds desc;";
		
		$result = $conn->query($sql);
$playerRush = [];

while ($row = $result->fetch_assoc()) {
    $playerRush[] = $row;
}


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
		
		foreach ($playerPass as $player) {
			$playerid = $player["player_id"];
	        echo "<tr>";
	        echo "<td class='link'><a class='leader' href='/api/playerPagePass.php?playerid=" . $playerid . "'>" . $player["player_name"]."</td>";
	        echo "<td>". $player["pos_abbr"]."</td>";
	        echo "<td>". $player["abbr"]."</td>";
	        echo "<td>". $player["comp"]."</td>";
	        echo "<td>". $player["att"]."</td>";
	        echo "<td>". $player["yds"]."</td>";
	        echo "<td>". $player["td"]."</td>";
	        echo "<td>". $player["intr"]."</td>";
	        echo "</tr>";
		}	
		
		
		?>
		</table>

		<h3>Player Rushing</h3>
		<table>
			<tr>
				<th>Player</th>
				<th>Pos</th>
				<th>Team</th>
				<th>Att</th>
				<th>Yds</th>
				<th>TD</th>
			</tr>
		
		<?php
		
		foreach ($playerRush as $player) {
			$playerid = $player["player_id"];
	        echo "<tr>";
	        echo "<td class='link'><a class='leader' href='/api/playerRushPass.php?playerid=" . $playerid . "'>" . $player["player_name"]."</td>";
	        echo "<td>". $player["pos_abbr"]."</td>";
	        echo "<td>". $player["abbr"]."</td>";
	        echo "<td>". $player["att"]."</td>";
	        echo "<td>". $player["yds"]."</td>";
	        echo "<td>". $player["td"]."</td>";
	        echo "</tr>";
		}	

		
		?>
		</table>
	</div>
</div>


</body>
</html>
