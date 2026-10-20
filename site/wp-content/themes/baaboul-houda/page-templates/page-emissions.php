<?php
/** Template Name: Replays & émissions (on-demand, filterable by programme) */
defined('ABSPATH') || exit;
get_header();

$programmeFilter = isset($_GET['programme']) ? (int) $_GET['programme'] : 0;
$sujetFilter     = isset($_GET['sujet']) ? sanitize_title(wp_unslash($_GET['sujet'])) : '';
$thisWeek       = !empty($_GET['semaine']);

$args = [
    'post_type'      => 'episode',
    'posts_per_page' => 24,
    'orderby'        => 'date',
    'order'          => 'DESC',
];
if ($programmeFilter) {
    $args['meta_query'][] = ['key' => '_bh_programme_id', 'value' => $programmeFilter];
}
if ($sujetFilter) {
    $args['tax_query'] = [[
        'taxonomy' => 'sujet_episod',
        'field'    => 'slug',
        'terms'    => $sujetFilter,
    ]];
}
if ($thisWeek) {
    $args['date_query'] = [[
        'column' => 'post_date',
        'after'  => date('Y-m-d H:i:s', time() - 7 * DAY_IN_SECONDS),
        'inclusive' => true,
    ]];
}

$programmes = new WP_Query(['post_type' => 'programme', 'posts_per_page' => -1, 'orderby' => 'title', 'order' => 'ASC', 'no_found_rows' => true]);
$sujets     = get_terms(['taxonomy' => 'sujet_episod', 'hide_empty' => true, 'orderby' => 'name', 'order' => 'ASC']);
$episodes   = new WP_Query($args);
?>

<section class="bh-page-hero">
    <div class="bh-container">
        <h1><?php echo esc_html(bh_post_title(get_the_ID())); ?></h1>
        <p class="bh-page-hero__sub"><?php echo esc_html(bh_l('Re regardez et ré écoutez vos émissions préférées.', 'إعادة مشاهدة وإعادة استماع لبرامجك المفضلة.')); ?></p>
    </div>
</section>

<section class="bh-section">
    <div class="bh-container">
        <?php if ($programmes->have_posts()) : ?>
            <div class="bh-filter" role="group" aria-label="<?php echo esc_attr(bh_l('Filtrer par programme', 'تصفية حسب البرنامج')); ?>">
                <a class="bh-chip" href="<?php echo esc_url(bh_nav_url('emissions')); ?>" <?php echo $programmeFilter || $sujetFilter || $thisWeek ? '' : 'aria-current="true"'; ?>><?php echo esc_html(bh_l('Tous', 'الكل')); ?></a>
                <?php foreach ($programmes->posts as $p) : ?>
                    <a class="bh-chip" href="<?php echo esc_url(add_query_arg('programme', $p->ID, bh_nav_url('emissions'))); ?>" <?php echo ($programmeFilter === $p->ID && !$sujetFilter && !$thisWeek) ? 'aria-current="true"' : ''; ?>>
                        <?php echo esc_html(bh_post_title($p->ID)); ?>
                    </a>
                <?php endforeach; ?>
                <a class="bh-chip" href="<?php echo esc_url(add_query_arg('semaine', '1', bh_nav_url('emissions'))); ?>" <?php echo $thisWeek ? 'aria-current="true"' : ''; ?>><?php echo esc_html(bh_l('Cette semaine', 'هذا الأسبوع')); ?></a>
            </div>
        <?php endif; ?>

        <?php if (!is_wp_error($sujets) && $sujets) : ?>
            <div class="bh-filter" role="group" aria-label="<?php echo esc_attr(bh_l('Filtrer par thème', 'تصفية حسب الموضوع')); ?>">
                <?php foreach ($sujets as $s) : ?>
                    <a class="bh-chip" href="<?php echo esc_url(add_query_arg('sujet', $s->slug, bh_nav_url('emissions'))); ?>" <?php echo $sujetFilter === $s->slug ? 'aria-current="true"' : ''; ?>>
                        <?php echo esc_html($s->name); ?> (#<?php echo esc_html($s->count); ?>)
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if ($episodes->have_posts()) : ?>
            <div class="bh-grid bh-grid--3">
                <?php foreach ($episodes->posts as $e) : ?>
                    <?php get_template_part('template-parts/episode', 'card', ['post' => $e]); ?>
                <?php endforeach; ?>
            </div>
        <?php else : ?>
            <p class="bh-empty"><?php echo esc_html(bh_l('Aucun replay publié pour l’instant.', 'لا توجد حلقات مسجلة بعد.')); ?></p>
        <?php endif; ?>
        wp_reset_postdata();
    </div>
</section>

<?php get_footer(); ?>
