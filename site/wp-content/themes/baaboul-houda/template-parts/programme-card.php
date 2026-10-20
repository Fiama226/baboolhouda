<?php
/** Programme card. Expects $post (WP_Post). */
defined('ABSPATH') || exit;
$p = $args["post"] ?? get_post();
$logo = has_post_thumbnail($p) ? get_the_post_thumbnail_url($p, 'bh-card') : '';
?>
<a class="bh-card bh-card--programme" href="<?php echo esc_url(get_permalink($p)); ?>">
    <?php if ($logo) : ?>
        <span class="bh-card__img"><img src="<?php echo esc_url($logo); ?>" alt="" loading="lazy" width="640" height="400"></span>
    <?php else : ?>
        <span class="bh-card__img bh-card__img--placeholder" aria-hidden="true">📺</span>
    <?php endif; ?>
    <span class="bh-card__body">
        <strong><?php echo esc_html(bh_post_title($p->ID)); ?></strong>
        <small><?php echo esc_html(bh_post_excerpt($p->ID, 14)); ?></small>
    </span>
</a>
