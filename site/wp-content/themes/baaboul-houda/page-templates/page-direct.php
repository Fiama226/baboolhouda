<?php
/**
 * Template Name: Accueil — En direct
 * Front page: live player + now/next + today's schedule + programmes +
 * latest replays + daily ayah + news.
 */
defined('ABSPATH') || exit;
get_header();

$live   = bh_live();
$nowNext = bh_now_next();
?>

<section class="bh-hero">
    <div class="bh-container bh-hero__grid">
        <div class="bh-hero__live">
            <div class="bh-hero__live-head">
                <span class="bh-live-pill" x-data="{ on: <?php echo !empty($live['enabled']) ? 'true' : 'false'; ?> }" :class="{ 'is-on': on }">
                    <?php echo !empty($live['enabled'])
                        ? esc_html(bh_l('● EN DIRECT', '● بث مباشر'))
                        : esc_html(bh_l('Hors antenne', 'خارج البث')); ?>
                </span>
                <h1 class="bh-hero__title"><?php echo esc_html(bh_l($live['title_fr'] ?: 'Diffusion en direct', $live['title_ar'] ?: 'بث مباشر')); ?></h1>
            </div>

            <?php if (!empty($live['enabled']) && '' !== trim($live['url'])) : ?>
                <div class="bh-hero__player"><?php echo bh_live_embed_html(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
            <?php else : ?>
                <div class="bh-hero__off">
                    <div class="bh-hero__off-icon" aria-hidden="true">📡</div>
                    <p><?php echo esc_html(bh_l('La chaîne est momentanément hors antenne. Retrouvez la grille des programmes ci-dessous et suivez-nous sur Facebook pour les prochains directs.', 'القناة غير موجودة على الهواء حاليًا. تجدون أدناه شبكة البرامج، وتابعونا على فيسبوك لآخر البثوث المباشرة.')); ?></p>
                    <?php if ($nowNext['next']) : ?>
                        <p class="bh-hero__off-next"><?php echo esc_html(bh_l('Prochaine antenne', 'البث القادم')) ?> : <strong><?php echo esc_html(bh_slot_title($nowNext['next'])); ?></strong> <?php echo esc_html($nowNext['next']['start']); ?></p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <?php if ($nowNext['current']) : ?>
                <p class="bh-hero__now">
                    <?php echo esc_html(bh_l('À l’antenne maintenant', 'على الهواء الآن')) ?> :
                    <a href="<?php echo $nowNext['current']['programme_id'] ? esc_url(get_permalink($nowNext['current']['programme_id'])) : '#'; ?>">
                        <strong><?php echo esc_html(bh_slot_title($nowNext['current'])); ?></strong>
                    </a>
                    <span class="bh-time-range"><?php echo esc_html($nowNext['current']['start'] . '–' . $nowNext['current']['end']); ?></span>
                </p>
            <?php endif; ?>
        </div>

        <aside class="bh-hero__side">
            <div class="bh-card bh-card--ayah" x-data="bhAyah()">
                <h2 class="bh-card__title"><?php echo esc_html(bh_l('Verset du jour', 'آية اليوم')); ?></h2>
                <p class="bh-ayah-ar" dir="rtl" x-text="ayah.text"></p>
                <p class="bh-ayah-ref" x-text="ref"></p>
                <a class="bh-link" :href="coranUrl + (surah ? '/' + surah : '')"><?php echo esc_html(bh_l('Lire le Coran →', 'اقرأ القرآن →')); ?></a>
            </div>

            <div class="bh-card bh-card--prayer" x-data="bhPrayer()">
                <h2 class="bh-card__title">
                    <select x-model="city" @change="load()" aria-label="<?php echo esc_attr(bh_l('Ville', 'المدينة')); ?>" class="bh-select bh-select--small">
                        <template x-for="c in cities" :key="c"><option :value="c" x-text="c"></option></template>
                    </select>
                </h2>
                <div class="bh-prayer-grid">
                    <template x-for="p in order" :key="p.key">
                        <div class="bh-prayer" :class="{ 'is-next': p.key === nextKey }">
                            <span class="bh-prayer__name" x-text="names[p.key]"></span>
                            <strong x-text="times[p.key] || '—:—'"></strong>
                        </div>
                    </template>
                </div>
                <p class="bh-prayer__src" x-show="source === 'fallback'"><?php echo esc_html(bh_l('Horaires indicatifs (hors-ligne)', 'أوقات تقريبية (دون اتصال)')); ?></p>
            </div>
        </aside>
    </div>
</section>

<section class="bh-section">
    <div class="bh-container">
        <div class="bh-section__head">
            <h2><?php echo esc_html(bh_l('Au programme aujourd’hui', 'برنامج اليوم')); ?></h2>
            <a class="bh-link" href="<?php echo esc_url(bh_nav_url('programmes')); ?>"><?php echo esc_html(bh_l('Grille complète', 'الشبكة الكاملة')); ?></a>
        </div>
        <?php get_template_part('template-parts/schedule', 'strip', ['day' => bh_schedule_weekday()]); ?>
    </div>
</section>

<section class="bh-section bh-section--alt">
    <div class="bh-container">
        <div class="bh-section__head">
            <h2><?php echo esc_html(bh_l('Nos programmes', 'برامجنا')); ?></h2>
            <a class="bh-link" href="<?php echo esc_url(bh_nav_url('programmes')); ?>"><?php echo esc_html(bh_l('Tout voir →', 'عرض الكل →')); ?></a>
        </div>
        <?php
        $programmes = new WP_Query([
            'post_type' => 'programme', 'posts_per_page' => 6, 'orderby' => 'menu_order title', 'order' => 'ASC', 'no_found_rows' => true,
        ]);
        if ($programmes->have_posts()) {
            echo '<div class="bh-grid bh-grid--3">';
            foreach ($programmes->posts as $p) {
                get_template_part('template-parts/programme', 'card', ['post' => $p]);
            }
            echo '</div>';
        } else {
            echo '<p class="bh-empty">' . esc_html(bh_l('Les programmes apparaîtront ici dès leur publication.', 'ستظهر البرامج هنا فور نشرها.')) . '</p>';
        }
        wp_reset_postdata();
        ?>
    </div>
</section>

<section class="bh-section">
    <div class="bh-container">
        <div class="bh-section__head">
            <h2><?php echo esc_html(bh_l('Derniers replays', 'آخر البثوث المسجلة')); ?></h2>
            <a class="bh-link" href="<?php echo esc_url(bh_nav_url('emissions')); ?>"><?php echo esc_html(bh_l('Tous les replays →', 'جميع الحلقات →')); ?></a>
        </div>
        <?php
        $episodes = new WP_Query([
            'post_type' => 'episode', 'posts_per_page' => 4, 'no_found_rows' => true,
        ]);
        if ($episodes->have_posts()) {
            echo '<div class="bh-grid bh-grid--4">';
            foreach ($episodes->posts as $e) {
                get_template_part('template-parts/episode', 'card', ['post' => $e]);
            }
            echo '</div>';
        } else {
            echo '<p class="bh-empty">' . esc_html(bh_l('Les replays apparaîtront ici.', 'ستظهر الحلقات المسجلة هنا.')) . '</p>';
        }
        wp_reset_postdata();
        ?>
    </div>
</section>

<section class="bh-section bh-section--alt">
    <div class="bh-container">
        <div class="bh-section__head">
            <h2><?php echo esc_html(bh_l('Actualités', 'الأخبار')); ?></h2>
            <a class="bh-link" href="<?php echo esc_url(bh_nav_url('actualites')); ?>"><?php echo esc_html(bh_l('Toutes les actualités →', 'جميع الأخبار →')); ?></a>
        </div>
        <?php get_template_part('template-parts/news', 'list', ['limit' => 3]); ?>
    </div>
</section>

<?php get_footer(); ?>
