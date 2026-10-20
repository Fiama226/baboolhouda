<?php
/**
 * Bilingual FR/AR layer.
 *
 * French is the primary language (stored natively in WP). Arabic is stored in
 * companion meta fields and served under the same URL via ?lang=ar, plus pretty
 * /ar/{section}/ URLs for the main section pages (created at activation).
 *
 * The language switcher (theme) always generates matching FR/AR links.
 */

defined('ABSPATH') || exit;

/** Sections that get pretty /ar/{slug}/ URLs (created by activation.php). */
function bh_pretty_sections(): array {
    return ['direct', 'radio', 'programmes', 'emissions', 'coran', 'prieres', 'evenements', 'animateurs', 'contact', 'apropos', 'actualites'];
}

function bh_is_ar(): bool {
    if (isset($_GET['lang']) && 'ar' === $_GET['lang']) {
        return true;
    }
    $global_lang = get_query_var('bh_lang');
    return 'ar' === $global_lang;
}

function bh_lang(): string {
    return bh_is_ar() ? 'ar' : 'fr';
}

/** Current URL with the language flipped (for the switcher + hreflang). */
function bh_alt_url(string $lang): string {
    // Rebuild from the request URI for full reliability.
    $uri = isset($_SERVER['REQUEST_URI']) ? sanitize_text_field(wp_unslash($_SERVER['REQUEST_URI'])) : '/';
    $base = home_url('');
    $uri = str_replace($base, '', $uri);
    if ('' === $uri) {
        $uri = '/';
    }

    $parts = explode('?', $uri, 2);
    $path  = $parts[0];
    $query = isset($parts[1]) ? $parts[1] : '';
    parse_str($query, $args);
    unset($args['lang']);
    if ('ar' !== $lang) {
        unset($args['lang']);
    } else {
        $args['lang'] = 'ar';
    }

    // Pretty-ify known sections.
    if ('/' === $path && 'ar' === $lang) {
        $path = '/ar/';
    } elseif (preg_match('#^/ar/?$#', $path) && 'fr' === $lang) {
        $path = '/';
    } elseif (preg_match('#^/ar/([a-z0-9\-]+)/?$#', $path, $m) && 'fr' === $lang) {
        $path = '/' . $m[1] . '/';
    } elseif (in_array(substr($path, 1, -1), bh_pretty_sections(), true) && 'ar' === $lang) {
        $path = '/ar' . $path; // /section/ -> /ar/section/
    }

    $qs = $args ? '?' . http_build_query($args) : '';
    return home_url($path . $qs);
}

/** hreflang pair for <head>. */
function bh_hreflang_pairs(): array {
    return [
        'fr' => bh_alt_url('fr'),
        'ar' => bh_alt_url('ar'),
    ];
}

/**
 * Bilingual title: native WP title (FR) or _bh_title_ar. Falls back to FR.
 */
function bh_post_title(\WP_Post|int $post): string {
    $id   = ($post instanceof \WP_Post) ? $post->ID : (int) $post;
    $fr   = get_the_title($id);
    $ar   = get_post_meta($id, '_bh_title_ar', true);
    return (bh_is_ar() && $ar) ? $ar : $fr;
}

/**
 * Bilingual body: native post_content (FR) or _bh_content_ar (wpautop applied
 * on save). Falls back to FR.
 */
function bh_post_content(\WP_Post|int $post): string {
    $id   = ($post instanceof \WP_Post) ? $post->ID : (int) $post;
    $ar   = get_post_meta($id, '_bh_content_ar', true);
    $html = (bh_is_ar() && $ar) ? $ar : apply_filters('the_content', get_post_field('post_content', $id));
    return $html;
}

function bh_post_excerpt(\WP_Post|int $post, int $length = 120): string {
    $id   = ($post instanceof \WP_Post) ? $post->ID : (int) $post;
    $fr   = get_the_excerpt($id);
    $ar   = get_post_meta($id, '_bh_excerpt_ar', true);
    $text = (bh_is_ar() && $ar) ? $ar : $fr;
    $text = wp_strip_all_tags($text);
    $words = preg_split('/\s+/u', $text);
    if (count($words) > $length) {
        return implode(' ', array_slice($words, 0, $length)) . '…';
    }
    return $text;
}

/** Read a bilingual option: $settings['x_fr'] / $settings['x_ar']. */
function bh_opt(string $key, $default = '') {
    $settings = get_option('bh_settings', []);
    $value    = is_array($settings) ? ($settings[$key] ?? $default) : $default;
    return $value;
}

function bh_opt_l(string $base_key, $default = ''): string {
    $suffix = bh_is_ar() ? '_ar' : '_fr';
    $value  = bh_opt($base_key . $suffix);
    if ($value) {
        return (string) $value;
    }
    // Fall back to the other language, then default.
    $other = bh_opt($base_key . ('_ar' === $suffix ? '_fr' : '_ar'));
    return $other ? (string) $other : (string) $default;
}

/* ---------------------------------------------------------------------
 * Routing: ?lang=ar query var + pretty /ar/ + /ar/{section}/
 * ------------------------------------------------------------------- */
add_action('init', function () {
    add_rewrite_rule('^ar/?$', 'index.php?bh_lang=ar', 'top');
    add_rewrite_rule('^ar/([a-z0-9\-]+)/?$', 'index.php?bh_lang=ar&name=$matches[1]', 'top');
    add_rewrite_rule('^coran/([1-9][0-9]{0,2})/?$', 'index.php?bh_surah=$matches[1]', 'top');
});
add_filter('query_vars', function (array $vars): array {
    $vars[] = 'bh_lang';
    $vars[] = 'bh_surah';
    return $vars;
});

/** Serve the Quran surah template from the theme. */
add_filter('template_include', function ($template) {
    $surah = (int) get_query_var('bh_surah');
    if ($surah >= 1 && $surah <= 114) {
        $candidate = get_template_directory() . '/page-templates/quran-surah.php';
        if (file_exists($candidate)) {
            return $candidate;
        }
    }
    return $template;
}, 20);

/** Language-aware <html> attributes (dir/lang) and body class. */
add_filter('language_attributes', function (): string {
    $ar = bh_is_ar();
    return 'dir="' . ($ar ? 'rtl' : 'ltr') . '" lang="' . bh_lang() . '"';
});
add_filter('body_class', function (array $classes): array {
    $classes[] = 'bh-lang-' . bh_lang();
    $classes[] = bh_is_ar() ? 'bh-rtl' : 'bh-ltr';
    return $classes;
});

// Note: /ar/{slug}/ maps to index.php?name={slug} — WP natively resolves the
// page with that slug; the theme renders it in Arabic via bh_is_ar().

