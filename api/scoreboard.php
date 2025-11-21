<!DOCTYPE html>
<html>
<head>
<link rel="stylesheet" href="../mystyle.css">
</head>
<body>

<?php
require 'databaseConnect.php';

if (isset($_GET['teamid'])) {
    $teamid = $_GET['teamid'];
}

<h3>Week 2</h3>
<table">
        <tr>
            <th>Date</th>
            <th>Away Team</th>
            <th>Score</th>
            <th>Score</th>
            <th>Home Team</th>
        </tr>

<?php
$sql = "SELECT Game_Date,  AwayTeam.Team_Name, Away_Seed, Away_Score, Home_Score, HomeTeam.Team_Name, Home_Seed
from game
JOIN team AS AwayTeam ON game.Away_Team_ID = AwayTeam.Team_ID
JOIN team AS HomeTeam ON game.Home_Team_ID = HomeTeam.Team_ID
WHERE Season = 2024 and Week_Round = 'W2';";

$result = $conn->query($sql);

if ($result->num_rows > 0) {
    // output data of each row
    while($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>". $row["Game_Date"]."</td>";
        echo "<td>". $row["AwayTeam.Team_Name"].$row["Away_Seed"]."</td>";
        echo "<td>". $row["Away_Score)"]."</td>";
        echo "<td>". $row["Home_Score"]."</td>";
        echo "<td>". $row["HomeTeam.Team_Name"].$row["Home_Seed"]."</td>";
        echo "</tr>";
    }
} else {
    echo "0 results";
}?>
</table>















<?php
    $conn->close();
?>


</body>
</html>
