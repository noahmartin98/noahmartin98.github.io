<?php
function seasonDropdownForm($seasons, $selectedSeason, $paramName, $paramValue, $isSmall = true) {
    $sizeClass = $isSmall ? 'small' : '';
    ?>
    <form method="get">
        <input type="hidden" name="<?php echo $paramName; ?>" value="<?php echo $paramValue; ?>">
        <select class="season-select <?php echo $sizeClass; ?>" name="season" onchange="this.form.submit()">
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

function getTeamConference($abbr, $season) {
    $abbr = strtoupper($abbr);
    $season = (int)$season;

    // Handle historical conference realignments
    if ($abbr === 'SEA') {
        return ($season <= 2001) ? 'AFC' : 'NFC';
    }
    if ($abbr === 'TB' && $season === 1976) {
        return 'AFC';
    }

    // Standard AFC teams
    $afcTeams = [
        'BAL', 'BUF', 'CIN', 'CLE', 'DEN', 'HOU', 'IND', 'JAX',
        'KC', 'LAC', 'SD', 'LV', 'OAK', 'MIA', 'NE', 'NYJ', 'PIT', 'TEN'
    ];

    return in_array($abbr, $afcTeams) ? 'AFC' : 'NFC';
}

?>
