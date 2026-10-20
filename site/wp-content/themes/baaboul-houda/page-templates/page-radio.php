<?php
/** Template Name: Radio */
defined('ABSPATH') || exit;
get_header();
$s = bh_settings();
$radioEnabled = !empty($s['radio_enabled']) && '' !== trim((string) $s['radio_url']);
?>

<section class="bh-page-hero">
    <div class="bh-container">
        <h1><?php echo esc_html(bh_post_title(get_the_ID())); ?></h1>
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

        <div class="bh-radio-page__about">
            <?php echo bh_post_content(get_the_ID()); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
        </div>
    </div>
</section>

<section class="bh-section bh-section--alt">
    <div class="bh-container">
        <div class="bh-section__head">
            <h2><?php echo esc_html(bh_l('Replays radio', 'أرشيف الإذاعة')); ?></h2>
            <a class="bh-link" href="<?php echo esc_url(bh_nav_url('emissions')); ?>"><?php echo esc_html(bh_l('Tout le replay →', 'كل الأرشيف →')); ?></a>
        </div>
        <?php
        $audioEp = new WP_Query([
            'post_type' => 'episode', 'posts_per_page' => 6, 'no_found_rows' => true,
            'meta_query' => [['key' => '_bh_source_type', 'value' => ['audio', 'youtube', 'facebook', 'file'], 'compare' => 'IN']],
        ]);
        if ($audioEp->have_posts()) {
            echo '<div class="bh-grid bh-grid--3">';
            foreach ($audioEp->posts as $e) {
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

<?php get_footer(); ?>
