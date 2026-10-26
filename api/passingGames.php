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

$season = $_GET['season'] ?? 'All';

require 'navbar.php';

?>

<div class="header-container">

    <h1>Passing Game Stats</h1>


    <form method="get" action="passingGames.php">
        <select class="season-select" name="season" onchange="this.form.submit()">
            <option value="2015" <?php if ($season == '2015') echo 'selected'; ?>>2015</option>
            <option value="2012" <?php if ($season == '2012') echo 'selected'; ?>>2012</option>
			    <  option value="2024" <?php if ($season == '2024') echo 'selected'; ?>>2024</option>
			      <option value="2018" <?php if ($season == '2018') echo 'selected'; ?>>2018</option>
            <option value="All" <?php if ($season == 'All') echo 'selected'; ?>>All</option>
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
          <th>OT</th>
          <th>Player</th>
          <th>Pos</th>
          <th>Team</th>
          <th>Com</th>
          <th>Att</th>
          <th>Yds</th>
          <th>TD</th>
          <th>INT</th>
        </tr>
    </thead>


<?php

// 1. Conditionally add Season to both SELECT and GROUP BY blocks if it's not a lifetime total
if ($season !== "All") {
    $where_clause = " WHERE game.Season = :season";
} else {
    // For lifetime "Total", we show a hardcoded string label instead of an unpredictable single season
    $where_clause = "";
}

$sql = "SELECT 
      game.season AS Season,
      game.week AS Week,
      game.game_date AS Date,
      game.ot AS OT,
      player.Player_Name AS Player,
      pos.Pos_Abbr AS Pos,
      team.Abbr AS Team,
      pass_statline.Comp AS Com,
      pass_statline.Att AS Att,
      pass_statline.Yds AS Yds,
      pass_statline.TD AS TD,
      pass_statline.INTR AS INTR
      FROM pass_statline
      INNER JOIN player ON pass_statline.Player_ID = player.Player_ID
      INNER JOIN team ON pass_statline.Team_ID = team.Team_ID
      INNER JOIN pos ON pass_statline.Pos_ID = pos.Pos_ID
      INNER JOIN game ON pass_statline.Game_ID = game.Game_ID
      $where_clause
      ORDER BY Yds DESC;";

// 2. Prepare the statement to run safely
$stmt = $conn->prepare($sql);

// 3. Securely bind the season parameter if filtering
if ($season !== "All") {
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
        echo "<td>". $row["Season"]."</td>";
        echo "<td>". $row["Week"]."</td>";
        echo "<td>". $row["Date"]."</td>";
        echo "<td>". $row["OT"]."</td>";
        echo "<td>". $row["Player"]."</td>";
        echo "<td>". $row["Pos"]."</td>";
        echo "<td>". $row["Team"]."</td>";
        echo "<td>". $row["Com"]."</td>";
        echo "<td>". $row["Att"]."</td>";
        echo "<td>". $row["Yds"]."</td>";
        echo "<td>". $row["TD"]."</td>";
        echo "<td>". $row["INTR"]."</td>";
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
