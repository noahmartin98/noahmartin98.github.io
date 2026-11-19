<!DOCTYPE html>
<html>
<head>
<link rel="stylesheet" href="../mystyle.css">
</head>
<body>

<?php
require 'databaseConnect.php';
require 'functions.php';
require 'navbar.php';

$season = $_GET['season'] ?? 'Total';
$playerid = $_GET['playerid'] ?? null;
$playername = getPlayerName($conn, $playerid);
?>

<div class="flex-row margin-top-small gap-large">
    <h1><?php echo $playername?></h1>
    <h3>Career</h3>
</div>

    <table class="player-years">
        <tr>
            <th>Season</th>
            <th>Team</th>
            <th>Gms</th>
            <th>Rec</th>
            <th>Yds</th>
            <th>TD</th>
            <th>Yds/Rec</th>
            <th>Rec/Gm</th>
            <th>Yds/Gm</th>
        </tr>

<?php
$seasonGames = getPlayerSeasonGames($conn, $playerid);

// Pure sums only — no rate math in SQL!
$sql = "SELECT game.season, MIN(game.Game_ID) AS first_game,
            GROUP_CONCAT(DISTINCT team.Abbr SEPARATOR ', ') AS Teams,
            SUM(Rec) AS Rec, SUM(Yds) AS Yds, SUM(TD) AS TD
        FROM rec_statline
        INNER JOIN game ON rec_statline.Game_ID = game.Game_ID
        INNER JOIN team ON rec_statline.Team_ID = team.Team_ID
        WHERE Player_ID = :playerid
        GROUP BY game.Season
        ORDER BY game.Season ASC;";

$stmt = $conn->prepare($sql);
$stmt->bindParam(':playerid', $playerid, PDO::PARAM_INT);
$stmt->execute();

$playerSeasons = [];
$tot = ['Gms' => 0, 'Rec' => 0, 'Yds' => 0, 'TD' => 0];

if ($stmt->rowCount() > 0) {
    while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $yr  = $row["season"];
        $gms = $seasonGames[$yr] ?? 0;

        // Calculate season rate stats in PHP
        $ypc = ($row["Rec"] > 0) ? ($row["Yds"] / $row["Rec"]) : 0;
        $rpg = ($gms > 0) ? ($row["Rec"] / $gms) : 0;
        $ypg = ($gms > 0) ? ($row["Yds"] / $gms) : 0;

        $playerSeasons[$row["first_game"]] = $yr;
        $tot['Gms'] += $gms;
        foreach (['Rec', 'Yds', 'TD'] as $key) {
            $tot[$key] += $row[$key];
        }

        echo "<tr>";
        echo "<td>". $yr ."</td>";
        echo "<td>". $row["Teams"] ."</td>";
        echo "<td>". $gms ."</td>";
        echo "<td>". $row["Rec"] ."</td>";
        echo "<td>". $row["Yds"] ."</td>";
        echo "<td>". $row["TD"] ."</td>";
        echo "<td>". sprintf('%.2f', $ypc) ."</td>";
        echo "<td>". sprintf('%.1f', $rpg) ."</td>";
        echo "<td>". sprintf('%.1f', $ypg) ."</td>";
        echo "</tr>";
    }
    ksort($playerSeasons);

    // Career Total Row
    $totYpc = ($tot['Rec'] > 0) ? ($tot['Yds'] / $tot['Rec']) : 0;
    $totRpg = ($tot['Gms'] > 0) ? ($tot['Rec'] / $tot['Gms']) : 0;
    $totYpg = ($tot['Gms'] > 0) ? ($tot['Yds'] / $tot['Gms']) : 0;

    echo "<tr class='highlight'>";
    echo "<td>Total</td>";
    echo "<td></td>";
    echo "<td>". $tot['Gms'] ."</td>";
    echo "<td>". $tot['Rec'] ."</td>";
    echo "<td>". $tot['Yds'] ."</td>";
    echo "<td>". $tot['TD'] ."</td>";
    echo "<td>". sprintf('%.2f', $totYpc) ."</td>";
    echo "<td>". sprintf('%.1f', $totRpg) ."</td>";
    echo "<td>". sprintf('%.1f', $totYpg) ."</td>";
    echo "</tr>";
} else {
    echo "0 results";
}
?>

    </table>

<div class="flex-row margin-top-large">
    <h3>Game Log</h3>
    <?php seasonDropdownForm($playerSeasons, $season, 'playerid', $playerid); ?>
</div>

    <table class="player">
        <tr>
            <th>Season</th>
            <th>Team</th>
            <th>Week</th>
            <th>Date</th>
            <th>User</th>
            <th></th>
            <th>Opp</th>
            <th>Rec</th>
            <th>Yds</th>
            <th>TD</th>
        </tr>


<?php

if ($season !== "Total") {
    $where_clause = "AND game.Season = :season";
} else {
    $where_clause = "";
}

$sql = "SELECT game.season, tm.Abbr AS Tm, game.week, game.game_date, t1.team_user, 
        CASE
            WHEN (t1.home_away = 'Home')
                THEN 'vs.'
            WHEN (t1.home_away = 'Away')
                THEN '@'
            ELSE ''
        END AS loc,
    opp.Abbr AS Opp, Rec, Yds, TD
    FROM rec_statline
    INNER JOIN game ON rec_statline.game_id = game.game_id
    JOIN team_statline t1
        ON t1.game_id = rec_statline.game_id
        AND t1.team_id = rec_statline.team_id
    JOIN team_statline t2
        ON t2.game_id = rec_statline.game_id
        AND t2.team_id <> rec_statline.team_id
    JOIN team tm ON t1.team_id = tm.team_id
    JOIN team opp ON t2.team_id = opp.team_id
    WHERE Player_ID = :playerid $where_clause
    ORDER BY game.Game_ID ASC;";

$stmt = $conn->prepare($sql);
$stmt->bindParam(':playerid', $playerid, PDO::PARAM_INT);
if ($season !== "Total") {
    $stmt->bindParam(':season', $season, PDO::PARAM_INT);
}
$stmt->execute();

if ($stmt->rowCount() > 0) {
    // output data of each row
    while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "<tr>";
        echo "<td>". $row["season"]."</td>";
        echo "<td>". $row["Tm"]."</td>";
        echo "<td>". $row["week"]."</td>";
        echo "<td>". $row["game_date"]."</td>";
        echo "<td>". $row["team_user"]."</td>";
        echo "<td>". $row["loc"]."</td>";
        echo "<td>". $row["Opp"]."</td>";
        echo "<td>". $row["Rec"]."</td>";
        echo "<td>". $row["Yds"]."</td>";
        echo "<td>". $row["TD"]."</td>";
        echo "</tr>";
    }
} else {
    echo "0 results";
}

$conn = null;
?>

  </table>  




</body>
</html>
