<?php
/**
 * Template Name: Accueil — En direct
 * Front page: live player (TV1/TV2 switcher, quality, QR, countdown) +
 * now/next + today's schedule + programmes + latest replays + daily ayah + news.
 */
defined('ABSPATH') || exit;
get_header();

$live    = bh_live();
$live2   = function_exists('bh_live_channel2') ? bh_live_channel2() : ['enabled' => false];
$nowNext = bh_now_next();
$hasCh2  = !empty($live2['enabled']);
?>

<section class="bh-hero">
    <div class="bh-container bh-hero__grid"
         x-data="bhLive(<?php echo esc_attr(wp_json_encode([
             'ch1'   => [
                 'enabled'  => !empty($live['enabled']) && '' !== trim((string) $live['url']),
                 'platform' => $live['platform'],
                 'title'    => function_exists('bh_l') ? bh_l($live['title_fr'] ?: 'Diffusion en direct', $live['title_ar'] ?: 'بث مباشر') : '',
             ],
             'ch2'   => [
                 'enabled'  => $hasCh2,
                 'platform' => $live2['platform'],
                 'title'    => function_exists('bh_l') ? bh_l($live2['title_fr'] ?: 'Coran en continu', $live2['title_ar'] ?: 'القرآن الكريم 24/7') : '',
             ],
             'now'   => $nowNext,
             'page'  => get_permalink(),
         ])); ?>)">
        <div class="bh-hero__live">
            <div class="bh-hero__live-head">
                <span class="bh-live-pill" :class="{ 'is-on': currentOn() }" x-text="currentOn() ? liveLabel() : offLabel()"></span>
                <h1 class="bh-hero__title" x-text="currentTitle()"></h1>

                <?php if ($hasCh2) : ?>
                    <span class="bh-channels" role="tablist" aria-label="<?php echo esc_attr(bh_l('Chaînes', 'القنوات')); ?>">
                        <button type="button" class="bh-channels__btn" :class="{ 'is-active': ch === 1 }" @click="ch = 1" role="tab"><?php echo esc_html(bh_l('TV1', 'قناة 1')); ?></button>
                        <button type="button" class="bh-channels__btn" :class="{ 'is-active': ch === 2 }" @click="ch = 2" role="tab"><?php echo esc_html(bh_l('TV2', 'قناة 2')); ?></button>
                    </span>
                <?php endif; ?>
            </div>

            <template x-if="ch1.enabled && (!hasCh2 || ch === 1)">
                <div class="bh-hero__player"><?php echo bh_live_embed_html(['autoplay' => true, 'mute' => true]); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
            </template>
            <template x-if="hasCh2 && ch === 2 && ch2.enabled">
                <div class="bh-hero__player"><?php echo function_exists('bh_live_embed_html_for') ? bh_live_embed_html_for(function_exists('bh_live_channel2') ? bh_live_channel2() : [], ['autoplay' => true]) : ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></div>
            </template>
            <template x-if="!ch1.enabled && (!hasCh2 || ch === 1)">
                <div class="bh-hero__off">
                    <div class="bh-hero__off-icon" aria-hidden="true">📡</div>
                    <p><?php echo esc_html(bh_l('La chaîne est momentanément hors antenne. Retrouvez la grille des programmes ci-dessous et suivez-nous sur Facebook pour les prochains directs.', 'القناة غير موجودة على الهواء حاليًا. تجدون أدناه شبكة البرامج، وتابعونا على فيسبوك لآخر البثوث المباشرة.')); ?></p>
                    <p class="bh-hero__off-next" x-show="nextTitle()">
                        <?php echo esc_html(bh_l('Prochaine antenne', 'البث القادم')) ?> :
                        <strong x-text="nextTitle()"></strong>
                        <span class="bh-time-range" x-text="nextStart()"></span>
                        <span class="bh-countdown" x-text="countdown()"></span>
                    </p>
                </div>
            </template>

            <!-- Autoplay starts muted (browser rule): tell visitors how to get the sound. -->
            <p class="bh-hero__sound-hint" x-data="{ show: true }" x-show="show && ch1.enabled && (!hasCh2 || ch === 1)" x-init="setTimeout(() => show = false, 12000)" x-cloak>
                <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 9.5v5h3.5L12 18.5v-13L7.5 9.5H4z"/><path d="m16 9.5 4 5m0-5-4 5"/></svg>
                <?php echo esc_html(bh_l('La vidéo démarre sans le son (règle des navigateurs) : touchez le haut-parleur du lecteur pour l’activer.', 'يبدأ الفيديو بدون صوت (قاعدة المتصفحات): اضغط على أيقونة مكبّر الصوت في المشغّل لتفعيله.')); ?>
            </p>

            <?php if ($nowNext['current']) : ?>
                <p class="bh-hero__now">
                    <?php echo esc_html(bh_l('À l’antenne maintenant', 'على الهواء الآن')) ?> :
                    <a href="<?php echo $nowNext['current']['programme_id'] ? esc_url(get_permalink($nowNext['current']['programme_id'])) : '#'; ?>">
                        <strong><?php echo esc_html(bh_slot_title($nowNext['current'])); ?></strong>
                    </a>
                    <span class="bh-time-range"><?php echo esc_html($nowNext['current']['start'] . '–' . $nowNext['current']['end']); ?></span>
                </p>
            <?php else : ?>
                <p class="bh-hero__now" x-show="nextTitle()">
                    <?php echo esc_html(bh_l('Prochaine antenne', 'البث القادم')) ?> :
                    <strong x-text="nextTitle()"></strong>
                    <span class="bh-time-range" x-text="nextStart()"></span>
                    <span class="bh-countdown" x-text="countdown()"></span>
                </p>
            <?php endif; ?>

            <!-- Just-ended banner (filled by JS from the schedule) -->
            <div class="bh-just-ended" x-show="justEndedTitle()" x-cloak>
                <?php echo esc_html(bh_l('C’est terminé : ', 'انتهى للتو: ')) ?>
                <strong x-text="justEndedTitle()"></strong>
                <a class="bh-link" :href="justEndedUrl()" x-show="justEndedUrl()"><?php echo esc_html(bh_l('Voir le programme →', 'عرض البرنامج ←')); ?></a>
            </div>

            <!-- Live tools: QR + quality (HLS only) -->
            <div class="bh-live-tools">
                <button type="button" class="bh-btn bh-btn--ghost" @click="showQr = !showQr">
                    📱 <?php echo esc_html(bh_l('Regarder sur téléphone', 'شاهد على الهاتف')) ?>
                </button>
                <select class="bh-select bh-select--small" x-show="currentPlatform() === 'hls'"
                        x-model="quality" @change="applyQuality()" aria-label="<?php echo esc_attr(bh_l('Qualité vidéo', 'جودة الفيديو')); ?>">
                    <option value="auto"><?php echo esc_html(bh_l('Qualité auto (recommandée)', 'جودة تلقائية (مقترح)')); ?></option>
                    <option value="720"><?php echo esc_html(bh_l('720p', '720p')); ?></option>
                    <option value="480"><?php echo esc_html(bh_l('480p', '480p')); ?></option>
                </select>
            </div>
            <div class="bh-qr-modal" x-show="showQr" x-cloak @click.outside="showQr = false">
                <div class="bh-qr-modal__box" role="dialog" aria-label="<?php echo esc_attr(bh_l('QR code', 'رمز QR')); ?>">
                    <canvas id="bh-live-qr" width="220" height="220" aria-hidden="true"></canvas>
                    <small x-text="qrText()"></small>
                    <button type="button" class="bh-btn bh-btn--ghost" @click="showQr = false"><?php echo esc_html(bh_l('Fermer', 'إغلاق')); ?></button>
                </div>
            </div>

            <!-- Prayer times: sits under the player so both hero columns stay balanced -->
            <div class="bh-card bh-card--prayer" x-data="bhPrayer()">
                <h2 class="bh-card__title">
                    <select x-model="city" @change="load()" aria-label="<?php echo esc_attr(bh_l('Ville', 'المدينة')); ?>" class="bh-select bh-select--small">
                        <template x-for="c in cities" :key="c"><option :value="c" x-text="c"></option></template>
                    </select>
                </h2>
                <div class="bh-prayer-grid">
                    <template x-for="p in order" :key="p.key">
                        <div class="bh-prayer" :class="{ 'is-next': p.key === nextKey }">
                            <span class="bh-prayer__name" x-text="names[p.key]"></span>
                            <strong x-text="times[p.key] || '—:—'"></strong>
                        </div>
                    </template>
                </div>
                <p class="bh-prayer__src" x-show="source === 'fallback'"><?php echo esc_html(bh_l('Horaires indicatifs (hors-ligne)', 'أوقات تقريبية (دون اتصال)')); ?></p>
            </div>
        </div>

        <aside class="bh-hero__side">
            <div class="bh-card bh-card--ayah" x-data="bhAyah()">
                <h2 class="bh-card__title"><?php echo esc_html(bh_l('Verset du jour', 'آية اليوم')); ?></h2>
                <p class="bh-ayah-ar" dir="rtl" x-text="text"></p>
                <p class="bh-ayah-ref" x-text="ref"></p>
                <a class="bh-link" :href="coranUrl + (surah ? '/' + surah : '')"><?php echo esc_html(bh_l('Lire le Coran →', 'اقرأ القرآن →')); ?></a>
            </div>

            <div class="bh-card bh-card--dhikr">
                <?php if (function_exists('bh_name_of_day')) : $bh_name = bh_name_of_day(); ?>
                    <h2 class="bh-card__title"><?php echo esc_html(bh_l('Nom d’Allah du jour', 'اسم من أسماء الله اليوم')); ?></h2>
                    <p class="bh-dhikr__name" dir="rtl"><?php echo esc_html($bh_name['ar'] ?? ''); ?></p>
                    <p class="bh-dhikr__fr"><?php echo esc_html($bh_name['fr'] ?? ''); ?></p>
                <?php endif; ?>
                <?php if (function_exists('bh_daily_hadith')) : $bh_hadith = bh_daily_hadith(); ?>
                    <h2 class="bh-card__title"><?php echo esc_html(bh_l('Hadith du jour', 'حديث اليوم')); ?></h2>
                    <blockquote class="bh-dhikr__hadith">
                        <p dir="rtl"><?php echo esc_html($bh_hadith['ar'] ?? ''); ?></p>
                        <p><?php echo esc_html($bh_hadith['fr'] ?? ''); ?></p>
                        <?php if (!empty($bh_hadith['source'])) : ?><footer><?php echo esc_html($bh_hadith['source']); ?></footer><?php endif; ?>
                    </blockquote>
                <?php endif; ?>
            </div>
        </aside>
    </div>
</section>

<section class="bh-section">
    <div class="bh-container">
        <div class="bh-section__head">
            <h2><?php echo esc_html(bh_l('Au programme aujourd’hui', 'برنامج اليوم')); ?></h2>
            <a class="bh-link" href="<?php echo esc_url(bh_nav_url('programmes')); ?>"><?php echo esc_html(bh_l('Grille complète', 'الشبكة الكاملة')); ?></a>
        </div>
        <?php get_template_part('template-parts/schedule', 'strip', ['day' => bh_schedule_weekday()]); ?>
    </div>
</section>

<section class="bh-section bh-section--alt">
    <div class="bh-container">
        <div class="bh-section__head">
            <h2><?php echo esc_html(bh_l('Nos programmes', 'برامجنا')); ?></h2>
            <a class="bh-link" href="<?php echo esc_url(bh_nav_url('programmes')); ?>"><?php echo esc_html(bh_l('Tout voir →', 'عرض الكل →')); ?></a>
        </div>
        <?php
        $programmes = new WP_Query([
            'post_type' => 'programme', 'posts_per_page' => 6, 'orderby' => 'menu_order title', 'order' => 'ASC', 'no_found_rows' => true,
        ]);
        if ($programmes->have_posts()) {
            echo '<div class="bh-grid bh-grid--3">';
            foreach ($programmes->posts as $p) {
                get_template_part('template-parts/programme', 'card', ['post' => $p]);
            }
            echo '</div>';
        } else {
            echo '<p class="bh-empty">' . esc_html(bh_l('Les programmes apparaîtront ici dès leur publication.', 'ستظهر البرامج هنا فور نشرها.')) . '</p>';
        }
        wp_reset_postdata();
        ?>
    </div>
</section>

<section class="bh-section">
    <div class="bh-container">
        <div class="bh-section__head">
            <h2><?php echo esc_html(bh_l('Derniers replays', 'آخر البثوث المسجلة')); ?></h2>
            <a class="bh-link" href="<?php echo esc_url(bh_nav_url('emissions')); ?>"><?php echo esc_html(bh_l('Tous les replays →', 'جميع الحلقات →')); ?></a>
        </div>
        <?php
        $episodes = new WP_Query([
            'post_type' => 'episode', 'posts_per_page' => 4, 'no_found_rows' => true,
        ]);
        if ($episodes->have_posts()) {
            echo '<div class="bh-grid bh-grid--4">';
            foreach ($episodes->posts as $e) {
                get_template_part('template-parts/episode', 'card', ['post' => $e]);
            }
            echo '</div>';
        } else {
            echo '<p class="bh-empty">' . esc_html(bh_l('Les replays apparaîtront ici.', 'ستظهر الحلقات المسجلة هنا.')) . '</p>';
        }
        wp_reset_postdata();
        ?>
    </div>
</section>

<section class="bh-section bh-section--alt">
    <div class="bh-container">
        <div class="bh-section__head">
            <h2><?php echo esc_html(bh_l('Actualités', 'الأخبار')); ?></h2>
            <a class="bh-link" href="<?php echo esc_url(bh_nav_url('actualites')); ?>"><?php echo esc_html(bh_l('Toutes les actualités →', 'جميع الأخبار →')); ?></a>
        </div>
        <?php get_template_part('template-parts/news', 'list', ['limit' => 3, 'variant' => 'featured']); ?>
    </div>
</section>

<?php get_footer(); ?>
