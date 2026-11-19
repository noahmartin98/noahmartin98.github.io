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
            <th>Att</th>
            <th>Yds</th>
            <th>TD</th>
            <th>Yds/Att</th>
            <th>Att/Gm</th>
            <th>Yds/Gm</th>
        </tr>

<?php

$sql = "SELECT game.season, MIN(game.Game_ID) AS first_game,
            GROUP_CONCAT(DISTINCT team.Abbr SEPARATOR ', ') AS Teams, 
            COUNT(*) as Gms, SUM(Att) as Att, SUM(Yds) as Yds, SUM(TD) as TD,
            (SUM(Yds)/NULLIF(SUM(Att), 0)) AS Ypc,
            (SUM(Att)/count(*)) AS Apg,
            (SUM(Yds)/count(*)) AS Ypg
        FROM rush_statline
        INNER JOIN team ON rush_statline.Team_ID = team.Team_ID
        INNER JOIN pos ON rush_statline.Pos_ID = pos.Pos_ID
        INNER JOIN game ON rush_statline.Game_ID = game.Game_ID
        WHERE Player_ID = :playerid
        GROUP BY game.Season
        ORDER BY game.Season ASC;
        ";

$stmt = $conn->prepare($sql);
$stmt->bindParam(':playerid', $playerid, PDO::PARAM_INT);
$stmt->execute();

$playerSeasons = [];
$tot = ['Gms' => 0, 'Att' => 0, 'Yds' => 0, 'TD' => 0];

if ($stmt->rowCount() > 0) {
    while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $playerSeasons[$row["first_game"]] = $row["season"];
        foreach ($tot as $key => $val) $tot[$key] += $row[$key];

        echo "<tr>";
        echo "<td>". $row["season"]."</td>";
        echo "<td>". $row["Teams"]."</td>";
        echo "<td>". $row["Gms"]."</td>";
        echo "<td>". $row["Att"]."</td>";
        echo "<td>". $row["Yds"]."</td>";
        echo "<td>". $row["TD"]."</td>";
        echo "<td>". sprintf('%.2f', $row["Ypc"])."</td>";
        echo "<td>". sprintf('%.2f', $row["Apg"])."</td>";
        echo "<td>". sprintf('%.1f', $row["Ypg"])."</td>";
        echo "</tr>";
    }
    ksort($playerSeasons);

    // Calculate career rate stats
    $totYpc = ($tot['Att'] > 0) ? ($tot['Yds'] / $tot['Att']) : 0;
    $totApg = ($tot['Gms'] > 0) ? ($tot['Att'] / $tot['Gms']) : 0;
    $totYpg = ($tot['Gms'] > 0) ? ($tot['Yds'] / $tot['Gms']) : 0;

    // Output the Career Total row
    echo "<tr class='highlight'>";
    echo "<td>Total</td>";
    echo "<td></td>";
    echo "<td>". $tot['Gms'] ."</td>";
    echo "<td>". $tot['Att'] ."</td>";
    echo "<td>". $tot['Yds'] ."</td>";
    echo "<td>". $tot['TD'] ."</td>";
    echo "<td>". sprintf('%.2f', $totYpc) ."</td>";
    echo "<td>". sprintf('%.2f', $totApg) ."</td>";
    echo "<td>". sprintf('%.1f', $totYpg) ."</td>";
    echo "</tr>";
} else {
    echo "0 results";
}
?>

    </table>

<div class="flex-row margin-top-large">
    <h3>Game Log</h3>
    <?php playerSeasonForm($playerSeasons, $season, $playerid); ?>
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
            <th>Att</th>
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
    opp.Abbr AS Opp, Att, Yds, TD
    FROM rush_statline
    INNER JOIN game ON rush_statline.game_ID = game.game_ID
    JOIN team_statline t1
        ON t1.game_id = rush_statline.game_id
        AND t1.team_id = rush_statline.team_id
    JOIN team_statline t2
        ON t2.game_id = rush_statline.game_id
        AND t2.team_id <> rush_statline.team_id
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
        echo "<td>". $row["Att"]."</td>";
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
