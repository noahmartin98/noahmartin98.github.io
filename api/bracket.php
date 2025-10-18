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

<nav class="navbar">
    <ul class="nav-links">
        <li><a href="../home.html">Home</a></li>
        <li><a href="/api/passingLeaders.php">Passing Leaders</a></li>
        <li><a href="/api/rushingLeaders.php">Rushing Leaders</a></li>
        <li><a href="/api/receivingLeaders.php">Receiving Leaders</a></li>
        <li><a href="/api/bracket.php">Playoff Brackets</a></li>
    </ul>
</nav>

<?php

require 'databaseConnect.php';


$season = $_GET['season'] ?? '2015';



$sql = "SELECT Game_ID, Week_Round, AwayTeam.Short AS Away, HomeTeam.Short AS Home, Away_Seed, Home_Seed, Away_Score, Home_Score
FROM game
JOIN team AS AwayTeam ON game.Away_Team_ID = AwayTeam.Team_ID
JOIN team AS HomeTeam ON game.Home_Team_ID = HomeTeam.Team_ID
WHERE Season = $season AND 
	(Week_Round = 'WC' OR Week_Round = 'DIV' OR Week_Round = 'CC' OR Week_Round = 'SB')
ORDER BY Game_ID asc;";
    
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    
    
} else {
    echo "0 results";
}

$games = [];

while ($game = $result->fetch_assoc()) {
    $games[] = $game;
}

$round = 'WC';


?>

<div class="header-container">

    <h1>Playoff Bracket</h1>


    <form method="get" action="bracket.php">
        <select class="season-select" name="season" onchange="this.form.submit()">
            <option value="2015" <?php if ($season == '2015') echo 'selected'; ?>>2015</option>
            <option value="2013" <?php if ($season == '2013') echo 'selected'; ?>>2013</option>
        </select>
    </form>
</div>




  <div class="bracket">
    
    <!-- AFC Bracket -->
      <h2 class="afc-title">AFC</h2>
        <h3 class="wildcard1">Wild Card</h3>

        <div class="matchup afc27">
            <?php
                $round = 'WC';
                $currentHome = "";
                $currentAway = "";
                $currentHomeScore = 99;
                $currentAwayScore = 99;
                foreach ($games as $key => $game) {
                    if ($game['Week_Round'] === $round && $game['Away_Seed'] === "5.") {
                        $currentHome = $game['Home_Seed'] ." ". $game['Home'];
                        $currentAway = $game['Away_Seed'] ." ". $game['Away'];
                        $currentHomeScore = $game['Home_Score'];
                        $currentAwayScore = $game['Away_Score'];

                        unset($games[$key]);
                        break;
                    }
                }
            ?>
          <div class="team"> <?php echo "$currentAway"; ?>
            <span class="score"> <?php echo "$currentAwayScore"; ?> </span>
          </div>
          <div class="team"> <?php echo "$currentHome"; ?>
            <span class="score"> <?php echo "$currentHomeScore"; ?> </span>
          </div>
        </div>

        <div class="afc36">
          <div class="team"><br></div>
          <div class="team"><br></div>
        </div>

        <div class="matchup afc45">
            <?php
                $round = 'WC';
                $currentHome = "";
                $currentAway = "";
                $currentHomeScore = 99;
                $currentAwayScore = 99;
                foreach ($games as $key => $game) {
                    if ($game['Week_Round'] === $round && $game['Away_Seed'] === "6.") {
                        $currentHome = $game['Home_Seed'] ." ". $game['Home'];
                        $currentAway = $game['Away_Seed'] ." ". $game['Away'];
                        $currentHomeScore = $game['Home_Score'];
                        $currentAwayScore = $game['Away_Score'];

                        unset($games[$key]);
                        break;
                    }
                }
            ?>
          <div class="team"> <?php echo "$currentAway"; ?>
            <span class="score"> <?php echo "$currentAwayScore"; ?> </span>
          </div>
          <div class="team"> <?php echo "$currentHome"; ?>
            <span class="score"> <?php echo "$currentHomeScore"; ?> </span>
          </div>
        </div>

        <h3 class="divisional1">Divisional</h3>

        <div class="matchup afcd1">
          <?php
                $round = 'DIV';
                $currentHome = "";
                $currentAway = "";
                $currentHomeScore = 99;
                $currentAwayScore = 99;
                foreach ($games as $key => $game) {
                    if ($game['Week_Round'] === $round && ($game['Away_Seed'] === "5." || $game['Away_Seed'] === "4.")) {
                        $currentHome = $game['Home_Seed'] ." ". $game['Home'];
                        $currentAway = $game['Away_Seed'] ." ". $game['Away'];
                        $currentHomeScore = $game['Home_Score'];
                        $currentAwayScore = $game['Away_Score'];

                        unset($games[$key]);
                        break;
                    }
                }
            ?>
          <div class="team"> <?php echo "$currentAway"; ?>
            <span class="score"> <?php echo "$currentAwayScore"; ?> </span>
          </div>
          <div class="team"> <?php echo "$currentHome"; ?>
            <span class="score"> <?php echo "$currentHomeScore"; ?> </span>
          </div>
        </div>

        <div class="matchup afcd2">
          <?php
                $round = 'DIV';
                $currentHome = "";
                $currentAway = "";
                $currentHomeScore = 99;
                $currentAwayScore = 99;
                foreach ($games as $key => $game) {
                    if ($game['Week_Round'] === $round && ($game['Away_Seed'] === "6." || $game['Away_Seed'] === "3.")) {
                        $currentHome = $game['Home_Seed'] ." ". $game['Home'];
                        $currentAway = $game['Away_Seed'] ." ". $game['Away'];
                        $currentHomeScore = $game['Home_Score'];
                        $currentAwayScore = $game['Away_Score'];

                        unset($games[$key]);
                        break;
                    }
                }
            ?>
          <div class="team"> <?php echo "$currentAway"; ?>
            <span class="score"> <?php echo "$currentAwayScore"; ?> </span>
          </div>
          <div class="team"> <?php echo "$currentHome"; ?>
            <span class="score"> <?php echo "$currentHomeScore"; ?> </span>
          </div>
        </div>

        <h3 class="champ1">Championship</h3>
        <div class="matchup afccg">
          <?php
                $round = 'CC';
                $currentHome = "";
                $currentAway = "";
                $currentHomeScore = 99;
                $currentAwayScore = 99;
                foreach ($games as $key => $game) {
                    if ($game['Week_Round'] === $round) {
                        $currentHome = $game['Home_Seed'] ." ". $game['Home'];
                        $currentAway = $game['Away_Seed'] ." ". $game['Away'];
                        $currentHomeScore = $game['Home_Score'];
                        $currentAwayScore = $game['Away_Score'];

                        unset($games[$key]);
                        break;
                    }
                }
            ?>
          <div class="team"> <?php echo "$currentAway"; ?>
            <span class="score"> <?php echo "$currentAwayScore"; ?> </span>
          </div>
          <div class="team"> <?php echo "$currentHome"; ?>
            <span class="score"> <?php echo "$currentHomeScore"; ?> </span>
          </div>
        </div>

    <!-- Super Bowl -->
      <h2 class="superbowl">Super Bowl</h2>
        <div class="matchup sb">
          <?php
                $round = 'SB';
                $currentHome = "";
                $currentAway = "";
                $currentHomeScore = 99;
                $currentAwayScore = 99;
                foreach ($games as $key => $game) {
                    if ($game['Week_Round'] === $round) {
                        $currentHome = $game['Home_Seed'] ." ". $game['Home'];
                        $currentAway = $game['Away_Seed'] ." ". $game['Away'];
                        $currentHomeScore = $game['Home_Score'];
                        $currentAwayScore = $game['Away_Score'];

                        unset($games[$key]);
                        break;
                    }
                }
            ?>
          <div class="team"> <?php echo "$currentAway"; ?>
            <span class="score"> <?php echo "$currentAwayScore"; ?> </span>
          </div>
          <div class="team"> <?php echo "$currentHome"; ?>
            <span class="score"> <?php echo "$currentHomeScore"; ?> </span>
          </div>
        </div>

    <!-- NFC Bracket -->
      <h2 class="nfc-title">NFC</h2>
        <h3 class="wildcard2">Wild Card</h3>

        <div class="matchup nfc27">
          <?php
                $round = 'WC';
                $currentHome = "";
                $currentAway = "";
                $currentHomeScore = 99;
                $currentAwayScore = 99;
                foreach ($games as $key => $game) {
                    if ($game['Week_Round'] === $round && $game['Away_Seed'] === "5.") {
                        $currentHome = $game['Home_Seed'] ." ". $game['Home'];
                        $currentAway = $game['Away_Seed'] ." ". $game['Away'];
                        $currentHomeScore = $game['Home_Score'];
                        $currentAwayScore = $game['Away_Score'];

                        unset($games[$key]);
                        break;
                    }
                }
            ?>
          <div class="team"> <?php echo "$currentAway"; ?>
            <span class="score"> <?php echo "$currentAwayScore"; ?> </span>
          </div>
          <div class="team"> <?php echo "$currentHome"; ?>
            <span class="score"> <?php echo "$currentHomeScore"; ?> </span>
          </div>
        </div>

        <div class="nfc36">
          <div class="team"><br></div>
          <div class="team"><br></div>
        </div>

        <div class="matchup nfc45">
          <?php
                $round = 'WC';
                $currentHome = "";
                $currentAway = "";
                $currentHomeScore = 99;
                $currentAwayScore = 99;
                foreach ($games as $key => $game) {
                    if ($game['Week_Round'] === $round && $game['Away_Seed'] === "6.") {
                        $currentHome = $game['Home_Seed'] ." ". $game['Home'];
                        $currentAway = $game['Away_Seed'] ." ". $game['Away'];
                        $currentHomeScore = $game['Home_Score'];
                        $currentAwayScore = $game['Away_Score'];

                        unset($games[$key]);
                        break;
                    }
                }
            ?>
          <div class="team"> <?php echo "$currentAway"; ?>
            <span class="score"> <?php echo "$currentAwayScore"; ?> </span>
          </div>
          <div class="team"> <?php echo "$currentHome"; ?>
            <span class="score"> <?php echo "$currentHomeScore"; ?> </span>
          </div>
        </div>

        <h3 class="divisional2">Divisional</h3>

        <div class="matchup nfcd1">
          <?php
                $round = 'DIV';
                $currentHome = "";
                $currentAway = "";
                $currentHomeScore = 99;
                $currentAwayScore = 99;
                foreach ($games as $key => $game) {
                    if ($game['Week_Round'] === $round && ($game['Away_Seed'] === "5." || $game['Away_Seed'] === "4.")) {
                        $currentHome = $game['Home_Seed'] ." ". $game['Home'];
                        $currentAway = $game['Away_Seed'] ." ". $game['Away'];
                        $currentHomeScore = $game['Home_Score'];
                        $currentAwayScore = $game['Away_Score'];

                        unset($games[$key]);
                        break;
                    }
                }
            ?>
          <div class="team"> <?php echo "$currentAway"; ?>
            <span class="score"> <?php echo "$currentAwayScore"; ?> </span>
          </div>
          <div class="team"> <?php echo "$currentHome"; ?>
            <span class="score"> <?php echo "$currentHomeScore"; ?> </span>
          </div>
        </div>

        <div class="matchup nfcd2">
          <?php
                $round = 'DIV';
                $currentHome = "";
                $currentAway = "";
                $currentHomeScore = 99;
                $currentAwayScore = 99;
                foreach ($games as $key => $game) {
                    if ($game['Week_Round'] === $round && ($game['Away_Seed'] === "6." || $game['Away_Seed'] === "3.")) {
                        $currentHome = $game['Home_Seed'] ." ". $game['Home'];
                        $currentAway = $game['Away_Seed'] ." ". $game['Away'];
                        $currentHomeScore = $game['Home_Score'];
                        $currentAwayScore = $game['Away_Score'];

                        unset($games[$key]);
                        break;
                    }
                }
            ?>
          <div class="team"> <?php echo "$currentAway"; ?>
            <span class="score"> <?php echo "$currentAwayScore"; ?> </span>
          </div>
          <div class="team"> <?php echo "$currentHome"; ?>
            <span class="score"> <?php echo "$currentHomeScore"; ?> </span>
          </div>
        </div>

        <h3 class="champ2">Championship</h3>
        <div class="matchup nfccg">
          <?php
                $round = 'CC';
                $currentHome = "";
                $currentAway = "";
                $currentHomeScore = 99;
                $currentAwayScore = 99;
                foreach ($games as $key => $game) {
                    if ($game['Week_Round'] === $round) {
                        $currentHome = $game['Home_Seed'] ." ". $game['Home'];
                        $currentAway = $game['Away_Seed'] ." ". $game['Away'];
                        $currentHomeScore = $game['Home_Score'];
                        $currentAwayScore = $game['Away_Score'];

                        unset($games[$key]);
                        break;
                    }
                }
            ?>
          <div class="team"> <?php echo "$currentAway"; ?>
            <span class="score"> <?php echo "$currentAwayScore"; ?> </span>
          </div>
          <div class="team"> <?php echo "$currentHome"; ?>
            <span class="score"> <?php echo "$currentHomeScore"; ?> </span>
          </div>
        </div>

  </div>
</body>
</html>
