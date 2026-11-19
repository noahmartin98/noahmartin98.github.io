<!DOCTYPE html>
<html>
<head>

<!-- DataTables CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
<link rel="stylesheet" href="../mystyle.css">
	
<!-- jQuery + DataTables JS -->
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="../myjavascript.js"></script>
</head>
<body>

<?php
require 'databaseConnect.php';
require 'functions.php';

$season = $_GET['season'] ?? 'Total';

require 'navbar.php';
?>

<div class="flex-row">
    <h1>Rushing Leaders</h1>

    <form method="get" action="rushingLeaders.php">
        <select class="season-select" name="season" onchange="this.form.submit()">
            <?php include 'seasonOptions.php'; ?>
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
    <tbody>

<?php
$allGames = getAllPlayersGames($conn, $season);

if ($season !== "Total") {
    $where_clause = " WHERE game.Season = :season";
    $group_by_season = ", game.Season";
} else {
    $where_clause = "";
    $group_by_season = "";
}

$sql = "SELECT player.Player_Name, 
        GROUP_CONCAT(DISTINCT pos.Pos_Abbr SEPARATOR ', ') AS Poss,
        GROUP_CONCAT(DISTINCT team.Abbr SEPARATOR ', ') AS Teams,
        rush_statline.Player_ID, 
        SUM(Att) AS Att, SUM(Yds) AS Yds, SUM(TD) AS TD
    FROM rush_statline
    INNER JOIN player ON rush_statline.Player_ID = player.Player_ID
    INNER JOIN team ON rush_statline.Team_ID = team.Team_ID
    INNER JOIN pos ON rush_statline.Pos_ID = pos.Pos_ID
    INNER JOIN game ON rush_statline.Game_ID = game.Game_ID
    $where_clause
    GROUP BY rush_statline.Player_ID $group_by_season
    ORDER BY SUM(Yds) DESC;";

$stmt = $conn->prepare($sql);

if ($season !== "Total") {
    $stmt->bindParam(':season', $season, PDO::PARAM_INT);
}

$stmt->execute();

if ($stmt->rowCount() > 0) {
    $cur_rank = 1;
    while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $playerid = $row["Player_ID"];
        $gms      = $allGames[$playerid] ?? 0;

        // Calculate rate stats in PHP
        $ypc = ($row["Att"] > 0) ? ($row["Yds"] / $row["Att"]) : 0;
        $apg = ($gms > 0) ? ($row["Att"] / $gms) : 0;
        $ypg = ($gms > 0) ? ($row["Yds"] / $gms) : 0;

        echo "<tr>";
        echo "<td>".$cur_rank."</td>";
        echo "<td class='link'><a class='leader' href='/api/playerPageRush.php?playerid=" . $playerid . "'>" . $row["Player_Name"]."</a></td>";
        echo "<td>". $row["Poss"]."</td>";
        echo "<td>". $row["Teams"]."</td>";
        echo "<td>". $gms."</td>";
        echo "<td>". $row["Att"]."</td>";
        echo "<td>". $row["Yds"]."</td>";
        echo "<td>". $row["TD"]."</td>";
        echo "<td>". sprintf('%.2f', $ypc)."</td>";
        echo "<td>". sprintf('%.1f', $apg)."</td>";
        echo "<td>". sprintf('%.1f', $ypg)."</td>";
        echo "</tr>";
        $cur_rank++;
    }
}

$conn = null;
?>
    </tbody>
</table>

</body>
</html>
