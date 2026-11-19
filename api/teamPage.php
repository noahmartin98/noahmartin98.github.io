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

if ($season !== "Total") {
    $season_clause = "AND game.Season = :season";
} else {
    $season_clause = "";
}
?>

    
<!--Header with team name and season select-->
<div class="flex-row margin-top-small gap-large">
    <h1><?php echo htmlspecialchars($teamname); ?></h1>
    <?php seasonDropdownForm($teamSeasons, $season, 'teamid', $teamid); ?>
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
$sql = "SELECT game.game_id, game.season, game.week, game.game_date, t1.home_away, t1.team_user,
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

$stmt = $conn->prepare($sql);
$stmt->bindParam(':teamid', $teamid, PDO::PARAM_INT);
if ($season !== "Total") {
    $stmt->bindParam(':season', $season, PDO::PARAM_INT);
}
$stmt->execute();

if ($stmt->rowCount() > 0) {
    while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
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
    COUNT(*) as Gms, SUM(Comp), SUM(Att), SUM(Yds), SUM(TD), SUM(INTR)
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
        echo "<tr>";
        echo "<td class='link'><a class='leader' href='/api/playerPagePass.php?playerid=" . $playerid . "'>" . $row["Player_Name"]."</a></td>";
        echo "<td>". $row["Poss"]."</td>";
        echo "<td>". $row["Gms"]."</td>";
        echo "<td>". $row["SUM(Comp)"]."</td>";
        echo "<td>". $row["SUM(Att)"]."</td>";
        echo "<td>". $row["SUM(Yds)"]."</td>";
        echo "<td>". $row["SUM(TD)"]."</td>";
        echo "<td>". $row["SUM(INTR)"]."</td>";
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
    COUNT(*) as Gms, SUM(Att), SUM(Yds), SUM(TD)
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
        echo "<tr>";
        echo "<td class='link'><a class='leader' href='/api/playerPageRush.php?playerid=" . $playerid . "'>" . $row["Player_Name"]."</a></td>";
        echo "<td>". $row["Poss"]."</td>";
        echo "<td>". $row["Gms"]."</td>";
        echo "<td>". $row["SUM(Att)"]."</td>";
        echo "<td>". $row["SUM(Yds)"]."</td>";
        echo "<td>". $row["SUM(TD)"]."</td>";
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
    COUNT(*) as Gms, SUM(Rec), SUM(Yds), SUM(TD)
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
        echo "<tr>";
        echo "<td class='link'><a class='leader' href='/api/playerPageRec.php?playerid=" . $playerid . "'>" . $row["Player_Name"]."</a></td>";
        echo "<td>". $row["Poss"]."</td>";
        echo "<td>". $row["Gms"]."</td>";
        echo "<td>". $row["SUM(Rec)"]."</td>";
        echo "<td>". $row["SUM(Yds)"]."</td>";
        echo "<td>". $row["SUM(TD)"]."</td>";
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
    GROUP_CONCAT(DISTINCT team.Abbr SEPARATOR ', ') AS Teams,
    def_statline.Player_ID,
    COUNT(*) as Gms,
    SUM(Sack) as Sack, SUM(INTR) as INTR, SUM(FF) as FF, SUM(FR) as FR, SUM(TD) as TD, SUM(TFL) as TFL, SUM(PDEF) as PDEF
    FROM def_statline
    INNER JOIN player ON def_statline.Player_ID = player.Player_ID
    INNER JOIN team ON def_statline.Team_ID = team.Team_ID
    INNER JOIN pos ON def_statline.Pos_ID = pos.Pos_ID
    INNER JOIN game ON def_statline.Game_ID = game.Game_ID
    WHERE team.Team_ID = :teamid $season_clause
    GROUP BY Player_ID
    ORDER BY SUM(Sack) desc;";

$stmt = $conn->prepare($sql);
$stmt->bindParam(':teamid', $teamid, PDO::PARAM_INT);
if ($season !== "Total") {
    $stmt->bindParam(':season', $season, PDO::PARAM_INT);
}
$stmt->execute();

if ($stmt->rowCount() > 0) {
    // output data of each row
    while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $playerid = $row["Player_ID"];
        echo "<tr>";
        echo "<td class='link'><a class='leader' href='/api/playerPageDef.php?playerid=" . $playerid . "'>" . $row["Player_Name"]."</a></td>";
        echo "<td>". $row["Poss"]."</td>";
        echo "<td>". $row["Gms"]."</td>";
        echo "<td>". $row["Sack"]."</td>";
        echo "<td>". $row["INTR"]."</td>";
        echo "<td>". $row["FF"]."</td>";
        echo "<td>". $row["FR"]."</td>";
        echo "<td>". $row["TD"]."</td>";
        echo "<td>". $row["TFL"]."</td>";
        echo "<td>". $row["PDEF"]."</td>";
        echo "</tr>";
    }
} else {
    echo "0 results";
}
?>
</table>

<?php
    $conn = null;
?>

</body>
</html>
