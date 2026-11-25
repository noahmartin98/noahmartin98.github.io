<!DOCTYPE html>
<html>
<head>
<!-- DataTables CSS -->
<link rel="stylesheet" href="https://datatables.net">
<link rel="stylesheet" href="../mystyle.css">

<!-- jQuery + DataTables JS -->
<script src="https://jquery.com"></script>
<script src="https://datatables.net"></script>
<script src="../myjavascript.js"></script>
<title>Player Game Stats - Receiving</title>
</head>
<body>

<?php
require 'databaseConnect.php';
require 'functions.php';

$season = $_GET['season'] ?? 'Total';
$allSeasons = getSeasons($conn, 'rec_statline');

require 'navbar.php';
?>

<div class="flex-row" id="table-header-bar">
    <h1>Player Game Stats - Receiving</h1>
    <?php seasonDropdownForm($allSeasons, $season, null, null, false); ?>
</div>

<table id="leaders">
    <thead>
        <tr>
            <th>Rank</th>
            <th>Season</th>
            <th>Team</th>
            <th>Week</th>
            <th>Date</th>
            <th>User</th>
            <th>Player</th>
            <th>Pos</th>
            <th>Rec</th>
            <th>Yds</th>
            <th>TD</th>
            <th>Yds/Rec</th>
        </tr>
    </thead>
    <tbody>

<?php
$where_clause = ($season !== "Total") ? " WHERE game.Season = :season" : "";

$sql = "SELECT game.game_id, game.season, game.week, game.game_date,
        player.player_name,
        pos.Pos_Abbr AS poss,
        team.Abbr AS team,
        ts.team_user,
        rec_statline.Player_ID,
        Rec, Yds, TD
    FROM rec_statline
    INNER JOIN player ON rec_statline.Player_ID = player.Player_ID
    INNER JOIN team ON rec_statline.Team_ID = team.Team_ID
    INNER JOIN pos ON rec_statline.Pos_ID = pos.Pos_ID
    INNER JOIN game ON rec_statline.Game_ID = game.Game_ID
    INNER JOIN team_statline ts ON rec_statline.Game_ID = ts.game_id AND rec_statline.Team_ID = ts.team_id
    $where_clause
    ORDER BY Yds DESC, Rec DESC, TD DESC;";

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
        $ypc      = ($row["Rec"] > 0) ? ($row["Yds"] / $row["Rec"]) : 0;

        echo "<tr>";
        echo "<td>" . $cur_rank . "</td>";
        echo "<td>" . $row["season"] . "</td>";
        echo "<td>" . $row["team"] . "</td>";
        echo "<td class='link'><a class='leader' href='/api/gamePage.php?gameid=" . $gameid . "'>" . $row["week"] . "</a></td>";
        echo "<td>" . $row["game_date"] . "</td>";
        echo "<td>" . $row["team_user"] . "</td>";
        echo "<td class='link'><a class='leader' href='/api/playerPageRec.php?playerid=" . $playerid . "'>" . $row["player_name"] . "</a></td>";
        echo "<td>" . $row["poss"] . "</td>";
        echo "<td>" . $row["Rec"] . "</td>";
        echo "<td>" . $row["Yds"] . "</td>";
        echo "<td>" . $row["TD"] . "</td>";
        echo "<td>" . sprintf('%.2f', $ypc) . "</td>";
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
