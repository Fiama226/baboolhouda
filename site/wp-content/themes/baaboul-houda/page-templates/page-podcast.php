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
    'meta_query'     => [[
        'key'   => '_bh_source_type',
        'value' => 'audio',
    ]],
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
            <?php
            while ($q->have_posts()) :
                $q->the_post();
                $src = bh_episode_source(get_the_ID());
                $audio = $src['audio'] ?: $src['url'];
                ?>
                <div class="bh-card bh-card--podcast" data-bh-audio-player data-url="<?php echo esc_attr($audio); ?>">
                    <span class="bh-card__body">
                        <strong><?php echo esc_html(bh_post_title(get_queried_object_id())); ?></strong>
                        <small><?php echo esc_html(get_the_date()); ?> — <?php echo esc_html(bh_post_excerpt(get_the_ID(), 40)); ?></small>
                    </span>
                    <span class="bh-audio-controls">
                        <button type="button" class="bh-audio-play" aria-label="<?php echo esc_attr(bh_l('Écouter', 'استمع')); ?>">▶</button>
                    </span>
                </div>
            <?php endwhile; ?>
        <?php endif; ?>
    </div>
</div>
<?php get_footer();
