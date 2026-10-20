<?php
/** Single presenter. */
defined('ABSPATH') || exit;
get_header();
?>

<section class="bh-section">
    <div class="bh-container bh-container--narrow bh-presenter-single">
        <article class="bh-card bh-card--presenter-lg">
            <?php if (has_post_thumbnail()) : ?>
                <?php echo get_the_post_thumbnail(get_the_ID(), 'bh-cover', ['class' => 'bh-presenter-single__photo']); ?>
            <?php endif; ?>
            <h1><?php echo esc_html(bh_post_title(get_the_ID())); ?></h1>
            <div class="bh-prose"><?php echo bh_post_content(get_the_ID()); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
        </article>
    </div>
</section>

<?php
// Episodes presented by this animateur.
$epQ = new WP_Query([
    'post_type'      => 'episode',
    'posts_per_page' => 12,
    'no_found_rows'  => true,
    'meta_query'     => [[
        'key'   => '_bh_presenter_id',
        'value' => get_the_ID(),
    ]],
]);
if ($epQ->have_posts()) :
    ?>
    <section class="bh-section bh-section--alt">
        <div class="bh-container">
            <div class="bh-section__head">
                <h2><?php echo esc_html(bh_l('Émissions présentées', 'البرامج المقدمة')); ?></h2>
            </div>
            <div class="bh-grid bh-grid--3">
                <?php
                foreach ($epQ->posts as $ep) {
                    get_template_part('template-parts/episode', 'card', ['post' => $ep]);
                }
                wp_reset_postdata();
                ?>
            </div>
        </div>
    </section>
    <?php
endif;
?>

<?php get_footer(); ?>
