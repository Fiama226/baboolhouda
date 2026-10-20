<?php
/**
 * Template Name: Guide du Wudu
 */
defined('ABSPATH') || exit;
get_header();
$steps = function_exists('bh_wudu_steps') ? bh_wudu_steps() : [];
?>
<div class="bh-container bh-page">
    <nav class="bh-breadcrumb" aria-label="Fil d’Ariane">
        <a href="<?php echo esc_url(bh_nav_url('direct')); ?>"><?php echo esc_html(bh_l('Accueil', 'الرئيسية')); ?></a>
        <span aria-hidden="true">/</span>
        <a href="<?php echo esc_url(bh_nav_url('outils')); ?>"><?php echo esc_html(bh_l('Outils', 'الأدوات')); ?></a>
        <span aria-hidden="true">/</span> <span><?php echo esc_html(bh_l('Wudu', 'الوضوء')); ?></span>
    </nav>
    <header class="bh-page__head">
        <h1><?php echo esc_html(bh_post_title(get_queried_object_id())); ?></h1>
        <p class="bh-page__lead"><?php echo esc_html(bh_l('La petite ablution, étape par étape.', 'الوضوء خطوة بخطوة.')); ?></p>
    </header>
    <ol class="bh-steps">
        <?php foreach ($steps as $i => $step) : ?>
            <li class="bh-steps__item">
                <span class="bh-steps__num" aria-hidden="true"><?php echo (int) ($i + 1); ?></span>
                <span class="bh-steps__text">
                    <span><?php echo esc_html($step['fr']); ?></span>
                    <span class="bh-steps__ar" dir="rtl"><?php echo esc_html($step['ar']); ?></span>
                </span>
            </li>
        <?php endforeach; ?>
    </ol>
    <p class="bh-note"><?php echo esc_html(bh_l('Ne pas oublier l’intention (niyya) dans le cœur, et de faire chaque partie trois fois (la tête une fois).', 'لا تنس النيّة في القلب، وثلاث مرات لكل جزء (الرأس مرة).')); ?></p>
</div>
<?php get_footer();
