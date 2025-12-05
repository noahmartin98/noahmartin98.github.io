<!DOCTYPE html>
<html>
<head>
<link rel="stylesheet" href="../mystyle.css">
</head>
<body>

<?php
require 'databaseConnect.php';

if (isset($_GET['playerid'])) {
    $playerid = $_GET['playerid'];
}

$sql = "SELECT Player_Name
    FROM player 
    WHERE Player_ID = $playerid;";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    // output data of each row
    while($row = $result->fetch_assoc()) {
        $playername =  $row["Player_Name"];
    }
} else {
    echo "0 results";
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

<div class="header-container">
    <h1><?php echo $playername?></h1>
    <h3>Career</h3>
</div>


    <table class="player-years">
        <tr>
            <th>Season</th>
            <th>Team</th>
            <th>Gms</th>
            <th>Att</th>
            <th>Yds</th>
            <th>TD</th>
            <th>Yds/Att</th>
            <th>Att/Gm</th>
            <th>Yds/Gm</th>
        </tr>

<?php

$sql = "SELECT game.Season, GROUP_CONCAT(DISTINCT team.Abbr SEPARATOR ', ') AS Teams, 
	COUNT(*) as Gms, SUM(Att), SUM(Yds), SUM(TD),
    (SUM(Yds)/SUM(Att)) AS Ypc,
    (SUM(Att)/count(*)) AS Apg,
    (SUM(Yds)/count(*)) AS Ypg
    FROM rush_statline
    INNER JOIN team ON rush_statline.Team_ID = team.Team_ID
    INNER JOIN pos ON rush_statline.Pos_ID = pos.Pos_ID
    INNER JOIN game ON rush_statline.Game_ID = game.Game_ID
    WHERE Player_ID = $playerid
    GROUP BY game.Season;";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    // output data of each row
    while($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>". $row["Season"]."</td>";
        echo "<td>". $row["Teams"]."</td>";
        echo "<td>". $row["Gms"]."</td>";
        echo "<td>". $row["SUM(Att)"]."</td>";
        echo "<td>". $row["SUM(Yds)"]."</td>";
        echo "<td>". $row["SUM(TD)"]."</td>";
        echo "<td>". sprintf('%.2f', $row["Ypc"])."</td>";
        echo "<td>". sprintf('%.2f', $row["Apg"])."</td>";
        echo "<td>". sprintf('%.1f', $row["Ypg"])."</td>";
        echo "</tr>";
    }
} else {
    echo "0 results";
}

?>

    </table>


<h3>Game Log</h3>

    <table class="player">
        <tr>
            <th>Season</th>
            <th>Team</th>
            <th>Week</th>
            <th>Date</th>
			<th>User</th>
			<th></th>
			<th>Opp</th>
            <th>Att</th>
            <th>Yds</th>
            <th>TD</th>
        </tr>


<?php

$sql = "SELECT game.Season, team.Abbr, game.Week_Round, game.Game_Date, t1.team_user, 
		CASE
			WHEN (t1.home_away = 'Home')
				THEN 'vs.'
			WHEN (t1.home_away = 'Away')
				THEN '@'
			ELSE ''
		END AS loc,
    opp.Abbr, Att, Yds, TD
    FROM rush_statline
    INNER JOIN game ON rush_statline.Game_ID = game.Game_ID
    INNER JOIN team ON rush_statline.Team_ID = team.Team_ID
    JOIN team_statline t1
		ON t1.game_id = rush_statline.game_id
		AND t1.team_id = rush_statline.team_id
	JOIN team_statline t2
		ON t2.game_id = rush_statline.game_id
		AND t2.team_id <> rush_statline.team_id
	JOIN team opp ON t2.team_id = opp.team_id
    WHERE Player_ID = $playerid;";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    // output data of each row
    while($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>". $row["Season"]."</td>";
        echo "<td>". $row["Abbr"]."</td>";
        echo "<td>". $row["Week_Round"]."</td>";
        echo "<td>". $row["Game_Date"]."</td>";
		echo "<td>". $row["team_user"]."</td>";
		echo "<td>". $row["loc"]."</td>";
		echo "<td>". $row["Abbr"]."</td>";
        echo "<td>". $row["Att"]."</td>";
        echo "<td>". $row["Yds"]."</td>";
        echo "<td>". $row["TD"]."</td>";
        echo "</tr>";
    }
} else {
    echo "0 results";
}

$conn->close();
?>

    </table>

</body>
</html>
