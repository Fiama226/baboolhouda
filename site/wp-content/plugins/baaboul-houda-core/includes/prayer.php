<?php
/**
 * Prayer times via the free Aladhan API, cached in a transient, with an
 * offline fallback table for Ouagadougou (approximate, labelled as such).
 */

defined('ABSPATH') || exit;

/** Coordinates of a configured city (geocoding by name is unreliable on Aladhan). */
function bh_prayer_coords(string $city): ?array {
    if (function_exists('bh_known_cities')) {
        foreach (bh_known_cities() as $name => $c) {
            if (0 === strcasecmp($name, $city)) {
                return $c;
            }
        }
    }
    return null;
}

/** Strip AM/PM and timezone suffixes such as "04:45 (GMT)". */
function bh_prayer_clean_time($v): string {
    return trim((string) preg_replace('/\s*(\(.*\)|AM|PM)\s*$/i', '', trim((string) $v)));
}

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
    $coords = bh_prayer_coords($city);
    $api    = $coords
        ? sprintf('https://api.aladhan.com/v1/timings/%02d-%02d-%04d?latitude=%s&longitude=%s&method=%d', $d, $m, $y, $coords['lat'], $coords['lon'], $method)
        : sprintf('https://api.aladhan.com/v1/timingsByCity/%02d-%02d-%04d?city=%s&country=%s&method=%d', $d, $m, $y, rawurlencode($city), rawurlencode('Burkina Faso'), $method);
    $resp = wp_remote_get($api, ['timeout' => 6, 'sslverify' => true]);

    if (!is_wp_error($resp) && 200 === wp_remote_retrieve_response_code($resp)) {
        $body = json_decode(wp_remote_retrieve_body($resp), true);
        if (isset($body['code'], $body['data']['timings'])) {
            $t = $body['data']['timings'];
            $clean = static fn ($v) => bh_prayer_clean_time($v);
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

/** Month of the current city for the "monthly table" page (one Aladhan call, cached). */
function bh_prayer_month(int $month, int $year, string $city): array {
    $method = (int) bh_settings()['prayer_method'];
    $key    = 'bh_pm_' . md5($city . '|' . $year . '-' . $month . '|' . $method);
    $cached = get_transient($key);
    if (is_array($cached) && $cached) {
        return $cached;
    }
    $coords = bh_prayer_coords($city);
    if ($coords) {
        $api  = sprintf('https://api.aladhan.com/v1/calendar/%d/%d?latitude=%s&longitude=%s&method=%d', $year, $month, $coords['lat'], $coords['lon'], $method);
        $resp = wp_remote_get($api, ['timeout' => 8]);
        if (!is_wp_error($resp) && 200 === wp_remote_retrieve_response_code($resp)) {
            $body = json_decode(wp_remote_retrieve_body($resp), true);
            if (!empty($body['data']) && is_array($body['data'])) {
                $out = [];
                foreach ($body['data'] as $i => $row) {
                    $t = $row['timings'] ?? [];
                    $d = (int) ($row['date']['gregorian']['day'] ?? ($i + 1));
                    $out[$d] = ['times' => [
                        'fajr'    => bh_prayer_clean_time($t['Fajr'] ?? ''),
                        'sunrise' => bh_prayer_clean_time($t['Sunrise'] ?? ''),
                        'dhuhr'   => bh_prayer_clean_time($t['Dhuhr'] ?? ''),
                        'asr'     => bh_prayer_clean_time($t['Asr'] ?? ''),
                        'maghrib' => bh_prayer_clean_time($t['Maghrib'] ?? ''),
                        'isha'    => bh_prayer_clean_time($t['Isha'] ?? ''),
                    ], 'source' => 'aladhan'];
                }
                if ($out) {
                    set_transient($key, $out, 12 * HOUR_IN_SECONDS);
                    return $out;
                }
            }
        }
    }
    // Offline: approximate monthly table (not cached, so the API is retried next time).
    $out = [];
    for ($d = 1; $d <= (int) date('t', mktime(0, 0, 0, $month, 1, $year)); $d++) {
        $out[$d] = bh_prayer_fallback($city, sprintf('%04d-%02d-%02d', $year, $month, $d));
    }
    return $out;
}
