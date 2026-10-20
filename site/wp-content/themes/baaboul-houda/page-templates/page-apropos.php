<?php
/** Template Name: À propos */
defined('ABSPATH') || exit;
get_header();
?>

<section class="bh-page-hero">
    <div class="bh-container">
        <h1><?php echo esc_html(bh_post_title(get_queried_object_id())); ?></h1>
    </div>
</section>

<section class="bh-section">
    <div class="bh-container bh-container--narrow bh-prose">
        <?php echo bh_post_content(get_the_ID()); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
    </div>
</section>

<?php
$s  = bh_settings();
$fb = (string) ($s['facebook_url'] ?? '');
$yt = (string) ($s['youtube_url'] ?? '');
if ($fb || $yt) :
    ?>
<section class="bh-section bh-section--alt">
    <div class="bh-container bh-container--narrow">
        <h2 class="bh-section__title"><?php echo esc_html(bh_l('Suivez-nous', 'تابعونا')); ?></h2>
        <div class="bh-social-grid">
            <?php if ($fb) : ?>
                <div class="bh-card">
                    <h3 class="bh-card__title"><?php echo esc_html(bh_l('Notre page Facebook', 'صفحتنا على فيسبوك')); ?></h3>
                    <div class="bh-fb-embed">
                        <div class="fb-page" data-href="<?php echo esc_attr($fb); ?>" data-tabs="timeline" data-width="340" data-small-header="false" data-adapt-container-width="true" data-hide-cover="false" data-show-facepile="false"></div>
                    </div>
                    <p class="bh-note"><?php echo esc_html(bh_l('Le flux s’affiche si votre navigateur le permet — sinon cliquez pour ouvrir la page.', 'يظهر الشريط مباشرة إذا سمح المتصفح بذلك، وإلا فافتح الصفحة من الرابط.')); ?></p>
                    <a class="bh-btn bh-btn--ghost" href="<?php echo esc_url($fb); ?>" target="_blank" rel="noopener"><?php echo esc_html(bh_l('Ouvrir la page Facebook ↗', 'افتح صفحة فيسبوك ↗')); ?></a>
                </div>
            <?php endif; ?>
            <?php if ($yt) : ?>
                <div class="bh-card">
                    <h3 class="bh-card__title"><?php echo esc_html(bh_l('Notre chaîne YouTube', 'قناتنا على يوتيوب')); ?></h3>
                    <p><?php echo esc_html(bh_l('Replays complets, émissions et directs enregistrements sur notre chaîne YouTube.', 'المعادلات الكاملة والبرامج والتسجيلات المباشرة على قناتنا في يوتيوب.')); ?></p>
                    <a class="bh-btn bh-btn-primary" href="<?php echo esc_url($yt); ?>" target="_blank" rel="noopener"><?php echo esc_html(bh_l('Voir la chaîne ↗', 'شاهد القناة ↗')); ?></a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<script async defer crossorigin="anonymous" src="https://connect.facebook.net/fr_FR/sdk.js#xfbml=1&version=v19.0"></script>

<?php get_footer(); ?>
