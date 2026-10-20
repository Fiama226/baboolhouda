<?php
/**
 * Template Name: Galleries photo
 */
defined('ABSPATH') || exit;
get_header();
$q = new \WP_Query([
    'post_type'      => 'galerie',
    'posts_per_page' => 12,
]);
?>
<div class="bh-container bh-page">
    <nav class="bh-breadcrumb" aria-label="Fil d’Ariane">
        <a href="<?php echo esc_url(bh_nav_url('direct')); ?>"><?php echo esc_html(bh_l('Accueil', 'الرئيسية')); ?></a>
        <span aria-hidden="true">/</span> <span><?php echo esc_html(bh_l('Galleries photo', 'معرض الصور')); ?></span>
    </nav>
    <header class="bh-page__head">
        <h1><?php echo esc_html(bh_post_title(get_queried_object_id())); ?></h1>
        <p class="bh-page__lead"><?php echo esc_html(bh_l('Nos événements en images.', 'فعالياتنا بالصور.')); ?></p>
    </header>
    <div class="bh-grid bh-grid--3">
        <?php if (!$q->have_posts()) : ?>
            <p class="bh-empty"><?php echo esc_html(bh_l('Aucune galerie publiée.', 'لا معارض منشورة.')); ?></p>
        <?php else : ?>
            <?php
            while ($q->have_posts()) :
                $q->the_post();
                $ids = array_filter(array_map('intval', explode(',', (string) get_post_meta(get_the_ID(), '_bh_gallery_ids', true))));
                $cover = !empty($ids) ? wp_get_attachment_image_url($ids[0], 'bh-card') : '';
                ?>
                <a class="bh-card bh-card--gallery" href="<?php echo esc_url(get_permalink()); ?>">
                    <?php if ($cover) : ?>
                        <span class="bh-card__img"><img src="<?php echo esc_url($cover); ?>" alt="<?php echo esc_attr(get_the_title()); ?>" loading="lazy"></span>
                    <?php endif; ?>
                    <span class="bh-card__body">
                        <strong><?php echo esc_html(bh_post_title(get_the_ID())); ?></strong>
                        <?php if ($ids) : ?>
                            <small><?php echo esc_html(sprintf(bh_l('%d photo(s)', '%d صورة'), count($ids))); ?></small>
                        <?php endif; ?>
                    </span>
                </a>
            <?php endwhile; ?>
        <?php endif; ?>
    </div>
</div>
<?php get_footer();
