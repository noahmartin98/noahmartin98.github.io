<?php
require 'databaseConnect.php';

$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (($_POST['passcode'] ?? '') !== 'madden98') {
        $message = "<p style='color:red;'>Invalid passcode.</p>";
    } else {
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

            $playerName = $cols[0];
            $teamAbbr = $cols[1];

            $findPlayer->execute([':name' => $playerName]);
            $playerId = $findPlayer->fetchColumn();

            $findTeam->execute([':abbr' => $teamAbbr]);
            $teamId = $findTeam->fetchColumn();

            if (!$playerId) {
                $errors[] = "Row " . ($lineNum + 1) . ": Player '$playerName' not found in database.";
                continue;
            }
            if (!$teamId) {
                $errors[] = "Row " . ($lineNum + 1) . ": Team '$teamAbbr' not found.";
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
            } elseif ($type === 'rec') {
                // Expected cols: Name, Team, Pos, Rec, Yds, TD
                $findPos->execute([':pos' => $cols[2]]);
                $posId = $findPos->fetchColumn() ?: 1;
                $sql = "INSERT INTO rec_statline (Game_ID, Player_ID, Team_ID, Pos_ID, Rec, Yds, TD)
                        VALUES (?, ?, ?, ?, ?, ?, ?)";
                $conn->prepare($sql)->execute([$gameId, $playerId, $teamId, $posId, $cols[3], $cols[4], $cols[5]]);
                $inserted++;
            } elseif ($type === 'def') {
                // Expected cols: Name, Team, Sack, INT, FF, FR, TD, TFL, PDEF
                $sql = "INSERT INTO def_statline (Game_ID, Player_ID, Team_ID, Sack, INTR, FF, FR, TD, TFL, PDEF)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                $conn->prepare($sql)->execute([
                    $gameId, $playerId, $teamId,
                    $cols[2], $cols[3], $cols[4], $cols[5], $cols[6], $cols[7], $cols[8]
                ]);
                $inserted++;
            }
        }

        $message = "<p style='color:green;'><strong>Successfully inserted $inserted rows!</strong></p>";
        if (!empty($errors)) {
            $message .= "<p style='color:red;'><strong>Skipped:</strong><br>" . implode("<br>", $errors) . "</p>";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="../mystyle.css">
    <title>Import Game Stats</title>
</head>
<body>

<?php require 'navbar.php'; ?>

<h1>Import Stats from Google Sheets</h1>

<?php echo $message; ?>

<form method="post">
    <p>
        <label><strong>Passcode:</strong></label><br>
        <input type="password" name="passcode" required>
    </p>

    <p>
        <label><strong>Game ID:</strong></label><br>
        <input type="number" name="game_id" required>
    </p>

    <p>
        <label><strong>Stat Category:</strong></label><br>
        <select name="stat_type" class="season-select small">
            <option value="pass">Passing (Name | Team | Cmp | Att | Yds | TD | INT)</option>
            <option value="rush">Rushing (Name | Team | Pos | Att | Yds | TD)</option>
            <option value="rec">Receiving (Name | Team | Pos | Rec | Yds | TD)</option>
            <option value="def">Defense (Name | Team | Sack | INT | FF | FR | TD | TFL | PDEF)</option>
        </select>
    </p>

    <p>
        <label><strong>Paste Rows from Google Sheets:</strong></label><br>
        <textarea name="paste_data" rows="12" cols="80" placeholder="Copy cells in Google Sheets (Ctrl+C) and paste here (Ctrl+V)..." required></textarea>
    </p>

    <button type="submit">Import Stats</button>
</form>

</body>
</html>
