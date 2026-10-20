<?php
/** Template Name: Actualités (liste des articles) */
defined('ABSPATH') || exit;
get_header();
?>

<section class="bh-page-hero">
    <div class="bh-container">
        <h1><?php echo esc_html(bh_post_title(get_queried_object_id())); ?></h1>
        <p class="bh-page-hero__sub"><?php echo esc_html(bh_l('Dernières nouvelles et reportages de la chaîne.', 'آخر أخبار القناة وتقاريرها.')); ?></p>
    </div>
</section>

<section class="bh-section">
    <div class="bh-container bh-news-layout">
        <div class="bh-news-layout__main">
            <?php get_template_part('template-parts/news', 'list', ['limit' => 20]); ?>
        </div>
        <aside class="bh-news-layout__side">
            <?php $trending = function_exists('bh_trending') ? bh_trending(6) : []; ?>
            <?php if ($trending) : ?>
                <div class="bh-card bh-card--trending">
                    <h2 class="bh-card__title"><?php echo esc_html(bh_l('Les plus lus', 'الأكثر قراءة')); ?></h2>
                    <ol class="bh-trending">
                        <?php foreach ($trending as $t) : ?>
                            <li>
                                <a href="<?php echo esc_url(get_permalink($t['id'])); ?>"><?php echo esc_html(bh_post_title($t['id'])); ?></a>
                            </li>
                        <?php endforeach; ?>
                    </ol>
                </div>
            <?php endif; ?>
            <div class="bh-card">
<?php $lb = bh_l('Vous avez une info ?', 'لديكم خبر؟'); ?>
                <h2 class="bh-card__title"><?php echo esc_html($lb); ?></h2>
                <h2 class="bh-card__title"><?php echo esc_html($lb); ?></h2>
                <p class="bh-muted"><?php echo esc_html(bh_l('Écrivez-nous par WhatsApp :', 'راسلونا عبر واتساب:')); ?></p>
                <p><a class="bh-btn" href="https://wa.me/<?php echo esc_attr(preg_replace('/[^0-9+]/', '', (string) (bh_settings()['whatsapp'] ?? ''))); ?>" target="_blank" rel="noopener"><?php echo esc_html(bh_l('WhatsApp', 'واتساب')); ?></a></p>
            </div>
        </aside>
    </div>
</section>

<?php get_footer(); ?>
