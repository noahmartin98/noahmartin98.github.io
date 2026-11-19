<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="../mystyle.css">
    <link rel="stylesheet" href="../bracket.css">
    <title>Playoff Bracket</title>
</head>
<body>

<?php
require 'databaseConnect.php';
require 'functions.php';

// Fetch all seasons that have at least one playoff game, ordered by play order
$poSeasons = $conn->query("
    SELECT Season, MIN(game_id) AS first_game
    FROM game
    WHERE week IN ('WC', 'DIV', 'CC', 'SB')
    GROUP BY Season
    ORDER BY first_game DESC
")->fetchAll(PDO::FETCH_COLUMN);

$season = $_GET['season'] ?? ($poSeasons[0] ?? '2014');

require 'navbar.php';

// Fetch all playoff games for the selected season
$sql = "SELECT 
            g.game_id, g.week,
            t1.team_id AS team1_id, t1.seed AS team1_seed, tm1.Abbr AS team1_abbr, tm1.Team_Name AS team1_name, t1.score AS team1_score,
            t2.team_id AS team2_id, t2.seed AS team2_seed, tm2.Abbr AS team2_abbr, tm2.Team_Name AS team2_name, t2.score AS team2_score
        FROM game g
        JOIN team_statline t1 ON g.game_id = t1.game_id
        JOIN team_statline t2 ON g.game_id = t2.game_id AND t1.team_id < t2.team_id
        JOIN team tm1 ON t1.team_id = tm1.team_id
        JOIN team tm2 ON t2.team_id = tm2.team_id
        WHERE g.Season = :season
          AND g.week IN ('WC', 'DIV', 'CC', 'SB')
        ORDER BY g.game_id ASC;";

$stmt = $conn->prepare($sql);
$stmt->bindParam(':season', $season, PDO::PARAM_INT);
$stmt->execute();
$allPlayoffGames = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Organize games by round
$rounds = ['WC' => [], 'DIV' => [], 'CC' => [], 'SB' => []];
foreach ($allPlayoffGames as $g) {
    $rounds[$g['week']][] = $g;
}

// Split games into Left Side (AFC) and Right Side (NFC)
$left  = ['WC' => [], 'DIV' => [], 'CC' => []];
$right = ['WC' => [], 'DIV' => [], 'CC' => []];

foreach (['WC', 'DIV', 'CC'] as $round) {
    foreach ($rounds[$round] as $g) {
        if (getTeamConference($g['team1_abbr'], $season) === 'AFC') {
            $left[$round][] = $g;
        } else {
            $right[$round][] = $g;
        }
    }
}

// Helper function to render a single matchup card
function renderMatchup($game, $season) {
    if (!$game) {
        echo "<div class='matchup-card'><div class='empty-slot'>TBD</div></div>";
        return;
    }

    $t1Win = ($game['team1_score'] > $game['team2_score']) ? 'winner' : 'loser';
    $t2Win = ($game['team2_score'] > $game['team1_score']) ? 'winner' : 'loser';

    $t1Seed = !empty($game['team1_seed']) ? "<span class='team-seed'>({$game['team1_seed']})</span> " : "";
    $t2Seed = !empty($game['team2_seed']) ? "<span class='team-seed'>({$game['team2_seed']})</span> " : "";

    $t1Url = "/api/teamPage.php?teamid={$game['team1_id']}&season=$season";
    $t2Url = "/api/teamPage.php?teamid={$game['team2_id']}&season=$season";
    $boxUrl = "/api/gamePage.php?gameid={$game['game_id']}";

    echo "<div class='matchup-card'>";
    echo "  <div class='team-slot $t1Win'>";
    echo "      <div>$t1Seed<a href='$t1Url'>{$game['team1_name']}</a></div>";
    echo "      <span>{$game['team1_score']}</span>";
    echo "  </div>";
    echo "  <div class='team-slot $t2Win'>";
    echo "      <div>$t2Seed<a href='$t2Url'>{$game['team2_name']}</a></div>";
    echo "      <span>{$game['team2_score']}</span>";
    echo "  </div>";
    echo "  <div class='matchup-footer'><a href='$boxUrl'>Box Score</a></div>";
    echo "</div>";
}
?>

<div class="flex-row margin-top-small gap-large">
    <h1>Playoff Bracket</h1>

    <form method="get">
        <select class="season-select" name="season" onchange="this.form.submit()">
            <?php foreach ($poSeasons as $yr): ?>
                <option value="<?php echo $yr; ?>" <?php if ($season == $yr) echo 'selected'; ?>>
                    <?php echo $yr; ?>
                </option>
            <?php endforeach; ?>
        </select>
    </form>
</div>

<?php if (empty($allPlayoffGames)): ?>
    <p>No playoff games recorded for the <?php echo htmlspecialchars($season); ?> season yet.</p>
<?php else: ?>

<div class="bracket-wrapper">
    <!-- AFC Side (Left) -->
    <div class="conference-side">
        <div class="conference-title afc-title">AFC</div>
        <div class="conference-rounds">
            <div class="bracket-round">
                <div class="round-title">Wild Card</div>
                <?php foreach ($left['WC'] as $g) renderMatchup($g, $season); ?>
            </div>

            <div class="bracket-round">
                <div class="round-title">Divisional</div>
                <?php foreach ($left['DIV'] as $g) renderMatchup($g, $season); ?>
            </div>

            <div class="bracket-round">
                <div class="round-title">Conf. Champ</div>
                <?php foreach ($left['CC'] as $g) renderMatchup($g, $season); ?>
            </div>
        </div>
    </div>

    <!-- Center Super Bowl -->
    <div class="bracket-round super-bowl-round">
        <div class="round-title">Super Bowl</div>
        <?php renderMatchup($rounds['SB'][0] ?? null, $season); ?>
    </div>

    <!-- NFC Side (Right) -->
    <div class="conference-side">
        <div class="conference-title nfc-title">NFC</div>
        <div class="conference-rounds">
            <div class="bracket-round">
                <div class="round-title">Conf. Champ</div>
                <?php foreach ($right['CC'] as $g) renderMatchup($g, $season); ?>
            </div>

            <div class="bracket-round">
                <div class="round-title">Divisional</div>
                <?php foreach ($right['DIV'] as $g) renderMatchup($g, $season); ?>
            </div>

            <div class="bracket-round">
                <div class="round-title">Wild Card</div>
                <?php foreach ($right['WC'] as $g) renderMatchup($g, $season); ?>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<?php $conn = null; ?>
</body>
</html>
