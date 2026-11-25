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

// Fetch all seasons that have defensive stats, ordered by play order
$allSeasons = $conn->query("
    SELECT game.Season, MIN(game.Game_ID) AS first_game
    FROM def_statline
    INNER JOIN game ON def_statline.Game_ID = game.Game_ID
    GROUP BY game.Season
    ORDER BY first_game ASC
")->fetchAll(PDO::FETCH_COLUMN);

require 'navbar.php';
?>

<div class="flex-row">
    <h1>Player Game Stats - Defense</h1>
    <?php seasonDropdownForm($allSeasons, $season); ?>
</div>

<table id="leaders">
    <thead>
        <tr>
            <th>Rank</th>
            <th>Season</th>
            <th>Week</th>
            <th>Date</th>
            <th>Player</th>
            <th>Pos</th>
            <th>Team</th>
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
$where_clause = ($season !== "Total") ? " WHERE game.Season = :season" : "";

$sql = "SELECT game.game_id, game.season, game.week, game.game_date,
        player.player_name,
        pos.Pos_Abbr AS poss,
        team.Abbr AS team,
        def_statline.Player_ID,
        sack, intr, ff, fr, td, tfl, pdef
    FROM def_statline
    INNER JOIN player ON def_statline.Player_ID = player.Player_ID
    INNER JOIN team ON def_statline.Team_ID = team.Team_ID
    INNER JOIN pos ON def_statline.Pos_ID = pos.Pos_ID
    INNER JOIN game ON def_statline.Game_ID = game.Game_ID
    $where_clause
    ORDER BY sack DESC, intr DESC, tfl DESC;";

$stmt = $conn->prepare($sql);

if ($season !== "Total") {
    $stmt->bindParam(':season', $season, PDO::PARAM_INT);
}

$stmt->execute();

if ($stmt->rowCount() > 0) {
    $cur_rank = 1;
    while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $playerid = $row["Player_ID"];
        $gameid   = $row["game_id"];

        echo "<tr>";
        echo "<td>" . $cur_rank . "</td>";
        echo "<td>" . $row["season"] . "</td>";
        echo "<td class='link'><a class='leader' href='/api/gamePage.php?gameid=" . $gameid . "'>" . $row["week"] . "</a></td>";
        echo "<td>" . $row["game_date"] . "</td>";
        echo "<td class='link'><a class='leader' href='/api/playerPageDef.php?playerid=" . $playerid . "'>" . $row["player_name"] . "</a></td>";
        echo "<td>" . $row["poss"] . "</td>";
        echo "<td>" . $row["team"] . "</td>";
        echo "<td>" . $row["sack"] . "</td>";
        echo "<td>" . $row["intr"] . "</td>";
        echo "<td>" . $row["ff"] . "</td>";
        echo "<td>" . $row["fr"] . "</td>";
        echo "<td>" . $row["td"] . "</td>";
        echo "<td>" . $row["tfl"] . "</td>";
        echo "<td>" . $row["pdef"] . "</td>";
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
