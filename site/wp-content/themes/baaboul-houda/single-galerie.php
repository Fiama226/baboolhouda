<?php
/**
 * Single gallery.
 */
defined('ABSPATH') || exit;
get_header();
while (have_posts()) :
    the_post();
    $ids = array_filter(array_map('intval', explode(',', (string) get_post_meta(get_the_ID(), '_bh_gallery_ids', true))));
    ?>
    <div class="bh-container bh-page">
        <nav class="bh-breadcrumb" aria-label="Fil d’Ariane">
            <a href="<?php echo esc_url(bh_nav_url('direct')); ?>"><?php echo esc_html(bh_l('Accueil', 'الرئيسية')); ?></a>
            <span aria-hidden="true">/</span>
            <a href="<?php echo esc_url(bh_nav_url('galeries')); ?>"><?php echo esc_html(bh_l('Galleries', 'المعارض')); ?></a>
            <span aria-hidden="true">/</span> <span><?php echo esc_html(get_the_title()); ?></span>
        </nav>
        <header class="bh-page__head">
            <h1><?php echo esc_html(get_the_title()); ?></h1>
            <?php if (get_the_content()) : ?>
                <p class="bh-page__lead"><?php echo esc_html(wp_strip_all_tags(get_the_content())); ?></p>
            <?php endif; ?>
        </header>
        <div class="bh-gallery">
            <?php foreach ($ids as $attId) : ?>
                <?php $src = wp_get_attachment_image_url($attId, 'large'); ?>
                <?php if ($src) : ?>
                    <figure>
                        <img src="<?php echo esc_url($src); ?>" alt="<?php echo esc_attr(get_the_title()); ?>" loading="lazy">
                        <?php $cap = wp_get_attachment_caption($attId); ?>
                        <?php if ($cap) : ?><figcaption><?php echo esc_html($cap); ?></figcaption><?php endif; ?>
                    </figure>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    </div>
    <?php
endwhile;
get_footer();
