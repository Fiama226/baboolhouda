<?php
/**
 * Template Name: Newsletter
 */
defined('ABSPATH') || exit;
get_header();
$s = function_exists('bh_settings') ? bh_settings() : [];
?>
<div class="bh-container bh-page" x-data="bhNewsletter(<?php echo wp_json_encode([
    'rest' => esc_url_raw(rest_url('bh/v1/newsletter')),
]); ?>)">
    <nav class="bh-breadcrumb" aria-label="Fil d’Ariane">
        <a href="<?php echo esc_url(bh_nav_url('direct')); ?>"><?php echo esc_html(bh_l('Accueil', 'الرئيسية')); ?></a>
        <span aria-hidden="true">/</span> <span><?php echo esc_html(bh_l('Newsletter', 'النشرة')); ?></span>
    </nav>
    <header class="bh-page__head">
        <h1><?php echo esc_html(bh_post_title(get_queried_object_id())); ?></h1>
        <p class="bh-page__lead"><?php echo esc_html(bh_l('Recevez les annonces de la chaîne : directs spéciaux, événements, nouveaux programmes.', 'توصلوا ببيانات القناة: بث خاص، فعاليات، برامج جديدة.')); ?></p>
    </header>
    <?php if (!empty($s['newsletter_enabled'])) : ?>
        <form class="bh-card bh-newsletter-form" @submit.prevent="subscribe()">
            <label><?php echo esc_html(bh_l('Votre email', 'بريدك الإلكتروني')); ?>
                <input type="email" x-model="email" required>
            </label>
            <input type="text" name="website" class="bh-hp" tabindex="-1" autocomplete="off" aria-hidden="true" x-ref="hp">
            <button type="submit" class="bh-btn" :disabled="busy"><?php echo esc_html(bh_l('S’abonner', 'اشترك')); ?></button>
            <p class="bh-form-ok" x-show="msg && ok" x-text="msg"></p>
            <p class="bh-form-err" x-show="msg && !ok" x-text="msg"></p>
        </form>
    <?php else : ?>
        <p class="bh-muted"><?php echo esc_html(bh_l('Inscriptions en pause.', 'الاشتراك متوقف مؤقتاً.')); ?></p>
    <?php endif; ?>
</div>
<?php get_footer();
