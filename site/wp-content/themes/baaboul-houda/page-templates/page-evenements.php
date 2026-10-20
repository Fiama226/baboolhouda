<?php
/** Template Name: Événements */
defined('ABSPATH') || exit;
get_header();

$events = new WP_Query([
    'post_type'      => 'evenement',
    'posts_per_page' => -1,
    'orderby'        => 'date',
    'order'          => 'ASC',
]);
$past   = [];
$upcoming = [];
if ($events->have_posts()) {
    foreach ($events->posts as $ev) {
        $ts = strtotime(get_the_date('Y-m-d', $ev));
        if (date('Y-m-d', $ts) >= date('Y-m-d')) {
            $upcoming[] = $ev;
        } else {
            $past[] = $ev;
        }
    }
}
?>

<section class="bh-page-hero">
    <div class="bh-container">
        <h1><?php echo esc_html(bh_post_title(get_the_ID())); ?></h1>
        <p class="bh-page-hero__sub"><?php echo esc_html(bh_l('Conférences, soirées, cours en ligne et activités de la chaîne.', 'محاضرات وليالي ودروس أونلاين وأنشطة القناة.')); ?></p>
    </div>
</section>

<section class="bh-section">
    <div class="bh-container">
        <h2><?php echo esc_html(bh_l('À venir', 'القادمة')); ?></h2>
        <?php if ($upcoming) : ?>
            <div class="bh-events-list">
                <?php foreach ($upcoming as $ev) : ?>
                    <a class="bh-event-card" href="<?php echo esc_url(get_permalink($ev)); ?>">
                        <span class="bh-event-card__date">
                            <strong><?php echo esc_html(date_i18n('j', strtotime(get_the_date('Y-m-d', $ev)))); ?></strong>
                            <em><?php echo esc_html(date_i18n('M', strtotime(get_the_date('Y-m-d', $ev)))); ?></em>
                        </span>
                        <span class="bh-event-card__body">
                            <strong><?php echo esc_html(bh_post_title($ev)); ?></strong>
                            <small><?php echo esc_html(bh_post_excerpt($ev, 14)); ?></small>
                        </span>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php else : ?>
            <p class="bh-empty"><?php echo esc_html(bh_l('Aucun événement à venir annoncé.', 'لا توجد فعاليات قادمة معلنة.')); ?></p>
        <?php endif; ?>

        <?php if ($past) : ?>
            <h2><?php echo esc_html(bh_l('Passés', 'الماضية')); ?></h2>
            <div class="bh-events-list">
                <?php foreach ($past as $ev) : ?>
                    <a class="bh-event-card bh-event-card--past" href="<?php echo esc_url(get_permalink($ev)); ?>">
                        <span class="bh-event-card__date">
                            <strong><?php echo esc_html(date_i18n('j', strtotime(get_the_date('Y-m-d', $ev)))); ?></strong>
                            <em><?php echo esc_html(date_i18n('M Y', strtotime(get_the_date('Y-m-d', $ev)))); ?></em>
                        </span>
                        <span class="bh-event-card__body">
                            <strong><?php echo esc_html(bh_post_title($ev)); ?></strong>
                            <small><?php echo esc_html(bh_post_excerpt($ev, 14)); ?></small>
                        </span>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php get_footer(); ?>
