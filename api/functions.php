<?php
function playerSeasonOptions($seasons, $selectedSeason) {
    $selected = ($selectedSeason === 'Total') ? 'selected' : '';
    echo "<option value='Total' $selected>All Seasons</option>";

    foreach ($seasons as $yr) {
        $selected = ($selectedSeason == $yr) ? 'selected' : '';
        echo "<option value='$yr' $selected>$yr</option>";
    }
}
?>
