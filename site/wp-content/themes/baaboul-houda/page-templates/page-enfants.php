<?php
/**
 * Template Name: Enfants & jeunesse
 */
defined('ABSPATH') || exit;
get_header();
$q = new \WP_Query([
    'post_type'      => 'episode',
    'posts_per_page' => 12,
    'tax_query'      => [[
        'taxonomy' => 'sujet_episod',
        'terms'    => ['enfants', 'jeunesse'],
    ]],
]);
if (!$q->have_posts()) {
    // No "enfants/jeunesse" topics yet — show all episodes as fallback.
    $q = new \WP_Query(['post_type' => 'episode', 'posts_per_page' => 12]);
}
?>
<div class="bh-container bh-page">
    <nav class="bh-breadcrumb" aria-label="Fil d’Ariane">
        <a href="<?php echo esc_url(bh_nav_url('direct')); ?>"><?php echo esc_html(bh_l('Accueil', 'الرئيسية')); ?></a>
        <span aria-hidden="true">/</span> <span><?php echo esc_html(bh_l('Enfants & jeunesse', 'الأطفال والشباب')); ?></span>
    </nav>
    <header class="bh-page__head">
        <h1><?php echo esc_html(bh_post_title(get_queried_object_id())); ?></h1>
        <p class="bh-page__lead"><?php echo esc_html(bh_l('Programmes pour les enfants et la jeunesse : histoires, cours, nasheeds.', 'برامج للأطفال والشباب: قصص، دروس، أناشيد.')); ?></p>
    </header>
    <div class="bh-grid bh-grid--3">
        <?php if (!$q->have_posts()) : ?>
            <p class="bh-empty"><?php echo esc_html(bh_l('Aucun contenu jeunesse pour le moment.', 'لا محتوى شبابي بعد.')); ?></p>
        <?php else : ?>
            <?php
            while ($q->have_posts()) :
                $q->the_post();
                get_template_part('template-parts/episode-card');
            endwhile;
            ?>
        <?php endif; ?>
    </div>
</div>
<?php get_footer();
