<?php
/**
 * Template Name: Espace presse
 */
defined('ABSPATH') || exit;
get_header();
$s = function_exists('bh_settings') ? bh_settings() : [];
?>
<div class="bh-container bh-page">
    <nav class="bh-breadcrumb" aria-label="Fil d’Ariane">
        <a href="<?php echo esc_url(bh_nav_url('direct')); ?>"><?php echo esc_html(bh_l('Accueil', 'الرئيسية')); ?></a>
        <span aria-hidden="true">/</span> <span><?php echo esc_html(bh_l('Espace presse', 'الصحافة')); ?></span>
    </nav>
    <header class="bh-page__head">
        <h1><?php echo esc_html(bh_post_title(get_queried_object_id())); ?></h1>
        <p class="bh-page__lead"><?php echo esc_html(bh_l('Identité, logo et informations pour les médias et partenaires.', 'الهوية والشعار والمعلومات للإعلام والشركاء.')); ?></p>
    </header>
    <div class="bh-grid bh-grid--2">
        <div class="bh-card">
            <h2 class="bh-card__title"><?php echo esc_html(bh_l('La chaîne en bref', 'القناة باختصار')); ?></h2>
            <ul class="bh-footer__contact">
                <li><?php bloginfo('name'); ?> — <?php echo esc_html($s['footer_text_fr']); ?></li>
                <li><?php echo esc_html($s['address']); ?></li>
                <?php if (!empty($s['phone'])) : ?><li><a href="tel:<?php echo esc_attr(preg_replace('/[^0-9+]/', '', (string) $s['phone'])); ?>"><?php echo esc_html($s['phone']); ?></a></li><?php endif; ?>
                <?php if (!empty($s['email'])) : ?><li><a href="mailto:<?php echo esc_attr($s['email']); ?>"><?php echo esc_html($s['email']); ?></a></li><?php endif; ?>
            </ul>
            <h3 class="bh-card__title" style="margin-top:16px"><?php echo esc_html(bh_l('Réseaux', 'الشبكات')); ?></h3>
            <p class="bh-footer__socials">
                <?php if (!empty($s['facebook_url'])) : ?><a href="<?php echo esc_url($s['facebook_url']); ?>" target="_blank" rel="noopener">Facebook</a><span>·</span><?php endif; ?>
                <?php if (!empty($s['youtube_url'])) : ?><a href="<?php echo esc_url($s['youtube_url']); ?>" target="_blank" rel="noopener">YouTube</a><?php endif; ?>
            </p>
        </div>
        <div class="bh-card">
            <h2 class="bh-card__title"><?php echo esc_html(bh_l('Logo & identité', 'الشعار والهوية')); ?></h2>
            <div class="bh-presskit__logo">
                <?php echo bh_logo_html(96); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
            </div>
            <p>
                <a class="bh-btn" href="<?php echo esc_url(bh_brand_url('logo-512.png')); ?>" download><?php echo esc_html(bh_l('Logo (PNG transparent)', 'الشعار (PNG شفاف)')); ?></a>
                <a class="bh-btn" href="<?php echo esc_url(bh_brand_url('logo-tv-baaboul-houda.jpg')); ?>" download><?php echo esc_html(bh_l('Logo original (JPG)', 'الشعار الأصلي (JPG)')); ?></a>
            </p>
            <p class="bh-muted"><?php echo esc_html(bh_l('Image de partage : 1200×630 — sur simple demande.', 'صورة المشاركة: 1200×630 — عند الطلب.')); ?></p>
        </div>
    </div>
</div>
<?php get_footer();
