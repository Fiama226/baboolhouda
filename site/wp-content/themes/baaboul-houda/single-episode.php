<?php
/**
 * Single episode (replay): player by source type + metadata + share.
 */
defined('ABSPATH') || exit;
get_header();
$id   = get_the_ID();
$src  = bh_episode_source($id);
$prog = bh_episode_programme($id);
$epDate = get_post_meta($id, '_bh_date', true);
?>

<section class="bh-page-hero">
    <div class="bh-container">
        <?php if ($prog) : ?>
            <a class="bh-breadcrumb" href="<?php echo esc_url(get_permalink($prog->ID)); ?>"><?= esc_html(bh_l('Retour à', 'العودة إلى')) ?> <?php echo esc_html(bh_post_title($prog->ID)); ?></a>
        <?php endif; ?>
        <h1><?php echo esc_html(bh_post_title($id)); ?></h1>
        <p class="bh-page-hero__sub">
            <?php if ($epDate) : ?>
                <span><?php echo esc_html(date_i18n('j F Y', strtotime($epDate))); ?></span> ·
            <?php endif; ?>
            <span><?php echo esc_html(bh_l('Replay', 'إعادة')); ?></span>
            <?php if ($prog) : ?>
                · <span><?php echo esc_html(bh_post_title($prog->ID)); ?></span>
            <?php endif; ?>
        </p>
    </div>
</section>

<section class="bh-section">
    <div class="bh-container bh-container--wide bh-episode-layout">
        <div class="bh-episode__player">
            <?php
            switch ($src['type']) {
                case 'youtube':
                    $ytId = bh_youtube_id($src['url']);
                    if ($ytId) {
                        echo '<div class="bh-embed bh-embed-youtube"><iframe src="https://www.youtube.com/embed/' . rawurlencode($ytId) . '?rel=0" title="' . esc_attr(bh_post_title($id)) . '" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen loading="lazy"></iframe></div>';
                    } else {
                        echo '<p class="bh-empty">' . esc_html(bh_l('Lien YouTube invalide.', 'رابط يوتيوب غير صالح.')) . '</p>';
                    }
                    break;
                case 'facebook':
                    $href = rawurlencode($src['url']);
                    echo '<div class="bh-embed bh-embed-facebook"><iframe src="https://www.facebook.com/plugins/video.php?href=' . $href . '&show_text=true&width=560" width="560" height="315" style="border:none;overflow:hidden" scrolling="no" frameborder="0" allowfullscreen loading="lazy" title="' . esc_attr(bh_post_title($id)) . '"></iframe></div>';
                    break;
                case 'file':
                    echo '<div class="bh-embed"><video controls preload="metadata" poster="' . esc_attr(has_post_thumbnail($id) ? get_the_post_thumbnail_url($id, 'large') : '') . '"><source src="' . esc_url($src['url']) . '" type="video/mp4">' . esc_html(bh_l('Votre navigateur ne prend pas en charge la lecture vidéo.', 'متصفحكم لا يدعم تشغيل الفيديو.')) . '</video></div>';
                    break;
                case 'audio':
                    $audio = $src['audio'] ?: $src['url'];
                    echo '<div class="bh-audio-card"><span class="bh-audio-card__icon" aria-hidden="true">🎧</span>'
                        . '<audio controls preload="none" src="' . esc_url($audio) . '"></audio></div>';
                    break;
            }
            ?>
        </div>

        <div class="bh-episode__side">
            <div class="bh-prose">
                <?php echo bh_post_content($id); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
            </div>

            <div class="bh-share">
                <strong><?php echo esc_html(bh_l('Partager', 'مشاركة')); ?></strong>
                <?php $shareUrl = urlencode(get_permalink($id)); $shareText = urlencode(bh_post_title($id)); ?>
                <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo esc_attr($shareUrl); ?>" target="_blank" rel="noopener" class="bh-share__btn">Facebook</a>
                <a href="https://wa.me/?text=<?php echo esc_attr($shareText . ' ' . $shareUrl); ?>" target="_blank" rel="noopener" class="bh-share__btn">WhatsApp</a>
                <a href="https://twitter.com/intent/tweet?url=<?php echo esc_attr($shareUrl); ?>&text=<?php echo esc_attr($shareText); ?>" target="_blank" rel="noopener" class="bh-share__btn">X</a>
                <button class="bh-share__btn" type="button" onclick="navigator.clipboard.writeText('<?php echo esc_js(get_permalink($id)); ?>')">
                    <?php echo esc_html(bh_l('Copier le lien', 'نسخ الرابط')); ?>
                </button>
            </div>
        </div>
    </div>
</section>

<?php get_footer(); ?>
