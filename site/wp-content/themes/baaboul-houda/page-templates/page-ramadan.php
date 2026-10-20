<?php
/**
 * Template Name: Ramadan
 *
 * Seasonal dashboard: seerah of the day, (in-Ramadan) imsak/iftar
 * countdown, Ramadan content, community iftars.
 */
defined('ABSPATH') || exit;
get_header();
$seerah  = function_exists('bh_seerah_ramadan') ? bh_seerah_ramadan() : [];
$todayI  = (int) date('j') - 1; // 0..29 within the month
$entry   = isset($seerah[$todayI]) ? $seerah[$todayI] : ($seerah[0] ?? null);
$events  = new \WP_Query(['post_type' => 'evenement', 'posts_per_page' => 6, 'no_found_rows' => true]);
?>
<div class="bh-container bh-page" x-data="bhRamadan()">
    <nav class="bh-breadcrumb" aria-label="Fil d’Ariane">
        <a href="<?php echo esc_url(bh_nav_url('direct')); ?>"><?php echo esc_html(bh_l('Accueil', 'الرئيسية')); ?></a>
        <span aria-hidden="true">/</span> <span><?php echo esc_html(bh_l('Ramadan', 'رمضان')); ?></span>
    </nav>
    <header class="bh-page__head">
        <h1><?php echo esc_html(bh_post_title(get_queried_object_id())); ?></h1>
        <p class="bh-page__lead"><?php echo esc_html(bh_l('Le mois de la révélation : suivez la chaîne pendant les 30 jours.', 'شهر الوحي: رافقوا القناة طوال الأيام الثلاثين.')); ?></p>
    </header>

    <template x-if="inRamadan()">
        <div class="bh-card bh-ramadan-count">
            <strong><?php echo esc_html(bh_l('Iftar dans', 'الإفطار بعد')); ?></strong>
            <span class="bh-ramadan__clock" x-text="iftarCountdown()"></span>
            <small><?php echo esc_html(bh_l('le jour de la fin du jeûne (19:30 environ, vérifiez la page prières)', 'وقت رفع الصوم (تقريباً 19:30، راجعوا صفحة الصلاة)')); ?></small>
        </div>
    </template>

    <div class="bh-grid bh-grid--2">
        <div class="bh-card bh-card--seerah">
            <h2 class="bh-card__title"><?php echo esc_html(bh_l('Seerah du jour', 'سيرة اليوم')); ?></h2>
            <?php if ($entry) : ?>
                <p><?php echo esc_html($entry['fr']); ?></p>
                <p class="bh-seerah__ar" dir="rtl" style="text-align:right;font-family:var(--bh-font-quran)"><?php echo esc_html($entry['ar']); ?></p>
            <?php endif; ?>
        </div>
        <div class="bh-card">
            <h2 class="bh-card__title"><?php echo esc_html(bh_l('Événements Ramadan', 'فعاليات رمضان')); ?></h2>
            <?php if ($events->have_posts()) : ?>
                <ul class="bh-rm-list">
                    <?php
                    while ($events->have_posts()) :
                        $events->the_post();
                        ?>
                        <li>
                            <a href="<?php echo esc_url(get_permalink()); ?>"><?php echo esc_html(get_the_title()); ?></a>
                            <small class="bh-muted"><?php echo esc_html(get_the_date()); ?></small>
                        </li>
                    <?php endwhile; ?>
                </ul>
            <?php else : ?>
                <p class="bh-muted"><?php echo esc_html(bh_l('Les iftars et soirées seront annoncés ici.', 'سيُعلن عن الإفطارات والسهريات هنا.')); ?></p>
            <?php endif; ?>
        </div>
    </div>

    <div class="bh-card">
        <h2 class="bh-card__title"><?php echo esc_html(bh_l('Contenu spécial Ramadan', 'محتوى خاص برمضان')); ?></h2>
        <p><?php echo esc_html(bh_l('Replays des émissions de Ramadan, réécoutes des tafsirs, spéciales de la radio : retrouvez-les dans Replays (filtre par programme).', 'مقاطع برامج رمضان، إعادة التفسيرات، خاصيات الإذاعة: تجدونها في "البث المسجل".')); ?></p>
        <p><a class="bh-btn" href="<?php echo esc_url(bh_nav_url('emissions')); ?>"><?php echo esc_html(bh_l('Voir les replays', 'عرض المقاطع المسجلة')); ?></a></p>
    </div>
</div>
<?php get_footer();
