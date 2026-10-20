<?php
/** Template Name: Replays & émissions (on-demand, filterable by programme) */
defined('ABSPATH') || exit;
get_header();

$programmeFilter = isset($_GET['programme']) ? (int) $_GET['programme'] : 0;

$programmes = new WP_Query(['post_type' => 'programme', 'posts_per_page' => -1, 'orderby' => 'title', 'order' => 'ASC', 'no_found_rows' => true]);
$episodes   = new WP_Query([
    'post_type'      => 'episode',
    'posts_per_page' => 24,
    'orderby'        => 'date',
    'order'          => 'DESC',
    'meta_query'     => $programmeFilter ? [['key' => '_bh_programme_id', 'value' => $programmeFilter]] : [],
]);
?>

<section class="bh-page-hero">
    <div class="bh-container">
        <h1><?php echo esc_html(bh_post_title(get_the_ID())); ?></h1>
        <p class="bh-page-hero__sub"><?php echo esc_html(bh_l('Re regardez et ré écoutez vos émissions préférées.', 'إعادة مشاهدة وإعادة استماع لبرامجك المفضلة.')); ?></p>
    </div>
</section>

<section class="bh-section">
    <div class="bh-container">
        <?php if ($programmes->have_posts()) : ?>
            <div class="bh-filter" role="group" aria-label="<?php echo esc_attr(bh_l('Filtrer par programme', 'تصفية حسب البرنامج')); ?>">
                <a class="bh-chip" href="<?php echo esc_url(bh_nav_url('emissions')); ?>" <?php echo $programmeFilter ? '' : 'aria-current="true"'; ?>><?php echo esc_html(bh_l('Tous', 'الكل')); ?></a>
                <?php foreach ($programmes->posts as $p) : ?>
                    <a class="bh-chip" href="<?php echo esc_url(add_query_arg('programme', $p->ID, bh_nav_url('emissions'))); ?>" <?php echo $programmeFilter === $p->ID ? 'aria-current="true"' : ''; ?>>
                        <?php echo esc_html(bh_post_title($p->ID)); ?>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if ($episodes->have_posts()) : ?>
            <div class="bh-grid bh-grid--3">
                <?php foreach ($episodes->posts as $e) : ?>
                    <?php get_template_part('template-parts/episode', 'card', ['post' => $e]); ?>
                <?php endforeach; ?>
            </div>
        <?php else : ?>
            <p class="bh-empty"><?php echo esc_html(bh_l('Aucun replay publié pour l’instant.', 'لا توجد حلقات مسجلة بعد.')); ?></p>
        <?php endif; ?>
        wp_reset_postdata();
    </div>
</section>

<?php get_footer(); ?>
