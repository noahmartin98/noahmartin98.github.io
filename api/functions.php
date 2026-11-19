<?php
function playerSeasonForm($seasons, $selectedSeason, $playerid) {
    ?>
    <form method="get">
        <input type="hidden" name="playerid" value="<?php echo $playerid; ?>">
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
?>
