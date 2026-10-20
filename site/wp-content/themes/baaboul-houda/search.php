<?php
/** Search results. */
defined('ABSPATH') || exit;
get_header();
$query = get_search_query();
?>

<section class="bh-page-hero">
    <div class="bh-container">
        <h1><?php echo esc_html(bh_l('Recherche', 'البحث')); ?></h1>
        <p class="bh-page-hero__sub">
            <?php echo esc_html(bh_l('Résultats pour', 'نتائج البحث عن')); ?> : <strong><?php echo esc_html($query); ?></strong>
        </p>
        <p class="bh-page-hero__sub">
            <?php echo esc_html(bh_l('Vous cherchez un verset ? Utilisez', 'هل تبحث عن آية؟ استخدم')); ?>
            <a class="bh-link" href="<?php echo esc_url(home_url('/coran/')); ?>"><?php echo esc_html(bh_l('la recherche Coran →', 'البحث في القرآن →')); ?></a>
        </p>
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
                    if ('post' === $t) {
                        // Inline news-style result.
                        ?>
                        <a class="bh-card bh-card--news" href="<?php the_permalink(); ?>">
                            <span class="bh-card__body">
                                <time datetime="<?php echo esc_attr(get_the_date('c')); ?>"><?php echo esc_html(date_i18n('j F Y', get_the_time('U'))); ?></time>
                                <strong><?php echo esc_html(bh_post_title(get_the_ID())); ?></strong>
                                <small><?php echo esc_html(bh_post_excerpt(get_the_ID(), 16)); ?></small>
                            </span>
                        </a>
                        <?php
                    } elseif ('episode' === $t) {
                        get_template_part('template-parts/episode', 'card', ['post' => get_post()]);
                    } else {
                        get_template_part('template-parts/programme', 'card', ['post' => get_post()]);
                    }
                endwhile;
                ?>
            </div>
            <?php the_posts_pagination(); ?>
        <?php else : ?>
            <p class="bh-empty"><?php echo esc_html(bh_l('Aucun résultat. Essayez un autre mot-clé.', 'لا توجد نتائج. جربوا كلمة أخرى.')); ?></p>
        <?php endif; ?>
    </div>
</section>

<?php get_footer(); ?>
