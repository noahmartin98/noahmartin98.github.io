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
for ($week = 1; $week <= 18; $week++) { 

        // Format week label (W1, W2, ...)
        $weekCode = "W" . $week;

        echo "<h3>Week $week</h3>";
        
        
        echo '<table class="player-passing">
                <tr>
                    <th>Date</th>
                    <th>Away Team</th>
                    <th>Score</th>
                    <th>Score</th>
                    <th>Home Team</th>
                </tr>';
        
        
        $sql = "SELECT Game_Date,  AwayTeam.Team_Name AS AwayName, Away_Seed, Away_Score, Home_Score, HomeTeam.Team_Name AS HomeName, Home_Seed
        from game
        JOIN team AS AwayTeam ON game.Away_Team_ID = AwayTeam.Team_ID
        JOIN team AS HomeTeam ON game.Home_Team_ID = HomeTeam.Team_ID
        WHERE Season = $season and Week_Round = '$weekCode';";
        
        $result = $conn->query($sql);
        
        if ($result->num_rows > 0) {
            // output data of each row
            while($row = $result->fetch_assoc()) {
                echo "<tr>";
                echo "<td>". $row["Game_Date"]."</td>";
                echo "<td>". $row["AwayName"] . " (" . $row["Away_Seed"] . ")" . "</td>";
                echo "<td>". $row["Away_Score"]."</td>";
                echo "<td>". $row["Home_Score"]."</td>";
                echo "<td>". $row["HomeName"] . " (" . $row["Home_Seed"] . ")" . "</td>";
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
$playoffsWeeks = ["WC", "DIV", "CC", "SB"];

foreach ($playoffsWeeks as $weekCode) {
	
	echo "<h3>Week $weekCode</h3>";
	
	
	echo '<table class="player-passing">
			<tr>
				<th>Date</th>
				<th>Away Team</th>
				<th>Score</th>
				<th>Score</th>
				<th>Home Team</th>
			</tr>';
	
	
	$sql = "SELECT Game_Date,  AwayTeam.Team_Name AS AwayName, Away_Seed, Away_Score, Home_Score, HomeTeam.Team_Name AS HomeName, Home_Seed
	from game
	JOIN team AS AwayTeam ON game.Away_Team_ID = AwayTeam.Team_ID
	JOIN team AS HomeTeam ON game.Home_Team_ID = HomeTeam.Team_ID
	WHERE Season = $season and Week_Round = '$weekCode';";
	
	$result = $conn->query($sql);
	
	if ($result->num_rows > 0) {
		// output data of each row
		while($row = $result->fetch_assoc()) {
			echo "<tr>";
			echo "<td>". $row["Game_Date"]."</td>";
			echo "<td>". $row["AwayName"] . " (" . $row["Away_Seed"] . ")" . "</td>";
			echo "<td>". $row["Away_Score"]."</td>";
			echo "<td>". $row["Home_Score"]."</td>";
			echo "<td>". $row["HomeName"] . " (" . $row["Home_Seed"] . ")" . "</td>";
			echo "</tr>";
		}
	} else {
		echo "<tr><td colspan='5'>No games this week</td></tr>";
	}
	echo "</table>";
}















    $conn->close();
?>


</body>
</html>
