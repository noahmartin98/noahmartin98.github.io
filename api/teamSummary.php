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

$teamid = $_GET['teamid'] ?? null;
$sort   = $_GET['sort'] ?? 'play';

if ($sort === 'season') {
    $orderBy    = "game.Season ASC";
    $nextSort   = "play";
    $toggleText = "Switch to Play Order";
} else {
    $orderBy    = "MIN(game.game_id) ASC";
    $nextSort   = "season";
    $toggleText = "Switch to Season Order";
}

$stmt = $conn->prepare("SELECT Team_Name FROM team WHERE Team_ID = :teamid;");
$stmt->bindParam(':teamid', $teamid, PDO::PARAM_INT);
$stmt->execute();
$teamname = $stmt->fetchColumn() ?: "Team Not Found";

// Find all seasons where the Super Bowl has already been completed
$completedSeasons = $conn->query("SELECT DISTINCT Season FROM game WHERE week = 'SB'")
                         ->fetchAll(PDO::FETCH_COLUMN);

// Calculate overall franchise playoff record (WC, DIV, CC, SB)
$poStmt = $conn->prepare("
    SELECT 
        SUM(CASE WHEN t1.score > t2.score THEN 1 ELSE 0 END) AS po_w,
        SUM(CASE WHEN t2.score > t1.score THEN 1 ELSE 0 END) AS po_l
    FROM team_statline t1
    JOIN team_statline t2 
        ON t1.game_id = t2.game_id AND t1.team_id <> t2.team_id
    JOIN game ON t1.game_id = game.game_id
    WHERE t1.team_id = :teamid 
      AND game.week IN ('WC', 'DIV', 'CC', 'SB');
");
$poStmt->bindParam(':teamid', $teamid, PDO::PARAM_INT);
$poStmt->execute();
$poRow = $poStmt->fetch(PDO::FETCH_ASSOC);

$poWins   = (int)($poRow['po_w'] ?? 0);
$poLosses = (int)($poRow['po_l'] ?? 0);
$poRecordText = "Playoffs: $poWins-$poLosses";
?>

<div class="flex-row margin-top-small gap-large">
    <h1><?php echo htmlspecialchars($teamname); ?></h1>
    <h3>Franchise History</h3>
    <a class="sort-toggle" href="?teamid=<?php echo urlencode($teamid); ?>&sort=<?php echo $nextSort; ?>">
        <?php echo $toggleText; ?>
    </a>
</div>

<table class="table-spacious margin-top-large">
    <thead>
        <tr>
            <th>Season</th>
            <th>W</th>
            <th>L</th>
            <th>T</th>
            <th>Win%</th>
            <th>PF</th>
            <th>PA</th>
            <th>Playoffs</th>
        </tr>
    </thead>
    <tbody>
<?php
$sql = "SELECT 
            game.Season,
            SUM(CASE WHEN t1.score > t2.score THEN 1 ELSE 0 END) AS W,
            SUM(CASE WHEN t2.score > t1.score THEN 1 ELSE 0 END) AS L,
            SUM(CASE WHEN t1.score = t2.score THEN 1 ELSE 0 END) AS T,
            SUM(t1.score) AS PF,
            SUM(t2.score) AS PA,
            MAX(game.game_id) AS last_game_id
        FROM team_statline t1
        JOIN team_statline t2 
            ON t1.game_id = t2.game_id AND t1.team_id <> t2.team_id
        JOIN game ON t1.game_id = game.game_id
        WHERE t1.team_id = :teamid
        GROUP BY game.Season
        ORDER BY $orderBy;";

$stmt = $conn->prepare($sql);
$stmt->bindParam(':teamid', $teamid, PDO::PARAM_INT);
$stmt->execute();

$lastGameStmt = $conn->prepare("
    SELECT game.week, (t1.score > t2.score) AS won
    FROM game
    JOIN team_statline t1 ON game.game_id = t1.game_id AND t1.team_id = :teamid
    JOIN team_statline t2 ON game.game_id = t2.game_id AND t2.team_id <> :teamid
    WHERE game.game_id = :last_game_id
");

$tot = ['W' => 0, 'L' => 0, 'T' => 0, 'PF' => 0, 'PA' => 0];

if ($stmt->rowCount() > 0) {
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        foreach ($tot as $k => $v) $tot[$k] += $row[$k];

        $gamesPlayed = $row['W'] + $row['L'] + $row['T'];
        $winPct = ($gamesPlayed > 0) ? ($row['W'] + 0.5 * $row['T']) / $gamesPlayed : 0;

        $lastGameStmt->execute([':teamid' => $teamid, ':last_game_id' => $row['last_game_id']]);
        $lastGame = $lastGameStmt->fetch(PDO::FETCH_ASSOC);
        $lastWeek = $lastGame['week'] ?? '';
        $wonLast  = (bool)($lastGame['won'] ?? false);

        // Bold all playoff appearances; check if season is still in progress otherwise
        switch ($lastWeek) {
            case 'SB':
                $playoffFinish = $wonLast ? "<strong>Won Super Bowl</strong>" : "<strong>Lost Super Bowl</strong>";
                break;
            case 'CC':
                $playoffFinish = "<strong>Lost Conf. Championship</strong>";
                break;
            case 'DIV':
                $playoffFinish = "<strong>Lost Divisional</strong>";
                break;
            case 'WC':
                $playoffFinish = "<strong>Lost Wild Card</strong>";
                break;
            default:
                $isSeasonDone = in_array($row['Season'], $completedSeasons);
                $playoffFinish = $isSeasonDone ? "Missed Playoffs" : "In Progress";
                break;
        }

        $seasonUrl = "/api/teamPage.php?teamid=" . urlencode($teamid) . "&season=" . urlencode($row['Season']);

        echo "<tr>";
        echo "<td class='link'><a class='leader' href='$seasonUrl'>{$row['Season']}</a></td>";
        echo "<td>{$row['W']}</td>";
        echo "<td>{$row['L']}</td>";
        echo "<td>{$row['T']}</td>";
        echo "<td>" . sprintf('%.3f', $winPct) . "</td>";
        echo "<td>{$row['PF']}</td>";
        echo "<td>{$row['PA']}</td>";
        echo "<td>$playoffFinish</td>";
        echo "</tr>";
    } // <-- Make sure the while loop closes right here!

    // Calculate Franchise Total values once after all seasons are summed
    $totGames = $tot['W'] + $tot['L'] + $tot['T'];
    $totPct   = ($totGames > 0) ? ($tot['W'] + 0.5 * $tot['T']) / $totGames : 0;
    $allSeasonsUrl = "/api/teamPage.php?teamid=" . urlencode($teamid) . "&season=Total";

    // Output the Franchise Total Row once at the bottom
    echo "<tr class='highlight'>";
    echo "<td class='link'><a class='leader' href='$allSeasonsUrl'>Total</a></td>";
    echo "<td>{$tot['W']}</td>";
    echo "<td>{$tot['L']}</td>";
    echo "<td>{$tot['T']}</td>";
    echo "<td>" . sprintf('%.3f', $totPct) . "</td>";
    echo "<td>{$tot['PF']}</td>";
    echo "<td>{$tot['PA']}</td>";
    echo "<td>$poRecordText</td>";
    echo "</tr>";
} else {
    echo "<tr><td colspan='8'>0 results</td></tr>";
}

$conn = null;
?>
    </tbody>
</table>

</body>
</html>
