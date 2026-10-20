<?php
/** Episode (replay) card. Expects $post (WP_Post). */
defined('ABSPATH') || exit;
$e   = $post;
$src = bh_episode_source($e->ID);
$prog = bh_episode_programme($e->ID);
$thumb = has_post_thumbnail($e) ? get_the_post_thumbnail_url($e, 'bh-card') : '';
$epDate = get_post_meta($e->ID, '_bh_date', true);
$sourceLabels = [
    'youtube'  => ['YouTube', 'يوتيوب'],
    'facebook' => ['Facebook', 'فيسبوك'],
    'file'     => ['Vidéo', 'فيديو'],
    'audio'    => ['Audio', 'صوت'],
];
$label = $sourceLabels[$src['type']][0];
$labelAr = $sourceLabels[$src['type']][1];
?>
<a class="bh-card bh-card--episode" href="<?php echo esc_url(get_permalink($e)); ?>">
    <?php if ($thumb) : ?>
        <span class="bh-card__img bh-card__img--episode">
            <img src="<?php echo esc_url($thumb); ?>" alt="" loading="lazy" width="640" height="400">
            <span class="bh-card__play" aria-hidden="true">▶</span>
        </span>
    <?php else : ?>
        <span class="bh-card__img bh-card__img--placeholder" aria-hidden="true">▶</span>
    <?php endif; ?>
    <span class="bh-card__body">
        <strong><?php echo esc_html(bh_post_title($e->ID)); ?></strong>
        <small>
            <?php if ($prog) : ?><?php echo esc_html(bh_post_title($prog->ID)); ?> · <?php endif; ?>
            <?php if ($epDate) : ?><?php echo esc_html(date_i18n('j M Y', strtotime($epDate))); ?> · <?php endif; ?>
            <?php echo esc_html(bh_l($label, $labelAr)); ?>
        </small>
    </span>
</a>
