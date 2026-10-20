<?php
/**
 * Lightweight SEO — no plugin dependency:
 *  - <title>, meta description, canonical
 *  - hreflang fr/ar alternates
 *  - Open Graph + Twitter card
 *  - JSON-LD: BroadcastChannel (home), VideoObject (episodes), Article (news)
 *  - preconnect hints for font & embed hosts
 *  - custom /sitemap.xml (FR + AR)
 */

defined('ABSPATH') || exit;

function bh_site_name(): string {
    $s  = bh_settings();
    $name = get_bloginfo('name');
    return $name ? $name : 'TV Baaboul Houda';
}

function bh_meta_description(): string {
    if (is_singular()) {
        $excerpt = get_the_excerpt();
        if ($excerpt) {
            return wp_strip_all_tags($excerpt);
        }
    }
    $s = bh_settings();
    return bh_is_ar()
        ? 'قناة باب الهدى — تلفزيون وإذاعة إسلامية في واغادوغو، بوركينا فاسو: بث مباشر، برامج، القرآن الكريم، مواقيت الصلاة.'
        : 'TV Baaboul Houda — TV et radio islamique à Ouagadougou (Burkina Faso) : en direct, programmes, Coran, prières, réplays.';
}

function bh_current_url(): string {
    if (is_singular()) {
        return get_permalink();
    }
    if (is_front_page()) {
        return home_url('/');
    }
    global $wp;
    return home_url((isset($wp->request) ? $wp->request : '') . '/');
}

add_action('wp_head', function () {
    $s   = bh_settings();
    $url = bh_current_url();
    $desc = html_entity_decode(bh_meta_description(), ENT_QUOTES, get_bloginfo('charset'));

    // Title
    $qo    = get_queried_object();
    $title = ($qo instanceof \WP_Post && function_exists('bh_post_title')) ? bh_post_title($qo) : get_the_title();
    if (is_404() || is_search()) {
        $title = is_404() ? (bh_is_ar() ? 'الصفحة غير موجودة' : 'Page introuvable') : (bh_is_ar() ? 'بحث' : 'Recherche');
    }
    if (!($qo instanceof \WP_Post) && is_home() && get_option('page_for_posts')) {
        $title = bh_post_title((int) get_option('page_for_posts'));
    }
    $sep   = ' | ';
    $site  = bh_site_name();
    $surahN = (int) get_query_var('bh_surah');
    $surah  = ($surahN && function_exists('bh_quran_surah')) ? bh_quran_surah($surahN) : null;
    if ($surah) {
        $full = (bh_is_ar() ? 'سورة ' . $surah['name_ar'] : 'Sourate ' . $surah['name_fr']) . ' (' . $surahN . ')' . $sep . $site;
        $desc = bh_is_ar()
            ? 'سورة ' . $surah['name_ar'] . ' — ' . $surah['ayah_count'] . ' آية: قراءة واستماع وتفسير.'
            : 'Sourate ' . $surah['name_fr'] . ' (' . $surah['meaning_fr'] . ') — ' . $surah['ayah_count'] . ' versets : lecture, écoute, traduction et tafsir.';
    } elseif (is_front_page()) {
        $full = $site . $sep . (bh_is_ar() ? 'قناة إسلامية — تلفزيون وإذاعة' : 'TV et radio islamiques');
    } else {
        $full = $title ? $title . $sep . $site : $site;
    }
    echo '<title>' . esc_html($full) . '</title>' . "\n";

    // Description / canonical
    echo '<meta name="description" content="' . esc_attr(wp_trim_words($desc, 30)) . '">' . "\n";
    echo '<link rel="canonical" href="' . esc_url(bh_alt_url(bh_lang())) . '">' . "\n";

    // hreflang
    $pairs = bh_hreflang_pairs();
    echo '<link rel="alternate" hreflang="fr" href="' . esc_url($pairs['fr']) . '">' . "\n";
    echo '<link rel="alternate" hreflang="ar" href="' . esc_url($pairs['ar']) . '">' . "\n";
    echo '<link rel="alternate" hreflang="x-default" href="' . esc_url($pairs['fr']) . '">' . "\n";

    // OG / Twitter
    $img = '';
    if (is_singular() && has_post_thumbnail()) {
        $src = wp_get_attachment_image_src(get_post_thumbnail_id(), 'large');
        $img = $src ? $src[0] : '';
    }
    $img = $img ?: $s['og_image_url'];
    echo '<meta property="og:type" content="' . (is_singular('episode') ? 'video.other' : 'website') . '">' . "\n";
    echo '<meta property="og:site_name" content="' . esc_attr($site) . '">' . "\n";
    echo '<meta property="og:title" content="' . esc_attr(wp_trim_words($full, 20)) . '">' . "\n";
    echo '<meta property="og:description" content="' . esc_attr(wp_trim_words($desc, 30)) . '">' . "\n";
    echo '<meta property="og:url" content="' . esc_url($url) . '">' . "\n";
    echo '<meta property="og:locale" content="' . (bh_is_ar() ? 'ar_AR' : 'fr_FR') . '">' . "\n";
    if ($img) {
        echo '<meta property="og:image" content="' . esc_url($img) . '">' . "\n";
    }
    echo '<meta name="twitter:card" content="summary' . ($img ? '_large_image' : '') . '">' . "\n";

    // preconnects (fonts are self-hosted; only video/live hosts are external)
    echo '<link rel="preconnect" href="https://www.youtube.com">' . "\n";
    echo '<link rel="preconnect" href="https://www.facebook.com">' . "\n";

    // JSON-LD
    $jsonld = ['@context' => 'https://schema.org', '@type' => 'BroadcastChannel', 'name' => 'TV Baaboul Houda', 'alternateName' => 'قناة باب الهدى', 'url' => home_url('/')];
    if (!empty($s['facebook_url'])) {
        $jsonld['sameAs'][] = $s['facebook_url'];
    }
    if (!empty($s['youtube_url'])) {
        $jsonld['sameAs'][] = $s['youtube_url'];
    }
    if (!empty($s['address'])) {
        $jsonld['location'] = ['@type' => 'Place', 'name' => 'Ouagadougou', 'addressCountry' => 'BF'];
    }
    if (is_singular('episode')) {
        $src = bh_episode_source(get_the_ID());
        if ($src['url']) {
            $jsonld = [
                '@context'      => 'https://schema.org',
                '@type'         => 'VideoObject',
                'name'          => get_the_title(),
                'description'   => wp_trim_words(bh_meta_description(), 30),
                'uploadDate'    => get_the_date('c'),
                'contentUrl'    => $src['url'],
                'embedUrl'      => $src['url'],
                'publisher'     => ['@type' => 'Organization', 'name' => 'TV Baaboul Houda'],
            ];
        }
    }
    echo '<script type="application/ld+json">' . wp_json_encode($jsonld, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . '</script>' . "\n";

    // Analytics (client-pasted snippet)
    if (!empty($s['analytics_snippet'])) {
        echo "\n" . $s['analytics_snippet'] . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- trusted admin snippet
    }
}, 1);

/**
 * Custom sitemap.xml — main FR + AR routes, posts, programmes, episodes.
 */
add_action('init', function () {
    add_rewrite_rule('^sitemap\.xml$', 'index.php?bh_sitemap=1', 'top');
});
add_filter('query_vars', function (array $vars): array {
    $vars[] = 'bh_sitemap';
    return $vars;
}, 20);

add_action('template_redirect', function () {
    if (!get_query_var('bh_sitemap')) {
        return;
    }
    nocache_headers();
    header('Content-Type: application/xml; charset=utf-8');
    $urls = [];
    $push = static function (string $url) use (&$urls) {
        $urls[] = $url;
    };
    $push(home_url('/'));
    $push(home_url('/ar/'));
    foreach (bh_pretty_sections() as $slug) {
        $push(home_url('/' . $slug . '/'));
        $push(home_url('/ar/' . $slug . '/'));
    }
    foreach (get_posts(['post_type' => 'post', 'numberposts' => -1, 'fields' => 'ids']) as $id) {
        $push(get_permalink($id));
    }
    foreach (['programme', 'episode', 'animateur', 'evenement'] as $type) {
        foreach (get_posts(['post_type' => $type, 'numberposts' => -1, 'fields' => 'ids']) as $id) {
            $push(get_permalink($id));
        }
    }
    echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
    echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xhtml="http://www.w3.org/1999/xhtml">' . "\n";
    foreach ($urls as $u) {
        echo "  <url><loc>" . esc_url($u) . "</loc></url>\n";
    }
    echo '</urlset>';
    exit;
});
