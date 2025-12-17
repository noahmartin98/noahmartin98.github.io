<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>NFL Playoff Bracket</title>
  <link rel="stylesheet" href="../bracketStyle.css">
  <link rel="stylesheet" href="../mystyle.css">
</head>
<body>

<?php

require 'databaseConnect.php';

$season = $_GET['season'] ?? '2015';

require 'navbar.php';



$sql = "SELECT Game_ID, Week_Round, AwayTeam.Short AS Away, HomeTeam.Short AS Home, Away_Seed, Home_Seed, Away_Score, Home_Score
FROM game
JOIN team AS AwayTeam ON game.Away_Team_ID = AwayTeam.Team_ID
JOIN team AS HomeTeam ON game.Home_Team_ID = HomeTeam.Team_ID
WHERE Season = $season AND 
	Week_Round IN ('WC', 'DIV', 'CC', 'SB')
ORDER BY Game_ID asc;";
    
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    
    
} else {
    echo "0 results";
}

$gamesByRound = [];

while ($row = $result -> fetch_assoc()) {
  $gamesByRound[$row['Week_Round']][] = $row;
}

function renderMatchup(&$games, $round, $filter = null) {
  foreach ($games[$round] as $key => $game) {
    if ($filter && !$filter($game)) continue;

    echo '<div class="team">' . $game['Away_Seed'] . ' ' . $game['Away'] . 
      '<span class="score">' . $game['Away_Score'] . '</span></div>';
    echo '<div class="team">' . $game['Home_Seed'] . ' ' . $game['Home'] . 
      '<span class="score">' . $game['Home_Score'] . '</span></div>';
    unset($games[$round][$key]);
    return;
  }
  echo '<div class="team"><br></div><div class="team"><br></div>';
}

?>

<div class="header-container">

    <h1>Playoff Bracket</h1>


    <form method="get" action="bracket.php">
        <select class="season-select" name="season" onchange="this.form.submit()">
            <option value="2015" <?php if ($season == '2015') echo 'selected'; ?>>2015</option>
            <option value="2012" <?php if ($season == '2012') echo 'selected'; ?>>2012</option>
        </select>
    </form>
</div>




  <div class="bracket">
    
    <!-- AFC Bracket -->
      <h2 class="afc-title">AFC</h2>
        <h3 class="wildcard1">Wild Card</h3>

        <div class="matchup afc27">
            <?php renderMatchup($gamesByRound, 'WC', fn($g) => $g['Away_Seed'] === '5.'); ?>
        </div>

        <div class="afc36">
          <div class="team"><br></div>
          <div class="team"><br></div>
        </div>

        <div class="matchup afc45">
            <?php renderMatchup($gamesByRound, 'WC', fn($g) => $g['Away_Seed'] === '6.'); ?>
        </div>

        <h3 class="divisional1">Divisional</h3>

        <div class="matchup afcd1">
          <?php renderMatchup($gamesByRound, 'DIV', fn($g) => in_array($g['Away_Seed'], ['5.', '4.'])); ?>
        </div>

        <div class="matchup afcd2">
          <?php renderMatchup($gamesByRound, 'DIV', fn($g) => in_array($g['Away_Seed'], ['6.', '3.'])); ?>
        </div>

        <h3 class="champ1">Championship</h3>
        <div class="matchup afccg">
          <?php renderMatchup($gamesByRound, 'CC'); ?>
        </div>

    <!-- Super Bowl -->
      <h2 class="superbowl">Super Bowl</h2>
        <div class="matchup sb">
          <?php renderMatchup($gamesByRound, 'SB'); ?>
        </div>

    <!-- NFC Bracket -->
      <h2 class="nfc-title">NFC</h2>
        <h3 class="wildcard2">Wild Card</h3>

        <div class="matchup nfc27">
          <?php renderMatchup($gamesByRound, 'WC', fn($g) => $g['Away_Seed'] === '5.'); ?>
        </div>

        <div class="nfc36">
          <div class="team"><br></div>
          <div class="team"><br></div>
        </div>

        <div class="matchup nfc45">
          <?php renderMatchup($gamesByRound, 'WC', fn($g) => $g['Away_Seed'] === '6.'); ?>
        </div>

        <h3 class="divisional2">Divisional</h3>

        <div class="matchup nfcd1">
          <?php renderMatchup($gamesByRound, 'DIV', fn($g) => in_array($g['Away_Seed'], ['5.', '4.'])); ?>
        </div>

        <div class="matchup nfcd2">
          <?php renderMatchup($gamesByRound, 'DIV', fn($g) => in_array($g['Away_Seed'], ['6.', '3.'])); ?>
        </div>

        <h3 class="champ2">Championship</h3>
        <div class="matchup nfccg">
          <?php renderMatchup($gamesByRound, 'CC'); ?>
        </div>

  </div>
</body>
</html>
