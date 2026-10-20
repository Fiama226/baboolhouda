<?php
/**
 * Quran accessors over the bundled dataset (data/quran.json).
 * Arabic text ships with the site (offline-capable); French translation is
 * fetched lazily at runtime from alquran.cloud (CC-BY) and cached client-side.
 */

defined('ABSPATH') || exit;

function bh_quran_data(): array {
    static $data = null;
    if (null === $data) {
        $raw = wp_cache_get('bh_quran');
        if (is_array($raw)) {
            $data = $raw;
        } else {
            $json = file_get_contents(BH_DATA_DIR . 'quran.json');
            $data = json_decode($json, true) ?: [];
            wp_cache_set('bh_quran', $data, '', 12 * HOUR_IN_SECONDS);
        }
    }
    return $data;
}

function bh_quran_surah(int $n): ?array {
    $data = bh_quran_data();
    foreach ($data['surahs'] as $s) {
        if ($s['n'] === $n) {
            return $s;
        }
    }
    return null;
}

function bh_quran_meta(): array {
    $data = bh_quran_data();
    return $data['meta'] ?? [];
}

/** "Verset du jour" — stable per calendar day. */
function bh_daily_ayah(): array {
    $data = bh_quran_data();
    $total = (int) ($data['meta']['verse_count'] ?? 6236);
    $dayOfYear = (int) date('z') + 1;
    $global = (($dayOfYear - 1) % $total) + 1;
    $offset = 0;
    foreach ($data['surahs'] as $s) {
        if ($global <= $offset + $s['ayah_count']) {
            $idx = $global - $offset - 1;
            return [
                'surah'   => $s['n'],
                'name_ar' => $s['name_ar'],
                'name_fr' => $s['name_fr'],
                'ayah'    => $idx + 1,
                'text'    => $s['ayahs'][$idx],
                'global'  => $global,
            ];
        }
        $offset += $s['ayah_count'];
    }
    return [
        'surah'   => 1,
        'name_ar' => 'الفاتحة',
        'name_fr' => 'Al-Fātiha',
        'ayah'    => 1,
        'text'    => 'بِسْمِ ٱللَّهِ ٱلرَّحْمَـٰنِ ٱلرَّحِيمِ',
        'global'  => 1,
    ];
}

/** Canonical alquran.cloud URLs used by the reader (client-side fetch). */
function bh_quran_audio_base(string $reciter): string {
    $reciters = [
        'ar.alafasy'       => 'https://cdn.islamic.network/quran/audio/128/ar.alafasy/',
        'ar.minshawi'      => 'https://cdn.islamic.network/quran/audio/128/ar.minshawi/',
        'ar.abdulbasitmurattal' => 'https://cdn.islamic.network/quran/audio/128/ar.abdulbasitmurattal/',
    ];
    return $reciters[$reciter] ?? $reciters['ar.alafasy'];
}
