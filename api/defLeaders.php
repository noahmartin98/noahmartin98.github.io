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
    <h1>Defensive Leaders</h1>

    <form method="get" action="defLeaders.php">
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
            <th>Sack</th>
            <th>INT</th>
            <th>FF</th>
            <th>FR</th>
            <th>TD</th>
            <th>TFL</th>
            <th>PDEF</th>
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
        def_statline.Player_ID,
        SUM(Sack) AS Sack, SUM(INTR) AS INTR, SUM(FF) AS FF, SUM(FR) AS FR,
        SUM(TD) AS TD, SUM(TFL) AS TFL, SUM(PDEF) AS PDEF
    FROM def_statline
    INNER JOIN player ON def_statline.Player_ID = player.Player_ID
    INNER JOIN team ON def_statline.Team_ID = team.Team_ID
    INNER JOIN pos ON def_statline.Pos_ID = pos.Pos_ID
    INNER JOIN game ON def_statline.Game_ID = game.Game_ID
    $where_clause
    GROUP BY def_statline.Player_ID $group_by_season
    ORDER BY SUM(Sack) DESC;";

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

        echo "<tr>";
        echo "<td>".$cur_rank."</td>";
        echo "<td class='link'><a class='leader' href='/api/playerPageDef.php?playerid=" . $playerid . "'>" . $row["Player_Name"] . "</a></td>";
        echo "<td>". $row["Poss"]."</td>";
        echo "<td>". $row["Teams"]."</td>";
        echo "<td>". $gms."</td>";
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
}

$conn = null;
?>
    </tbody>
</table>

</body>
</html>
