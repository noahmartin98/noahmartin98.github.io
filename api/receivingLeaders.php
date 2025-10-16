<!DOCTYPE html>
<html>
<head>
<link rel="stylesheet" href="../mystyle.css">
<!-- DataTables CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
<!-- jQuery + DataTables JS -->
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

<script src="../myjavascript.js"></script>
</head>
<body>

<?php
require 'databaseConnect.php';

$season = $_GET['season'] ?? '2015';

?>

<nav>
    <a href="/../home.html" class="nav">Back to home</a>
</nav>

<div class="header-container">

    <h1>Receiving Leaders</h1>


    <form method="get" action="receivingLeaders.php">
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
            <th>Rec</th>
            <th>Yds</th>
            <th>TD</th>
            <th>Yds/Rec</th>
            <th>Rec/Gm</th>
            <th>Yds/Gm</th>
        </tr>
    </thead>

<?php

$sql = "SELECT game.Season, player.Player_Name, GROUP_CONCAT(DISTINCT pos.Pos_Abbr SEPARATOR ', ') AS Poss, GROUP_CONCAT(DISTINCT team.Abbr SEPARATOR ', ') AS Teams,
	rec_statline.Player_ID, COUNT(*) as Gms, SUM(Rec), SUM(Yds), SUM(TD),
	(SUM(Yds)/SUM(Rec)) AS Ypc,
    (SUM(Rec)/count(*)) AS Rpg,
    (SUM(Yds)/count(*)) AS Ypg
    FROM rec_statline
    INNER JOIN player ON rec_statline.Player_ID = player.Player_ID
    INNER JOIN team ON rec_statline.Team_ID = team.Team_ID
    INNER JOIN pos ON rec_statline.Pos_ID = pos.Pos_ID
    INNER JOIN Game ON rec_statline.Game_ID = game.Game_ID";

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
        echo "<td  class='link'><a class='leader' href='/api/playerPageRec.php?playerid=" . $playerid . "'>" . $row["Player_Name"]."</td>";
        echo "<td>". $row["Poss"]."</td>";
        echo "<td>". $row["Teams"]."</td>";
        echo "<td>". $row["Gms"]."</td>";
        echo "<td>". $row["SUM(Rec)"]."</td>";
        echo "<td>". $row["SUM(Yds)"]."</td>";
        echo "<td>". $row["SUM(TD)"]."</td>";
        echo "<td>". sprintf('%.2f', $row["Ypc"])."</td>";
        echo "<td>". sprintf('%.2f', $row["Rpg"])."</td>";
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
