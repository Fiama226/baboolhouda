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

/**
 * The 30 Juz (parts) with their starting verse (Madani pagination).
 * Used by the Quran index "juz" navigation.
 */
function bh_juz_list(): array {
    static $starts = [
        [1, 1], [2, 142], [2, 253], [3, 93], [4, 24], [4, 148], [5, 82], [6, 111],
        [7, 88], [8, 41], [9, 93], [11, 6], [12, 53], [15, 1], [17, 1], [18, 75],
        [21, 1], [23, 1], [25, 21], [27, 56], [29, 46], [33, 31], [36, 28], [39, 32],
        [41, 47], [46, 1], [51, 31], [58, 1], [67, 1], [78, 1],
    ];
    $out = [];
    foreach ($starts as $i => [$s, $a]) {
        $out[] = [
            'n'      => $i + 1,
            'surah'  => $s,
            'ayah'   => $a,
            'pages'  => 20, // ~20 pages per juz in the 604-page mushaf
        ];
    }
    return $out;
}
