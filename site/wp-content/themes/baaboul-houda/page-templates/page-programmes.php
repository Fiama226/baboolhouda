<?php
/** Template Name: Programmes (grille hebdomadaire + liste) */
defined('ABSPATH') || exit;
get_header();
?>

<section class="bh-page-hero">
    <div class="bh-container">
        <h1><?php echo esc_html(bh_post_title(get_the_ID())); ?></h1>
        <p class="bh-page-hero__sub"><?php echo esc_html(bh_l('La grille complète de la semaine.', 'الشبكة الكاملة للأسبوع.')); ?></p>
    </div>
</section>

<section class="bh-section">
    <div class="bh-container">
        <div class="bh-section__head">
            <h2><?php echo esc_html(bh_l('Grille hebdomadaire', 'الشبكة الأسبوعية')); ?></h2>
        </div>
        <?php get_template_part('template-parts/schedule', 'grid'); ?>
    </div>
</section>

<section class="bh-section bh-section--alt">
    <div class="bh-container">
        <div class="bh-section__head">
            <h2><?php echo esc_html(bh_l('Tous les programmes', 'جميع البرامج')); ?></h2>
        </div>
        <?php
        $programmes = new WP_Query([
            'post_type' => 'programme', 'posts_per_page' => -1, 'orderby' => 'menu_order title', 'order' => 'ASC',
        ]);
        if ($programmes->have_posts()) {
            echo '<div class="bh-grid bh-grid--3">';
            foreach ($programmes->posts as $p) {
                get_template_part('template-parts/programme', 'card', ['post' => $p]);
            }
            echo '</div>';
        } else {
            echo '<p class="bh-empty">' . esc_html(bh_l('Aucun programme publié pour l’instant.', 'لا توجد برامج منشورة بعد.')) . '</p>';
        }
        wp_reset_postdata();
        ?>
    </div>
</section>

<?php get_footer(); ?>
