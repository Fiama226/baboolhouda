<?php
/** Template Name: Actualités (liste des articles) */
defined('ABSPATH') || exit;
get_header();
?>

<section class="bh-page-hero">
    <div class="bh-container">
        <h1><?php echo esc_html(bh_post_title(get_the_ID())); ?></h1>
        <p class="bh-page-hero__sub"><?php echo esc_html(bh_l('Dernières nouvelles et reportages de la chaîne.', 'آخر أخبار القناة وتقاريرها.')); ?></p>
    </div>
</section>

<section class="bh-section">
    <div class="bh-container">
        <?php get_template_part('template-parts/news', 'list', ['limit' => 20]); ?>
    </div>
</section>

<?php get_footer(); ?>
