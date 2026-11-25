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

$allSeasons = getSeasons($conn, 'pass_statline');

require 'navbar.php';
?>

<div class="flex-row" id="table-header-bar">
    <h1>Passing Leaders</h1>
    <?php seasonDropdownForm($allSeasons, $season, null, null, false); ?>
</div>

<table id="leaders">
    <thead>
        <tr>
            <th>Rank</th>
            <th>Player</th>
            <th>Position</th>
            <th>Team</th>
            <th>Gms</th>
            <th>Cmp</th>
            <th>Att</th>
            <th>Yds</th>
            <th>TD</th>
            <th>INT</th>
            <th>Comp%</th>
            <th>Yds/Gm</th>
            <th>Yds/Att</th>
            <th>TD/INT</th>
        </tr>
    </thead>
    <tbody>

<?php
// 1. Fetch true team games played for all players in one query
$allGames = getAllPlayersGames($conn, $season);

if ($season !== "Total") {
    $where_clause = " WHERE game.Season = :season";
    $group_by_season = ", game.Season";
} else {
    $where_clause = "";
    $group_by_season = "";
}

// 2. Pure sums only — all rate stats calculated cleanly in PHP
$sql = "SELECT player.Player_Name, 
        GROUP_CONCAT(DISTINCT pos.Pos_Abbr SEPARATOR ', ') AS Poss, 
        GROUP_CONCAT(DISTINCT team.Abbr SEPARATOR ', ') AS Teams, 
        pass_statline.Player_ID, 
        SUM(Comp) AS Comp, SUM(Att) AS Att, SUM(Yds) AS Yds, SUM(TD) AS TD, SUM(INTR) AS INTR
    FROM pass_statline
    INNER JOIN player ON pass_statline.Player_ID = player.Player_ID
    INNER JOIN team ON pass_statline.Team_ID = team.Team_ID
    INNER JOIN pos ON pass_statline.Pos_ID = pos.Pos_ID
    INNER JOIN game ON pass_statline.Game_ID = game.Game_ID
    $where_clause
    GROUP BY pass_statline.Player_ID $group_by_season
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
        $compPct = ($row["Att"] > 0) ? ($row["Comp"] / $row["Att"]) * 100 : 0;
        $ypg     = ($gms > 0) ? ($row["Yds"] / $gms) : 0;
        $ypa     = ($row["Att"] > 0) ? ($row["Yds"] / $row["Att"]) : 0;
        $tdInt   = ($row["INTR"] > 0) ? sprintf('%.2f', $row["TD"] / $row["INTR"]) : '—';

        echo "<tr>";
        echo "<td>".$cur_rank."</td>";
        echo "<td class='link'><a class='leader' href='/api/playerPagePass.php?playerid=" . $playerid . "'>" . $row["Player_Name"] . "</a></td>";
        echo "<td>". $row["Poss"]."</td>";
        echo "<td>". $row["Teams"]."</td>";
        echo "<td>". $gms."</td>";
        echo "<td>". $row["Comp"]."</td>";
        echo "<td>". $row["Att"]."</td>";
        echo "<td>". $row["Yds"]."</td>";
        echo "<td>". $row["TD"]."</td>";
        echo "<td>". $row["INTR"]."</td>";
        echo "<td>". sprintf('%.1f%%', $compPct)."</td>";
        echo "<td>". sprintf('%.1f', $ypg)."</td>";
        echo "<td>". sprintf('%.2f', $ypa)."</td>";
        echo "<td>". $tdInt."</td>";
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
