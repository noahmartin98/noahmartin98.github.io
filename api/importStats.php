<?php
require 'databaseConnect.php';
require 'navbar.php';

$message = "";
$importedGameId = $_POST['game_id'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (($_POST['passcode'] ?? '') !== 'madden98') {
        $message = "<p style='color:red;'><strong>Invalid passcode.</strong></p>";
    } elseif (($_POST['action'] ?? '') === 'import_game') {
        // ==========================================
        // STEP 1: IMPORT GAME & TEAM STATLINES
        // ==========================================
        $gameLines = array_filter(array_map('trim', explode("\n", trim($_POST['game_data']))));
        $teamLines = array_filter(array_map('trim', explode("\n", trim($_POST['team_data']))));

        $findTeam = $conn->prepare("SELECT Team_ID FROM team WHERE Team_Name = :name LIMIT 1");

        $gameMeta = null;
        $teamsMap = [];

        foreach ($gameLines as $line) {
            $c = array_map('trim', explode("\t", $line));
            if (count($c) < 18) continue;

            list($rawGameId, $rawMad, $rawYr, $week, $teamUser, $difficulty, $rawDate, $homeAway, $rawOtFlag, $wl, $seed, $teamName, $q1, $q2, $q3, $q4, $otScore, $finalScore) = $c;
            if (strtolower($rawGameId) === 'game id') continue;

            $gameId = (int)ltrim($rawGameId, '0');
            $mad    = (int)$rawMad;
            $yr     = (int)$rawYr;
            $season = !empty($_POST['season_override']) ? (int)$_POST['season_override'] : (2000 + $mad - 1);
            $dateFormatted = date('Y-m-d', strtotime($rawDate));

            if (!$gameMeta) {
                $gameMeta = [
                    'game_id'    => $gameId,
                    'season'     => $season,
                    'week'       => $week,
                    'game_date'  => $dateFormatted,
                    'difficulty' => $difficulty,
                    'OT'         => $rawOtFlag,
                    'mad'        => $mad,
                    'yr'         => $yr
                ];
            }

            $findTeam->execute([':name' => $teamName]);
            $teamId = $findTeam->fetchColumn();

            $teamsMap[$teamName] = [
                'team_id'   => $teamId,
                'team_user' => $teamUser,
                'home_away' => $homeAway,
                'seed'      => $seed,
                'q1'        => (int)$q1,
                'q2'        => (int)$q2,
                'q3'        => (int)$q3,
                'q4'        => (int)$q4,
                'ot'        => (int)$otScore,
                'score'     => (int)$finalScore
            ];
        }

        foreach ($teamLines as $line) {
            $c = array_map('trim', explode("\t", $line));
            if (count($c) < 19) continue;

            list($teamName, $tScore, $tTotal, $rush, $pass, $firsts, $to, $conv3, $att3, $conv4, $att4, $conv2, $att2, $rzAtt, $rzTd, $rzFg, $penalties, $penYds, $top) = $c;
            if (empty($teamName) || strtolower($tScore) === 'score') continue;

            if (isset($teamsMap[$teamName])) {
                $teamsMap[$teamName]['rush']      = (int)$rush;
                $teamsMap[$teamName]['pass']      = (int)$pass;
                $teamsMap[$teamName]['firsts']    = (int)$firsts;
                $teamsMap[$teamName]['to']        = (int)$to;
                $teamsMap[$teamName]['conv3']     = (int)$conv3;
                $teamsMap[$teamName]['att3']      = (int)$att3;
                $teamsMap[$teamName]['conv4']     = (int)$conv4;
                $teamsMap[$teamName]['att4']      = (int)$att4;
                $teamsMap[$teamName]['conv2']     = (int)$conv2;
                $teamsMap[$teamName]['att2']      = (int)$att2;
                $teamsMap[$teamName]['rz_att']    = (int)$rzAtt;
                $teamsMap[$teamName]['rz_rd']     = (int)$rzTd;
                $teamsMap[$teamName]['rz_fg']     = (int)$rzFg;
                $teamsMap[$teamName]['penalties'] = (int)$penalties;
                $teamsMap[$teamName]['pen_yds']   = (int)$penYds;
                $teamsMap[$teamName]['top']       = $top;
            }
        }

        if (!$gameMeta || count($teamsMap) !== 2) {
            $message = "<p style='color:red;'><strong>Error:</strong> Make sure both boxes contain the 2 team rows and matching team names.</p>";
        } else {
            try {
                $conn->beginTransaction();

                $gameSql = "INSERT INTO game (game_id, season, week, game_date, difficulty, OT, mad, yr)
                            VALUES (:game_id, :season, :week, :game_date, :difficulty, :OT, :mad, :yr)";
                $conn->prepare($gameSql)->execute($gameMeta);

                $nextTsId = (int)$conn->query("SELECT COALESCE(MAX(team_stat_id), 0) + 1 FROM team_statline")->fetchColumn();

                $tsSql = "INSERT INTO team_statline (
                            team_stat_id, game_id, team_id, seed, home_away, team_user,
                            q1, q2, q3, q4, ot, score,
                            rush, pass, sacked, firsts, `to`,
                            conv3, att3, conv4, att4, conv2, att2,
                            rz_att, rz_rd, rz_fg, penalties, pen_yds, top
                          ) VALUES (
                            :team_stat_id, :game_id, :team_id, :seed, :home_away, :team_user,
                            :q1, :q2, :q3, :q4, :ot, :score,
                            :rush, :pass, 0, :firsts, :to,
                            :conv3, :att3, :conv4, :att4, :conv2, :att2,
                            :rz_att, :rz_rd, :rz_fg, :penalties, :pen_yds, :top
                          )";
                $tsStmt = $conn->prepare($tsSql);

                foreach ($teamsMap as $tName => $t) {
                    if (!$t['team_id']) {
                        throw new Exception("Team '$tName' not found in database.");
                    }
                    $tsStmt->execute([
                        ':team_stat_id' => $nextTsId++,
                        ':game_id'      => $gameMeta['game_id'],
                        ':team_id'      => $t['team_id'],
                        ':seed'         => $t['seed'],
                        ':home_away'    => $t['home_away'],
                        ':team_user'    => $t['team_user'],
                        ':q1'           => $t['q1'],
                        ':q2'           => $t['q2'],
                        ':q3'           => $t['q3'],
                        ':q4'           => $t['q4'],
                        ':ot'           => $t['ot'],
                        ':score'        => $t['score'],
                        ':rush'         => $t['rush'] ?? 0,
                        ':pass'         => $t['pass'] ?? 0,
                        ':firsts'       => $t['firsts'] ?? 0,
                        ':to'           => $t['to'] ?? 0,
                        ':conv3'        => $t['conv3'] ?? 0,
                        ':att3'         => $t['att3'] ?? 0,
                        ':conv4'        => $t['conv4'] ?? 0,
                        ':att4'         => $t['att4'] ?? 0,
                        ':conv2'        => $t['conv2'] ?? 0,
                        ':att2'         => $t['att2'] ?? 0,
                        ':rz_att'       => $t['rz_att'] ?? 0,
                        ':rz_rd'        => $t['rz_rd'] ?? 0,
                        ':rz_fg'        => $t['rz_fg'] ?? 0,
                        ':penalties'    => $t['penalties'] ?? 0,
                        ':pen_yds'      => $t['pen_yds'] ?? 0,
                        ':top'          => $t['top'] ?? '00:00'
                    ]);
                }

                $conn->commit();
                $importedGameId = $gameMeta['game_id'];
                $message = "<p style='color:green;'><strong>Success! Imported Game #{$importedGameId} ({$gameMeta['season']} {$gameMeta['week']}) and both team statlines.</strong></p>";
            } catch (Exception $e) {
                $conn->rollBack();
                $message = "<p style='color:red;'><strong>Database Error:</strong> " . htmlspecialchars($e->getMessage()) . "</p>";
            }
        }
    } elseif (($_POST['action'] ?? '') === 'import_players') {
        // ==========================================
        // STEP 2: IMPORT PLAYER STATLINES
        // ==========================================
        $gameId = (int)ltrim(trim($_POST['game_id'] ?? ''), '0');

        if (!$gameId) {
            $message = "<p style='color:red;'><strong>Error:</strong> Please enter a valid Game ID.</p>";
        } else {
            try {
                $conn->beginTransaction();

                // Cache teams (Abbr => Team_ID) and positions (Pos_Abbr => Pos_ID)
                $teamStmt = $conn->prepare("
                    SELECT t.Abbr, t.Team_ID
                    FROM team_statline ts
                    INNER JOIN team t ON ts.team_id = t.Team_ID
                    WHERE ts.game_id = :gid
                ");
                $teamStmt->execute([':gid' => $gameId]);
                $teamLookup = $teamStmt->fetchAll(PDO::FETCH_KEY_PAIR);
                $posLookup  = $conn->query("SELECT Pos_Abbr, Pos_ID FROM pos")->fetchAll(PDO::FETCH_KEY_PAIR);

                // 1. Load all existing players into a normalized PHP lookup map [clean_lowercase_name => Player_ID]
                $cleanStr = function($str) {
                    // Replace non-breaking spaces, tabs, newlines, and collapse multiple spaces (preserves capitalization)
                    $str = str_replace(["\xC2\xA0", "\r", "\n", "\t"], ' ', (string)$str);
                    return trim(preg_replace('/\s+/', ' ', $str));
                };

                $playerLookup = [];
                $allPlayersStmt = $conn->query("SELECT Player_ID, Player_Name FROM player");
                while ($pRow = $allPlayersStmt->fetch(PDO::FETCH_ASSOC)) {
                    $normName = $cleanStr($pRow['Player_Name']);
                    if ($normName !== '') {
                        $playerLookup[$normName] = $pRow['Player_ID'];
                    }
                }

                $insertPlayer = $conn->prepare("INSERT INTO player (Player_ID, Player_Name) VALUES (:id, :name)");
                $nextPlayerId = (int)$conn->query("SELECT COALESCE(MAX(Player_ID), 0) + 1 FROM player")->fetchColumn();
                $newPlayers   = [];

                $resolveIds = function($playerName, $posAbbr, $teamAbbr) use (&$playerLookup, $cleanStr, $insertPlayer, &$nextPlayerId, &$newPlayers, $teamLookup, $posLookup) {
                    $teamAbbr = trim(str_replace("\xC2\xA0", ' ', $teamAbbr));
                    $posAbbr  = trim(str_replace("\xC2\xA0", ' ', $posAbbr));

                    $teamId = $teamLookup[$teamAbbr] ?? null;
                    if (!$teamId) throw new Exception("Team abbreviation '$teamAbbr' not found in team table.");

                    $posId = $posLookup[$posAbbr] ?? null;
                    if (!$posId) throw new Exception("Position '$posAbbr' not found in pos table.");

                    $displayName = trim(preg_replace('/\s+/', ' ', str_replace("\xC2\xA0", ' ', $playerName)));
                    $lookupKey   = $cleanStr($displayName);

                    if (isset($playerLookup[$lookupKey])) {
                        $playerId = $playerLookup[$lookupKey];
                    } else {
                        $playerId = $nextPlayerId++;
                        $insertPlayer->execute([':id' => $playerId, ':name' => $displayName]);
                        $playerLookup[$lookupKey] = $playerId; // Cache so same game doesn't re-add
                        $newPlayers[] = "$displayName ($posAbbr, $teamAbbr)";
                    }

                    return [$playerId, $posId, $teamId];
                };
                $counts = ['pass' => 0, 'rush' => 0, 'rec' => 0, 'def' => 0];
                
                // 1. PASSING
                $passLines = array_filter(explode("\n", trim($_POST['pass_data'] ?? '')), 'trim');
                if (!empty($passLines)) {
                    $nextPassId = (int)$conn->query("SELECT COALESCE(MAX(pass_stat_id), 0) + 1 FROM pass_statline")->fetchColumn();
                    $passStmt = $conn->prepare("INSERT INTO pass_statline (pass_stat_id, Player_ID, Game_ID, Team_ID, Pos_ID, Comp, Att, Yds, TD, INTR)
                    VALUES (:id, :pid, :gid, :tid, :posid, :comp, :att, :yds, :td, :intr)");
                    foreach ($passLines as $line) {
                        $c = array_map('trim', explode("\t", rtrim($line, "\r\n")));
                        if (count($c) < 8) continue;
                        list($pName, $posAbbr, $teamAbbr, $comp, $att, $yds, $td, $intr) = $c;
                        if (strtolower($pName) === 'passing') continue;
                        list($pid, $posid, $tid) = $resolveIds($pName, $posAbbr, $teamAbbr);
                        $passStmt->execute([
                            ':id' => $nextPassId++, ':pid' => $pid, ':gid' => $gameId, ':tid' => $tid, ':posid' => $posid,
                            ':comp' => (int)$comp, ':att' => (int)$att, ':yds' => (int)$yds, ':td' => (int)$td, ':intr' => (int)$intr
                        ]);
                        $counts['pass']++;
                    }
                }
                // 2. RUSHING
                $rushLines = array_filter(explode("\n", trim($_POST['rush_data'] ?? '')), 'trim');
                if (!empty($rushLines)) {
                    $nextRushId = (int)$conn->query("SELECT COALESCE(MAX(rush_stat_id), 0) + 1 FROM rush_statline")->fetchColumn();
                    $rushStmt = $conn->prepare("INSERT INTO rush_statline (rush_stat_id, Player_ID, Game_ID, Team_ID, Pos_ID, Att, Yds, TD)
                    VALUES (:id, :pid, :gid, :tid, :posid, :att, :yds, :td)");
                    foreach ($rushLines as $line) {
                        $c = array_map('trim', explode("\t", rtrim($line, "\r\n")));
                        if (count($c) < 6) continue;
                        list($pName, $posAbbr, $teamAbbr, $att, $yds, $td) = $c;
                        if (strtolower($pName) === 'rushing') continue;
                        list($pid, $posid, $tid) = $resolveIds($pName, $posAbbr, $teamAbbr);
                        $rushStmt->execute([
                            ':id' => $nextRushId++, ':pid' => $pid, ':gid' => $gameId, ':tid' => $tid, ':posid' => $posid,
                            ':att' => (int)$att, ':yds' => (int)$yds, ':td' => (int)$td
                        ]);
                        $counts['rush']++;
                    }
                }
                // 3. RECEIVING
                $recLines = array_filter(explode("\n", trim($_POST['rec_data'] ?? '')), 'trim');
                if (!empty($recLines)) {
                    $nextRecId = (int)$conn->query("SELECT COALESCE(MAX(rec_stat_id), 0) + 1 FROM rec_statline")->fetchColumn();
                    $recStmt = $conn->prepare("INSERT INTO rec_statline (rec_stat_id, Player_ID, Game_ID, Team_ID, Pos_ID, Rec, Yds, TD)
                    VALUES (:id, :pid, :gid, :tid, :posid, :rec, :yds, :td)");
                    foreach ($recLines as $line) {
                        $c = array_map('trim', explode("\t", rtrim($line, "\r\n")));
                        if (count($c) < 6) continue;
                        list($pName, $posAbbr, $teamAbbr, $rec, $yds, $td) = $c;
                        if (strtolower($pName) === 'receiving') continue;
                        list($pid, $posid, $tid) = $resolveIds($pName, $posAbbr, $teamAbbr);
                        $recStmt->execute([
                            ':id' => $nextRecId++, ':pid' => $pid, ':gid' => $gameId, ':tid' => $tid, ':posid' => $posid,
                            ':rec' => (int)$rec, ':yds' => (int)$yds, ':td' => (int)$td
                        ]);
                        $counts['rec']++;
                    }
                }
                // 4. DEFENSIVE
                $defLines = array_filter(explode("\n", trim($_POST['def_data'] ?? '')), 'trim');
                $teamSacksRecorded = [];
                if (!empty($defLines)) {
                    $nextDefId = (int)$conn->query("SELECT COALESCE(MAX(def_stat_id), 0) + 1 FROM def_statline")->fetchColumn();
                    $defStmt = $conn->prepare("INSERT INTO def_statline (def_stat_id, Player_ID, Game_ID, Team_ID, Pos_ID, Sack, INTR, FF, FR, TD, TFL, PDEF)
                    VALUES (:id, :pid, :gid, :tid, :posid, :sack, :intr, :ff, :fr, :td, :tfl, :pdef)");
                    foreach ($defLines as $line) {
                        $c = array_pad(array_map('trim', explode("\t", rtrim($line, "\r\n"))), 10, '');
                        list($pName, $posAbbr, $teamAbbr, $rawSack, $rawIntr, $rawFf, $rawFr, $rawTd, $rawTfl, $rawPdef) = $c;
                        if (empty($pName) || strtolower($pName) === 'defensive') continue;
                        list($pid, $posid, $tid) = $resolveIds($pName, $posAbbr, $teamAbbr);
                        $sacks = (float)($rawSack ?: 0);
                        $teamSacksRecorded[$tid] = ($teamSacksRecorded[$tid] ?? 0) + $sacks;
                        $defStmt->execute([
                            ':id' => $nextDefId++, ':pid' => $pid, ':gid' => $gameId, ':tid' => $tid, ':posid' => $posid,
                            ':sack' => $sacks,
                            ':intr' => (int)($rawIntr ?: 0),
                            ':ff'   => (int)($rawFf ?: 0),
                            ':fr'   => (int)($rawFr ?: 0),
                            ':td'   => (int)($rawTd ?: 0),
                            ':tfl'  => (int)($rawTfl ?: 0),
                            ':pdef' => (int)($rawPdef ?: 0)
                        ]);
                        $counts['def']++;
                    }
                    // Always round down .5 sacks when updating opponent's 'sacked' count
                    $updateSacked = $conn->prepare("UPDATE team_statline SET sacked = :sacked WHERE game_id = :gid AND team_id <> :def_tid");
                    foreach ($teamSacksRecorded as $defTid => $totalSacks) {
                        $updateSacked->execute([
                            ':sacked'  => (int)floor($totalSacks),
                            ':gid'     => $gameId,
                            ':def_tid' => $defTid
                        ]);
                    }
                }
                $conn->commit();
                $message = "Success! Imported stats for Game #{$gameId}: {$counts['pass']} Passing, {$counts['rush']} Rushing, {$counts['rec']} Receiving, {$counts['def']} Defensive rows.";
                if (!empty($newPlayers)) {
                    $message .= "New Players Added (" . count($newPlayers) . "): " . htmlspecialchars(implode(', ', $newPlayers)) . "";
                }
                } catch (Exception $e) {
                    $conn->rollBack();
                    $message = "Database Error: " . htmlspecialchars($e->getMessage()) . "";
                }
            }
        }
    }
?>

<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="../mystyle.css">
    <title>Import Game & Player Stats</title>
</head>
<body>

    <h1>Step 1: Import Game & Team Stats</h1>
    
    <?php echo $message; ?>

    <form method="post">
        <input type="hidden" name="action" value="import_game">
    
        <div class="flex-row">
            <div>
                <label><strong>Passcode:</strong></label><br>
                <input type="password" name="passcode" value="<?php echo htmlspecialchars($_POST['passcode'] ?? ''); ?>" required>
            </div>
            <div>
                <label><strong>Season Override (Optional):</strong></label><br>
                <input type="number" name="season_override" placeholder="Auto: 2000 + Mad - 1">
            </div>
        </div>
    
        <p class="margin-top-small">
            <label><strong>1. Paste Game Info (2 rows from Game ID through F):</strong></label><br>
            <textarea name="game_data" rows="4" cols="110" style="width: 100%; max-width: 1500px; white-space: pre; overflow-x: auto;" placeholder="0673	15	02	W8	CPU	All-Pro	11/19/26	Away		L	4-3	Green Bay Packers	0	0	0	10	0	10" required></textarea>
        </p>
    
        <p class="margin-top-small">
            <label><strong>2. Paste Team Stats (2 rows from Team Name through Opponent):</strong></label><br>
            <textarea name="team_data" rows="4" cols="110" style="width: 100%; max-width: 1500px; white-space: pre; overflow-x: auto;" placeholder="Green Bay Packers	10	206	36	170	9	1	1	5	0	0	0	0	2	1	1	0	0	13:54	New Orleans Saints	GB" required></textarea>
        </p>
    
        <button type="submit">Import Game & Team Stats</button>
    </form>

    <hr>

    <h3>Step 2: Import Player Stats</h3>
    <form method="post">
        <input type="hidden" name="action" value="import_players">
    
        <div class="flex-row">
            <div>
                <label><strong>Passcode:</strong></label><br>
                <input type="password" name="passcode" value="<?php echo htmlspecialchars($_POST['passcode'] ?? ''); ?>" required>
            </div>
            <div>
                <label><strong>Game ID:</strong></label><br>
                <input type="text" name="game_id" value="<?php echo htmlspecialchars($importedGameId); ?>" placeholder="e.g. 673" required>
            </div>
        </div>
    
        <p class="margin-top-small">
            <label><strong>Passing (Player, Pos, Team, Comp, Att, Yds, TD, INT):</strong></label><br>
            <textarea name="pass_data" rows="5" style="width: 100%; max-width: 1150px; box-sizing: border-box; white-space: pre; overflow-x: auto;"></textarea>
        </p>
    
        <p class="margin-top-small">
            <label><strong>Rushing (Player, Pos, Team, Att, Yds, TD):</strong></label><br>
            <textarea name="rush_data" rows="7" style="width: 100%; max-width: 1150px; box-sizing: border-box; white-space: pre; overflow-x: auto;"></textarea>
        </p>
    
        <p class="margin-top-small">
            <label><strong>Receiving (Player, Pos, Team, Rec, Yds, TD):</strong></label><br>
            <textarea name="rec_data" rows="10" style="width: 100%; max-width: 1150px; box-sizing: border-box; white-space: pre; overflow-x: auto;"></textarea>
        </p>
    
        <p class="margin-top-small">
            <label><strong>Defensive (Player, Pos, Team, Sack, INT, FF, FR, TD, TFL, PDEF):</strong></label><br>
            <textarea name="def_data" rows="12" style="width: 100%; max-width: 1150px; box-sizing: border-box; white-space: pre; overflow-x: auto;"></textarea>
        </p>
    
        <button type="submit">Import All Player Stats</button>
    </form>

</body>
</html>



</body>
</html>
