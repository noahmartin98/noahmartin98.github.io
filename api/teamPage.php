<!DOCTYPE html>
<html>
<head>
<link rel="stylesheet" href="../mystyle.css">
</head>
<body>

<?php
require 'databaseConnect.php';
require 'functions.php';

$teamid = $_GET['teamid'] ?? null;
$season = $_GET['season'] ?? 'Total';

$sql = "SELECT Team_Name FROM team WHERE Team_ID = :teamid;";
$stmt = $conn->prepare($sql);
$stmt->bindParam(':teamid', $teamid, PDO::PARAM_INT);
$stmt->execute();

if ($stmt->rowCount() > 0) {
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $teamname = $row["Team_Name"];
} else {
    $teamname = "Team Not Found";
}

// Fetch only the seasons this team has played in, ordered by play order (Game_ID)
$teamSeasons = [];
$seasonSql = "SELECT game.Season, MIN(game.Game_ID) AS first_game
              FROM team_statline
              INNER JOIN game ON team_statline.game_id = game.game_id
              WHERE team_statline.team_id = :teamid
              GROUP BY game.Season
              ORDER BY first_game ASC;";
$seasonStmt = $conn->prepare($seasonSql);
$seasonStmt->bindParam(':teamid', $teamid, PDO::PARAM_INT);
$seasonStmt->execute();

while ($sRow = $seasonStmt->fetch(PDO::FETCH_ASSOC)) {
    $teamSeasons[] = $sRow["Season"];
}

require 'navbar.php';

$season_clause = ($season !== "Total") ? "AND game.Season = :season" : "";

// Fetch team's games first so we can count W-L-T in PHP without an extra query
$gamesSql = "SELECT game.game_id, game.season, game.week, game.game_date, t1.home_away, t1.team_user,
        t1.score AS team_score,
        opp.Team_Name AS opponent,
        t2.score AS opp_score,
        CASE
            WHEN (t1.score > t2.score) THEN 'W'
            WHEN (t2.score > t1.score) THEN 'L'
            ELSE 'T'
        END AS result
    FROM team_statline t1
    JOIN team_statline t2
        ON t1.game_id = t2.game_id
        AND t1.team_id <> t2.team_id
    JOIN game ON t1.game_id = game.game_id
    JOIN team opp ON t2.team_id = opp.team_id
    WHERE t1.team_id = :teamid $season_clause
    ORDER BY game.game_id ASC;";

$gamesStmt = $conn->prepare($gamesSql);
$gamesStmt->bindParam(':teamid', $teamid, PDO::PARAM_INT);
if ($season !== "Total") {
    $gamesStmt->bindParam(':season', $season, PDO::PARAM_INT);
}
$gamesStmt->execute();
$games = $gamesStmt->fetchAll(PDO::FETCH_ASSOC);

// Count W-L-T in PHP
$wins = 0; $losses = 0; $ties = 0;
foreach ($games as $g) {
    if ($g['result'] === 'W') $wins++;
    elseif ($g['result'] === 'L') $losses++;
    else $ties++;
}
$recordText = ($ties > 0) ? "$wins-$losses-$ties" : "$wins-$losses";

// Fetch true team games played for all players on this roster
$teamPlayerGames = getAllPlayersGames($conn, $season);
?>

<!--Header with team name, record, season select, and franchise history link-->
<div class="flex-row margin-top-small gap-large">
    <h1><?php echo htmlspecialchars($teamname); ?></h1>
    <h3>(<?php echo $recordText; ?>)</h3>
    <?php seasonDropdownForm($teamSeasons, $season, 'teamid', $teamid, false); ?>
    <a class="home" href="/api/teamSummary.php?teamid=<?php echo urlencode($teamid); ?>">Franchise History</a>
</div>

<h3>Games</h3>
    <table>
        <tr>
            <?php if ($season === "Total") echo "<th>Season</th>"; ?>
            <th>Week</th>
            <th>Date</th>
            <th>User</th>
            <th>H/A</th>
            <th>Opponent</th>
            <th>Score</th>
            <th>Opp Score</th>
            <th>Result</th>
            <th></th>
        </tr>

<?php
if (!empty($games)) {
    foreach ($games as $row) {
        echo "<tr>";
        if ($season === "Total") echo "<td>". $row["season"]."</td>";
        echo "<td>". $row["week"]."</td>";
        echo "<td>". $row["game_date"]."</td>";
        echo "<td>". $row["team_user"]."</td>";
        echo "<td>". $row["home_away"]."</td>";
        echo "<td>". $row["opponent"]."</td>";
        echo "<td>". $row["team_score"]."</td>";
        echo "<td>". $row["opp_score"]."</td>";
        echo "<td>". $row["result"]."</td>";
        echo "<td class='link'><a class='leader' href='/api/gamePage.php?gameid=" . $row["game_id"] . "'>Box Score</a></td>";
        echo "</tr>";
    }
} else {
    echo "0 results";
}
?>        
    </table> 


<h3>Passing</h3>
<table class="player-passing">
        <tr>
            <th>Player</th>
            <th>Position</th>
            <th>Gms</th>
            <th>Cmp</th>
            <th>Att</th>
            <th>Yds</th>
            <th>TD</th>
            <th>INT</th>
        </tr>

<?php
$sql = "SELECT player.Player_Name, 
    GROUP_CONCAT(DISTINCT pos.Pos_Abbr SEPARATOR ', ') AS Poss,
    pass_statline.Player_ID,
    SUM(Comp) AS Comp, SUM(Att) AS Att, SUM(Yds) AS Yds, SUM(TD) AS TD, SUM(INTR) AS INTR
    FROM pass_statline
    INNER JOIN player ON pass_statline.Player_ID = player.Player_ID
    INNER JOIN team ON pass_statline.Team_ID = team.Team_ID
    INNER JOIN pos ON pass_statline.Pos_ID = pos.Pos_ID
    INNER JOIN game ON pass_statline.Game_ID = game.Game_ID
    WHERE team.Team_ID = :teamid $season_clause
    GROUP BY pass_statline.Player_ID, player.Player_Name
    ORDER BY SUM(Yds) DESC;";

$stmt = $conn->prepare($sql);
$stmt->bindParam(':teamid', $teamid, PDO::PARAM_INT);
if ($season !== "Total") {
    $stmt->bindParam(':season', $season, PDO::PARAM_INT);
}
$stmt->execute();

if ($stmt->rowCount() > 0) {
    while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $playerid = $row["Player_ID"];
        $gms      = $teamPlayerGames[$playerid] ?? 0;

        echo "<tr>";
        echo "<td class='link'><a class='leader' href='/api/playerPagePass.php?playerid=" . $playerid . "'>" . $row["Player_Name"]."</a></td>";
        echo "<td>". $row["Poss"]."</td>";
        echo "<td>". $gms."</td>";
        echo "<td>". $row["Comp"]."</td>";
        echo "<td>". $row["Att"]."</td>";
        echo "<td>". $row["Yds"]."</td>";
        echo "<td>". $row["TD"]."</td>";
        echo "<td>". $row["INTR"]."</td>";
        echo "</tr>";
    }
} else {
    echo "0 results";
}
?>
</table>


<h3>Rushing</h3>
<table class="player">
        <tr>
            <th>Player</th>
            <th>Position</th>
            <th>Gms</th>
            <th>Att</th>
            <th>Yds</th>
            <th>TD</th>
        </tr>

<?php
$sql = "SELECT player.Player_Name, GROUP_CONCAT(DISTINCT pos.Pos_Abbr SEPARATOR ', ') AS Poss,
    rush_statline.Player_ID,
    SUM(Att) AS Att, SUM(Yds) AS Yds, SUM(TD) AS TD
    FROM rush_statline
    INNER JOIN player ON rush_statline.Player_ID = player.Player_ID
    INNER JOIN team ON rush_statline.Team_ID = team.Team_ID
    INNER JOIN pos ON rush_statline.Pos_ID = pos.Pos_ID
    INNER JOIN game ON rush_statline.Game_ID = game.Game_ID
    WHERE team.Team_ID = :teamid $season_clause
    GROUP BY rush_statline.Player_ID, player.Player_Name
    ORDER BY SUM(Yds) DESC;";

$stmt = $conn->prepare($sql);
$stmt->bindParam(':teamid', $teamid, PDO::PARAM_INT);
if ($season !== "Total") {
    $stmt->bindParam(':season', $season, PDO::PARAM_INT);
}
$stmt->execute();

if ($stmt->rowCount() > 0) {
    while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $playerid = $row["Player_ID"];
        $gms      = $teamPlayerGames[$playerid] ?? 0;

        echo "<tr>";
        echo "<td class='link'><a class='leader' href='/api/playerPageRush.php?playerid=" . $playerid . "'>" . $row["Player_Name"]."</a></td>";
        echo "<td>". $row["Poss"]."</td>";
        echo "<td>". $gms."</td>";
        echo "<td>". $row["Att"]."</td>";
        echo "<td>". $row["Yds"]."</td>";
        echo "<td>". $row["TD"]."</td>";
        echo "</tr>";
    }
} else {
    echo "0 results";
}
?>
</table>


<h3>Receiving</h3>
<table class="player">
        <tr>
            <th>Player</th>
            <th>Position</th>
            <th>Gms</th>
            <th>Rec</th>
            <th>Yds</th>
            <th>TD</th>
        </tr>

<?php
$sql = "SELECT player.Player_Name, GROUP_CONCAT(DISTINCT pos.Pos_Abbr SEPARATOR ', ') AS Poss,
    rec_statline.Player_ID,
    SUM(Rec) AS Rec, SUM(Yds) AS Yds, SUM(TD) AS TD
    FROM rec_statline
    INNER JOIN player ON rec_statline.Player_ID = player.Player_ID
    INNER JOIN team ON rec_statline.Team_ID = team.Team_ID
    INNER JOIN pos ON rec_statline.Pos_ID = pos.Pos_ID
    INNER JOIN game ON rec_statline.Game_ID = game.Game_ID
    WHERE team.Team_ID = :teamid $season_clause
    GROUP BY rec_statline.Player_ID, player.Player_Name
    ORDER BY SUM(Yds) DESC;";

$stmt = $conn->prepare($sql);
$stmt->bindParam(':teamid', $teamid, PDO::PARAM_INT);
if ($season !== "Total") {
    $stmt->bindParam(':season', $season, PDO::PARAM_INT);
}
$stmt->execute();

if ($stmt->rowCount() > 0) {
    while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $playerid = $row["Player_ID"];
        $gms      = $teamPlayerGames[$playerid] ?? 0;

        echo "<tr>";
        echo "<td class='link'><a class='leader' href='/api/playerPageRec.php?playerid=" . $playerid . "'>" . $row["Player_Name"]."</a></td>";
        echo "<td>". $row["Poss"]."</td>";
        echo "<td>". $gms."</td>";
        echo "<td>". $row["Rec"]."</td>";
        echo "<td>". $row["Yds"]."</td>";
        echo "<td>". $row["TD"]."</td>";
        echo "</tr>";
    }
} else {
    echo "0 results";
}
?>
</table>


<h3>Defense</h3>
<table class="player-passing">
        <tr>
            <th>Player</th>
            <th>Position</th>
            <th>Gms</th>
            <th>Sack</th>
            <th>INT</th>
            <th>FF</th>
            <th>FR</th>
            <th>TD</th>
            <th>TFL</th>
            <th>PDEF</th>
        </tr>

<?php
$sql = "SELECT player.Player_Name, GROUP_CONCAT(DISTINCT pos.Pos_Abbr SEPARATOR ', ') AS Poss,
            def_statline.Player_ID,
            SUM(Sack) AS Sack, SUM(INTR) AS INTR, SUM(FF) AS FF, SUM(FR) AS FR,
            SUM(TD) AS TD, SUM(TFL) AS TFL, SUM(PDEF) AS PDEF
            FROM def_statline
            INNER JOIN player ON def_statline.Player_ID = player.Player_ID
            INNER JOIN team ON def_statline.Team_ID = team.Team_ID
            INNER JOIN pos ON def_statline.Pos_ID = pos.Pos_ID
            INNER JOIN game ON def_statline.Game_ID = game.Game_ID
            WHERE team.Team_ID = :teamid $season_clause
            GROUP BY def_statline.Player_ID, player.Player_Name
            ORDER BY SUM(Sack) DESC;";

    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':teamid', $teamid, PDO::PARAM_INT);
    if ($season !== "Total") {
        $stmt->bindParam(':season', $season, PDO::PARAM_INT);
    }
    $stmt->execute();
    if ($stmt->rowCount() > 0) {
        while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $playerid = $row["Player_ID"];
    $gms = $teamPlayerGames[$playerid] ?? 0;
    echo "";
    echo "" . $row["Player_Name"]."";
    echo "". $row["Poss"]."";
    echo "". $gms."";
    echo "". $row["Sack"]."";
    echo "". $row["INTR"]."";
    echo "". $row["FF"]."";
    echo "". $row["FR"]."";
    echo "". $row["TD"]."";
    echo "". $row["TFL"]."";
    echo "". $row["PDEF"]."";
    echo "";
}
} else {
echo "0 results";
}
$conn = null;
?>
