<?php
/**
 * Single article (news).
 */
defined('ABSPATH') || exit;
get_header();
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
    </div>
</section>

<?php get_footer(); ?>
