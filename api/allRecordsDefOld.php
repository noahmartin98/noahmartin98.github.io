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

    <h1>Player Game Stats - Defensive</h1>


    <form method="get" action="allRecordsDef.php">
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

$sql = "SELECT g.game_id, g.season, g.week, g.game_date, p.player_id, p.player_name, pos.pos_abbr, t.abbr, ds.sack, ds.intr, ds.ff, ds.fr, ds.td, ds.tfl, ds.pdef
		    FROM game g
			JOIN def_statline ds ON g.game_id = ds.game_id
		    JOIN player p ON ds.player_id = p.player_id
		    JOIN team t ON ds.team_id = t.team_id
		    JOIN pos ON ds.pos_id = pos.pos_id";

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
        echo "<td>". $row["sack"]."</td>";
        echo "<td>". $row["intr"]."</td>";
        echo "<td>". $row["ff"]."</td>";
        echo "<td>". $row["fr"]."</td>";
        echo "<td>". $row["td"]."</td>";
        echo "<td>". $row["tfl"]."</td>";
	    echo "<td>". $row["pdef"]."</td>";
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
