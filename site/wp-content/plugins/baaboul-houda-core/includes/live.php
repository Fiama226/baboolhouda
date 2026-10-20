<?php
/**
 * Live TV module — one admin field switches the platform, no code change.
 */

defined('ABSPATH') || exit;

function bh_live(): array {
    $s = bh_settings();
    return [
        'enabled'  => !empty($s['live_enabled']) && 'off' !== $s['live_platform'],
        'platform' => in_array($s['live_platform'], ['youtube', 'facebook', 'hls'], true) ? $s['live_platform'] : 'off',
        'url'      => $s['live_url'],
        'title_fr' => $s['live_title_fr'],
        'title_ar' => $s['live_title_ar'],
    ];
}

/** Second channel (e.g. "Coran 24/7") — same shape as bh_live(). */
function bh_live_channel2(): array {
    $s = bh_settings();
    return [
        'enabled'  => !empty($s['live2_enabled']) && !empty($s['live2_platform']) && 'off' !== $s['live2_platform'],
        'platform' => in_array($s['live2_platform'] ?? '', ['youtube', 'facebook', 'hls'], true) ? $s['live2_platform'] : 'off',
        'url'      => (string) ($s['live2_url'] ?? ''),
        'title_fr' => (string) ($s['live2_title_fr'] ?? ''),
        'title_ar' => (string) ($s['live2_title_ar'] ?? ''),
    ];
}

/** "Last played" radio list (admin textarea, one "Title — HH:MM" per line). */
function bh_radio_history(): array {
    $s = bh_settings();
    $out = [];
    foreach (preg_split('/\n+/', (string) ($s['radio_history'] ?? '')) as $line) {
        $line = trim($line);
        if ('' === $line) {
            continue;
        }
        $time = '';
        if (preg_match('/—\s*(\d{1,2}:\d{2})\s*$/', $line, $m)) {
            $time = $m[1];
            $line = trim(substr($line, 0, -strlen($m[0])));
        }
        $out[] = ['title' => $line, 'time' => $time];
        if (count($out) >= 8) {
            break;
        }
    }
    return $out;
}

/** Extract a YouTube video ID from any common URL shape. */
function bh_youtube_id(string $url): string {
    if (preg_match('~(?:youtube\.com/watch\?v=|youtu\.be/|youtube\.com/embed/|youtube\.com/shorts/)([A-Za-z0-9_-]{11})~', $url, $m)) {
        return $m[1];
    }
    if (preg_match('~^([A-Za-z0-9_-]{11})$~', trim($url), $m)) {
        return $m[1];
    }
    return '';
}

/** Embed HTML for the live platform. Returns '' when unconfigured. */
function bh_live_embed_html(): string {
    return bh_live_embed_html_for(bh_live());
}

/** Embed HTML for ANY channel config (used for the TV2 "Coran 24/7" channel). */
function bh_live_embed_html_for(array $live): string {
    if (empty($live['enabled']) || '' === trim((string) ($live['url'] ?? ''))) {
        return '';
    }
    switch ($live['platform']) {
        case 'youtube':
            $id = bh_youtube_id($live['url']);
            if (!$id) {
                return '';
            }
            return '<div class="bh-embed bh-embed-youtube"><iframe src="https://www.youtube.com/embed/' . rawurlencode($id) . '?rel=0&modestbranding=1" title="' . esc_attr($live['title_fr']) . '" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen loading="lazy"></iframe></div>';
        case 'facebook':
            // Facebook video embed (works with a public page video URL).
            $href = rawurlencode($live['url']);
            return '<div class="bh-embed bh-embed-facebook"><iframe src="https://www.facebook.com/plugins/video.php?href=' . $href . '&show_text=true&width=560" width="560" height="315" style="border:none;overflow:hidden" scrolling="no" frameborder="0" allowfullscreen loading="lazy" title="' . esc_attr($live['title_fr']) . '"></iframe></div>';
        case 'hls':
            $url = esc_url_raw($live['url']);
            return '<div class="bh-embed bh-embed-hls"><video id="bh-live-video" controls playsinline preload="metadata" data-src="' . esc_attr($url) . '" style="width:100%;background:#000"></video></div>';
    }
    return '';
}

/** Is "now" a scheduled slot? (used for the automatic EN DIRECT hint). */
function bh_is_now_on_air(): bool {
    foreach (bh_schedule_by_day(int_date('w')) as $slot) {
        $now   = int_date('H:i');
        $start = $slot['start'];
        $end   = $slot['end'];
        if ($now >= $start && $now < $end) {
            return true;
        }
    }
    return false;
}
