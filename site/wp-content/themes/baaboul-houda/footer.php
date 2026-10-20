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
            <h3><?php echo esc_html(bh_l('La chaîne', 'القناة')); ?></h3>
            <ul>
                <li><a href="<?php echo esc_url(bh_nav_url('outils')); ?>"><?php echo esc_html(bh_l('Outils & calculs', 'الأدوات')); ?></a></li>
                <li><a href="<?php echo esc_url(bh_nav_url('podcast')); ?>"><?php echo esc_html(bh_l('Podcasts', 'البودكاست')); ?></a></li>
                <li><a href="<?php echo esc_url(bh_nav_url('fatwas')); ?>"><?php echo esc_html(bh_l('Questions & réponses', 'الأسئلة والأجوبة')); ?></a></li>
                <li><a href="<?php echo esc_url(bh_nav_url('dons')); ?>"><?php echo esc_html(bh_l('Dons & soutien', 'التبرع')); ?></a></li>
                <li><a href="<?php echo esc_url(bh_nav_url('publicite')); ?>"><?php echo esc_html(bh_l('Publicité', 'الإعلان')); ?></a></li>
                <li><a href="<?php echo esc_url(bh_nav_url('presse')); ?>"><?php echo esc_html(bh_l('Espace presse', 'الصحافة')); ?></a></li>
            </ul>
            <?php if (!empty($s['newsletter_enabled'])) : ?>
                <form class="bh-footer__newsletter" x-data="bhNewsletterFooter(<?php echo wp_json_encode([
                    'rest' => esc_url_raw(rest_url('bh/v1/newsletter')),
                ]); ?>)" @submit.prevent="subscribe()">
                    <label class="bh-visually-hidden" for="bh-fn-email"><?php echo esc_html(bh_l('Votre email pour la newsletter', 'بريدك للنشرة')); ?></label>
                    <input id="bh-fn-email" type="email" x-model="email" required placeholder="<?php echo esc_attr(bh_l('Votre email (newsletter)', 'بريدك (النشرة)')); ?>">
                    <button type="submit" class="bh-btn"><?php echo esc_html(bh_l('OK', 'حسناً')); ?></button>
                    <small x-text="msg" x-show="msg" class="bh-muted"></small>
                </form>
            <?php endif; ?>
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

<!-- Floating WhatsApp (hidden by JS when unconfigured) -->
<a class="bh-wa-float" data-bh-wa-float href="#" target="_blank" rel="noopener" aria-label="<?php echo esc_attr(bh_l('Écrire sur WhatsApp', 'راسلنا واتساب')) ?>" style="display:none">
    <svg viewBox="0 0 24 24" width="26" height="26" fill="currentColor" aria-hidden="true"><path d="M12.04 2c-5.46 0-9.91 4.45-9.91 9.91 0 1.75.46 3.45 1.32 4.95L2.05 22l5.25-1.38c1.45.79 3.08 1.21 4.74 1.21 5.46 0 9.91-4.45 9.91-9.91 0-2.65-1.03-5.14-2.9-7.01A9.82 9.82 0 0 0 12.04 2m0 18.15c-1.48 0-2.93-.4-4.2-1.15l-.3-.18-3.12.82.83-3.04-.2-.31a8.26 8.26 0 0 1-1.26-4.38c0-4.54 3.7-8.24 8.25-8.24 2.2 0 4.27.86 5.82 2.42a8.18 8.18 0 0 1 2.41 5.83c0 4.54-3.7 8.23-8.23 8.23M18.12 15.09c-.03-.06-.59-.7-.7-.78s-.18-.08-.26-.01-.32.28-.39.34-.13.07-.24 0a6.72 6.72 0 0 1-3.16-2.74c-.24-.4.24-.37.7-1.13.07-.12.03-.22 0-.3s-.26-.72-.36-.98c-.09-.26-.18-.22-.26-.23h-.22c-.08 0-.2.03-.31.16-.12.12-.44.42-.44 1.02s.46 1.19.53 1.3c.07.12 1.02 1.56 2.46 2.19.86.38 1.52.49 2.07.5.86.02 1.32-.45 1.47-.73.16-.28.16-.51.12-.71z"/></svg>
</a>

<!-- Live "we're on air" toast (JS) -->
<div class="bh-live-toast" id="bh-live-toast" data-bh-live-toast hidden role="status"></div>

<?php
// Analytics: built-in pageview counter (no third party) + optional custom snippet.
if (!empty($s['analytics_enabled'])) {
    echo '<script>window.addEventListener("load",function(){try{fetch("' . esc_js(rest_url('bh/v1/analytics')) . '",{method:"POST",keepalive:true,credentials:"omit"})}catch(err){}});</script>';
}
if (!empty($s['analytics_snippet'])) {
    echo wp_kses_post($s['analytics_snippet']);
}
?>
<?php wp_footer(); ?>
</body>
</html>
