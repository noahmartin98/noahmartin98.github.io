<!DOCTYPE html>
<html>
<head>
<link rel="stylesheet" href="../mystyle.css">

<!-- DataTables CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
<!-- jQuery + DataTables JS -->
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

<script src="../myjavascript.js"></script>
</head>
<body>

<?php
require 'databaseConnect.php';

$season = $_GET['season'] ?? 'Total';

require 'navbar.php';

?>

<div class="header-container">

    <h1>Defensive Leaders</h1>


    <form method="get" action="defLeaders.php">
        <select class="season-select" name="season" onchange="this.form.submit()">
            <option value="2015" <?php if ($season == '2015') echo 'selected'; ?>>2015</option>
            <option value="2012" <?php if ($season == '2012') echo 'selected'; ?>>2012</option>
			      <option value="2024" <?php if ($season == '2024') echo 'selected'; ?>>2024</option>
			      <option value="2018" <?php if ($season == '2018') echo 'selected'; ?>>2018</option>
            <option value="Total" <?php if ($season == 'Total') echo 'selected'; ?>>Total</option>
        </select>
    </form>

</div>


<table id="leaders">
    <thead>
        <tr>
            <th>Rank</th>
            <th>Season</th>
            <th>Week</th>
            <th>Date</th>
            <th>Player</th>
            <th>Pos</th>
            <th>Team</th>
            <th>Sack</th>
            <th>INT</th>
            <th>FF</th>
            <th>FR</th>
            <th>TD</th>
            <th>TFL</th>
            <th>PDEF</th>
        </tr>
    </thead>


<?php

// 1. Conditionally add Season to both SELECT and GROUP BY blocks if it's not a lifetime total
if ($season !== "Total") {
    $where_clause = " WHERE game.Season = :season";
} else {
    // For lifetime "Total", we show a hardcoded string label instead of an unpredictable single season
    $where_clause = "";
}

$sql = "SELECT game.season, game.week, game.game_date,
  player.Player_Name,
	pos.Pos_Abbr AS Poss,
	team.Abbr AS Team,
	def_statline.Player_ID,
	Sack, INTR, FF, FR, TD, TFL, PDEF
    FROM def_statline
    INNER JOIN player ON def_statline.Player_ID = player.Player_ID
    INNER JOIN team ON def_statline.Team_ID = team.Team_ID
    INNER JOIN pos ON def_statline.Pos_ID = pos.Pos_ID
    INNER JOIN game ON def_statline.Game_ID = game.Game_ID
	$where_clause
    ORDER BY Sack DESC;";

// 2. Prepare the statement to run safely
$stmt = $conn->prepare($sql);

// 3. Securely bind the season parameter if filtering
if ($season !== "Total") {
    $stmt->bindParam(':season', $season, PDO::PARAM_INT); // or PARAM_STR depending on your column type
}

$stmt->execute();

if ($stmt->rowCount() > 0) {
    // output data of each row
    $cur_rank = 1;
    while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $playerid = $row["Player_ID"];
        echo "<tr>";
        echo "<td>".$cur_rank."</td>";
        echo "<td>". $row["season"]."</td>";
        echo "<td>". $row["week"]."</td>";
        echo "<td>". $row["game_date"]."</td>";
        echo "<td class='link'><a class='leader' href='/api/playerPageDef.php?playerid=" . $playerid . "'>" . $row["player_name"] . "</a></td>";
        echo "<td>". $row["poss"]."</td>";
        echo "<td>". $row["team"]."</td>";
        echo "<td>". $row["sack"]."</td>";
        echo "<td>". $row["intr"]."</td>";
        echo "<td>". $row["ff"]."</td>";
        echo "<td>". $row["fr"]."</td>";
        echo "<td>". $row["td"]."</td>";
        echo "<td>". $row["tfl"]."</td>";
	      echo "<td>". $row["pdef"]."</td>";
        echo "</tr>";
        $cur_rank++;
    }
} else {
    echo "0 results";
}

$conn = null;
?>

</table>

</body>
</html>
