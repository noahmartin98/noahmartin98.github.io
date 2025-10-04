<!DOCTYPE html>
<html>
<head>
<link rel="stylesheet" href="mystyle.css">
</head>
<body>

<?php
require 'databaseConnect.php';

if (isset($_GET['playerid'])) {
    $playerid = $_GET['playerid'];
}

$sql = "SELECT Player_Name
    FROM Player 
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


<nav>
    <a href="/football-app/rushingLeaders.php" class="nav">Back to leaders</a> |
    <a href="/football-app/teamPage.php" class="nav">Back to team</a> |
    <a href="/football-app/home.html" class="nav">Back to home</a>
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

$sql = "SELECT Game.Season, GROUP_CONCAT(DISTINCT Team.Abbr SEPARATOR ', ') AS Teams, 
	COUNT(*) as Gms, SUM(Att), SUM(Yds), SUM(TD),
    (SUM(Yds)/SUM(Att)) AS Ypc,
    (SUM(Att)/count(*)) AS Apg,
    (SUM(Yds)/count(*)) AS Ypg
    FROM Rush_Statline
    INNER JOIN Team ON Rush_Statline.Team_ID = Team.Team_ID
    INNER JOIN Pos ON Rush_Statline.Pos_ID = Pos.Pos_ID
    INNER JOIN Game ON Rush_Statline.Game_ID = Game.Game_ID
    WHERE Player_ID = $playerid
    GROUP BY Game.Season;";
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
            <th>Att</th>
            <th>Yds</th>
            <th>TD</th>
        </tr>


<?php

$sql = "SELECT Game.Season, Team.Abbr, Game.Week_Round, Game.Game_Date, Att, Yds, TD
    FROM Rush_Statline
    INNER JOIN Game ON Rush_Statline.Game_ID = Game.Game_ID
    INNER JOIN Team ON Rush_Statline.Team_ID = Team.Team_ID
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