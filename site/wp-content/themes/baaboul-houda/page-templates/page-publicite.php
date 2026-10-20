<?php
/**
 * Template Name: Publicité
 */
defined('ABSPATH') || exit;
get_header();
$s = function_exists('bh_settings') ? bh_settings() : [];
?>
<div class="bh-container bh-page">
    <nav class="bh-breadcrumb" aria-label="Fil d’Ariane">
        <a href="<?php echo esc_url(bh_nav_url('direct')); ?>"><?php echo esc_html(bh_l('Accueil', 'الرئيسية')); ?></a>
        <span aria-hidden="true">/</span> <span><?php echo esc_html(bh_l('Publicité', 'الإعلان')); ?></span>
    </nav>
    <header class="bh-page__head">
        <h1><?php echo esc_html(bh_post_title(get_queried_object_id())); ?></h1>
        <p class="bh-page__lead"><?php echo esc_html(bh_l('Développez votre activité auprès de nos auditeurs : spots TV/radio, bannières, partenariats événementiels.', 'نوّوا نشاطكم لدى جمهورنا: إعلانات تلفزيونية/إذاعية، بانرات، شراكات.')); ?></p>
    </header>
    <div class="bh-grid bh-grid--2">
        <div class="bh-card">
            <h2 class="bh-card__title"><?php echo esc_html(bh_l('Formats & taux', 'الصيغ والأسعار')); ?></h2>
            <?php if (!empty($s['ads_fr'])) : ?>
                <div class="bh-ads__body"><?php echo wp_kses_post(wpautop($s['ads_fr'])); ?></div>
            <?php else : ?>
                <p class="bh-muted"><?php echo esc_html(bh_l('Tarifs sur simple demande.', 'الأسعار عند الطلب.')); ?></p>
            <?php endif; ?>
        </div>
        <div class="bh-card">
            <h2 class="bh-card__title"><?php echo esc_html(bh_l('Contact annonceurs', 'تواصل المعلنين')); ?></h2>
            <ul class="bh-footer__contact">
                <?php if (!empty($s['ads_email'])) : ?>
                    <li><a href="mailto:<?php echo esc_attr($s['ads_email']); ?>"><?php echo esc_html($s['ads_email']); ?></a></li>
                <?php endif; ?>
                <?php if (!empty($s['phone'])) : ?>
                    <li><a href="tel:<?php echo esc_attr(preg_replace('/[^0-9+]/', '', (string) $s['phone'])); ?>"><?php echo esc_html($s['phone']); ?></a></li>
                <?php endif; ?>
                <?php if (!empty($s['whatsapp'])) : ?>
                    <li><a href="https://wa.me/<?php echo esc_attr(preg_replace('/[^0-9+]/', '', (string) $s['whatsapp'])); ?>" target="_blank" rel="noopener">WhatsApp</a></li>
                <?php endif; ?>
            </ul>
            <?php if (!empty($s['ads_ar'])) : ?>
                <hr>
                <div class="bh-ads__body" dir="rtl" style="text-align:right"><?php echo wp_kses_post(wpautop($s['ads_ar'])); ?></div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php get_footer();
