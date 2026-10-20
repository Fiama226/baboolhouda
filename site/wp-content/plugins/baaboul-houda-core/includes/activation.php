<?php
/**
 * Activation: create the section pages (FR + AR titles) and map them to
 * their theme templates. Idempotent — safe to run on every upgrade.
 */

defined('ABSPATH') || exit;

register_activation_hook(BH_CORE_FILE, 'bh_activate');
register_deactivation_hook(BH_CORE_FILE, 'bh_deactivate');

function bh_section_pages(): array {
    return [
        'direct'    => ['Accueil — En direct', 'البث المباشر', 'page-direct.php'],
        'radio'     => ['Radio', 'الإذاعة', 'page-radio.php'],
        'programmes' => ['Programmes', 'البرامج', 'page-programmes.php'],
        'emissions' => ['Replays & émissions', 'البث المسجل', 'page-emissions.php'],
        'coran'     => ['Coran', 'القرآن الكريم', 'page-coran.php'],
        'prieres'   => ['Heures de prière', 'مواقيت الصلاة', 'page-prieres.php'],
        'evenements' => ['Événements', 'الفعاليات', 'page-evenements.php'],
        'animateurs' => ['Animateurs', 'المقدمون', 'page-animateurs.php'],
        'contact'   => ['Contact', 'اتصل بنا', 'page-contact.php'],
        'apropos'   => ['À propos', 'من نحن', 'page-apropos.php'],
        'actualites' => ['Actualités', 'الأخبار', 'page-actualites.php'],
    ];
}

function bh_activate(): void {
    foreach (bh_section_pages() as $slug => [$title, $titleAr, $template]) {
        $page = get_page_by_path($slug);
        if (!$page) {
            $page = wp_insert_post([
                'post_type'    => 'page',
                'post_status'  => 'publish',
                'post_title'   => $title,
                'post_name'    => $slug,
                'post_content' => '',
            ]);
        }
        if (is_wp_error($page) || !$page) {
            continue;
        }
        update_post_meta($page, '_bh_title_ar', $titleAr);
        update_post_meta($page, '_wp_page_template', $template);
    }

    // Mark the "direct" page as the static front page (recommended layout).
    $front = get_option('show_on_front');
    if ('page' !== $front || !get_option('page_on_front')) {
        $direct = get_page_by_path('direct');
        if ($direct) {
            update_option('show_on_front', 'page');
            update_option('page_on_front', $direct->ID);
            $news = get_page_by_path('actualites');
            if ($news && !get_option('page_for_posts')) {
                update_option('page_for_posts', $news->ID);
            }
        }
    }

    flush_rewrite_rules();
}

function bh_deactivate(): void {
    flush_rewrite_rules();
}

/** Helper for themes: the page ID of a section (0 if missing). */
function bh_page_id(string $slug): int {
    $page = get_page_by_path($slug);
    return $page ? $page->ID : 0;
}
