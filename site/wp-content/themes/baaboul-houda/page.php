<?php
/** Generic page (bilingual content). */
defined('ABSPATH') || exit;
get_header();
?>

<section class="bh-page-hero">
    <div class="bh-container">
        <h1><?php echo esc_html(bh_post_title(get_the_ID())); ?></h1>
    </div>
</section>

<section class="bh-section">
    <div class="bh-container bh-container--narrow bh-prose">
        <?php echo bh_post_content(get_the_ID()); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
    </div>
</section>

<?php get_footer(); ?>
