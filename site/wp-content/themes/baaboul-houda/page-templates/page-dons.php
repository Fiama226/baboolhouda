<?php
/**
 * Template Name: Dons & soutien
 */
defined('ABSPATH') || exit;
get_header();
$s = function_exists('bh_settings') ? bh_settings() : [];
?>
<div class="bh-container bh-page">
    <nav class="bh-breadcrumb" aria-label="Fil d’Ariane">
        <a href="<?php echo esc_url(bh_nav_url('direct')); ?>"><?php echo esc_html(bh_l('Accueil', 'الرئيسية')); ?></a>
        <span aria-hidden="true">/</span> <span><?php echo esc_html(bh_l('Dons', 'التبرع')); ?></span>
    </nav>
    <header class="bh-page__head">
        <h1><?php echo esc_html(bh_post_title(get_queried_object_id())); ?></h1>
        <p class="bh-page__lead"><?php echo esc_html(bh_l('Soutenez la chaîne : chaque geste aide à poursuivre la diffusion.', 'ادعموا القناة: كل مساهمة تساعد على مواصلة البث.')); ?></p>
    </header>
    <div class="bh-grid bh-grid--3 bh-donate-grid">
        <div class="bh-card bh-card--donate">
            <span class="bh-card__icon" aria-hidden="true">📱</span>
            <span class="bh-card__body">
                <strong><?php echo esc_html(bh_l('Orange Money', 'أورانج موني')); ?></strong>
                <?php if (!empty($s['donate_om'])) : ?>
                    <span class="bh-donate__account"><?php echo esc_html($s['donate_om']); ?></span>
                <?php else : ?>
                    <small><?php echo esc_html(bh_l('N° à venir', 'الرقم قادم')); ?></small>
                <?php endif; ?>
            </span>
        </div>
        <div class="bh-card bh-card--donate">
            <span class="bh-card__icon" aria-hidden="true">🌊</span>
            <span class="bh-card__body">
                <strong><?php echo esc_html(bh_l('Wave', 'ويف')); ?></strong>
                <?php if (!empty($s['donate_wave'])) : ?>
                    <span class="bh-donate__account"><?php echo esc_html($s['donate_wave']); ?></span>
                <?php else : ?>
                    <small><?php echo esc_html(bh_l('N° à venir', 'الرقم قادم')); ?></small>
                <?php endif; ?>
            </span>
        </div>
        <div class="bh-card bh-card--donate">
            <span class="bh-card__icon" aria-hidden="true">🏦</span>
            <span class="bh-card__body">
                <strong><?php echo esc_html(bh_l('Virement bancaire', 'تحويل بنكي')); ?></strong>
                <?php if (!empty($s['donate_bank'])) : ?>
                    <span class="bh-donate__account"><?php echo esc_html($s['donate_bank']); ?></span>
                <?php else : ?>
                    <small><?php echo esc_html(bh_l('Coordonnées à venir', 'البيانات قادمة')); ?></small>
                <?php endif; ?>
            </span>
        </div>
    </div>
    <p class="bh-note">
        <?php
        echo esc_html(bh_l(
            'Pour toute question sur un don, écrivez-nous : ',
            'لأي سؤال حول التبرع، راسلونا: '
        ));
        ?>
        <?php if (!empty($s['donate_email'])) : ?>
            <a href="mailto:<?php echo esc_attr($s['donate_email']); ?>"><?php echo esc_html($s['donate_email']); ?></a>
        <?php else : ?>
            <a href="https://wa.me/<?php echo esc_attr(preg_replace('/[^0-9+]/', '', (string) ($s['whatsapp'] ?? ''))); ?>" target="_blank" rel="noopener">WhatsApp</a>
        <?php endif; ?>
    </p>
</div>
<?php get_footer();
