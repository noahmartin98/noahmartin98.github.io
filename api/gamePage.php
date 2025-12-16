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
    g.game_id, g.season, g.game_date, g.difficulty, g.week,
    t.team_name, t.abbr, t.short, t.team_id,
    ts.seed, ts.home_away, ts.team_user,
    ts.q1, ts.q2, ts.q3, ts.q4, ts.ot, ts.score, ts.rush+ts.pass AS total, ts.rush, ts.pass, ts.sacked
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

////
$sql = "SELECT *
	FROM team_game_passing
	WHERE game_id = $gameid";

$result = $conn->query($sql);

while ($row = $result->fetch_assoc()) {
    $teamPass[$row['team_id']] = $row;
}
$home['passing'] = $teamPass[$home['team_id']] ?? null;
$away['passing'] = $teamPass[$away['team_id']] ?? null;
////
$sql = "SELECT *
	FROM team_game_rushing
	WHERE game_id = $gameid";

$result = $conn->query($sql);

while ($row = $result->fetch_assoc()) {
    $teamRush[$row['team_id']] = $row;
}
$home['rushing'] = $teamRush[$home['team_id']] ?? null;
$away['rushing'] = $teamRush[$away['team_id']] ?? null;

$away["pass_plays"] = ($away['passing']['pass_att'] + $away["sacked"]);
$home["pass_plays"] = ($home['passing']['pass_att'] + $home["sacked"]);
	


// Player passing stats query 
$sql = "SELECT p.player_id, p.player_name, pos.pos_abbr, t.abbr, ps.comp, ps.att, ps.yds, ps.td, ps.intr
		    FROM game g
			JOIN pass_statline ps ON g.game_id = ps.game_id
		    JOIN player p ON ps.player_id = p.player_id
		    JOIN team t ON ps.team_id = t.team_id
		    JOIN pos ON ps.pos_id = pos.pos_id
		    WHERE g.game_id = $gameid
		    ORDER BY yds desc;";
		
		$result = $conn->query($sql);

$homePass = [];
$awayPass = [];
while ($row = $result->fetch_assoc()) {
    if ($row["abbr"] === $home["abbr"]) {
        $homePass[] = $row;   // Add to home array
    } else {
        $awayPass[] = $row;   // Add to away array
    }
}

// Player rushing stats query 
$sql = "SELECT p.player_id, p.player_name, pos.pos_abbr, t.abbr, rs.att, rs.yds, rs.td
		    FROM game g
			JOIN rush_statline rs ON g.game_id = rs.game_id
		    JOIN player p ON rs.player_id = p.player_id
		    JOIN team t ON rs.team_id = t.team_id
		    JOIN pos ON rs.pos_id = pos.pos_id
		    WHERE g.game_id = $gameid
		    ORDER BY yds desc;";
		
		$result = $conn->query($sql);
$homeRush = [];
$awayRush = [];
while ($row = $result->fetch_assoc()) {
    if ($row["abbr"] === $home["abbr"]) {
        $homeRush[] = $row;   // Add to home array
    } else {
        $awayRush[] = $row;   // Add to away array
    }
}

// Player receiving stats query 
$sql = "SELECT p.player_id, p.player_name, pos.pos_abbr, t.abbr, rs.rec, rs.yds, rs.td
		    FROM game g
			JOIN rec_statline rs ON g.game_id = rs.game_id
		    JOIN player p ON rs.player_id = p.player_id
		    JOIN team t ON rs.team_id = t.team_id
		    JOIN pos ON rs.pos_id = pos.pos_id
		    WHERE g.game_id = $gameid
		    ORDER BY yds desc;";
		
		$result = $conn->query($sql);
$homeRec = [];
$awayRec = [];
while ($row = $result->fetch_assoc()) {
    if ($row["abbr"] === $home["abbr"]) {
        $homeRec[] = $row;   // Add to home array
    } else {
        $awayRec[] = $row;   // Add to away array
    }
}

// Player defensive stats query 
$sql = "SELECT p.player_id, p.player_name, pos.pos_abbr, t.abbr, ds.sack, ds.intr, ds.ff, ds.fr, ds.td, ds.tfl, ds.pdef
		    FROM game g
			JOIN def_statline ds ON g.game_id = ds.game_id
		    JOIN player p ON ds.player_id = p.player_id
		    JOIN team t ON ds.team_id = t.team_id
		    JOIN pos ON ds.pos_id = pos.pos_id
		    WHERE g.game_id = $gameid
		    ORDER BY sack desc, intr desc, ff desc, fr desc, td desc, tfl desc, pdef desc;";
		
		$result = $conn->query($sql);
$homeDef = [];
$awayDef = [];
while ($row = $result->fetch_assoc()) {
    if ($row["abbr"] === $home["abbr"]) {
        $homeDef[] = $row;   // Add to home array
    } else {
        $awayDef[] = $row;   // Add to away array
    }
}

?>

<div class="box-score-top">

    <h1><?php echo $away["team_name"] . "   (". $away["seed"] . ") @ " . $home["team_name"] . "   (" . $home["seed"] . ")"; ?></h1>

	<div class="box-score-top2">
		<h2><?php echo $away["game_date"] ?></h2>
		<h2><?php echo $away["season"] . " " . $away["week"] ?></h2>
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
		<td><?php echo $away["short"] . " (" . $away["team_user"] . ") "?></td>
		<td><?php echo $away["q1"] ?></td>
		<td><?php echo $away["q2"] ?></td>
		<td><?php echo $away["q3"] ?></td>
		<td><?php echo $away["q4"] ?></td>
		<td><?php echo $away["ot"] ?></td>
		<td><?php echo $away["score"] ?></td>
	</tr>
	<tr>
		<td><?php echo $home["short"] . " (" . $home["team_user"] . ") "?></td>
		<td><?php echo $home["q1"] ?></td>
		<td><?php echo $home["q2"] ?></td>
		<td><?php echo $home["q3"] ?></td>
		<td><?php echo $home["q4"] ?></td>
		<td><?php echo $home["ot"] ?></td>
		<td><?php echo $home["score"] ?></td>
	</tr>
</table>
		
	</div>
</div>

<div class="main-box">
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
					<td><?php echo ($away['passing']['pass_att'] + $away["sacked"]) ?></td>
					<td>Pass Plays</td>
					<td><?php echo ($home['passing']['pass_att'] + $home["sacked"]) ?></td>
				</tr>
				<tr>
					<td><?php echo $away["pass"] ?></td>
					<td>Passing Yards</td>
					<td><?php echo $home["pass"] ?></td>
				</tr>
				<tr>
					<td><?php echo number_format(($away["pass"] / $away["pass_plays"]), 1) ?></td>
					<td>Yards per Pass</td>
					<td><?php echo number_format(($home["pass"] / $home["pass_plays"]), 1) ?></td>
				</tr>
				<tr>
					<td><?php echo $away['passing']['pass_comp'] . "/" . $away['passing']['pass_att'] ?></td>
					<td>Comp/Att</td>
					<td><?php echo $home['passing']['pass_comp'] . "/" . $home['passing']['pass_att'] ?></td>
				</tr>
				<tr>
					<td><?php echo $away["sacked"] . " - " . ($away['passing']['pass_yds'] - $away["pass"]) ?></td>
					<td>Sacked-Yards Lost</td>
					<td><?php echo $home["sacked"] . " - " . ($home['passing']['pass_yds'] - $home["pass"]) ?></td>
				</tr>
				<tr>
					<td><?php echo $away['rushing']['rush_att']; ?></td>
					<td>Rush Att</td>
					<td><?php echo $home['rushing']['rush_att']; ?></td>
				</tr>
				<tr>
					<td><?php echo $away["rush"] ?></td>
					<td>Rushing Yards</td>
					<td><?php echo $home["rush"] ?></td>
				</tr>
				<tr>
					<td><?php echo $away['rushing']['rush_yds'] ?></td>
					<td>Rushing Yards</td>
					<td><?php echo $home['rushing']['rush_yds'] ?></td>
				</tr>
				<tr>
					<td><?php echo number_format(($away['rushing']['rush_yds'] / $away['rushing']['rush_att']), 1) ?></td>
					<td>Yards per Rush</td>
					<td><?php echo number_format(($home['rushing']['rush_yds'] / $home['rushing']['rush_att']), 1) ?></td>
				</tr>
			</table>
		</div>
		
		<!-- Player Stats -->
		<div class="right-side-box">
	
			<h3><?php echo $away["abbr"] ?> Passing</h3>
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
			foreach ($awayPass as $player) {
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
			}?>
			</table>
	
			<h3><?php echo $away["abbr"] ?> Rushing</h3>
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
			foreach ($awayRush as $player) {
				$playerid = $player["player_id"];
		        echo "<tr>";
		        echo "<td class='link'><a class='leader' href='/api/playerPageRush.php?playerid=" . $playerid . "'>" . $player["player_name"]."</td>";
		        echo "<td>". $player["pos_abbr"]."</td>";
		        echo "<td>". $player["abbr"]."</td>";
		        echo "<td>". $player["att"]."</td>";
		        echo "<td>". $player["yds"]."</td>";
		        echo "<td>". $player["td"]."</td>";
		        echo "</tr>";
			}?>
			</table>
	
			<h3><?php echo $away["abbr"] ?> Receiving</h3>
			<table>
				<tr>
					<th>Player</th>
					<th>Pos</th>
					<th>Team</th>
					<th>Rec</th>
					<th>Yds</th>
					<th>TD</th>
				</tr>
				
			<?php		
			foreach ($awayRec as $player) {
				$playerid = $player["player_id"];
		        echo "<tr>";
		        echo "<td class='link'><a class='leader' href='/api/playerPageRec.php?playerid=" . $playerid . "'>" . $player["player_name"]."</td>";
		        echo "<td>". $player["pos_abbr"]."</td>";
		        echo "<td>". $player["abbr"]."</td>";
		        echo "<td>". $player["rec"]."</td>";
		        echo "<td>". $player["yds"]."</td>";
		        echo "<td>". $player["td"]."</td>";
		        echo "</tr>";
			}?>
			</table>
	
			<h3><?php echo $away["abbr"] ?> Defense</h3>
			<table>
				<tr>
					<th>Player</th>
					<th>Pos</th>
					<th>Team</th>
					<th>Sack</th>
					<th>INT</th>
					<th>FF</th>
					<th>FR</th>
					<th>TD</th>
					<th>TFL</th>
					<th>PDEF</th>
				</tr>
				
			<?php		
			foreach ($awayDef as $player) {
				$playerid = $player["player_id"];
		        echo "<tr>";
		        echo "<td class='link'><a class='leader' href='/api/playerPageDef.php?playerid=" . $playerid . "'>" . $player["player_name"]."</td>";
		        echo "<td>". $player["pos_abbr"]."</td>";
		        echo "<td>". $player["abbr"]."</td>";
		        echo "<td>". $player["sack"]."</td>";
		        echo "<td>". $player["intr"]."</td>";
		        echo "<td>". $player["ff"]."</td>";
				echo "<td>". $player["fr"]."</td>";
				echo "<td>". $player["td"]."</td>";
				echo "<td>". $player["tfl"]."</td>";
				echo "<td>". $player["pdef"]."</td>";
		        echo "</tr>";
			}?>
			</table>
			
		</div>
	
		<div class="right-side-box">
			<h3><?php echo $home["abbr"] ?> Passing</h3>
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
			foreach ($homePass as $player) {
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
			}?>
			</table>

			<h3><?php echo $home["abbr"] ?> Rushing</h3>
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
			foreach ($homeRush as $player) {
				$playerid = $player["player_id"];
		        echo "<tr>";
		        echo "<td class='link'><a class='leader' href='/api/playerPageRush.php?playerid=" . $playerid . "'>" . $player["player_name"]."</td>";
		        echo "<td>". $player["pos_abbr"]."</td>";
		        echo "<td>". $player["abbr"]."</td>";
		        echo "<td>". $player["att"]."</td>";
		        echo "<td>". $player["yds"]."</td>";
		        echo "<td>". $player["td"]."</td>";
		        echo "</tr>";
			}?>
			</table>

			<h3><?php echo $home["abbr"] ?> Receiving</h3>
			<table>
				<tr>
					<th>Player</th>
					<th>Pos</th>
					<th>Team</th>
					<th>Rec</th>
					<th>Yds</th>
					<th>TD</th>
				</tr>
				
			<?php		
			foreach ($homeRec as $player) {
				$playerid = $player["player_id"];
		        echo "<tr>";
		        echo "<td class='link'><a class='leader' href='/api/playerPageRec.php?playerid=" . $playerid . "'>" . $player["player_name"]."</td>";
		        echo "<td>". $player["pos_abbr"]."</td>";
		        echo "<td>". $player["abbr"]."</td>";
		        echo "<td>". $player["rec"]."</td>";
		        echo "<td>". $player["yds"]."</td>";
		        echo "<td>". $player["td"]."</td>";
		        echo "</tr>";
			}?>
			</table>

			<h3><?php echo $home["abbr"] ?> Defense</h3>
			<table>
				<tr>
					<th>Player</th>
					<th>Pos</th>
					<th>Team</th>
					<th>Sack</th>
					<th>INT</th>
					<th>FF</th>
					<th>FR</th>
					<th>TD</th>
					<th>TFL</th>
					<th>PDEF</th>
				</tr>
				
			<?php		
			foreach ($homeDef as $player) {
				$playerid = $player["player_id"];
		        echo "<tr>";
		        echo "<td class='link'><a class='leader' href='/api/playerPageDef.php?playerid=" . $playerid . "'>" . $player["player_name"]."</td>";
		        echo "<td>". $player["pos_abbr"]."</td>";
		        echo "<td>". $player["abbr"]."</td>";
		        echo "<td>". $player["sack"]."</td>";
		        echo "<td>". $player["intr"]."</td>";
		        echo "<td>". $player["ff"]."</td>";
				echo "<td>". $player["fr"]."</td>";
				echo "<td>". $player["td"]."</td>";
				echo "<td>". $player["tfl"]."</td>";
				echo "<td>". $player["pdef"]."</td>";
		        echo "</tr>";
			}?>
			</table>
	
		</div>
	</div>
</div>


</body>
</html>
