<?php
/**
 * Baaboul Houda theme — setup & asset plumbing.
 * All runtime assets are pre-built (assets/dist) and committed:
 * the cPanel release zip never needs Node.js.
 */

defined('ABSPATH') || exit;

define('BH_THEME_VERSION', '1.0.0');
define('BH_THEME_ASSETS', get_template_directory_uri() . '/assets/dist');
define('BH_THEME_SRC', get_template_directory() . '/assets-src');

add_action('after_setup_theme', function () {
    load_theme_textdomain('baaboul-houda', get_template_directory() . '/languages');
    // Note: no 'title-tag' support — the core plugin (seo.php) outputs the
    // language-aware <title>, meta description, hreflang and OG tags itself.
    add_theme_support('post-thumbnails');
    add_theme_support('automatic-feed-links');
    add_theme_support('html5', ['search-form', 'gallery', 'caption', 'style', 'script', 'navigation-widgets']);
    add_theme_support('customize-selective-refresh-widgets');
    add_image_size('bh-card', 640, 400, true);
    add_image_size('bh-cover', 1200, 630, true);

    register_nav_menus([
        'primary' => bh_l('Menu principal', 'القائمة الرئيسية'),
    ]);

    // Make AR query var reachable for menus/links.
    add_rewrite_rule('^ar/?$', 'index.php?bh_lang=ar', 'top');
});

add_action('init', function () {
    add_rewrite_rule('^ar/?$', 'index.php?bh_lang=ar', 'top');
    add_rewrite_rule('^ar/([a-z0-9\-]+)/?$', 'index.php?bh_lang=ar&name=$matches[1]', 'top');
    add_rewrite_rule('^coran/([1-9][0-9]{0,2})/?$', 'index.php?bh_surah=$matches[1]', 'top');
});
add_filter('query_vars', function (array $vars): array {
    foreach (['bh_lang', 'bh_surah'] as $v) {
        if (!in_array($v, $vars, true)) {
            $vars[] = $v;
        }
    }
    return $vars;
});

add_action('wp_enqueue_scripts', function () {
    wp_enqueue_style('bh-fonts', 'https://fonts.googleapis.com/css2?family=Amiri+Quran&family=Amiri:wght@400;700&family=Cairo:wght@400;500;600;700;800&display=swap', [], null);
    wp_enqueue_style('bh-style', BH_THEME_ASSETS . '/css/style.css', [], BH_THEME_VERSION);
    wp_enqueue_script('bh-main', BH_THEME_ASSETS . '/js/main.js', [], BH_THEME_VERSION, true);
    wp_localize_script('bh-main', 'bhCfg', bh_client_config());
    // Quran reader features — only on /coran/{n}/ to keep other pages light.
    if (get_query_var('bh_surah')) {
        wp_enqueue_script('bh-quran', BH_THEME_ASSETS . '/js/quran.js', ['bh-main'], BH_THEME_VERSION, true);
    }
});

/**
 * Everything the front-end JS needs, in one small JSON blob.
 */
function bh_client_config(): array {
    $s = function_exists('bh_settings') ? bh_settings() : [];
    $live = function_exists('bh_live') ? bh_live() : [];
    $radio = [
        'enabled' => !empty($s['radio_enabled']) && '' !== trim((string) ($s['radio_url'] ?? '')),
        'url'     => (string) ($s['radio_url'] ?? ''),
        'title_fr' => $s['radio_title_fr'] ?? '',
        'title_ar' => $s['radio_title_ar'] ?? '',
        'cover'   => (string) ($s['radio_cover_url'] ?? ''),
    ];

    $quranIndex = [];
    if (function_exists('bh_quran_data')) {
        foreach (bh_quran_data()['surahs'] ?? [] as $su) {
            $quranIndex[] = [
                'n' => $su['n'], 'ar' => $su['name_ar'], 'fr' => $su['name_fr'],
                'meaning' => $su['meaning_fr'], 'type' => $su['type'], 'ayahs' => $su['ayah_count'],
            ];
        }
    }

    $fallback = [];
    $f = function_exists('bh_prayer_fallback') ? bh_prayer_fallback('Ouagadougou', date('Y-m-d')) : [];
    if ($f) {
        $fallback = $f['times'];
    }

    $qiblaBears = [];
    if (function_exists('bh_known_cities') && function_exists('bh_qibla_for_city')) {
        foreach (array_keys(bh_known_cities()) as $city) {
            $b = bh_qibla_for_city($city);
            if ($b !== null) {
                $qiblaBears[(string) $city] = round((float) $b, 1);
            }
        }
    }

    return [
        'lang'          => function_exists('bh_lang') ? bh_lang() : 'fr',
        'isAr'          => function_exists('bh_is_ar') ? bh_is_ar() : false,
        'home'          => home_url('/'),
        'altFr'         => function_exists('bh_alt_url') ? bh_alt_url('fr') : home_url('/'),
        'altAr'         => function_exists('bh_alt_url') ? bh_alt_url('ar') : '',
        'coranUrl'      => get_permalink(function_exists('bh_page_id') ? bh_page_id('coran') : 0) ?: home_url('/coran/'),
        'live'          => $live,
        'radio'         => $radio,
        'now'           => function_exists('bh_now_next') ? bh_now_next() : null,
        'whatsapp'      => preg_replace('/[^0-9+]/', '', (string) ($s['whatsapp'] ?? '')),
        'phone'         => (string) ($s['phone'] ?? ''),
        'accent'        => (string) ($s['color_accent'] ?? '#0e7a4e'),
        'facebook'      => (string) ($s['facebook_url'] ?? ''),
        'youtube'       => (string) ($s['youtube_url'] ?? ''),
        'prayer'        => [
            'city'    => (string) ($s['prayer_city'] ?? 'Ouagadougou'),
            'method'  => (int) ($s['prayer_method'] ?? 3),
            'cities'  => array_values(array_filter(array_map('trim', explode(',', (string) ($s['prayer_cities'] ?? ''))))),
            'times'   => $fallback,
            'source'  => $f['source'] ?? 'fallback',
        ],
        'quran'         => [
            'index'   => $quranIndex,
            'audioBases' => function_exists('bh_quran_audio_base') ? [
                'ar.alafasy'              => bh_quran_audio_base('ar.alafasy'),
                'ar.minshawi'             => bh_quran_audio_base('ar.minshawi'),
                'ar.abdulbasitmurattal'   => bh_quran_audio_base('ar.abdulbasitmurattal'),
            ] : [],
        ],
        'dailyAyah'     => function_exists('bh_daily_ayah') ? bh_daily_ayah() : null,
        'hijri'         => null, // filled client-side by the prayer widget
        'live2'         => function_exists('bh_live_channel2') ? bh_live_channel2() : ['enabled' => false],
        'rest'          => [
            'status'     => esc_url_raw(rest_url('bh/v1/status')),
            'search'     => esc_url_raw(rest_url('bh/v1/search')),
            'newsletter' => esc_url_raw(rest_url('bh/v1/newsletter')),
        ],
        'qiblaBears'    => $qiblaBears,
        'analytics'     => !empty($s['analytics_enabled']),
    ];
}

/** Logo: admin URL if set, otherwise the theme SVG. */
function bh_logo_html(int $height = 52): string {
    $s = function_exists('bh_settings') ? bh_settings() : [];
    if (!empty($s['logo_url'])) {
        return '<img class="bh-logo-img" src="' . esc_url($s['logo_url']) . '" alt="' . esc_attr(get_bloginfo('name')) . '" height="' . (int) $height . '" loading="eager">';
    }
    return '<svg class="bh-logo" viewBox="0 0 64 64" height="' . (int) $height . '" role="img" aria-label="' . esc_attr(get_bloginfo('name')) . '">'
        . '<rect width="64" height="64" rx="14" fill="var(--bh-green)"/>'
        . '<path d="M32 12c-9 0-16 7-16 16v6h4v-6c0-6.6 5.4-12 12-12s12 5.4 12 12v6h4v-6c0-9-7-16-16-16z" fill="#fff"/>'
        . '<circle cx="32" cy="38" r="7" fill="#fff"/>'
        . '<path d="M30 38a6 6 0 0 1 0-7.5 5 5 0 1 0 0 15A6 6 0 0 1 30 38z" fill="var(--bh-green)"/>'
        . '<rect x="18" y="49" width="28" height="3.5" rx="1.75" fill="var(--bh-gold)"/>'
        . '</svg>';
}

/** Current nav URL (language-aware: /ar/{slug}/ in Arabic mode). */
function bh_nav_url(string $slug): string {
    if (function_exists('bh_is_ar') && bh_is_ar()) {
        return home_url('/ar/' . $slug . '/');
    }
    $id = function_exists('bh_page_id') ? bh_page_id($slug) : 0;
    if ($id) {
        return get_permalink($id);
    }
    return home_url('/' . $slug . '/');
}

/** Language switcher link (current URL in the other language). */
function bh_lang_switch_html(): string {
    if (!function_exists('bh_alt_url')) {
        return '';
    }
    $other = bh_is_ar() ? 'fr' : 'ar';
    $label = $other === 'ar' ? 'العربية' : 'Français';
    $cls   = 'bh-lang-switch bh-lang-switch--' . $other;
    return '<a class="' . $cls . '" href="' . esc_url(bh_alt_url($other)) . '" rel="alternate" hreflang="' . esc_attr($other) . '">' . esc_html($label) . '</a>';
}

/** Nav items — hand-rolled (no walker bloat), bilingual labels. */
function bh_nav_items(): array {
    return [
        ['label_fr' => 'Accueil', 'label_ar' => 'الرئيسية', 'slug' => 'direct', 'front' => true],
        ['label_fr' => 'En direct', 'label_ar' => 'مباشر', 'slug' => 'direct', 'live' => true],
        ['label_fr' => 'Radio', 'label_ar' => 'الإذاعة', 'slug' => 'radio'],
        ['label_fr' => 'Programmes', 'label_ar' => 'البرامج', 'slug' => 'programmes'],
        ['label_fr' => 'Replays', 'label_ar' => 'البث المسجل', 'slug' => 'emissions'],
        ['label_fr' => 'Coran', 'label_ar' => 'القرآن', 'slug' => 'coran'],
        ['label_fr' => 'Prières', 'label_ar' => 'الصلاة', 'slug' => 'prieres'],
        ['label_fr' => 'Outils', 'label_ar' => 'الأدوات', 'slug' => 'outils'],
        ['label_fr' => 'Actualités', 'label_ar' => 'الأخبار', 'slug' => 'actualites'],
        ['label_fr' => 'Événements', 'label_ar' => 'الفعاليات', 'slug' => 'evenements'],
        ['label_fr' => 'Contact', 'label_ar' => 'اتصل بنا', 'slug' => 'contact'],
    ];
}

add_action('wp_head', function () {
    // PWA
    echo '<link rel="manifest" href="' . esc_url(home_url('/manifest.json')) . '">' . "\n";
    echo '<link rel="icon" href="' . esc_url(get_template_directory_uri() . '/assets/icons/favicon.svg') . '" type="image/svg+xml">' . "\n";
    echo '<link rel="apple-touch-icon" href="' . esc_url(get_template_directory_uri() . '/assets/icons/apple-touch-icon.png') . '">' . "\n";
    echo '<meta name="theme-color" content="' . esc_attr(function_exists('bh_opt') ? bh_opt('color_accent', '#0e7a4e') : '#0e7a4e') . '">' . "\n";
});
