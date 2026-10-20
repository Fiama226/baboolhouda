<?php
/** News list. Expects optional $limit. */
defined('ABSPATH') || exit;
$limit = isset($args['limit']) ? (int) $args['limit'] : 6;
$news = new WP_Query([
    'post_type' => 'post', 'posts_per_page' => $limit, 'no_found_rows' => true,
]);
if ($news->have_posts()) :
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
