<?php
require 'databaseConnect.php';
require 'navbar.php';

$message = "";
$importedGameId = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (($_POST['passcode'] ?? '') !== 'madden98') {
        $message = "<p style='color:red;'><strong>Invalid passcode.</strong></p>";
    } elseif (($_POST['action'] ?? '') === 'import_game') {
        $gameLines = array_filter(array_map('trim', explode("\n", trim($_POST['game_data']))));
        $teamLines = array_filter(array_map('trim', explode("\n", trim($_POST['team_data']))));

        $findTeam = $conn->prepare("SELECT Team_ID FROM team WHERE Team_Name = :name LIMIT 1");

        // Parse Box 1: Game Info (2 rows)
        $gameMeta = null;
        $teamsMap = [];

        foreach ($gameLines as $line) {
            $c = array_map('trim', explode("\t", $line));
            if (count($c) < 18 || strtolower($c[0]) === 'game id') continue; // Skip header if copied

            $gameId = (int)ltrim($c[0], '0');
            $mad    = (int)$c[1];
            $yr     = (int)$c[2];
            $season = !empty($_POST['season_override']) ? (int)$_POST['season_override'] : (2000 + $mad - 1);
            $dateFormatted = date('Y-m-d', strtotime($c[6]));
            $otFlag = !empty($c[8]) ? 1 : 0;

            if (!$gameMeta) {
                $gameMeta = [
                    'game_id'    => $gameId,
                    'season'     => $season,
                    'week'       => $c[3],
                    'game_date'  => $dateFormatted,
                    'difficulty' => $c[5],
                    'OT'         => $otFlag,
                    'mad'        => $mad,
                    'yr'         => $yr
                ];
            }

            $teamName = $c[11];
            $findTeam->execute([':name' => $teamName]);
            $teamId = $findTeam->fetchColumn();

            $teamsMap[$teamName] = [
                'team_id'   => $teamId,
                'team_user' => $c[4],
                'home_away' => $c[7],
                'seed'      => $c[10],
                'q1'        => (int)$c[12],
                'q2'        => (int)$c[13],
                'q3'        => (int)$c[14],
                'q4'        => (int)$c[15],
                'ot'        => (int)$c[16],
                'score'     => (int)$c[17]
            ];
        }

        // Parse Box 2: Team Box Score Stats (2 rows)
        foreach ($teamLines as $line) {
            $c = array_map('trim', explode("\t", $line));
            if (count($c) < 19 || empty($c[0]) || strtolower($c[1]) === 'score') continue; // Skip header rows

            $teamName = $c[0];
            if (isset($teamsMap[$teamName])) {
                $teamsMap[$teamName]['rush']      = (int)$c[3];
                $teamsMap[$teamName]['pass']      = (int)$c[4];
                $teamsMap[$teamName]['firsts']    = (int)$c[5];
                $teamsMap[$teamName]['to']        = (int)$c[6];
                $teamsMap[$teamName]['conv3']     = (int)$c[7];
                $teamsMap[$teamName]['att3']      = (int)$c[8];
                $teamsMap[$teamName]['conv4']     = (int)$c[9];
                $teamsMap[$teamName]['att4']      = (int)$c[10];
                $teamsMap[$teamName]['conv2']     = (int)$c[11];
                $teamsMap[$teamName]['att2']      = (int)$c[12];
                $teamsMap[$teamName]['rz_att']    = (int)$c[13];
                $teamsMap[$teamName]['rz_rd']     = (int)$c[14];
                $teamsMap[$teamName]['rz_fg']     = (int)$c[15];
                $teamsMap[$teamName]['penalties'] = (int)$c[16];
                $teamsMap[$teamName]['pen_yds']   = (int)$c[17];
                $teamsMap[$teamName]['top']       = $c[18];
            }
        }

        // Validate and Insert into database
        if (!$gameMeta || count($teamsMap) !== 2) {
            $message = "<p style='color:red;'><strong>Error:</strong> Make sure both boxes contain the 2 team rows and matching team names.</p>";
        } else {
            try {
                $conn->beginTransaction();

                // 1. Insert into game table
                $gameSql = "INSERT INTO game (game_id, season, week, game_date, difficulty, OT, mad, yr)
                            VALUES (:game_id, :season, :week, :game_date, :difficulty, :OT, :mad, :yr)";
                $conn->prepare($gameSql)->execute($gameMeta);

                // 2. Insert both rows into team_statline
                $tsSql = "INSERT INTO team_statline (
                            game_id, team_id, seed, home_away, team_user,
                            q1, q2, q3, q4, ot, score,
                            rush, pass, sacked, firsts, `to`,
                            conv3, att3, conv4, att4, conv2, att2,
                            rz_att, rz_rd, rz_fg, penalties, pen_yds, top
                          ) VALUES (
                            :game_id, :team_id, :seed, :home_away, :team_user,
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
                        ':game_id'   => $gameMeta['game_id'],
                        ':team_id'   => $t['team_id'],
                        ':seed'      => $t['seed'],
                        ':home_away' => $t['home_away'],
                        ':team_user' => $t['team_user'],
                        ':q1'        => $t['q1'],
                        ':q2'        => $t['q2'],
                        ':q3'        => $t['q3'],
                        ':q4'        => $t['q4'],
                        ':ot'        => $t['ot'],
                        ':score'     => $t['score'],
                        ':rush'      => $t['rush'] ?? 0,
                        ':pass'      => $t['pass'] ?? 0,
                        ':firsts'    => $t['firsts'] ?? 0,
                        ':to'        => $t['to'] ?? 0,
                        ':conv3'     => $t['conv3'] ?? 0,
                        ':att3'      => $t['att3'] ?? 0,
                        ':conv4'     => $t['conv4'] ?? 0,
                        ':att4'      => $t['att4'] ?? 0,
                        ':conv2'     => $t['conv2'] ?? 0,
                        ':att2'      => $t['att2'] ?? 0,
                        ':rz_att'    => $t['rz_att'] ?? 0,
                        ':rz_rd'     => $t['rz_rd'] ?? 0,
                        ':rz_fg'     => $t['rz_fg'] ?? 0,
                        ':penalties' => $t['penalties'] ?? 0,
                        ':pen_yds'   => $t['pen_yds'] ?? 0,
                        ':top'       => $t['top'] ?? '00:00'
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

</body>
</html>
