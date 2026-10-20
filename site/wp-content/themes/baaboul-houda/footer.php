<?php
/** Theme footer + radio mini-player. */
defined('ABSPATH') || exit;
$s = function_exists('bh_settings') ? bh_settings() : [];
?>
</main>

<footer class="bh-footer">
    <div class="bh-container bh-footer__grid">
        <div class="bh-footer__col bh-footer__about">
            <?php echo bh_logo_html(44); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
            <p><?php echo esc_html(bh_l($s['footer_text_fr'], $s['footer_text_ar'])); ?></p>
            <p class="bh-footer__socials">
                <?php if (!empty($s['facebook_url'])) : ?>
                    <a href="<?php echo esc_url($s['facebook_url']); ?>" target="_blank" rel="noopener" aria-label="Facebook">Facebook</a><span aria-hidden="true">·</span>
                <?php endif; ?>
                <?php if (!empty($s['youtube_url'])) : ?>
                    <a href="<?php echo esc_url($s['youtube_url']); ?>" target="_blank" rel="noopener" aria-label="YouTube">YouTube</a><span aria-hidden="true">·</span>
                <?php endif; ?>
                <?php if (!empty($s['whatsapp'])) : ?>
                    <a href="https://wa.me/<?php echo esc_attr(preg_replace('/[^0-9+]/', '', $s['whatsapp'])); ?>" target="_blank" rel="noopener" aria-label="WhatsApp">WhatsApp</a>
                <?php endif; ?>
            </p>
        </div>
        <nav class="bh-footer__col" aria-label="<?php echo esc_attr(bh_l('Liens rapides', 'روابط سريعة')); ?>">
            <h3><?php echo esc_html(bh_l('Liens rapides', 'روابط سريعة')); ?></h3>
            <ul>
                <?php foreach (array_slice(bh_nav_items(), 0, 8) as $item) : ?>
                    <li><a href="<?php echo esc_url(bh_nav_url($item['slug'])); ?>"><?php echo esc_html(bh_l($item['label_fr'], $item['label_ar'])); ?></a></li>
                <?php endforeach; ?>
            </ul>
        </nav>
        <div class="bh-footer__col">
            <h3><?php echo esc_html(bh_l('Contact', 'اتصل بنا')); ?></h3>
            <ul class="bh-footer__contact">
                <?php if (!empty($s['phone'])) : ?>
                    <li><a href="tel:<?php echo esc_attr(preg_replace('/[^0-9+]/', '', $s['phone'])); ?>"><?php echo esc_html($s['phone']); ?></a></li>
                <?php endif; ?>
                <?php if (!empty($s['email'])) : ?>
                    <li><a href="mailto:<?php echo esc_attr($s['email']); ?>"><?php echo esc_html($s['email']); ?></a></li>
                <?php endif; ?>
                <?php if (!empty($s['address'])) : ?>
                    <li><?php echo esc_html($s['address']); ?></li>
                <?php endif; ?>
            </ul>
        </div>
        <div class="bh-footer__col">
            <h3><?php echo esc_html(bh_l('Programmes du jour', 'برامج اليوم')); ?></h3>
            <ul class="bh-footer__schedule">
                <?php
                if (function_exists('bh_schedule_by_day') && function_exists('bh_schedule_weekday')) {
                    $slots = bh_schedule_by_day(bh_schedule_weekday());
                    foreach (array_slice($slots, 0, 5) as $slot) {
                        echo '<li><span class="bh-time-range">' . esc_html($slot['start'] . '–' . $slot['end']) . '</span> ' . esc_html(bh_slot_title($slot)) . '</li>';
                    }
                }
                ?>
            </ul>
        </div>
    </div>
    <div class="bh-footer__bottom">
        <div class="bh-container">
            <span>© <?php echo esc_html(date_i18n('Y')); ?> <?php bloginfo('name'); ?> — <?php echo esc_html(bh_l('Tous droits réservés.', 'جميع الحقوق محفوظة.')); ?></span>
            <span class="bh-footer__bottom-links">
                <a href="<?php echo esc_url(bh_nav_url('apropos')); ?>"><?php echo esc_html(bh_l('À propos', 'من نحن')); ?></a>
            </span>
        </div>
    </div>
</footer>

<?php
// Radio mini-player (persistent). Alpine component wired in main.js.
$radioCfg = [
    'enabled' => !empty($s['radio_enabled']) && '' !== trim((string) ($s['radio_url'] ?? '')),
    'url'     => (string) ($s['radio_url'] ?? ''),
    'title_fr' => $s['radio_title_fr'] ?? '',
    'title_ar' => $s['radio_title_ar'] ?? '',
    'cover'   => (string) ($s['radio_cover_url'] ?? ''),
];
if ($radioCfg['enabled']) :
    ?>
    <div class="bh-radio-bar" data-bh-radio
         data-url="<?php echo esc_attr($radioCfg['url']); ?>"
         data-title-fr="<?php echo esc_attr($radioCfg['title_fr']); ?>"
         data-title-ar="<?php echo esc_attr($radioCfg['title_ar']); ?>"
         data-cover="<?php echo esc_attr($radioCfg['cover']); ?>"
         role="region" aria-label="<?php echo esc_attr(bh_l('Player radio', 'مشغل الإذاعة')); ?>">
        <div class="bh-radio-bar__inner">
            <?php if ($radioCfg['cover']) : ?>
                <img class="bh-radio-bar__cover" src="<?php echo esc_url($radioCfg['cover']); ?>" alt="" width="40" height="40">
            <?php else : ?>
                <span class="bh-radio-bar__disc" aria-hidden="true">📻</span>
            <?php endif; ?>
            <div class="bh-radio-bar__meta">
                <strong data-radio-title></strong>
                <span class="bh-radio-bar__status" data-radio-status></span>
            </div>
            <span class="bh-eq" aria-hidden="true" data-bh-eq><i></i><i></i><i></i><i></i></span>
            <button class="bh-radio-bar__btn" type="button" data-bh-radio-toggle aria-label="<?php echo esc_attr(bh_l('Écouter / couper la radio', 'تشغيل / إيقاف الإذاعة')); ?>">
                <svg class="icon-play" viewBox="0 0 24 24" width="20" height="20" fill="currentColor" aria-hidden="true"><path d="M8 5v14l11-7z"/></svg>
                <svg class="icon-pause" viewBox="0 0 24 24" width="20" height="20" fill="currentColor" aria-hidden="true" hidden><path d="M6 5h4v14H6zM14 5h4v14h-4z"/></svg>
            </button>
        </div>
    </div>
    <?php
endif;
?>

<script>
    if ('serviceWorker' in navigator && location.protocol.startsWith('http')) {
        window.addEventListener('load', function () {
            navigator.serviceWorker.register('<?php echo esc_js(home_url('/sw.js')); ?>').catch(function () {});
        });
    }
</script>
<?php wp_footer(); ?>
</body>
</html>
