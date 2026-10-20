<?php
/** Template Name: Radio */
defined('ABSPATH') || exit;
get_header();
$s = bh_settings();
$radioEnabled = !empty($s['radio_enabled']) && '' !== trim((string) $s['radio_url']);
?>

<section class="bh-page-hero">
    <div class="bh-container">
        <h1><?php echo esc_html(bh_post_title(get_queried_object_id())); ?></h1>
        <p class="bh-page-hero__sub"><?php echo esc_html(bh_l('Votre radio islamique 24h/24 — où que vous soyez.', 'إذاعتكم الإسلامية على مدار الساعة — أينما كنتم.')); ?></p>
    </div>
</section>

<section class="bh-section">
    <div class="bh-container bh-radio-page">
        <div class="bh-card bh-card--radio" x-data="bhRadioPage('<?php echo esc_attr($s['radio_url']); ?>', '<?php echo esc_attr($s['radio_title_fr']); ?>', '<?php echo esc_attr($s['radio_title_ar']); ?>', '<?php echo esc_attr($s['radio_cover_url']); ?>')">
            <div class="bh-radio-page__cover">
                <template x-if="cover">
                    <img :src="cover" alt="" width="160" height="160">
                </template>
                <span x-show="!cover" class="bh-radio-page__disc" aria-hidden="true">📻</span>
            </div>
            <div class="bh-radio-page__body">
                <strong data-radio-title x-text="title()"></strong>
                <p class="bh-radio-page__status" data-radio-status x-text="status()"></p>
                <div class="bh-radio-page__controls">
                    <button class="bh-btn bh-btn-primary bh-btn--lg" type="button" @click="toggle()" :disabled="!url">
                        <span x-show="!playing"><?php echo esc_html(bh_l('Écouter la radio', 'استمع للإذاعة')); ?></span>
                        <span x-show="playing"><?php echo esc_html(bh_l('Couper', 'إيقاف')); ?></span>
                    </button>
                    <label class="bh-radio-page__vol">
                        <span aria-hidden="true">🔊</span>
                        <input type="range" min="0" max="1" step="0.05" x-model="vol" @input="setVol()" aria-label="<?php echo esc_attr(bh_l('Volume', 'مستوى الصوت')); ?>">
                    </label>
                </div>
                <p class="bh-radio-page__hint" x-show="!url"><?php echo esc_html(bh_l('Le flux radio sera bientôt disponible.', 'سيكون البث الإذاعي متاحًا قريبًا.')); ?></p>
            </div>
        </div>

        <?php
        $nowNext = function_exists('bh_now_next') ? bh_now_next() : ['slots' => []];
        $slots   = is_array($nowNext) ? (array) ($nowNext['slots'] ?? []) : [];
        $slotNow = null;
        $slotNext = null;
        foreach ($slots as $s) {
            if (!empty($s['now']) && $slotNow === null) {
                $slotNow = $s;
            } elseif ($slotNow !== null && $slotNext === null) {
                $slotNext = $s;
            }
        }
        ?>
        <?php if ($slotNow) : ?>
            <div class="bh-card bh-card--now">
                <p class="bh-now-banner"><?php echo esc_html(bh_l('À l\'antenne', 'على الموجة')); ?></p>
                <strong class="bh-now-banner__title"><?php echo esc_html($slotNow['title'] ?: ($slotNow['programme'] ? (string) get_the_title((int) $slotNow['programme']) : '')); ?></strong>
                <span class="bh-time-range"><?php echo esc_html(($slotNow['start'] ?? '') . ' – ' . ($slotNow['end'] ?? '')); ?></span>
                <?php if ($slotNext) : ?>
                    <p class="bh-note"><?php echo esc_html(bh_l('À suivre', 'يتبع')); ?> :
                        <strong><?php echo esc_html($slotNext['title'] ?: ($slotNext['programme'] ? (string) get_the_title((int) $slotNext['programme']) : '')); ?></strong>
                        <?php echo esc_html($slotNext['start'] ?? ''); ?>
                    </p>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <div class="bh-radio-page__about">
            <?php echo bh_post_content(get_the_ID()); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
        </div>

        <?php $history = function_exists('bh_radio_history') ? bh_radio_history() : []; ?>
        <?php if ($history) : ?>
            <div class="bh-card bh-card--history">
                <h2 class="bh-card__title"><?php echo esc_html(bh_l('Derniers programmes diffusés', 'آخر البرامج المذاعة')); ?></h2>
                <ul class="bh-history">
                    <?php foreach ($history as $h) : ?>
                        <li>
                            <strong><?php echo esc_html($h['title']); ?></strong>
                            <?php if (!empty($h['time'])) : ?><span class="bh-time-range"><?php echo esc_html($h['time']); ?></span><?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
    </div>
</section>

<section class="bh-section bh-section--alt">
    <div class="bh-container">
        <div class="bh-section__head">
            <h2><?php echo esc_html(bh_l('Replays radio', 'أرشيف الإذاعة')); ?></h2>
            <a class="bh-link" href="<?php echo esc_url(bh_nav_url('podcast')); ?>"><?php echo esc_html(bh_l('Tous les replays audio →', 'كل الحلقات الصوتية →')); ?></a>
        </div>
        <?php
        // Audio only: episodes whose source is an audio file, or that have an audio URL.
        $audioEp = new WP_Query([
            'post_type' => 'episode', 'posts_per_page' => 8, 'no_found_rows' => true,
            'meta_query' => [
                'relation' => 'OR',
                ['key' => '_bh_source_type', 'value' => 'audio'],
                ['key' => '_bh_audio_url', 'value' => '', 'compare' => '!='],
            ],
        ]);
        if ($audioEp->have_posts()) {
            echo '<div class="bh-card bh-audio-list">';
            foreach ($audioEp->posts as $e) {
                get_template_part('template-parts/audio-row', null, ['post' => $e]);
            }
            echo '</div>';
        } else {
            echo '<p class="bh-empty">' . esc_html(bh_l('Les replays audio apparaîtront ici.', 'ستظهر الحلقات الصوتية هنا.')) . '</p>';
        }
        wp_reset_postdata();
        ?>
    </div>
</section>

<?php get_footer(); ?>
