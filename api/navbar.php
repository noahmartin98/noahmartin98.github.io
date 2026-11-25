<?php $season = $season ?? null; ?>

<nav class="navbar">
    <ul class="nav-links">
        <li><a href="../home.html">Home</a></li>

        <li class="dropdown">
            <a href="#">Leaders ▾</a>
            <div class="dropdown-menu">
                <a href="/api/passingLeaders.php">Passing</a>
                <a href="/api/rushingLeaders.php">Rushing</a>
                <a href="/api/receivingLeaders.php">Receiving</a>
                <a href="/api/defLeaders.php">Defense</a>
            </div>
        </li>

        <li class="dropdown">
            <a href="#">Game Records ▾</a>
            <div class="dropdown-menu">
                <a href="/api/allRecordsPass.php">Passing</a>
                <a href="/api/allRecordsRush.php">Rushing</a>
                <a href="/api/allRecordsRec.php">Receiving</a>
                <a href="/api/allRecordsDef.php">Defense</a>
            </div>
        </li>

        <li>
            <a href="/api/scoreboard.php<?= $season ? '?season=' . urlencode($season) : '' ?>">
                Scores
            </a>
        </li>
        <li><a href="/api/bracket.php">Playoff Brackets</a></li>

        <li class="dropdown">
            <a href="#">Teams ▾</a>
            <div class="dropdown-menu teams-grid">
                <?php
                if (isset($conn)) {
                    $navTeams = $conn->query("SELECT * FROM team ORDER BY Abbr ASC");
                    while ($t = $navTeams->fetch(PDO::FETCH_ASSOC)) {
                        $tId = $t['Team_ID'] ?? $t['team_id'];
                        $tLabel = $t['Team_Name'] ?? $t['Abbr'];
                        echo "<a href='/api/teamPage.php?teamid=" . urlencode($tId) . "'>" . htmlspecialchars($tLabel) . "</a>";
                    }
                }
                ?>
            </div>
        </li>
        <li><a href="/api/importStats.php">Import Stats</a></li>
    </ul>
</nav>
