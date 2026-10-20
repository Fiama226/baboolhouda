<?php
/** Single event. */
defined('ABSPATH') || exit;
get_header();
$eventTs = strtotime(get_the_date('Y-m-d'));
?>

<section class="bh-section">
    <div class="bh-container bh-container--narrow">
        <article class="bh-card bh-card--event">
            <header class="bh-card--event__head">
                <span class="bh-event-card__date bh-event-card__date--lg">
                    <strong><?php echo esc_html(date_i18n('j', $eventTs)); ?></strong>
                    <em><?php echo esc_html(date_i18n('F Y', $eventTs)); ?></em>
                </span>
                <div>
                    <h1><?php echo esc_html(bh_post_title(get_the_ID())); ?></h1>
                    <p class="bh-card--event__meta"><?php echo esc_html(date_i18n('l j F Y', $eventTs)); ?></p>
                </div>
            </header>
            <div class="bh-prose"><?php echo bh_post_content(get_the_ID()); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
        </article>
    </div>
</section>

<?php get_footer(); ?>
