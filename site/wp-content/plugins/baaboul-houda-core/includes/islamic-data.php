<?php
/**
 * Curated Islamic datasets (bundled, offline):
 *  - 99 beautiful names of Allah (ar + fr)
 *  - Daily/selected du'as & adhkar (morning, evening, sleep, travel)
 *  - Short authenticated hadiths (fr + ar + source)
 */

defined('ABSPATH') || exit;

function bh_json_data(string $file): array {
    static $cache = [];
    if (!isset($cache[$file])) {
        $path = BH_DATA_DIR . $file;
        $cache[$file] = is_file($path) ? (json_decode(file_get_contents($path), true) ?: []) : [];
    }
    return $cache[$file];
}

function bh_names99(): array {
    return bh_json_data('names99.json');
}

function bh_adhkar(): array {
    return bh_json_data('adhkar.json');
}

function bh_hadiths(): array {
    return bh_json_data('hadiths.json');
}

/** Hadith du jour (stable per calendar day). */
function bh_daily_hadith(): array {
    $list = bh_hadiths();
    if (!$list) {
        return [];
    }
    $idx = ((int) date('z')) % count($list);
    return $list[$idx];
}
