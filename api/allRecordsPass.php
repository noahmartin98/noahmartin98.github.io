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
<title>Single Game Stats - Defense</title>
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
    <h1>Single Game Stats - Passing</h1>
    <?php seasonDropdownForm($allSeasons, $season, null, null, false); ?>
</div>

<table id="leaders">
    <thead>
        <tr>
            <th>Rank</th>
            <th>Season</th>
            <th>Week</th>
            <th>Date</th>
            <th>User</th>
            <th>OT</th>
            <th>Player</th>
            <th>Pos</th>
            <th>Team</th>
            <th>Cmp</th>
            <th>Att</th>
            <th>Yds</th>
            <th>TD</th>
            <th>INT</th>
            <th>Comp%</th>
            <th>Yds/Att</th>
            <th>TD/INT</th>
        </tr>
    </thead>
    <tbody>

<?php
$where_clause = ($season !== "Total") ? " WHERE game.Season = :season" : "";

$sql = "SELECT game.game_id, game.season, game.week, game.game_date, game.ot,
        player.player_name,
        pos.Pos_Abbr AS poss,
        team.Abbr AS team,
        ts.team_user,
        pass_statline.Player_ID,
        Comp, Att, Yds, TD, INTR
    FROM pass_statline
    INNER JOIN player ON pass_statline.Player_ID = player.Player_ID
    INNER JOIN team ON pass_statline.Team_ID = team.Team_ID
    INNER JOIN pos ON pass_statline.Pos_ID = pos.Pos_ID
    INNER JOIN game ON pass_statline.Game_ID = game.Game_ID
    INNER JOIN team_statline ts ON pass_statline.Game_ID = ts.game_id AND pass_statline.Team_ID = ts.team_id
    $where_clause
    ORDER BY Yds DESC, TD DESC;";

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

        $compPct = ($row["Att"] > 0) ? ($row["Comp"] / $row["Att"]) * 100 : 0;
        $ypa     = ($row["Att"] > 0) ? ($row["Yds"] / $row["Att"]) : 0;
        $tdInt   = ($row["INTR"] > 0) ? sprintf('%.2f', $row["TD"] / $row["INTR"]) : '—';

        echo "<tr>";
        echo "<td>" . $cur_rank . "</td>";
        echo "<td>" . $row["season"] . "</td>";
        echo "<td class='link'><a class='leader' href='/api/gamePage.php?gameid=" . $gameid . "'>" . $row["week"] . "</a></td>";
        echo "<td>" . $row["game_date"] . "</td>";
        echo "<td>" . $row["team_user"] . "</td>";
        echo "<td>" . $row["ot"] . "</td>";
        echo "<td class='link'><a class='leader' href='/api/playerPagePass.php?playerid=" . $playerid . "'>" . $row["player_name"] . "</a></td>";
        echo "<td>" . $row["poss"] . "</td>";
        echo "<td>" . $row["team"] . "</td>";
        echo "<td>" . $row["Comp"] . "</td>";
        echo "<td>" . $row["Att"] . "</td>";
        echo "<td>" . $row["Yds"] . "</td>";
        echo "<td>" . $row["TD"] . "</td>";
        echo "<td>" . $row["INTR"] . "</td>";
        echo "<td>" . sprintf('%.1f%%', $compPct) . "</td>";
        echo "<td>" . sprintf('%.2f', $ypa) . "</td>";
        echo "<td>" . $tdInt . "</td>";
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
