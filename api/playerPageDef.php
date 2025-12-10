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


</table>

    <table class="player-years-pass">
        <tr>
            <th>Season</th>
            <th>Team</th>
            <th>Gms</th>
            <th>Sack</th>
            <th>INT</th>
            <th>FF</th>
            <th>FR</th>
            <th>TD</th>
            <th>TFL</th>
            <th>PDEF</th>
        </tr>

<?php

$sql = "SELECT game.season, team.Abbr, count(*) as Gms, SUM(Sack) as Sack, SUM(INTR) as INTR, SUM(FF) as FF, SUM(FR) as FR, SUM(TD) as TD,
	SUM(TFL) as TFL, SUM(PDEF) as PDEF
    FROM def_statline
    INNER JOIN game ON def_statline.Game_ID = game.Game_ID
    INNER JOIN team ON def_statline.Team_ID = team.Team_ID
    WHERE Player_ID = $playerid
    GROUP BY game.Season;";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    // output data of each row
    while($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>". $row["season"]."</td>";
        echo "<td>". $row["Abbr"]."</td>";
        echo "<td>". $row["Gms"]."</td>";
        echo "<td>". $row["Sack"]."</td>";
        echo "<td>". $row["INTR"]."</td>";
        echo "<td>". $row["FF"]."</td>";
        echo "<td>". $row["FR"]."</td>";
        echo "<td>". $row["TD"]."</td>";
		echo "<td>". $row["TFL"]."</td>";
        echo "<td>". $row["PDEF"]."</td>";
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
            <th>Sack</th>
            <th>INT</th>
            <th>FF</th>
            <th>FR</th>
            <th>TD</th>
            <th>TFL</th>
            <th>PDEF</th>
        </tr>


<?php

$sql = "SELECT game.season, tm.Abbr AS Tm, game.week, game.game_date, t1.team_user, 
		CASE
			WHEN (t1.home_away = 'Home')
				THEN 'vs.'
			WHEN (t1.home_away = 'Away')
				THEN '@'
			ELSE ''
		END AS loc,
    opp.Abbr AS Opp, Sack, INTR, FF, FR, TD, TFL, PDEF
    FROM def_statline
    INNER JOIN game ON def_statline.game_id = game.game_id
    	JOIN team_statline t1
		ON t1.game_id = def_statline.game_id
		AND t1.team_id = def_statline.team_id
	JOIN team_statline t2
		ON t2.game_id = def_statline.game_id
		AND t2.team_id <> def_statline.team_id
	JOIN team tm ON t1.team_id = tm.team_id
	JOIN team opp ON t2.team_id = opp.team_id
    WHERE Player_ID = $playerid;";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    // output data of each row
    $cur_rank = 1;
    while($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>". $row["season"]."</td>";
        echo "<td>". $row["Tm"]."</td>";
        echo "<td>". $row["week"]."</td>";
        echo "<td>". $row["game_date"]."</td>";
		echo "<td>". $row["team_user"]."</td>";
		echo "<td>". $row["loc"]."</td>";
		echo "<td>". $row["Opp"]."</td>";
        echo "<td>". $row["Sack"]."</td>";
        echo "<td>". $row["INTR"]."</td>";
        echo "<td>". $row["FF"]."</td>";
        echo "<td>". $row["FR"]."</td>";
        echo "<td>". $row["TD"]."</td>";
		echo "<td>". $row["TFL"]."</td>";
        echo "<td>". $row["PDEF"]."</td>";
        echo "</tr>";
        $cur_rank++;
    }
} else {
    echo "0 results";
}

$conn->close();
?>

    




</body>
</html>
