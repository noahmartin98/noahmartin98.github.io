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

$season = $_GET['season'] ?? '2024';

require 'navbar.php';

?>

<div class="header-container">

    <h1>Passing Leaders</h1>


    <form method="get" action="passingLeaders.php">
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
            <th>Player</th>
            <th>Position</th>
            <th>Team</th>
            <th>Gms</th>
            <th>Cmp</th>
            <th>Att</th>
            <th>Yds</th>
            <th>TD</th>
            <th>INT</th>
            <th>Comp%</th>
            <th>Yds/Gm</th>
            <th>Yds/Att</th>
            <th>TD/INT</th>
        </tr>
    </thead>


<?php

// 1. Conditionally add Season to both SELECT and GROUP BY blocks if it's not a lifetime total
if ($season !== "Total") {
    $select_season = "game.Season, ";
    $where_clause = " WHERE game.Season = :season";
    $group_by_season = ", game.Season";
} else {
    // For lifetime "Total", we show a hardcoded string label instead of an unpredictable single season
    $select_season = "'All Seasons' AS Season, ";
    $where_clause = "";
    $group_by_season = "";
}

$sql = "SELECT $select_season player.Player_Name, 
        GROUP_CONCAT(DISTINCT pos.Pos_Abbr SEPARATOR ', ') AS Poss, 
        GROUP_CONCAT(DISTINCT team.Abbr SEPARATOR ', ') AS Teams, 
        pass_statline.Player_ID, 
        COUNT(*) as Gms, 
        SUM(Comp), SUM(Att), SUM(Yds), SUM(TD), SUM(INTR),
        (SUM(Comp)/SUM(Att)) AS CompPct,
        (SUM(Yds)/COUNT(*)) AS Ypg,
        (SUM(Yds)/SUM(Att)) AS Ypa,
        (SUM(TD)/SUM(INTR)) AS TDINT
    FROM pass_statline
    INNER JOIN player ON pass_statline.Player_ID = player.Player_ID
    INNER JOIN team ON pass_statline.Team_ID = team.Team_ID
    INNER JOIN pos ON pass_statline.Pos_ID = pos.Pos_ID
    INNER JOIN game ON pass_statline.Game_ID = game.Game_ID
    $where_clause
    GROUP BY Player_ID $group_by_season
    ORDER BY SUM(Yds) DESC;";

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
        echo "<td class='link'><a class='leader' href='/api/playerPagePass.php?playerid=" . $playerid . "'>" . $row["Player_Name"] . "</a></td>";
        echo "<td>". $row["Poss"]."</td>";
        echo "<td>". $row["Teams"]."</td>";
        echo "<td>". $row["Gms"]."</td>";
        echo "<td>". $row["SUM(Comp)"]."</td>";
        echo "<td>". $row["SUM(Att)"]."</td>";
        echo "<td>". $row["SUM(Yds)"]."</td>";
        echo "<td>". $row["SUM(TD)"]."</td>";
        echo "<td>". $row["SUM(INTR)"]."</td>";
        echo "<td>". sprintf('%.1f%%', $row["CompPct"] * 100)."</td>";
        echo "<td>". sprintf('%.1f', $row["Ypg"])."</td>";
        echo "<td>". sprintf('%.2f', $row["Ypa"])."</td>";
        echo "<td>". sprintf('%.2f', $row["TDINT"])."</td>";
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
