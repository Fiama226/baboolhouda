<?php
/** Template Name: Animateurs */
defined('ABSPATH') || exit;
get_header();

$presenters = new WP_Query(['post_type' => 'animateur', 'posts_per_page' => -1, 'orderby' => 'title', 'order' => 'ASC']);
?>

<section class="bh-page-hero">
    <div class="bh-container">
        <h1><?php echo esc_html(bh_post_title(get_queried_object_id())); ?></h1>
        <p class="bh-page-hero__sub"><?php echo esc_html(bh_l('Les voix et les visages de la chaîne.', 'أصوات وأوجه القناة.')); ?></p>
    </div>
</section>

<section class="bh-section">
    <div class="bh-container">
        <?php if ($presenters->have_posts()) : ?>
            <div class="bh-grid bh-grid--4 bh-presenters">
                <?php foreach ($presenters->posts as $p) : ?>
                    <a class="bh-card bh-card--presenter" href="<?php echo esc_url(get_permalink($p)); ?>">
                        <?php if (has_post_thumbnail($p)) : ?>
                            <?php echo get_the_post_thumbnail($p, 'bh-card', ['class' => 'bh-card--presenter-img']); ?>
                        <?php else : ?>
                            <span class="bh-presenter-placeholder" aria-hidden="true">🎙</span>
                        <?php endif; ?>
                        <strong><?php echo esc_html(bh_post_title($p)); ?></strong>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php else : ?>
            <p class="bh-empty"><?php echo esc_html(bh_l('Les animateurs apparaîtront ici.', 'سيظهر مقدمو البرامج هنا.')); ?></p>
        <?php endif; ?>
    </div>
</section>

<?php get_footer(); ?>
