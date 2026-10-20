<?php
/**
 * Single article (news).
 */
defined('ABSPATH') || exit;
get_header();

$bhWords = str_word_count(wp_strip_all_tags((string) get_post_field('post_content', get_the_ID())));
$bhMinRead = (int) max(1, ceil($bhWords / 200));
$bhViews = function_exists('bh_view_count') ? bh_view_count(get_the_ID()) : 0;
?>

<section class="bh-section">
    <div class="bh-container bh-container--narrow">
        <article class="bh-prose bh-article">
            <header class="bh-article__head">
                <h1><?php echo esc_html(bh_post_title(get_the_ID())); ?></h1>
                <p class="bh-article__meta">
                    <time datetime="<?php echo esc_attr(get_the_date('c')); ?>"><?php echo esc_html(date_i18n('j F Y', get_the_time('U'))); ?></time>
                    <?php $cats = get_the_category(); if ($cats) : ?>
                        · <span><?php echo esc_html(bh_l(implode(', ', wp_list_pluck($cats, 'name')), implode('، ', wp_list_pluck($cats, 'name')))); ?></span>
                    <?php endif; ?>
                    <?php if (get_the_author_meta('display_name')) : ?>
                        · <?php echo esc_html(bh_l('Par', 'بواسطة')); ?> <span class="bh-article__byline"><?php echo esc_html(get_the_author_meta('display_name')); ?></span>
                    <?php endif; ?>
                    · <span title="<?php echo esc_attr(bh_l('Temps de lecture estimé', 'وقت القراءة التقريبي')); ?>">⏱ <?php echo esc_html($bhMinRead); ?> <?php echo esc_html(bh_l('min', 'د')); ?></span>
                    <?php if ($bhViews > 0) : ?> · <span title="<?php echo esc_attr(bh_l('Lectures', 'مرات القراءة')); ?>">👁 <?php echo esc_html(number_format_i18n($bhViews)); ?></span><?php endif; ?>
                </p>
            </header>

            <?php if (has_post_thumbnail()) : ?>
                <?php echo get_the_post_thumbnail(get_the_ID(), 'bh-cover', ['class' => 'bh-article__hero']); ?>
            <?php endif; ?>

            <?php the_content(); ?>

            <div class="bh-share">
                <strong><?php echo esc_html(bh_l('Partager', 'مشاركة')); ?></strong>
                <?php $shareUrl = urlencode(get_permalink()); $shareText = urlencode(get_the_title()); ?>
                <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo esc_attr($shareUrl); ?>" target="_blank" rel="noopener" class="bh-share__btn">Facebook</a>
                <a href="https://wa.me/?text=<?php echo esc_attr($shareText . ' ' . $shareUrl); ?>" target="_blank" rel="noopener" class="bh-share__btn">WhatsApp</a>
                <a href="https://twitter.com/intent/tweet?url=<?php echo esc_attr($shareUrl); ?>&text=<?php echo esc_attr($shareText); ?>" target="_blank" rel="noopener" class="bh-share__btn">X</a>
            </div>
        </article>

        <?php if (function_exists('bh_trending')) :
            $trending = array_values(array_filter(
                (array) bh_trending(6),
                static fn (array $t): bool => (int) $t['post']->ID !== (int) get_the_ID()
            ));
            if ($trending) :
                ?>
        <aside class="bh-related">
            <h2 class="bh-related__title"><?php echo esc_html(bh_l('Les plus lus', 'الأكثر قراءة')); ?></h2>
            <ul class="bh-related__list">
                <?php foreach ($trending as $t) : ?>
                    <li><a href="<?php echo esc_url(get_permalink($t['post']->ID)); ?>"><?php echo esc_html(bh_post_title($t['post']->ID)); ?></a></li>
                <?php endforeach; ?>
            </ul>
        </aside>
        <?php
            endif;
        endif; ?>
    </div>
</section>

<?php get_footer(); ?>
