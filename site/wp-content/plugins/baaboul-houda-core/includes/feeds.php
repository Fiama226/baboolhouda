<?php
/**
 * Feeds & public endpoints:
 *  - /feed/podcast.xml        RSS 2.0 (audio episodes, MP3 enclosure)
 *  - /wp-json/bh/v1/status    live state (drives the "we're live" alert)
 *  - /wp-json/bh/v1/search    q → top-8 results (search suggest)
 *  - /wp-json/bh/v1/prayer-widget.js  embeddable prayer-time widget
 */

defined('ABSPATH') || exit;

/* ---------------------------------------------------------------------
 * Podcast feed (RSS 2.0 with enclosures) — audio episodes.
 * ------------------------------------------------------------------- */

add_action('init', function () {
    add_feed('podcast', 'bh_podcast_feed');
});

function bh_podcast_feed(): void {
    $s = bh_settings();
    $title = function_exists('bh_l') ? bh_l('Podcasts — TV Baaboul Houda', 'البودكاست — قناة باب الهدى') : 'Podcasts';
    header('Content-Type: application/rss+xml; charset=UTF-8');
    echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
    echo '<rss version="2.0" xmlns:media="http://search.yahoo.com/mrss/">' . "\n";
    echo '<channel>' . "\n";
    echo '<title>' . esc_xml($title) . '</title>' . "\n";
    echo '<link>' . esc_xml(home_url('/')) . '</link>' . "\n";
    echo '<description>' . esc_xml(bh_l('Récitations, conférences et émissions audio de la chaîne.', 'تلاوات ومحاضرات وبرامج صوتية من القناة.')) . '</description>' . "\n";
    $q = new \WP_Query([
        'post_type'      => 'episode',
        'posts_per_page' => 30,
        'meta_key'       => '_bh_audio_url',
        'meta_value'     => '',
        'meta_compare'   => '!=',
    ]);
    foreach ($q->posts as $p) {
        $src = bh_episode_source($p->ID);
        $audio = $src['audio'] ?: $src['url'];
        if ('' === $audio) {
            continue;
        }
        echo '<item>' . "\n";
        echo '<title>' . esc_xml(bh_post_title($p)) . '</title>' . "\n";
        echo '<link>' . esc_xml(get_permalink($p)) . '</link>' . "\n";
        echo '<guid>' . esc_xml(get_permalink($p)) . '</guid>' . "\n";
        echo '<pubDate>' . esc_xml(mysql2date('D, d M Y H:i:s O', $p->post_date_gmt)) . '</pubDate>' . "\n";
        echo '<description>' . esc_xml(bh_post_excerpt($p, 60)) . '</description>' . "\n";
        echo '<enclosure url="' . esc_xml($audio) . '" type="audio/mpeg" />' . "\n";
        echo '</item>' . "\n";
    }
    echo '</channel></rss>';
}

function esc_xml(string $s): string {
    return htmlspecialchars($s, ENT_XML1 | ENT_QUOTES, 'UTF-8');
}

/* ---------------------------------------------------------------------
 * REST: status (live state) — public, no auth.
 * ------------------------------------------------------------------- */

add_action('rest_api_init', function () {
    \register_rest_route('bh/v1', '/status', [
        'methods'             => 'GET',
        'permission_callback' => '__return_true',
        'callback'            => 'bh_rest_status',
    ]);
    \register_rest_route('bh/v1', '/search', [
        'methods'             => 'GET',
        'permission_callback' => '__return_true',
        'callback'            => 'bh_rest_search',
    ]);
    \register_rest_route('bh/v1', '/newsletter', [
        'methods'             => 'POST',
        'permission_callback' => '__return_true',
        'callback'            => 'bh_rest_newsletter',
    ]);
    \register_rest_route('bh/v1', '/prayer-widget.js', [
        'methods'             => 'GET',
        'permission_callback' => '__return_true',
        'callback'            => 'bh_rest_prayer_widget',
    ]);
});

function bh_rest_status(): \WP_REST_Response {
    $s      = bh_settings();
    $live   = bh_live();
    $live2  = bh_live_channel2();
    $radio  = [
        'enabled' => !empty($s['radio_enabled']) && '' !== trim((string) ($s['radio_url'] ?? '')),
        'title'   => bh_is_ar() ? $s['radio_title_ar'] : $s['radio_title_fr'],
    ];
    return new \WP_REST_Response([
        'live'   => [
            'enabled' => $live['enabled'],
            'title'   => bh_is_ar() ? $live['title_ar'] : $live['title_fr'],
            'url'     => $live['url'],
        ],
        'live2'  => [
            'enabled' => $live2['enabled'],
            'title'   => bh_is_ar() ? $live2['title_ar'] : $live2['title_fr'],
        ],
        'radio'  => $radio,
        'now'    => bh_now_next(),
    ], 200, ['Access-Control-Allow-Origin' => '*']);
}

/** Search suggest: top 8 across posts, pages, programmes, episodes. */
function bh_rest_search(\WP_REST_Request $req): \WP_REST_Response {
    $q = trim((string) $req->get_param('q'));
    if (mb_strlen($q) < 2) {
        return new \WP_REST_Response(['results' => []], 200);
    }
    $out = [];
    $types = ['post', 'page', 'programme', 'episode'];
    foreach ($types as $t) {
        $query = new \WP_Query([
            'post_type'      => $t,
            's'              => $q,
            'posts_per_page' => 3,
            'no_found_rows'  => true,
            'orderby'        => 'relevance',
        ]);
        foreach ($query->posts as $p) {
            $label = $t === 'post' ? 'Actualité' : $t === 'page' ? 'Page' : $t === 'programme' ? 'Programme' : 'Épisode';
            $out[] = [
                'title' => get_the_title($p),
                'url'   => get_permalink($p),
                'type'  => $label,
            ];
            if (count($out) >= 8) {
                break 2;
            }
        }
    }
    return new \WP_REST_Response(['results' => $out], 200, ['Access-Control-Allow-Origin' => '*']);
}

/* ---------------------------------------------------------------------
 * Newsletter (table created in community.php).
 * ------------------------------------------------------------------- */

function bh_newsletter_table(): string {
    global $wpdb;
    return $wpdb->prefix . 'bh_newsletter';
}

function bh_rest_newsletter(\WP_REST_Request $req): \WP_REST_Response {
    $email = strtolower(trim((string) $req->get_param('email')));
    // Honeypot: bots fill the hidden field.
    if ('' !== (string) $req->get_param('website')) {
        return new \WP_REST_Response(['ok' => true, 'msg' => 'Merci.'], 200);
    }
    if (!is_email($email)) {
        return new \WP_REST_Response(['ok' => false, 'msg' => bh_l('Adresse email invalide.', 'بريد إلكتروني غير صالح.')], 400);
    }
    global $wpdb;
    $table = bh_newsletter_table();
    $count = (int) $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$table} WHERE email = %s", $email));
    if ($count > 0) {
        return new \WP_REST_Response(['ok' => true, 'msg' => bh_l('Vous êtes déjà inscrit. Merci !', 'أنت مشترك بالفعل. شكراً!')], 200);
    }
    $wpdb->insert($table, ['email' => $email, 'created_at' => current_time('mysql')]);
    return new \WP_REST_Response(['ok' => true, 'msg' => bh_l('Inscription confirmée. À très vite !', 'تمت الاشتراك. نراك قريباً!')], 200);
}

/* ---------------------------------------------------------------------
 * Embeddable prayer-time widget (served as JavaScript).
 * Snippet: <script src="https://SITE/wp-json/bh/v1/prayer-widget.js?city=Ouagadougou" async></script>
 * The widget fetches Aladhan directly (CORS open) and renders a small card
 * linking back to our prayer page.
 * ------------------------------------------------------------------- */

function bh_rest_prayer_widget(\WP_REST_Request $req): \WP_REST_Response {
    $city   = sanitize_text_field((string) ($req->get_param('city') ?: 'Ouagadougou'));
    $prayerPage = get_permalink(bh_page_id('prieres')) ?: home_url('/prieres/');
    $js = <<<'JS'
(function () {
  var el = document.currentScript && document.currentScript.parentNode;
  if (!el) return;
  var host = location.host;
  function render(data, offline) {
    var t = data.times || {};
    var names = { fajr: 'Fajr', sunrise: 'Levée', dhuhr: 'Dhuhr', asr: 'Asr', maghrib: 'Maghrib', isha: 'Isha' };
    var keys = ['fajr', 'sunrise', 'dhuhr', 'asr', 'maghrib', 'isha'];
    var now = new Date();
    var hm = now.toTimeString().slice(0, 5);
    var next = '';
    keys.forEach(function (k) { if (t[k] && t[k] > hm && !next) next = k; });
    var html = '<div style="font-family:system-ui,sans-serif;max-width:340px;margin:8px auto;border:1px solid #d8e5de;border-radius:10px;overflow:hidden">';
    html += '<div style="background:#0e7a4e;color:#fff;padding:8px 12px;font-size:13px;font-weight:700">' + CITY + (offline ? ' — indicatif' : '') + '</div>';
    html += '<div style="display:flex;justify-content:space-between;padding:8px 12px;font-size:12px">';
    keys.forEach(function (k) {
      var strong = k === next ? 'font-weight:700;color:#0e7a4e' : 'color:#333';
      html += '<div style="text-align:center"><div style="' + strong + '">' + (t[k] || '—') + '</div><div style="color:#777">' + names[k] + '</div></div>';
    });
    html += '</div>';
    html += '<div style="padding:6px 12px;font-size:11px"><a href="' + PRAYER_PAGE + '" style="color:#0e7a4e;font-weight:600;text-decoration:none">Calendrier complet → TV Baaboul Houda</a></div>';
    html += '</div>';
    el.innerHTML = html;
  }
  var url = 'https://api.aladhan.com/v1/timingsByCity/?city=' + encodeURIComponent(CITY) + '&method=3';
  fetch(url).then(function (r) { return r.json(); }).then(function (j) {
    render({ times: j.data.timings }, false);
  }).catch(function () {
    fetch('https://' + host + '/wp-json/bh/v1/prayer-fallback')
      .then(function (r) { return r.json(); })
      .then(function (j) { render(j, true); })
      .catch(function () { el.innerHTML = ''; });
  });
})();
JS;
    $js = str_replace('CITY', var_export($city, true), $js);
    $js = str_replace('PRAYER_PAGE', var_export($prayerPage, true), $js);
    return new \WP_REST_Response($js, 200, [
        'Content-Type'              => 'application/javascript; charset=UTF-8',
        'Access-Control-Allow-Origin' => '*',
    ]);
}

/** Fallback prayer data endpoint for the widget (offline Aladhan). */
add_action('rest_api_init', function () {
    \register_rest_route('bh/v1', '/prayer-fallback', [
        'methods'             => 'GET',
        'permission_callback' => '__return_true',
        'callback'            => function () {
            $f = function_exists('bh_prayer_fallback') ? bh_prayer_fallback('Ouagadougou', date('Y-m-d')) : [];
            return new \WP_REST_Response($f, 200, ['Access-Control-Allow-Origin' => '*']);
        },
    ]);
});

/**
 * Lightweight built-in pageview counter (no third-party).
 * POST /wp-json/bh/v1/analytics?path=/foo — increments a daily counter.
 */
add_action('rest_api_init', function () {
    \register_rest_route('bh/v1', '/analytics', [
        'methods'             => 'POST',
        'permission_callback' => '__return_true',
        'callback'            => function ($req) {
            $day = date('Y-m-d');
            $key = "bh_views_{$day}";
            $all = get_option('bh_analytics', []);
            if (!is_array($all)) {
                $all = [];
            }
            // keep at most 60 days
            $keys = array_keys($all);
            if (count($keys) > 60) {
                ksort($all);
                $all = array_slice($all, -60, null, true);
            }
            $all[$day] = (int) ($all[$day] ?? 0) + 1;
            update_option('bh_analytics', $all, false);
            return new \WP_REST_Response(['ok' => true], 200, ['Access-Control-Allow-Origin' => '*']);
        },
    ]);
});
