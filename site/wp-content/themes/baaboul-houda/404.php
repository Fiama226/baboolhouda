<?php
/** 404. */
defined('ABSPATH') || exit;
get_header();
?>

<section class="bh-section">
    <div class="bh-container bh-container--narrow" style="text-align:center;padding:80px 16px">
        <div style="font-size:64px" aria-hidden="true">🕌</div>
        <h1><?php echo esc_html(bh_l('Page introuvable', 'الصفحة غير موجودة')); ?></h1>
        <p><?php echo esc_html(bh_l('Cette page n’existe pas ou a été déplacée.', 'هذه الصفحة غير موجودة أو تم نقلها.')); ?></p>
        <p>
            <a class="bh-btn bh-btn-primary" href="<?php echo esc_url(home_url('/')); ?>"><?php echo esc_html(bh_l('Retour à l’accueil', 'العودة إلى الرئيسية')); ?></a>
            <a class="bh-btn bh-btn-ghost" href="<?php echo esc_url(home_url('/coran/')); ?>"><?php echo esc_html(bh_l('Lire le Coran', 'اقرأ القرآن')); ?></a>
        </p>
    </div>
</section>

<?php get_footer(); ?>
