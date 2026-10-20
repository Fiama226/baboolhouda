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
        // Extension v1.1 (audit concurrentiel)
        'outils'      => ['Outils & calculs', 'الأدوات والحسابات', 'page-outils.php'],
        'zakat'       => ['Calculateur Zakat', 'حاسبة الزكاة', 'page-zakat.php'],
        'qibla'       => ['Direction de la Qibla', 'اتجاه القبلة', 'page-qibla.php'],
        'dates'       => ['Dates & calendrier', 'التواريخ والتقويم', 'page-dates.php'],
        'tasbih'      => ['Tasbih numérique', 'سبحة إلكترونية', 'page-tasbih.php'],
        'wudu'        => ['Guide du Wudu', 'دليل الوضوء', 'page-wudu.php'],
        'adhkar'      => ['Invocations (Adhkar)', 'الأذكار', 'page-adhkar.php'],
        'noms-allah'  => ['Les 99 noms d’Allah', 'أسماء الله الحسنى', 'page-noms.php'],
        'mosquees'    => ['Mosquées', 'الجمع', 'page-mosquees.php'],
        'dons'        => ['Dons & soutien', 'التبرع والدعم', 'page-dons.php'],
        'publicite'   => ['Publicité', 'الإعلان', 'page-publicite.php'],
        'temoignages' => ['Témoignages', 'الشهادات', 'page-temoignages.php'],
        'messages'    => ['Mots des fidèles', 'كلمات المصلين', 'page-messages.php'],
        'question'    => ['Posez une question', 'اطرح سؤالك', 'page-question.php'],
        'fatwas'      => ['Questions & réponses', 'الأسئلة والأجوبة', 'page-fatwas.php'],
        'galeries'    => ['Galleries photo', 'معرض الصور', 'page-galeries.php'],
        'podcast'     => ['Podcasts', 'البودكاست', 'page-podcast.php'],
        'ramadan'     => ['Ramadan', 'رمضان', 'page-ramadan.php'],
        'enfants'     => ['Enfants & jeunesse', 'الأطفال والشباب', 'page-enfants.php'],
        'presse'      => ['Espace presse', 'الصحافة', 'page-presse.php'],
        'newsletter'  => ['Newsletter', 'النشرة الإخبارية', 'page-newsletter.php'],
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
        // get_page_by_path() returns a WP_Post; wp_insert_post() an int ID.
        $page = $page instanceof WP_Post ? $page->ID : (int) $page;
        update_post_meta($page, '_bh_title_ar', $titleAr);
        // Templates live in the theme's page-templates/ subfolder.
        update_post_meta($page, '_wp_page_template', 'page-templates/' . $template);
    }

    // Newsletter table (idempotent).
    if (function_exists('bh_newsletter_install')) {
        bh_newsletter_install();
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

// Re-run the (idempotent) setup once per plugin version, so sites that were
// activated by an older release get their pages/templates repaired.
add_action('init', function () {
    if (get_option('bh_core_version') === BH_VERSION) {
        return;
    }
    update_option('bh_core_version', BH_VERSION);
    bh_activate();
}, 20);

function bh_deactivate(): void {
    flush_rewrite_rules();
}

/** Helper for themes: the page ID of a section (0 if missing). */
function bh_page_id(string $slug): int {
    $page = get_page_by_path($slug);
    return $page ? $page->ID : 0;
}
