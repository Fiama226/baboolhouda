<?php
/** Fallback index (archives, taxonomies, misc lists). */
defined('ABSPATH') || exit;
get_header();
?>

<section class="bh-page-hero">
    <div class="bh-container">
        <h1>
            <?php
            if (is_search()) {
                echo esc_html(bh_l('Résultats de recherche', 'نتائج البحث'));
            } elseif (is_category() || is_tag()) {
                single_term_title();
            } elseif (is_archive()) {
                the_archive_title();
            } else {
                single_post_title();
            }
            ?>
        </h1>
    </div>
</section>

<section class="bh-section">
    <div class="bh-container">
        <?php if (have_posts()) : ?>
            <div class="bh-grid bh-grid--3">
                <?php
                while (have_posts()) :
                    the_post();
                    $t = get_post_type();
                    if ('episode' === $t) {
                        get_template_part('template-parts/episode', 'card', ['post' => get_post()]);
                    } else {
                        get_template_part('template-parts/programme', 'card', ['post' => get_post()]);
                    }
                endwhile;
                ?>
            </div>
            <?php the_posts_pagination(); ?>
        <?php else : ?>
            <p class="bh-empty"><?php echo esc_html(bh_l('Aucun contenu trouvé.', 'لا يوجد محتوى.')); ?></p>
        <?php endif; ?>
    </div>
</section>

<?php get_footer(); ?>
