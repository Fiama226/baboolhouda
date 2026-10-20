<?php
/**
 * Template Name: Mosquées
 */
defined('ABSPATH') || exit;
get_header();
$mosques = function_exists('bh_mosques') ? bh_mosques() : [];
?>
<div class="bh-container bh-page">
    <nav class="bh-breadcrumb" aria-label="Fil d’Ariane">
        <a href="<?php echo esc_url(bh_nav_url('direct')); ?>"><?php echo esc_html(bh_l('Accueil', 'الرئيسية')); ?></a>
        <span aria-hidden="true">/</span> <span><?php echo esc_html(bh_l('Mosquées', 'الجمع')); ?></span>
    </nav>
    <header class="bh-page__head">
        <h1><?php echo esc_html(bh_post_title(get_queried_object_id())); ?></h1>
        <p class="bh-page__lead"><?php echo esc_html(bh_l('Annuaire des mosquées et points de contact.', 'دليل الجمع ونقاط الاتصال.')); ?></p>
    </header>
    <div class="bh-grid bh-grid--2">
        <?php if (!$mosques) : ?>
            <p class="bh-empty"><?php echo esc_html(bh_l('Aucune mosquée ajoutée pour le moment.', 'لم تُضف أي جُماع بعد.')); ?></p>
        <?php else : ?>
            <?php foreach ($mosques as $m) : ?>
                <div class="bh-card bh-card--mosque">
                    <span class="bh-card__body">
                        <strong><?php echo esc_html($m['name'] !== '' ? $m['name'] : $m['name_ar']); ?></strong>
                        <?php if (!empty($m['address'])) : ?>
                            <small class="bh-muted"><?php echo esc_html($m['address']); ?></small>
                        <?php endif; ?>
                        <?php if (!empty($m['imam'])) : ?>
                            <small><?php echo esc_html(bh_l('Contact :', 'التواصل: ')); ?> <?php echo esc_html($m['imam']); ?></small>
                        <?php endif; ?>
                        <?php if (!empty($m['phone'])) : ?>
                            <a class="bh-link" href="tel:<?php echo esc_attr(preg_replace('/[^0-9+]/', '', $m['phone'])); ?>"><?php echo esc_html($m['phone']); ?></a>
                        <?php endif; ?>
                        <?php if (!empty($m['notes'])) : ?>
                            <small class="bh-muted"><?php echo esc_html($m['notes']); ?></small>
                        <?php endif; ?>
                    </span>
                    <?php if (!empty($m['address'])) : ?>
                        <iframe class="bh-mosque-map" loading="lazy"
                            src="https://www.google.com/maps?q=<?php echo esc_attr(rawurlencode($m['address'])); ?>&output=embed"
                            title="<?php echo esc_attr($m['name']); ?>" referrerpolicy="no-referrer-when-downgrade"></iframe>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>
<?php get_footer();
