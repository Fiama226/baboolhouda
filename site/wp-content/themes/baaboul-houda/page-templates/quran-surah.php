<?php
/**
 * Quran surah reader — served via /coran/{n}/ (plugin rewrite rule).
 * Renders server-side (SEO + shareable URLs); FR translation & audio are
 * lazy-loaded client-side (alquran.cloud, cached in localStorage).
 */
defined('ABSPATH') || exit;

$surahNum = (int) get_query_var('bh_surah');
$surah    = function_exists('bh_quran_surah') ? bh_quran_surah($surahNum) : null;
if (!$surah) {
    wp_safe_redirect(home_url('/coran/'));
    exit;
}
$prevSurah = $surahNum > 1 ? $surahNum - 1 : 114;
$nextSurah = $surahNum < 114 ? $surahNum + 1 : 1;

get_header();
?>

<section class="bh-section bh-quran-reader-section">
    <div class="bh-container bh-container--narrow">
        <nav class="bh-quran-reader__nav" aria-label="<?php echo esc_attr(bh_l('Navigation entre les sourates', 'التنقل بين السور')); ?>">
            <a class="bh-btn bh-btn-ghost" href="<?php echo esc_url(home_url('/coran/' . $prevSurah . '/')); ?>">← <?php echo esc_html(bh_l('Sourate précédente', 'السورة السابقة')); ?></a>
            <a class="bh-btn bh-btn-ghost" href="<?php echo esc_url(home_url('/coran/')); ?>"><?php echo esc_html(bh_l('Toutes les sourates', 'كل السور')); ?></a>
            <a class="bh-btn bh-btn-ghost" href="<?php echo esc_url(home_url('/coran/' . $nextSurah . '/')); ?>"><?php echo esc_html(bh_l('Sourate suivante', 'السورة التالية')); ?> →</a>
        </nav>

        <div class="bh-quran-reader" x-data="bhReader(<?php echo (int) $su['n']; ?>)" x-init="init()">
            <header class="bh-quran-reader__head">
                <div class="bh-quran-reader__title">
                    <h1 dir="rtl" class="bh-font-quran"><?php echo esc_html($su['name_ar']); ?></h1>
                    <p>
                        <strong><?php echo esc_html($su['name_fr']); ?></strong> — <?php echo esc_html($su['meaning_fr']); ?>
                        · <?php echo (int) $su['ayah_count']; ?> <?php echo esc_html(bh_l('versets', 'آيات')); ?>
                        · <?php echo esc_html(bh_l($su['type'], $su['type'] === 'mecquoise' ? 'مكية' : 'مدنية')); ?>
                    </p>
                </div>
                <div class="bh-quran-reader__tools">
                    <label class="bh-reader-reciter">
                        <span><?php echo esc_html(bh_l('Récitateur', 'القارئ')); ?></span>
                        <select x-model="reciter" class="bh-select" aria-label="<?php echo esc_attr(bh_l('Récitateur', 'القارئ')); ?>">
                            <option value="ar.alafasy">Mishary Alafasy</option>
                            <option value="ar.minshawi">Minshawi</option>
                            <option value="ar.abdulbasitmurattal">Abdul Basit (Murattal)</option>
                        </select>
                    </label>
                    <label class="bh-reader-toggle">
                        <input type="checkbox" x-model="showFr">
                        <span><?php echo esc_html(bh_l('Traduction française', 'الترجمة الفرنسية')); ?></span>
                    </label>
                    <button class="bh-btn bh-btn-primary" type="button" @click="toggleAll()">
                        <span x-show="!allPlaying">▶ <?php echo esc_html(bh_l('Écouter la sourate', 'استمع للسورة')); ?></span>
                        <span x-show="allPlaying">⏸ <?php echo esc_html(bh_l('Arrêter', 'إيقاف')); ?></span>
                    </button>
                </div>
            </header>

            <div class="bh-quran-reader__body" dir="rtl" lang="ar">
                <p class="bh-bismillah bh-font-quran" x-show="surahN !== 1 && surahN !== 9">بِسْمِ اللَّهِ الرَّحْمَٰنِ الرَّحِيمِ</p>
                <?php foreach ($su['ayahs'] as $i => $text) : ?>
                    <p class="bh-ayah" data-ayah="<?php echo (int) $i + 1; ?>" data-global="<?php echo (int) $su['start_global'] + $i; ?>">
                        <span class="bh-ayah__ar bh-font-quran"><?php echo $text; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- verified Uthmani text ?>
                            <a class="bh-ayah__num" href="#a-<?php echo (int) $i + 1; ?>" id="a-<?php echo (int) $i + 1; ?>"><?php echo (int) $i + 1; ?></a>
                        </span>
                        <span class="bh-ayah__fr" x-show="showFr" x-text="frText(<?php echo (int) $i + 1; ?>)" dir="ltr" lang="fr"></span>
                    </p>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</section>

<?php get_footer(); ?>
