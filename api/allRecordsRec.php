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

$season = $_GET['season'] ?? '2018';

require 'navbar.php';

?>

<div class="header-container">

    <h1>Player Game Stats - Receiving</h1>


    <form method="get" action="allRecordsRec.php">
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
            <!--<th>Game#</th>-->
            <th>Season</th>
            <th>Week</th>
            <th>Date</th>
            <th>Player</th>
            <th>Pos</th>
            <th>Team</th>
            <th>Rec</th>
            <th>Yds</th>
            <th>TD</th>
        </tr>
    </thead>


<?php

$sql = "SELECT g.game_id, g.season, g.week, g.game_date, p.player_id, p.player_name, pos.pos_abbr, t.abbr, rs.rec, rs.yds, rs.td
		    FROM game g
			JOIN rec_statline rs ON g.game_id = rs.game_id
		    JOIN player p ON rs.player_id = p.player_id
		    JOIN team t ON rs.team_id = t.team_id
		    JOIN pos ON rs.pos_id = pos.pos_id";

if ($season !== "Total") {
    $sql .= " WHERE Season = $season";
}

$sql .= ";";


$result = $conn->query($sql);

if ($result->num_rows > 0) {
    // output data of each row
    while($row = $result->fetch_assoc()) {
        $playerid = $row["player_id"];
        echo "<tr>";
        //echo "<td>". $row["game_id"]."</td>";
        echo "<td>". $row["season"]."</td>";
        echo "<td>". $row["week"]."</td>";
        echo "<td>". $row["game_date"]."</td>";
        echo "<td class='link'><a class='leader' href='/api/playerPageDef.php?playerid=" . $playerid . "'>" . $row["player_name"] . "</a></td>";
        echo "<td>". $row["pos_abbr"]."</td>";
        echo "<td>". $row["abbr"]."</td>";
        echo "<td>". $row["rec"]."</td>";
        echo "<td>". $row["yds"]."</td>";
        echo "<td>". $row["td"]."</td>";
        echo "</tr>";
    }
} else {
    echo "0 results";
}

$conn->close();
?>

</table>

</body>
</html>
