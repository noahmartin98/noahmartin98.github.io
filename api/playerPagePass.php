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
    <a href="/football-app/passingLeaders.php" class="nav">Back to leaders</a> |
    <a href="/football-app/teamPage.php" class="nav">Back to team</a> |
    <a href="/football-app/home.html" class="nav">Back to home</a>
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
            <th>Comp</th>
            <th>Att</th>
            <th>Yds</th>
            <th>TD</th>
            <th>INT</th>
            <th>Comp%</th>
            <th>Yds/Gm</th>
            <th>Yds/Att</th>
            <th>TD/INT</th>
        </tr>

<?php

$sql = "SELECT Game.Season, Team.Abbr, count(*) as Gms, SUM(Comp) as Comp, SUM(Att) as Att, SUM(Yds) as Yds, SUM(TD) as TD, SUM(INTR) as INTR,
    (SUM(Comp)/SUM(Att)) AS CompPct,
    (SUM(Yds)/count(*)) AS Ypg,
    (SUM(Yds)/SUM(Att)) AS Ypa,
    (SUM(TD)/SUM(INTR)) AS TDINT
    FROM Pass_Statline
    INNER JOIN Game ON Pass_Statline.Game_ID = Game.Game_ID
    INNER JOIN Team ON Pass_Statline.Team_ID = Team.Team_ID
    WHERE Player_ID = $playerid
    GROUP BY Game.Season;";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    // output data of each row
    while($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>". $row["Season"]."</td>";
        echo "<td>". $row["Abbr"]."</td>";
        echo "<td>". $row["Gms"]."</td>";
        echo "<td>". $row["Comp"]."</td>";
        echo "<td>". $row["Att"]."</td>";
        echo "<td>". $row["Yds"]."</td>";
        echo "<td>". $row["TD"]."</td>";
        echo "<td>". $row["INTR"]."</td>";
        echo "<td>". sprintf('%.1f%%', $row["CompPct"] * 100)."</td>";
        echo "<td>". sprintf('%.1f', $row["Ypg"])."</td>";
        echo "<td>". sprintf('%.2f', $row["Ypa"])."</td>";
        echo "<td>". sprintf('%.2f', $row["TDINT"])."</td>";
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
            <th>Comp</th>
            <th>Att</th>
            <th>Yds</th>
            <th>TD</th>
            <th>INT</th>
        </tr>


<?php

$sql = "SELECT Game.Season, Team.Abbr, Game.Week_Round, Game.Game_Date, Comp, Att, Yds, TD, INTR
    FROM Pass_Statline
    INNER JOIN Game ON Pass_Statline.Game_ID = Game.Game_ID
    INNER JOIN Team ON Pass_Statline.Team_ID = Team.Team_ID
    WHERE Player_ID = $playerid;";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    // output data of each row
    $cur_rank = 1;
    while($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>". $row["Season"]."</td>";
        echo "<td>". $row["Abbr"]."</td>";
        echo "<td>". $row["Week_Round"]."</td>";
        echo "<td>". $row["Game_Date"]."</td>";
        echo "<td>". $row["Comp"]."</td>";
        echo "<td>". $row["Att"]."</td>";
        echo "<td>". $row["Yds"]."</td>";
        echo "<td>". $row["TD"]."</td>";
        echo "<td>". $row["INTR"]."</td>";
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
