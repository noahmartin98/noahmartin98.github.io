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

function getPlayerSeasonGames($conn, $playerid) {
    $sql = "SELECT g.Season, COUNT(DISTINCT ts.game_id) AS Gms
            FROM team_statline ts
            INNER JOIN game g ON ts.game_id = g.game_id
            WHERE (g.Season, ts.team_id) IN (
                SELECT g2.Season, s.Team_ID
                FROM (
                    SELECT Game_ID, Team_ID FROM pass_statline WHERE Player_ID = :p1
                    UNION
                    SELECT Game_ID, Team_ID FROM rush_statline WHERE Player_ID = :p2
                    UNION
                    SELECT Game_ID, Team_ID FROM rec_statline  WHERE Player_ID = :p3
                    UNION
                    SELECT Game_ID, Team_ID FROM def_statline  WHERE Player_ID = :p4
                ) s
                INNER JOIN game g2 ON s.Game_ID = g2.Game_ID
            )
            GROUP BY g.Season;";

    $stmt = $conn->prepare($sql);
    $stmt->execute([
        ':p1' => $playerid,
        ':p2' => $playerid,
        ':p3' => $playerid,
        ':p4' => $playerid
    ]);

    return $stmt->fetchAll(PDO::FETCH_KEY_PAIR); // Returns [Season => Gms]
}

function getAllPlayersGames($conn, $season = 'Total') {
    $seasonFilter = ($season !== 'Total') ? "WHERE g.Season = :season" : "";

    $sql = "SELECT p_teams.Player_ID, COUNT(DISTINCT ts.game_id) AS Gms
            FROM (
                SELECT DISTINCT s.Player_ID, g.Season, s.Team_ID
                FROM (
                    SELECT Player_ID, Game_ID, Team_ID FROM pass_statline
                    UNION
                    SELECT Player_ID, Game_ID, Team_ID FROM rush_statline
                    UNION
                    SELECT Player_ID, Game_ID, Team_ID FROM rec_statline
                    UNION
                    SELECT Player_ID, Game_ID, Team_ID FROM def_statline
                ) s
                INNER JOIN game g ON s.Game_ID = g.Game_ID
                $seasonFilter
            ) p_teams
            INNER JOIN game g2 ON p_teams.Season = g2.Season
            INNER JOIN team_statline ts ON g2.game_id = ts.game_id AND p_teams.Team_ID = ts.team_id
            GROUP BY p_teams.Player_ID;";

    $stmt = $conn->prepare($sql);
    if ($season !== 'Total') {
        $stmt->bindParam(':season', $season, PDO::PARAM_INT);
    }
    $stmt->execute();

    return $stmt->fetchAll(PDO::FETCH_KEY_PAIR); // Returns [Player_ID => Gms]
}


?>
