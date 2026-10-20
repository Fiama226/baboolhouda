<?php
/**
 * Prayer times via the free Aladhan API, cached in a transient, with an
 * offline fallback table for Ouagadougou (approximate, labelled as such).
 */

defined('ABSPATH') || exit;

function bh_prayer_times(string $city, string $date = ''): array {
    $city  = $city ? $city : bh_settings()['prayer_city'];
    $date  = $date ? $date : date('Y-m-d');
    $method = (int) bh_settings()['prayer_method'];
    $key = 'bh_pt_' . md5($city . '|' . $date . '|' . $method);

    $cached = get_transient($key);
    if (is_array($cached) && !empty($cached['times'])) {
        return $cached;
    }

    [$y, $m, $d] = array_map('intval', explode('-', $date));
    $api  = sprintf('https://api.aladhan.com/v1/timingsByCity/%d-%02d-%02d?city=%s&method=%d', $d, $m, $y, rawurlencode($city), $method);
    $resp = wp_remote_get($api, ['timeout' => 6, 'sslverify' => true]);

    if (!is_wp_error($resp) && 200 === wp_remote_retrieve_response_code($resp)) {
        $body = json_decode(wp_remote_retrieve_body($resp), true);
        if (isset($body['code'], $body['data']['timings'])) {
            $t = $body['data']['timings'];
            $clean = static fn ($v) => preg_replace('/\s*(AM|PM)$/', '', trim($v));
            $out = [
                'times' => [
                    'fajr'    => $clean($t['Fajr']),
                    'sunrise' => $clean($t['Sunrise']),
                    'dhuhr'   => $clean($t['Dhuhr']),
                    'asr'     => $clean($t['Asr']),
                    'maghrib' => $clean($t['Maghrib']),
                    'isha'    => $clean($t['Isha']),
                ],
                'hijri' => isset($body['data']['date']['hijri'])
                    ? [
                        'day'   => (string) $body['data']['date']['hijri']['day'],
                        'month' => $body['data']['date']['hijri']['month']['en'] ?? '',
                        'month_ar' => $body['data']['date']['hijri']['month']['ar'] ?? '',
                        'year'  => (string) $body['data']['date']['hijri']['year'],
                    ]
                    : null,
                'city' => $city,
                'date' => $date,
                'source' => 'aladhan',
            ];
            set_transient($key, $out, DAY_IN_SECONDS);
            return $out;
        }
    }

    return bh_prayer_fallback($city, $date);
}

/** Offline approximation table (Ouagadougou ≈ 12.37°N, 1.52°E). */
function bh_prayer_fallback(string $city, string $date): array {
    $fallback = json_decode(file_get_contents(BH_DATA_DIR . 'prayer-fallback.json'), true);
    $month    = (int) date('n', strtotime($date));
    $row      = $fallback['months'][$month] ?? $fallback['months'][6];
    return [
        'times' => [
            'fajr'    => $row['fajr'],
            'sunrise' => $row['sunrise'],
            'dhuhr'   => $row['dhuhr'],
            'asr'     => $row['asr'],
            'maghrib' => $row['maghrib'],
            'isha'    => $row['isha'],
        ],
        'hijri'  => null,
        'city'   => $city,
        'date'   => $date,
        'source' => 'fallback',
    ];
}

/** Month of the current city for the "monthly table" page. */
function bh_prayer_month(int $month, int $year, string $city): array {
    $out = [];
    for ($d = 1; $d <= (int) date('t', mktime(0, 0, 0, $month, 1, $year)); $d++) {
        $date = sprintf('%04d-%02d-%02d', $year, $month, $d);
        $out[$d] = bh_prayer_times($city, $date);
    }
    return $out;
}
