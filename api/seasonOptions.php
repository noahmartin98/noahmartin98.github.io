<?php

// Add or remove seasons here in the exact order you want them to appear
$available_seasons = ['2024', '2018', '2015', '2012', 'Total'];

// Fallback in case $season wasn't defined on the main page yet
$current_season = $season ?? '';

foreach ($available_seasons as $yr) {
    $selected = ($current_season == $yr) ? 'selected' : '';
    echo "<option value=\"{$yr}\" {$selected}>{$yr}</option>\n";
}
?>
