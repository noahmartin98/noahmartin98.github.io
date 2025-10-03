<!DOCTYPE html>
<html>
<head>
<link rel="stylesheet" href="mystyle.css">
</head>
<body>

<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "football";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}


$season = $_GET['season'] ?? '2015';

?>

<nav>
    <a href="/home.html" class="nav">Back to home</a>
</nav>

<div class="header-container">

    <h1>Passing Leaders</h1>


    <form method="get" action="passingLeaders.php">
        <select class="season-select" name="season" onchange="this.form.submit()">
            <option value="2015" <?php if ($season == '2015') echo 'selected'; ?>>2015</option>
            <option value="2013" <?php if ($season == '2013') echo 'selected'; ?>>2013</option>
            <option value="Total" <?php if ($season == 'Total') echo 'selected'; ?>>Total</option>
        </select>
    </form>
</div>


<table>
    <tr>
        <th>Rank</th>
        <th>Player</th>
        <th>Position</th>
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

if ($season === "Total") {
    $sql = "SELECT Game.Season, Player.Player_Name, GROUP_CONCAT(DISTINCT Pos.Pos_Abbr SEPARATOR ', ') AS Poss, GROUP_CONCAT(DISTINCT Team.Abbr SEPARATOR ', ') AS Teams, 
	Pass_Statline.Player_ID, COUNT(*) as Gms, SUM(Comp), SUM(Att), SUM(Yds), SUM(TD), SUM(INTR),
	(SUM(Comp)/SUM(Att)) AS CompPct,
    (SUM(Yds)/count(*)) AS Ypg,
    (SUM(Yds)/SUM(Att)) AS Ypa,
    (SUM(TD)/SUM(INTR)) AS TDINT
    FROM Pass_Statline
    INNER JOIN Player ON Pass_Statline.Player_ID = Player.Player_ID
    INNER JOIN Team ON Pass_Statline.Team_ID = Team.Team_ID
    INNER JOIN Pos ON Pass_Statline.Pos_ID = Pos.Pos_ID
    INNER JOIN Game ON Pass_Statline.Game_ID = Game.Game_ID
    GROUP BY Player_ID
    ORDER BY SUM(Yds) desc;";
} else {
    $sql = "SELECT Game.Season, Player.Player_Name, GROUP_CONCAT(DISTINCT Pos.Pos_Abbr SEPARATOR ', ') AS Poss, GROUP_CONCAT(DISTINCT Team.Abbr SEPARATOR ', ') AS Teams, 
	Pass_Statline.Player_ID, COUNT(*) as Gms, SUM(Comp), SUM(Att), SUM(Yds), SUM(TD), SUM(INTR),
	(SUM(Comp)/SUM(Att)) AS CompPct,
    (SUM(Yds)/count(*)) AS Ypg,
    (SUM(Yds)/SUM(Att)) AS Ypa,
    (SUM(TD)/SUM(INTR)) AS TDINT
    FROM Pass_Statline
    INNER JOIN Player ON Pass_Statline.Player_ID = Player.Player_ID
    INNER JOIN Team ON Pass_Statline.Team_ID = Team.Team_ID
    INNER JOIN Pos ON Pass_Statline.Pos_ID = Pos.Pos_ID
    INNER JOIN Game ON Pass_Statline.Game_ID = Game.Game_ID
    WHERE Season = $season
    GROUP BY Player_ID
    ORDER BY SUM(Yds) desc;";
}
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    // output data of each row
    $cur_rank = 1;
    while($row = $result->fetch_assoc()) {
        $playerid = $row["Player_ID"];
        echo "<tr>";
        echo "<td>".$cur_rank."</td>";
        echo "<td class='link'><a class='leader' href='/playerPagePass.php?playerid=" . $playerid . "'>" . $row["Player_Name"] . "</a></td>";
        echo "<td>". $row["Poss"]."</td>";
        echo "<td>". $row["Teams"]."</td>";
        echo "<td>". $row["Gms"]."</td>";
        echo "<td>". $row["SUM(Comp)"]."</td>";
        echo "<td>". $row["SUM(Att)"]."</td>";
        echo "<td>". $row["SUM(Yds)"]."</td>";
        echo "<td>". $row["SUM(TD)"]."</td>";
        echo "<td>". $row["SUM(INTR)"]."</td>";
        echo "<td>". sprintf('%.1f%%', $row["CompPct"] * 100)."</td>";
        echo "<td>". sprintf('%.1f', $row["Ypg"])."</td>";
        echo "<td>". sprintf('%.2f', $row["Ypa"])."</td>";
        echo "<td>". sprintf('%.2f', $row["TDINT"])."</td>";
        echo "</tr>";
        $cur_rank++;
    }
} else {
    echo "0 results";
}

$conn->close();
?>

</table>

</body>
</html>