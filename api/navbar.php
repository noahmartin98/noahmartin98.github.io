<?php $season = $season ?? null; ?>

<nav class="navbar">
    <ul class="nav-links">
        <li><a href="../home.html">Home</a></li>
        <li><a href="/api/passingLeaders.php">Passing Leaders</a></li>
        <li><a href="/api/rushingLeaders.php">Rushing Leaders</a></li>
        <li><a href="/api/receivingLeaders.php">Receiving Leaders</a></li>
        <li><a href="/api/defLeaders.php">Defensive Leaders</a></li>
        <li>
            <a href="/api/scoreboard.php<?= $season ? '?season=' . urlencode($season) : '' ?>">
                Scores
            </a>
        </li>
        <li><a href="/api/bracket.php">Playoff Brackets</a></li>

        <li class="dropdown">
            <a href="#">Teams ▾</a>
            <div class="dropdown-menu">
                <?php
                if (isset($conn)) {
                    $navTeams = $conn->query("SELECT * FROM team ORDER BY Abbr ASC");
                    while ($t = $navTeams->fetch(PDO::FETCH_ASSOC)) {
                        $tId = $t['Team_ID'] ?? $t['team_id'];
                        $tLabel = $t['Team_Name'] ?? $t['Full_Name'] ?? $t['Name'] ?? $t['Abbr'];
                        echo "<a href='/api/teamPage.php?teamid=" . urlencode($tId) . "'>" . htmlspecialchars($tLabel) . "</a>";
                    }
                }
                ?>
            </div>
        </li>
    </ul>
</nav>
