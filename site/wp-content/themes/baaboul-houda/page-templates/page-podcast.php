<?php
/**
 * Template Name: Podcasts
 *
 * On-demand radio episodes (audio sources) + RSS link.
 */
defined('ABSPATH') || exit;
get_header();
$q = new \WP_Query([
    'post_type'      => 'episode',
    'posts_per_page' => 30,
    'no_found_rows'  => true,
    // Audio replays: audio-type episodes, or any episode that has an audio URL.
    'meta_query'     => [
        'relation' => 'OR',
        ['key' => '_bh_source_type', 'value' => 'audio'],
        ['key' => '_bh_audio_url', 'value' => '', 'compare' => '!='],
    ],
]);
?>
<div class="bh-container bh-page">
    <nav class="bh-breadcrumb" aria-label="Fil d’Ariane">
        <a href="<?php echo esc_url(bh_nav_url('direct')); ?>"><?php echo esc_html(bh_l('Accueil', 'الرئيسية')); ?></a>
        <span aria-hidden="true">/</span> <span><?php echo esc_html(bh_l('Podcasts', 'البودكاست')); ?></span>
    </nav>
    <header class="bh-page__head">
        <h1><?php echo esc_html(bh_post_title(get_queried_object_id())); ?></h1>
        <p class="bh-page__lead">
            <?php echo esc_html(bh_l('Écoutez nos émissions audio à la demande.', 'استمعوا إلى برامجنا الصوتية عند الطلب.')); ?>
            <a class="bh-link" href="<?php echo esc_url(home_url('/feed/bh-podcast/')); ?>" target="_blank" rel="noopener"
               title="<?php echo esc_attr(bh_l('Ajouter à votre application podcast', 'أضِف إلى تطبيق البودكاست')); ?>">
                ⬇ <?php echo esc_html(bh_l('Flux RSS (votre app podcast)', 'قناة RSS (تطبيق البودكاست)')); ?>
            </a>
        </p>
    </header>
    <div class="bh-podcast-list">
        <?php if (!$q->have_posts()) : ?>
            <p class="bh-empty"><?php echo esc_html(bh_l('Aucun épisode audio pour le moment.', 'لا حلقات صوتية بعد.')); ?></p>
        <?php else : ?>
            <div class="bh-card bh-audio-list">
                <?php foreach ($q->posts as $bh_ep) : ?>
                    <?php get_template_part('template-parts/audio-row', null, ['post' => $bh_ep]); ?>
                <?php endforeach; ?>
            </div>
            <?php wp_reset_postdata(); ?>
        <?php endif; ?>
    </div>
</div>
<?php get_footer();
