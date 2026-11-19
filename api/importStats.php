<?php
require 'databaseConnect.php';

$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['passcode'] ?? '') === 'madden98') {
    $gameId = (int)$_POST['game_id'];
    $type = $_POST['stat_type'];
    $rawData = trim($_POST['paste_data']);
    $lines = explode("\n", $rawData);

    // Prepare lookup queries
    $findPlayer = $conn->prepare("SELECT Player_ID FROM player WHERE Player_Name = :name LIMIT 1");
    $findTeam = $conn->prepare("SELECT Team_ID FROM team WHERE Abbr = :abbr LIMIT 1");
    $findPos = $conn->prepare("SELECT Pos_ID FROM pos WHERE Pos_Abbr = :pos LIMIT 1");

    $inserted = 0;
    $errors = [];

    foreach ($lines as $lineNum => $line) {
        $cols = array_map('trim', explode("\t", $line));
        if (count($cols) < 4) continue; // Skip blank lines

        // Expected Sheets columns: [0] Player Name, [1] Team Abbr, [2] Pos, [3+] Stats...
        $findPlayer->execute([':name' => $cols[0]]);
        $playerId = $findPlayer->fetchColumn();

        $findTeam->execute([':abbr' => $cols[1]]);
        $teamId = $findTeam->fetchColumn();

        if (!$playerId) {
            $errors[] = "Row " . ($lineNum + 1) . ": Player '{$cols[0]}' not found in database.";
            continue;
        }
        if (!$teamId) {
            $errors[] = "Row " . ($lineNum + 1) . ": Team '{$cols[1]}' not found.";
            continue;
        }

        if ($type === 'pass') {
            // Expected cols: Name, Team, Cmp, Att, Yds, TD, INT
            $sql = "INSERT INTO pass_statline (Game_ID, Player_ID, Team_ID, Comp, Att, Yds, TD, INTR)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $conn->prepare($sql)->execute([$gameId, $playerId, $teamId, $cols[2], $cols[3], $cols[4], $cols[5], $cols[6]]);
            $inserted++;
        } elseif ($type === 'rush') {
            // Expected cols: Name, Team, Pos, Att, Yds, TD
            $findPos->execute([':pos' => $cols[2]]);
            $posId = $findPos->fetchColumn() ?: 1;
            $sql = "INSERT INTO rush_statline (Game_ID, Player_ID, Team_ID, Pos_ID, Att, Yds, TD)
                    VALUES (?, ?, ?, ?, ?, ?, ?)";
            $conn->prepare($sql)->execute([$gameId, $playerId, $teamId, $posId, $cols[3], $cols[4], $cols[5]]);
            $inserted++;
        }
        // Receiving and Defense follow the exact same pattern!
    }

    $message = "Successfully inserted $inserted rows!";
    if (!empty($errors)) {
        $message .= "<br><strong>Skipped:</strong><br>" . implode("<br>", $errors);
    }
}
?>
