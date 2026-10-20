<?php
/** Template Name: Contact */
defined('ABSPATH') || exit;
get_header();
$s = bh_settings();
?>

<section class="bh-page-hero">
    <div class="bh-container">
        <h1><?php echo esc_html(bh_post_title(get_the_ID())); ?></h1>
        <p class="bh-page-hero__sub"><?php echo esc_html(bh_l('Une question, une demande de programme, une publicité ? Écrivez-nous.', 'سؤال، طلب برنامج، إعلان؟ راسلونا.')); ?></p>
    </div>
</section>

<section class="bh-section">
    <div class="bh-container bh-contact-layout">
        <div class="bh-contact__form-wrap">
            <?php echo do_shortcode('[bh_contact_form]'); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
        </div>
        <div class="bh-contact__info">
            <?php if (!empty($s['whatsapp'])) : ?>
                <a class="bh-contact-card bh-contact-card--wa" href="https://wa.me/<?php echo esc_attr(preg_replace('/[^0-9+]/', '', $s['whatsapp'])); ?>" target="_blank" rel="noopener">
                    <span aria-hidden="true">💬</span>
                    <span><strong><?php echo esc_html(bh_l('WhatsApp', 'واتساب')); ?></strong><small><?php echo esc_html($s['whatsapp']); ?></small></span>
                </a>
            <?php endif; ?>
            <?php if (!empty($s['phone'])) : ?>
                <a class="bh-contact-card" href="tel:<?php echo esc_attr(preg_replace('/[^0-9+]/', '', $s['phone'])); ?>">
                    <span aria-hidden="true">📞</span>
                    <span><strong><?php echo esc_html(bh_l('Téléphone', 'الهاتف')); ?></strong><small><?php echo esc_html($s['phone']); ?></small></span>
                </a>
            <?php endif; ?>
            <?php if (!empty($s['email'])) : ?>
                <a class="bh-contact-card" href="mailto:<?php echo esc_attr($s['email']); ?>">
                    <span aria-hidden="true">✉️</span>
                    <span><strong>Email</strong><small><?php echo esc_html($s['email']); ?></small></span>
                </a>
            <?php endif; ?>
            <?php if (!empty($s['address'])) : ?>
                <div class="bh-contact-card">
                    <span aria-hidden="true">📍</span>
                    <span><strong><?php echo esc_html(bh_l('Adresse', 'العنوان')); ?></strong><small><?php echo esc_html($s['address']); ?></small></span>
                </div>
            <?php endif; ?>
            <?php if (!empty($s['facebook_url'])) : ?>
                <a class="bh-contact-card" href="<?php echo esc_url($s['facebook_url']); ?>" target="_blank" rel="noopener">
                    <span aria-hidden="true">📘</span>
                    <span><strong>Facebook</strong><small><?php echo esc_html(bh_l('Page officielle', 'الصفحة الرسمية')); ?></small></span>
                </a>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php get_footer(); ?>
