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

<?php get_footer(); ?>
