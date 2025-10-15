<!DOCTYPE html>
<html>
<head>
<link rel="stylesheet" href="../mystyle.css">
<!-- DataTables CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
<!-- jQuery + DataTables JS -->
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

<script src="myjavascript.js"></script>
</head>
<body>

<?php
require 'databaseConnect.php';

$season = $_GET['season'] ?? '2015';

?>

<nav>
    <a href="/football-app/home.html" class="nav">Back to home</a>
</nav>

<div class="header-container">

    <h1>Rushing Leaders</h1>


    <form method="get" action="rushingLeaders.php">
        <select class="season-select" name="season" onchange="this.form.submit()">
            <option value="2015" <?php if ($season == '2015') echo 'selected'; ?>>2015</option>
            <option value="2013" <?php if ($season == '2013') echo 'selected'; ?>>2013</option>
            <option value="Total" <?php if ($season == 'Total') echo 'selected'; ?>>Total</option>
        </select>
    </form>
</div>

<table id="leaders">
    <thead>    
        <tr>
            <th>Rank</th>
            <th>Player</th>
            <th>Position</th>
            <th>Team</th>
            <th>Gms</th>
            <th>Att</th>
            <th>Yds</th>
            <th>TD</th>
            <th>Yds/Att</th>
            <th>Att/Gm</th>
            <th>Yds/Gm</th>
        </tr>
    </thead>

<?php

$sql = "SELECT Game.Season, Player.Player_Name, GROUP_CONCAT(DISTINCT Pos.Pos_Abbr SEPARATOR ', ') AS Poss, GROUP_CONCAT(DISTINCT Team.Abbr SEPARATOR ', ') AS Teams, 
	Rush_Statline.Player_ID, COUNT(*) as Gms, SUM(Att), SUM(Yds), SUM(TD),
    (SUM(Yds)/SUM(Att)) AS Ypc,
    (SUM(Att)/count(*)) AS Apg,
    (SUM(Yds)/count(*)) AS Ypg
    FROM Rush_Statline
    INNER JOIN Player ON Rush_Statline.Player_ID = Player.Player_ID
    INNER JOIN Team ON Rush_Statline.Team_ID = Team.Team_ID
    INNER JOIN Pos ON Rush_Statline.Pos_ID = Pos.Pos_ID
    INNER JOIN Game ON Rush_Statline.Game_ID = Game.Game_ID";

if ($season !== "Total") {
    $sql .= " WHERE Season = $season";
}

$sql .= " GROUP BY Player_ID
    ORDER BY SUM(Yds) desc;";

    
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    // output data of each row
    $cur_rank = 1;
    while($row = $result->fetch_assoc()) {
        $playerid = $row["Player_ID"];
        echo "<tr>";
        echo "<td>".$cur_rank."</td>";
        echo "<td class='link'><a class='leader' href='/football-app/playerPageRush.php?playerid=" . $playerid . "'>" . $row["Player_Name"]."</td>";
        echo "<td>". $row["Poss"]."</td>";
        echo "<td>". $row["Teams"]."</td>";
        echo "<td>". $row["Gms"]."</td>";
        echo "<td>". $row["SUM(Att)"]."</td>";
        echo "<td>". $row["SUM(Yds)"]."</td>";
        echo "<td>". $row["SUM(TD)"]."</td>";
        echo "<td>". sprintf('%.2f', $row["Ypc"])."</td>";
        echo "<td>". sprintf('%.1f', $row["Apg"])."</td>";
        echo "<td>". sprintf('%.1f', $row["Ypg"])."</td>";
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
