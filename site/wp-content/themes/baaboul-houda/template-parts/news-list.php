<?php
/** News list. Expects optional $limit. */
defined('ABSPATH') || exit;
$limit = isset($args['limit']) ? (int) $args['limit'] : 6;
$news = new WP_Query([
    'post_type' => 'post', 'posts_per_page' => $limit, 'no_found_rows' => true,
]);
$variant = $args['variant'] ?? '';
if ($news->have_posts() && 'featured' === $variant) :
    // Home layout: one large lead story + compact stories beside it.
    $items = $news->posts;
    $lead  = array_shift($items);
    $media = static function (WP_Post $n, string $size): string {
        if (has_post_thumbnail($n)) {
            return '<img src="' . esc_url(get_the_post_thumbnail_url($n, $size)) . '" alt="" loading="lazy" width="640" height="400">';
        }
        // No picture: branded panel with the logo as a watermark.
        return '<span class="bh-news-ph" aria-hidden="true"><img src="' . esc_url(bh_brand_url('logo-192.png')) . '" alt="" width="96" height="96" loading="lazy"></span>';
    };
    ?>
    <div class="bh-news-featured">
        <a class="bh-news-lead" href="<?php echo esc_url(get_permalink($lead)); ?>">
            <span class="bh-news-lead__media"><?php echo $media($lead, 'large'); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
            <span class="bh-news-lead__body">
                <time class="bh-news-chip" datetime="<?php echo esc_attr(get_the_date('c', $lead)); ?>"><?php echo esc_html(date_i18n('j F Y', get_the_time('U', $lead))); ?></time>
                <strong class="bh-news-lead__title"><?php echo esc_html(bh_post_title($lead->ID)); ?></strong>
                <span class="bh-news-lead__excerpt"><?php echo esc_html(bh_post_excerpt($lead->ID, 28)); ?></span>
                <span class="bh-news-more"><?php echo esc_html(bh_l('Lire l’article', 'اقرأ المقال')); ?> <span aria-hidden="true"><?php echo bh_is_ar() ? '←' : '→'; ?></span></span>
            </span>
        </a>
        <?php if ($items) : ?>
            <div class="bh-news-side">
                <?php foreach ($items as $n) : ?>
                    <a class="bh-news-item" href="<?php echo esc_url(get_permalink($n)); ?>">
                        <span class="bh-news-item__media"><?php echo $media($n, 'bh-card'); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
                        <span class="bh-news-item__body">
                            <time datetime="<?php echo esc_attr(get_the_date('c', $n)); ?>"><?php echo esc_html(date_i18n('j F Y', get_the_time('U', $n))); ?></time>
                            <strong><?php echo esc_html(bh_post_title($n->ID)); ?></strong>
                            <small><?php echo esc_html(bh_post_excerpt($n->ID, 16)); ?></small>
                        </span>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
    <?php
elseif ($news->have_posts()) :
    ?>
    <div class="bh-news-list">
        <?php foreach ($news->posts as $n) : ?>
            <a class="bh-card bh-card--news" href="<?php echo esc_url(get_permalink($n)); ?>">
                <?php if (has_post_thumbnail($n)) : ?>
                    <span class="bh-card__img bh-card__img--news">
                        <img src="<?php echo esc_url(get_the_post_thumbnail_url($n, 'bh-card')); ?>" alt="" loading="lazy" width="640" height="400">
                    </span>
                <?php endif; ?>
                <span class="bh-card__body">
                    <time datetime="<?php echo esc_attr(get_the_date('c', $n)); ?>"><?php echo esc_html(date_i18n('j F Y', get_the_time('U', $n))); ?></time>
                    <strong><?php echo esc_html(bh_post_title($n->ID)); ?></strong>
                    <small><?php echo esc_html(bh_post_excerpt($n->ID, 18)); ?></small>
                </span>
            </a>
        <?php endforeach; ?>
    </div>
    <?php
else :
    echo '<p class="bh-empty">' . esc_html(bh_l('Aucune actualité publiée pour l’instant.', 'لا توجد أخبار منشورة بعد.')) . '</p>';
endif;
wp_reset_postdata();
