<!DOCTYPE html>
<html>
<head>
<link rel="stylesheet" href="../mystyle.css">
</head>
<body>

<?php
require 'databaseConnect.php';

$season = $_GET['season'] ?? '2024';
?>

<nav class="navbar">
    <ul class="nav-links">
        <li><a href="../home.html">Home</a></li>
        <li><a href="/api/passingLeaders.php">Passing Leaders</a></li>
        <li><a href="/api/rushingLeaders.php">Rushing Leaders</a></li>
        <li><a href="/api/receivingLeaders.php">Receiving Leaders</a></li>
		<li><a href="/api/defLeaders.php">Defensive Leaders</a></li>
        <li><a href="/api/bracket.php">Playoff Brackets</a></li>
    </ul>
</nav>

<div class="header-container">

    <h1>Scores</h1>

    <form method="get" action="scoreboard.php">
        <select class="season-select" name="season" onchange="this.form.submit()">
            <option value="2015" <?php if ($season == '2015') echo 'selected'; ?>>2015</option>
            <option value="2012" <?php if ($season == '2012') echo 'selected'; ?>>2012</option>
			<option value="2024" <?php if ($season == '2024') echo 'selected'; ?>>2024</option>
        </select>
    </form>

</div>
        

<?php

// Find last week with any games
$lastWeekQuery = "SELECT MAX(CAST(SUBSTRING(week, 2) AS UNSIGNED)) AS lastWeek
                  FROM game
                  WHERE season = $season AND week LIKE 'W%';";
$lastWeekResult = $conn->query($lastWeekQuery);
$lastWeekRow = $lastWeekResult->fetch_assoc();

$lastWeek = $lastWeekRow['lastWeek'] ?? 0;


for ($week = 1; $week <= $lastWeek; $week++) { 

        // Format week label (W1, W2, ...)
        $weekCode = "W" . $week;

        echo "<h3>Week $week</h3>";
        
        
        echo '<table class="scores">
                <tr>
                    <th>Date</th>
                    <th>Away Team</th>
                    <th>Score</th>
                    <th>Score</th>
                    <th>Home Team</th>
                </tr>';
        
        
        $sql = "SELECT game_date,  AwayTeam.team_name AS awayName, t1.seed AS awaySeed, t1.score AS awayScore,
 	t2.score AS homeScore, HomeTeam.team_name AS homeName, t2.seed AS homeSeed
        from game
        JOIN team_statline AS t1 ON game.game_id = t1.game_id AND t1.home_away = 'Away'
        JOIN team_statline AS t2 ON game.game_id = t2.game_id AND t2.home_away = 'Home'
        JOIN team AS AwayTeam ON t1.team_id = AwayTeam.team_id
        JOIN team AS HomeTeam ON t2.team_id = HomeTeam.team_id
        WHERE Season = $season and week = '$weekCode';";
        
        $result = $conn->query($sql);
        
        if ($result->num_rows > 0) {
            // output data of each row
            while($row = $result->fetch_assoc()) {
                echo "<tr>";
                echo "<td>". $row["game_date"]."</td>";
                echo "<td>". $row["awayName"] . " (" . $row["awaySeed"] . ")" . "</td>";
                echo "<td>". $row["awayScore"]."</td>";
                echo "<td>". $row["homeScore"]."</td>";
                echo "<td>". $row["homeName"] . " (" . $row["homeSeed"] . ")" . "</td>";
                echo "</tr>";
            }
        } else {
            echo "<tr><td colspan='5'>No games this week</td></tr>";
        }
        echo "</table>";
}
?>

	
<h1>Playoffs</h1>

<?php
$playoffsWeeks = ["WC", "DIV", "CC"];

foreach ($playoffsWeeks as $weekCode) {
	
	echo "<h3>Week $weekCode</h3>";
	
	
	echo '<table class="scores">
			<tr>
				<th>Date</th>
				<th>Away Team</th>
				<th>Score</th>
				<th>Score</th>
				<th>Home Team</th>
				<th></th>
			</tr>';
	
	
	$sql = "SELECT game_id, game_date,  AwayTeam.team_name AS awayName, t1.seed AS awaySeed, t1.score AS awayScore,
 	t2.score AS homeScore, HomeTeam.team_name AS homeName, t2.seed AS homeSeed
        from game
        JOIN team_statline AS t1 ON game.game_id = t1.game_id AND t1.home_away = 'Away'
        JOIN team_statline AS t2 ON game.game_id = t2.game_id AND t2.home_away = 'Home'
        JOIN team AS AwayTeam ON t1.team_id = AwayTeam.team_id
        JOIN team AS HomeTeam ON t2.team_id = HomeTeam.team_id
	WHERE season = $season and week = '$weekCode';";

	$result = $conn->query($sql);
	
	if ($result->num_rows > 0) {
		// output data of each row
		while($row = $result->fetch_assoc()) {
			echo "<tr>";
			echo "<td>". $row["game_date"]."</td>";
			echo "<td>".  "(" . $row["awaySeed"] . ") " . $row["awayName"] . "</td>";
			echo "<td>". $row["awayScore"]."</td>";
			echo "<td>". $row["homeScore"]."</td>";
			echo "<td>".  "(" . $row["homeSeed"] . ") " . $row["homeName"] . "</td>";
			echo "<td class='link'><a class='leader' href='/api/gamePage.php?gameid=" . $row["game_id"] . "'>Box Score</a></td>";
			echo "</tr>";
		}
	} else {
		echo "<tr><td colspan='5'>No games this week</td></tr>";
	}
	echo "</table>";
}


//Super Bowl
	
	echo "<h3>Super Bowl</h3>";
	$weekCode = "SB";
	
	echo '<table class="scores">
			<tr>
				<th>Date</th>
				<th>Team</th>
				<th>Score</th>
				<th>Score</th>
				<th>Team</th>
			</tr>';
	
	$sql = "SELECT game_date,  Team1.team_name AS team1Name, t1.seed AS team1Seed, t1.score AS team1Score,
 	t2.score AS team2Score, Team2.team_name AS team2Name, t2.seed AS team2Seed
        from game
        JOIN team_statline AS t1 ON game.game_id = t1.game_id AND t1.team_user = 'CPU'
        JOIN team_statline AS t2 ON game.game_id = t2.game_id AND t2.team_user = 'shady'
        JOIN team AS Team1 ON t1.team_id = Team1.team_id
        JOIN team AS Team2 ON t2.team_id = Team2.team_id
	WHERE season = $season and week = '$weekCode';";
	
	$result = $conn->query($sql);
	
	if ($result->num_rows > 0) {
		// output data of each row
		while($row = $result->fetch_assoc()) {
			echo "<tr>";
			echo "<td>". $row["game_date"]."</td>";
			echo "<td>".  "(" . $row["team1Seed"] . ") " . $row["team1Name"] . "</td>";
			echo "<td>". $row["team1Score"]."</td>";
			echo "<td>". $row["team2Score"]."</td>";
			echo "<td>".  "(" . $row["team2Seed"] . ") " . $row["team2Name"] . "</td>";
			echo "</tr>";
		}
	} else {
		echo "<tr><td colspan='5'>Not yet played</td></tr>";
	}
	echo "</table>";



    $conn->close();
?>


</body>
</html>
