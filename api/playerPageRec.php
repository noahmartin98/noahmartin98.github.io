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


<nav>
    <a href="/api/receivingLeaders.php" class="nav">Back to leaders</a> |
    <a href="/api/teamPage.php" class="nav">Back to team</a> |
    <a href="/../home.html" class="nav">Back to home</a>
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
            <th>Rec</th>
            <th>Yds</th>
            <th>TD</th>
            <th>Yds/Rec</th>
            <th>Rec/Gm</th>
            <th>Yds/Gm</th>
        </tr>

<?php

$sql = "SELECT game.Season, GROUP_CONCAT(DISTINCT team.Abbr SEPARATOR ', ') AS Teams,
    COUNT(*) as Gms, SUM(Rec), SUM(Yds), SUM(TD),
    (SUM(Yds)/SUM(Rec)) AS Ypc,
    (SUM(Rec)/count(*)) AS Rpg,
    (SUM(Yds)/count(*)) AS Ypg
    FROM rec_statline
    INNER JOIN team ON rec_statline.Team_ID = team.Team_ID
    INNER JOIN pos ON rec_statline.Pos_ID = pos.Pos_ID
    INNER JOIN game ON rec_statline.Game_ID = game.Game_ID
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
        echo "<td>". $row["SUM(Rec)"]."</td>";
        echo "<td>". $row["SUM(Yds)"]."</td>";
        echo "<td>". $row["SUM(TD)"]."</td>";
        echo "<td>". sprintf('%.2f', $row["Ypc"])."</td>";
        echo "<td>". sprintf('%.1f', $row["Rpg"])."</td>";
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

$sql = "SELECT game.Season, team.Abbr, game.Week_Round, game.Game_Date, Rec, Yds, TD
    FROM rec_statline
    INNER JOIN game ON rec_statline.Game_ID = game.Game_ID
    INNER JOIN team ON rec_statline.Team_ID = team.Team_ID
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
        echo "<td>". $row["Rec"]."</td>";
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
