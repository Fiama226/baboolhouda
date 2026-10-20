<?php
/** Theme footer + radio mini-player. */
defined('ABSPATH') || exit;
$s = function_exists('bh_settings') ? bh_settings() : [];
// Footer icons: one 1.75 stroke weight, drawn inline (no emoji / glyph icons).
$bhFooterIco = static function (string $name): string {
    $paths = [
        'phone'    => '<path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.13.96.36 1.9.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.91.34 1.85.57 2.81.7A2 2 0 0 1 22 16.92z"/>',
        'mail'     => '<rect x="2" y="4" width="20" height="16" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/>',
        'pin'      => '<path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0z"/><circle cx="12" cy="10" r="3"/>',
        'whatsapp' => '<path d="M7.9 20A9 9 0 1 0 4 16.1L2 22z"/><path d="M9 9.5c0 3 2.5 5.5 5.5 5.5l1-1.5-2-1-.75.75a3 3 0 0 1-1.5-1.5l.75-.75-1-2z"/>',
        'facebook' => '<path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"/>',
        'youtube'  => '<path d="M2.5 17a24.12 24.12 0 0 1 0-10 2 2 0 0 1 1.4-1.4 49.56 49.56 0 0 1 16.2 0A2 2 0 0 1 21.5 7a24.12 24.12 0 0 1 0 10 2 2 0 0 1-1.4 1.4 49.55 49.55 0 0 1-16.2 0A2 2 0 0 1 2.5 17"/><path d="m10 15 5-3-5-3z"/>',
        'up'       => '<path d="m5 12 7-7 7 7"/><path d="M12 19V5"/>',
        'globe'    => '<circle cx="12" cy="12" r="10"/><path d="M2 12h20"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/>',
        'mailplus' => '<rect x="2" y="4" width="20" height="16" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/>',
    ];
    return '<svg class="bh-footer__ico" viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">' . ($paths[$name] ?? '') . '</svg>';
};
$bhPhone  = preg_replace('/[^0-9+]/', '', (string) ($s['phone'] ?? ''));
$bhWa     = preg_replace('/[^0-9+]/', '', (string) ($s['whatsapp'] ?? ''));
$bhMail   = (string) (!empty($s['email']) ? $s['email'] : ($s['contact_to'] ?? ''));
$bhGroups = [
    [
        'title' => bh_l('Regarder', 'شاهد'),
        'links' => [
            ['direct', bh_l('En direct', 'البث المباشر')],
            ['programmes', bh_l('Programmes', 'البرامج')],
            ['emissions', bh_l('Replays', 'البث المسجل')],
            ['radio', bh_l('Radio', 'الإذاعة')],
            ['podcast', bh_l('Podcasts', 'البودكاست')],
        ],
    ],
    [
        'title' => bh_l('Apprendre', 'تعلّم'),
        'links' => [
            ['coran', bh_l('Coran', 'القرآن')],
            ['prieres', bh_l('Heures de prière', 'أوقات الصلاة')],
            ['outils', bh_l('Outils & calculs', 'الأدوات')],
            ['adhkar', bh_l('Invocations (Adhkar)', 'الأذكار')],
            ['noms-allah', bh_l('Les 99 noms d’Allah', 'أسماء الله الحسنى')],
            ['fatwas', bh_l('Questions & réponses', 'الأسئلة والأجوبة')],
        ],
    ],
    [
        'title' => bh_l('La chaîne', 'القناة'),
        'links' => [
            ['actualites', bh_l('Actualités', 'الأخبار')],
            ['evenements', bh_l('Événements', 'الفعاليات')],
            ['animateurs', bh_l('Animateurs', 'المقدمون')],
            ['apropos', bh_l('À propos', 'من نحن')],
            ['presse', bh_l('Espace presse', 'الصحافة')],
            ['publicite', bh_l('Publicité', 'الإعلان')],
            ['dons', bh_l('Dons & soutien', 'التبرع')],
        ],
    ],
];
$bhSlots = [];
if (function_exists('bh_schedule_by_day') && function_exists('bh_schedule_weekday')) {
    $bhSlots = array_slice((array) bh_schedule_by_day(bh_schedule_weekday()), 0, 5);
}
?>
</main>

<footer class="bh-footer" id="bh-footer">
    <?php if (!empty($s['newsletter_enabled'])) : ?>
        <div class="bh-container">
            <form class="bh-footer__newsletter" x-data="bhNewsletterFooter(<?php echo esc_attr(wp_json_encode([
                'rest' => esc_url_raw(rest_url('bh/v1/newsletter')),
            ])); ?>)" @submit.prevent="subscribe()" novalidate>
                <div class="bh-footer__newsletter-text">
                    <h2 class="bh-footer__newsletter-title"><?php echo esc_html(bh_l('Restez informés', 'ابقَ على اطلاع')); ?></h2>
                    <p><?php echo esc_html(bh_l('Programmes, rappels de prière et nouveautés de la chaîne, directement dans votre boîte mail.', 'البرامج وتذكيرات الصلاة وجديد القناة في بريدك الإلكتروني.')); ?></p>
                </div>
                <div class="bh-footer__newsletter-field">
                    <div class="bh-footer__newsletter-row">
                        <label class="bh-visually-hidden" for="bh-fn-email"><?php echo esc_html(bh_l('Votre email pour la newsletter', 'بريدك للنشرة')); ?></label>
                        <input id="bh-fn-email" type="email" name="email" autocomplete="email" dir="auto" x-model="email" required placeholder="<?php echo esc_attr(bh_l('Votre adresse email', 'بريدك الإلكتروني')); ?>">
                        <button type="submit" class="bh-footer__cta" :disabled="busy"><?php echo esc_html(bh_l('S’abonner', 'اشترك')); ?></button>
                    </div>
                    <p class="bh-footer__newsletter-msg" role="status" aria-live="polite" x-text="msg" x-show="msg" :class="ok ? 'is-ok' : 'is-err'"></p>
                </div>
            </form>
        </div>
    <?php endif; ?>

    <div class="bh-container bh-footer__grid">
        <div class="bh-footer__brand">
            <a class="bh-footer__logo" href="<?php echo esc_url(bh_nav_url('direct')); ?>">
                <?php echo bh_logo_html(48); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                <span class="bh-footer__name"><?php bloginfo('name'); ?></span>
            </a>
            <?php if (!empty($s['footer_text_fr']) || !empty($s['footer_text_ar'])) : ?>
                <p class="bh-footer__about"><?php echo esc_html(bh_l($s['footer_text_fr'] ?? '', $s['footer_text_ar'] ?? '')); ?></p>
            <?php endif; ?>
            <ul class="bh-footer__contact" aria-label="<?php echo esc_attr(bh_l('Contact', 'اتصل بنا')); ?>">
                <?php if ('' !== $bhPhone) : ?>
                    <li><a href="tel:<?php echo esc_attr($bhPhone); ?>"><?php echo $bhFooterIco('phone'); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><span dir="ltr"><?php echo esc_html($s['phone']); ?></span></a></li>
                <?php endif; ?>
                <?php if ('' !== $bhWa) : ?>
                    <li><a href="https://wa.me/<?php echo esc_attr(ltrim($bhWa, '+')); ?>" target="_blank" rel="noopener"><?php echo $bhFooterIco('whatsapp'); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><span><?php echo esc_html(bh_l('Écrire sur WhatsApp', 'راسلنا على واتساب')); ?></span></a></li>
                <?php endif; ?>
                <?php if ('' !== $bhMail) : ?>
                    <li><a href="mailto:<?php echo esc_attr($bhMail); ?>"><?php echo $bhFooterIco('mail'); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><span dir="ltr"><?php echo esc_html($bhMail); ?></span></a></li>
                <?php endif; ?>
                <?php if (!empty($s['address'])) : ?>
                    <li><span class="bh-footer__contact-static"><?php echo $bhFooterIco('pin'); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><span><?php echo esc_html($s['address']); ?></span></span></li>
                <?php endif; ?>
            </ul>
            <?php if (!empty($s['facebook_url']) || !empty($s['youtube_url'])) : ?>
                <ul class="bh-footer__socials" aria-label="<?php echo esc_attr(bh_l('Réseaux sociaux', 'مواقع التواصل')); ?>">
                    <?php if (!empty($s['facebook_url'])) : ?>
                        <li><a class="bh-footer__social" href="<?php echo esc_url($s['facebook_url']); ?>" target="_blank" rel="noopener" aria-label="Facebook"><?php echo $bhFooterIco('facebook'); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></a></li>
                    <?php endif; ?>
                    <?php if (!empty($s['youtube_url'])) : ?>
                        <li><a class="bh-footer__social" href="<?php echo esc_url($s['youtube_url']); ?>" target="_blank" rel="noopener" aria-label="YouTube"><?php echo $bhFooterIco('youtube'); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></a></li>
                    <?php endif; ?>
                </ul>
            <?php endif; ?>
        </div>

        <?php foreach ($bhGroups as $gi => $group) : ?>
            <nav class="bh-footer__col" aria-labelledby="bh-footer-h<?php echo (int) $gi; ?>">
                <h2 class="bh-footer__title" id="bh-footer-h<?php echo (int) $gi; ?>"><?php echo esc_html($group['title']); ?></h2>
                <ul class="bh-footer__links">
                    <?php foreach ($group['links'] as $link) : ?>
                        <li><a href="<?php echo esc_url(bh_nav_url($link[0])); ?>"><?php echo esc_html($link[1]); ?></a></li>
                    <?php endforeach; ?>
                </ul>
            </nav>
        <?php endforeach; ?>

        <div class="bh-footer__col bh-footer__today">
            <h2 class="bh-footer__title"><?php echo esc_html(bh_l('Programmes du jour', 'برامج اليوم')); ?></h2>
            <?php if ($bhSlots) : ?>
                <ul class="bh-footer__schedule">
                    <?php foreach ($bhSlots as $slot) : ?>
                        <li><span class="bh-time-range" dir="ltr"><?php echo esc_html($slot['start'] . '–' . $slot['end']); ?></span><span class="bh-footer__slot"><?php echo esc_html(bh_slot_title($slot)); ?></span></li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
            <a class="bh-footer__more" href="<?php echo esc_url(bh_nav_url('programmes')); ?>"><?php echo esc_html(bh_l('Voir la grille complète', 'عرض الشبكة كاملة')); ?></a>
        </div>
    </div>

    <div class="bh-footer__bottom">
        <div class="bh-container bh-footer__bottom-inner">
            <p class="bh-footer__copy">© <?php echo esc_html(date_i18n('Y')); ?> <?php bloginfo('name'); ?> — <?php echo esc_html(bh_l('Tous droits réservés.', 'جميع الحقوق محفوظة.')); ?></p>
            <nav class="bh-footer__bottom-links" aria-label="<?php echo esc_attr(bh_l('Informations', 'معلومات')); ?>">
                <a href="<?php echo esc_url(bh_nav_url('apropos')); ?>"><?php echo esc_html(bh_l('À propos', 'من نحن')); ?></a>
                <a href="<?php echo esc_url(bh_nav_url('contact')); ?>"><?php echo esc_html(bh_l('Contact', 'اتصل بنا')); ?></a>
                <a href="<?php echo esc_url(bh_nav_url('presse')); ?>"><?php echo esc_html(bh_l('Presse', 'الصحافة')); ?></a>
                <a href="<?php echo esc_url(bh_nav_url('publicite')); ?>"><?php echo esc_html(bh_l('Publicité', 'الإعلان')); ?></a>
            </nav>
            <div class="bh-footer__utility">
                <?php $bhLang = function_exists('bh_lang_switch_html') ? bh_lang_switch_html() : ''; ?>
                <?php if ($bhLang) : ?>
                    <span class="bh-footer__lang"><?php echo $bhFooterIco('globe'); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?><?php echo $bhLang; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
                <?php endif; ?>
                <button type="button" class="bh-footer__top-btn" x-data @click="window.scrollTo({ top: 0, behavior: window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 'auto' : 'smooth' })">
                    <?php echo $bhFooterIco('up'); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                    <span><?php echo esc_html(bh_l('Haut de page', 'أعلى الصفحة')); ?></span>
                </button>
            </div>
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
