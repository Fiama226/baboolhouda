<?php
/**
 * Single programme: logo, description (FR/AR), schedule slots, episodes.
 */
defined('ABSPATH') || exit;
get_header();
$id = get_the_ID();
$programme = get_post($id);

// Slots of the weekly grid featuring this programme.
$slots = [];
foreach (bh_schedule() as $row) {
    if ((int) $row['programme_id'] === $id) {
        $slots[] = $row;
    }
}
usort($slots, static fn ($a, $b) => ($a['day'] <=> $b['day']) || strcmp($a['start'], $b['start']));

$episodes = new WP_Query([
    'post_type' => 'episode', 'posts_per_page' => 12, 'no_found_rows' => true,
    'meta_query' => [['key' => '_bh_programme_id', 'value' => $id]],
]);
$dayLabels = [0 => 'Lundi', 1 => 'Mardi', 2 => 'Mercredi', 3 => 'Jeudi', 4 => 'Vendredi', 5 => 'Samedi', 6 => 'Dimanche'];
$dayLabelsAr = [0 => 'الاثنين', 1 => 'الثلاثاء', 2 => 'الأربعاء', 3 => 'الخميس', 4 => 'الجمعة', 5 => 'السبت', 6 => 'الأحد'];
?>

<section class="bh-page-hero">
    <div class="bh-container bh-programme-hero">
        <?php if (has_post_thumbnail()) : ?>
            <img class="bh-programme-hero__logo" src="<?php echo esc_url(bh_programme_logo($id, 300)); ?>" alt="<?php echo esc_attr(bh_post_title($id)); ?>" width="120" height="120">
        <?php endif; ?>
        <div>
            <h1><?php echo esc_html(bh_post_title($id)); ?></h1>
            <?php $cats = get_the_terms($id, 'categorie_programme'); if ($cats && !is_wp_error($cats)) : ?>
                <p class="bh-chips">
                    <?php foreach ($cats as $cat) : ?>
                        <span class="bh-chip"><?php echo esc_html(bh_l($cat->name, $cat->name)); ?></span>
                    <?php endforeach; ?>
                </p>
            <?php endif; ?>
        </div>
    </div>
</section>

<section class="bh-section">
    <div class="bh-container bh-prose">
        <?php echo bh_post_content($id); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>

        <?php if ($slots) : ?>
            <h2><?php echo esc_html(bh_l('Diffusé le', 'يُبث في')); ?></h2>
            <ul class="bh-slot-list">
                <?php foreach ($slots as $slot) : ?>
                    <li><strong><?php echo esc_html(bh_l($dayLabels[$slot['day']], $dayLabelsAr[$slot['day']])); ?></strong> — <?php echo esc_html($slot['start'] . '–' . $slot['end']); ?></li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>

        <h2><?php echo esc_html(bh_l('Replays de ce programme', 'حلقات هذا البرنامج')); ?></h2>
        <?php if ($episodes->have_posts()) : ?>
            <div class="bh-grid bh-grid--3">
                <?php foreach ($episodes->posts as $e) : ?>
                    <?php get_template_part('template-parts/episode', 'card', ['post' => $e]); ?>
                <?php endforeach; ?>
            </div>
        <?php else : ?>
            <p class="bh-empty"><?php echo esc_html(bh_l('Aucun replay pour ce programme pour l’instant.', 'لا توجد حلقات لهذا البرنامج بعد.')); ?></p>
        <?php endif; ?>
    </div>
</section>

<?php get_footer(); ?>
