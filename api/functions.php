<?php
function seasonDropdownForm($seasons, $selectedSeason, $paramName, $paramValue) {
    ?>
    <form method="get">
        <input type="hidden" name="<?php echo $paramName; ?>" value="<?php echo $paramValue; ?>">
        <select class="season-select small" name="season" onchange="this.form.submit()">
            <option value="Total" <?php if ($selectedSeason === 'Total') echo 'selected'; ?>>All Seasons</option>
            <?php foreach ($seasons as $yr): ?>
                <option value="<?php echo $yr; ?>" <?php if ($selectedSeason == $yr) echo 'selected'; ?>>
                    <?php echo $yr; ?>
                </option>
            <?php endforeach; ?>
        </select>
    </form>
    <?php
}

function getPlayerName($conn, $playerid) {
    if (!$playerid) {
        return "Player Not Found";
    }

    $sql = "SELECT Player_Name FROM player WHERE Player_ID = :playerid LIMIT 1;";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':playerid', $playerid, PDO::PARAM_INT);
    $stmt->execute();

    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row ? $row["Player_Name"] : "Player Not Found";
}
?>
