<!DOCTYPE html>
<html>
<head>
<link rel="stylesheet" href="../mystyle.css">
</head>
<body>

<?php
require 'databaseConnect.php';

if (isset($_GET['teamid'])) {
    $teamid = $_GET['teamid'];
}

$sql = "SELECT Team_Name
    FROM team 
    WHERE Team_ID = $teamid;";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    // output data of each row
    while($row = $result->fetch_assoc()) {
        $teamname =  $row["Team_Name"];
    }
} else {
    echo "0 results";
}

$season = $_GET['season'] ?? '2024';

?>

<nav class="navbar">
    <ul class="nav-links">
        <li><a href="../home.html">Home</a></li>
        <li><a href="/api/passingLeaders.php">Passing Leaders</a></li>
        <li><a href="/api/rushingLeaders.php">Rushing Leaders</a></li>
        <li><a href="/api/receivingLeaders.php">Receiving Leaders</a></li>
        <li><a href="/api/bracket.php">Playoff Brackets</a></li>
    </ul>
</nav>

<!--Header with team name and season select-->
<div class="header-container">

    <h1><?php echo $teamname?></h1>


    <form method="get" action="teamPage.php">
        <input type="hidden" name="teamid" value="<?php echo $teamid; ?>">
        <select class="season-select" name="season" onchange="this.form.submit()">
            <option value="2015" <?php if ($season == '2015') echo 'selected'; ?>>2015</option>
            <option value="2012" <?php if ($season == '2012') echo 'selected'; ?>>2012</option>
			<option value="2024" <?php if ($season == '2024') echo 'selected'; ?>>2024</option>
        </select>
    </form>
</div>


<h3>Games</h3>

    <table>
        <tr>
            <th>Week</th>
            <th>Date</th>
            <th>H/A</th>
            <th>Opponent</th>
            <th>Score</th>
            <th>Opp Score</th>
            <th>Result</th>
        </tr>

<?php

$sql = "SELECT Season, Week_Round, Game_Date,
CASE 
	WHEN Away_Team_ID = $teamid THEN 'Away'
	ELSE 'Home'
    END AS Location,
CASE
	WHEN Away_Team_ID = $teamid THEN HomeTeam.Team_Name
    ELSE AwayTeam.Team_Name
    END AS Opponent,
CASE
	WHEN Away_Team_ID = $teamid THEN Away_Score
    ELSE Home_Score
    END AS Score,
CASE
	WHEN Away_Team_ID = $teamid THEN Home_Score
    ELSE Away_Score
    END AS Opp_Score,
CASE 
	WHEN 
		(Away_Team_ID = $teamid AND Away_Score > Home_Score) OR
		(Home_Team_ID = $teamid AND Home_Score > Away_Score)
	THEN 'W'
	WHEN 
		(Away_Team_ID = $teamid AND Away_Score < Home_Score) OR
		(Home_Team_ID = $teamid AND Home_Score < Away_Score)
	THEN 'L'
	ELSE 'T'
END AS Result
FROM game 
JOIN team AS AwayTeam ON game.Away_Team_ID = AwayTeam.Team_ID
JOIN team AS HomeTeam ON game.Home_Team_ID = HomeTeam.Team_ID
WHERE Season = $season AND (Away_Team_ID = $teamid OR Home_Team_ID = $teamid);";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    // output data of each row
    while($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>". $row["Week_Round"]."</td>";
        echo "<td>". $row["Game_Date"]."</td>";
        echo "<td>". $row["Location"]."</td>";
        echo "<td>". $row["Opponent"]."</td>";
        echo "<td>". $row["Score"]."</td>";
        echo "<td>". $row["Opp_Score"]."</td>";
        echo "<td>". $row["Result"]."</td>";
        echo "</tr>";
    }
} else {
    echo "0 results";
}

?>        
    </table> 


<h3>Passing</h3>
<table class="player-passing">
        <tr>
            <th>Player</th>
            <th>Position</th>
            <th>Gms</th>
            <th>Comp</th>
            <th>Att</th>
            <th>Yds</th>
            <th>TD</th>
            <th>INT</th>
        </tr>

<?php
$sql = "SELECT game.Season, player.Player_Name, GROUP_CONCAT(DISTINCT pos.Pos_Abbr SEPARATOR ', ') AS Poss, GROUP_CONCAT(DISTINCT team.Abbr SEPARATOR ', ') AS Teams, pass_statline.Player_ID, COUNT(*) as Gms, SUM(Comp), SUM(Att), SUM(Yds), SUM(TD), SUM(INTR)
    FROM pass_statline
    INNER JOIN player ON pass_statline.Player_ID = player.Player_ID
    INNER JOIN team ON pass_statline.Team_ID = team.Team_ID
    INNER JOIN pos ON pass_statline.Pos_ID = pos.Pos_ID
    INNER JOIN game ON pass_statline.Game_ID = game.Game_ID
    WHERE team.Team_ID = $teamid AND Season = $season
    GROUP BY Player_ID
    ORDER BY SUM(Yds) desc;";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    // output data of each row
    while($row = $result->fetch_assoc()) {
        $playerid = $row["Player_ID"];
        echo "<tr>";
        echo "<td class='link'><a class='leader' href='/api/playerPagePass.php?playerid=" . $playerid . "'>" . $row["Player_Name"]."</td>";
        echo "<td>". $row["Poss"]."</td>";
        echo "<td>". $row["Gms"]."</td>";
        echo "<td>". $row["SUM(Comp)"]."</td>";
        echo "<td>". $row["SUM(Att)"]."</td>";
        echo "<td>". $row["SUM(Yds)"]."</td>";
        echo "<td>". $row["SUM(TD)"]."</td>";
        echo "<td>". $row["SUM(INTR)"]."</td>";
        echo "</tr>";
    }
} else {
    echo "0 results";
}?>
</table>



<h3>Rushing</h3>
<table class="player">
        <tr>
            <th>Player</th>
            <th>Position</th>
            <th>Gms</th>
            <th>Att</th>
            <th>Yds</th>
            <th>TD</th>
        </tr>

<?php
$sql = "SELECT player.Player_Name, GROUP_CONCAT(DISTINCT pos.Pos_Abbr SEPARATOR ', ') AS Poss, GROUP_CONCAT(DISTINCT team.Abbr SEPARATOR ', ') AS Teams, rush_statline.Player_ID, COUNT(*) as Gms, SUM(Att), SUM(Yds), SUM(TD)
    FROM rush_statline
    INNER JOIN player ON rush_statline.Player_ID = player.Player_ID
    INNER JOIN team ON rush_statline.Team_ID = team.Team_ID
    INNER JOIN pos ON rush_statline.Pos_ID = pos.Pos_ID
    INNER JOIN game ON rush_statline.Game_ID = game.Game_ID
    WHERE team.Team_ID = $teamid  AND Season = $season
    GROUP BY Player_ID
    ORDER BY SUM(Yds) desc;";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    // output data of each row
    while($row = $result->fetch_assoc()) {
        $playerid = $row["Player_ID"];
        echo "<tr>";
        echo "<td class='link'><a class='leader' href='/api/playerPageRush.php?playerid=" . $playerid . "'>" . $row["Player_Name"]."</td>";
        echo "<td>". $row["Poss"]."</td>";
        echo "<td>". $row["Gms"]."</td>";
        echo "<td>". $row["SUM(Att)"]."</td>";
        echo "<td>". $row["SUM(Yds)"]."</td>";
        echo "<td>". $row["SUM(TD)"]."</td>";
        echo "</tr>";
    }
} else {
    echo "0 results";
}?>
</table>


<h3>Receiving</h3>
<table class="player">
        <tr>
            <th>Player</th>
            <th>Position</th>
            <th>Gms</th>
            <th>Rec</th>
            <th>Yds</th>
            <th>TD</th>
        </tr>

<?php
$sql = "SELECT player.Player_Name, GROUP_CONCAT(DISTINCT pos.Pos_Abbr SEPARATOR ', ') AS Poss, GROUP_CONCAT(DISTINCT team.Abbr SEPARATOR ', ') AS Teams, rec_statline.Player_ID, COUNT(*) as Gms, SUM(Rec), SUM(Yds), SUM(TD)
    FROM rec_statline
    INNER JOIN player ON rec_statline.Player_ID = player.Player_ID
    INNER JOIN team ON rec_statline.Team_ID = team.Team_ID
    INNER JOIN pos ON rec_statline.Pos_ID = pos.Pos_ID
    INNER JOIN game ON rec_statline.Game_ID = game.Game_ID
    WHERE team.Team_ID = $teamid  AND Season = $season
    GROUP BY Player_ID
    ORDER BY SUM(Yds) desc;";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    // output data of each row
    while($row = $result->fetch_assoc()) {
        $playerid = $row["Player_ID"];
        echo "<tr>";
        echo "<td class='link'><a class='leader' href='/api/playerPageRec.php?playerid=" . $playerid . "'>" . $row["Player_Name"]."</td>";
        echo "<td>". $row["Poss"]."</td>";
        echo "<td>". $row["Gms"]."</td>";
        echo "<td>". $row["SUM(Rec)"]."</td>";
        echo "<td>". $row["SUM(Yds)"]."</td>";
        echo "<td>". $row["SUM(TD)"]."</td>";
        echo "</tr>";
    }
} else {
    echo "0 results";
}?>
</table>

<h3>Defense</h3>
<table class="player-passing">
        <tr>
            <th>Player</th>
            <th>Position</th>
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
$sql = "SELECT game.Season, player.Player_Name, GROUP_CONCAT(DISTINCT pos.Pos_Abbr SEPARATOR ', ') AS Poss, GROUP_CONCAT(DISTINCT team.Abbr SEPARATOR ', ') AS Teams, def_statline.Player_ID, COUNT(*) as Gms,
	SUM(Sack) as Sack, SUM(INTR) as INTR, SUM(FF) as FF, SUM(FR) as FR, SUM(TD) as TD, SUM(TFL) as TFL, SUM(PDEF) as PDEF
    FROM def_statline
    INNER JOIN player ON def_statline.Player_ID = player.Player_ID
    INNER JOIN team ON def_statline.Team_ID = team.Team_ID
    INNER JOIN pos ON def_statline.Pos_ID = pos.Pos_ID
    INNER JOIN game ON def_statline.Game_ID = game.Game_ID
    WHERE team.Team_ID = $teamid AND Season = $season
    GROUP BY Player_ID
    ORDER BY SUM(Sack) desc;";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    // output data of each row
    while($row = $result->fetch_assoc()) {
        $playerid = $row["Player_ID"];
        echo "<tr>";
        echo "<td class='link'><a class='leader' href='/api/playerPagePDef.php?playerid=" . $playerid . "'>" . $row["Player_Name"]."</td>";
        echo "<td>". $row["Poss"]."</td>";
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
}?>
</table>


<?php
    $conn->close();
?>


</body>
</html>
