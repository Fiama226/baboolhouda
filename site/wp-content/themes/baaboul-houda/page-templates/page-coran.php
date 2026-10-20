<?php
/** Template Name: Coran (index des 114 sourates + recherche) */
defined('ABSPATH') || exit;
get_header();
$quranMeta = bh_quran_meta();
$quranData = bh_quran_data();
?>

<section class="bh-page-hero bh-page-hero--quran">
    <div class="bh-container">
        <h1><?php echo esc_html(bh_post_title(get_queried_object_id())); ?></h1>
        <p class="bh-page-hero__sub" dir="rtl"><?php echo esc_html($quranMeta['note_ar'] ?? ''); ?></p>
        <p class="bh-page-hero__sub"><?php echo esc_html($quranMeta['note_fr'] ?? ''); ?></p>
    </div>
</section>

<section class="bh-section">
    <div class="bh-container" x-data="bhQuranIndex()" x-init="initAll()">
        <div class="bh-quran-extras">
            <a class="bh-btn" x-show="resumeUrl()" :href="resumeUrl()">
                ⏵ <?php echo esc_html(bh_l('Reprendre ma lecture', 'أكمل قراءتي')); ?>
                <small x-text="resumeLabel()"></small>
            </a>
            <a class="bh-btn bh-btn--ghost" href="<?php echo esc_url(bh_nav_url('dates')); ?>">
                📅 <?php echo esc_html(bh_l('Calendrier & jeûnes', 'التقويم والصيام')); ?>
            </a>
        </div>
        <div class="bh-quran-tools">
            <input type="search" x-model.debounce.200ms="q" class="bh-input bh-quran-search"
                   placeholder="<?php echo esc_attr(bh_l('Rechercher une sourate (nom arabe ou français)…', 'ابحث عن سورة (بالاسم العربي أو الفرنسي)…')); ?>"
                   aria-label="<?php echo esc_attr(bh_l('Rechercher une sourate', 'ابحث عن سورة')); ?>">
            <label class="bh-reader-toggle">
                <input type="checkbox" x-model="fullText">
                <span><?php echo esc_html(bh_l('Recherche dans les versets', 'البحث في الآيات')); ?></span>
            </label>
            <span class="bh-quran-count" x-text="count()"></span>
        </div>
        <div class="bh-quran-search-results" x-show="fullText && hits.length" x-cloak>
            <template x-for="r in hits.slice(0, 12)" :key="r.ref">
                <a class="bh-quran-hit" :href="r.url">
                    <strong x-text="r.ref"></strong>
                    <span x-text="r.text"></span>
                </a>
            </template>
            <p class="bh-muted" x-text="resultsHint()"></p>
        </div>
        <div class="bh-quran-tabs" role="tablist" style="display:flex;gap:8px;margin:0 0 14px">
            <button type="button" role="tab" class="bh-btn" :class="{ 'bh-btn--ghost': view !== 'surahs' }" :aria-selected="view === 'surahs'" @click="view = 'surahs'"><?php echo esc_html(bh_l('114 sourates', '١١٤ سورة')); ?></button>
            <button type="button" role="tab" class="bh-btn" :class="{ 'bh-btn--ghost': view !== 'juz' }" :aria-selected="view === 'juz'" @click="view = 'juz'"><?php echo esc_html(bh_l('30 juz’', '٣٠ جزءًا')); ?></button>
        </div>
        <div class="bh-quran-grid" x-show="view === 'juz'" x-cloak>
            <?php foreach (bh_juz_list() as $ju) : ?>
                <a class="bh-quran-card" href="<?php echo esc_url(home_url('/coran/' . $ju['surah'] . '/#a-' . $ju['ayah'])); ?>">
                    <span class="bh-quran-card__num"><?php echo (int) $ju['n']; ?></span>
                    <span class="bh-quran-card__names">
                        <strong><?php echo esc_html(bh_l('Juz’ ' . $ju['n'], 'الجزء ' . $ju['n'])); ?></strong>
                        <em><?php echo esc_html(bh_l('Dès la sourate ', 'من سورة ') . bh_l($quranData['surahs'][$ju['surah'] - 1]['name_fr'], $quranData['surahs'][$ju['surah'] - 1]['name_ar']) . ' : ' . $ju['ayah']); ?></em>
                    </span>
                </a>
            <?php endforeach; ?>
        </div>
        <div class="bh-quran-grid" x-show="view === 'surahs'">
            <?php foreach ($quranData['surahs'] as $su) : ?>
                <a class="bh-quran-card" href="<?php echo esc_url(home_url('/coran/' . $su['n'] . '/')); ?>"
                   x-show="!q.trim() || matchCard($el.dataset.name)"
                   data-n="<?php echo (int) $su['n']; ?>"
                   data-name="<?php echo esc_attr(mb_strtolower($su['name_fr'] . ' ' . $su['name_ar'])); ?>">
                    <span class="bh-quran-card__num"><?php echo (int) $su['n']; ?></span>
                    <span class="bh-quran-card__names">
                        <strong dir="rtl" class="bh-font-quran"><?php echo esc_html($su['name_ar']); ?></strong>
                        <em><?php echo esc_html($su['name_fr']); ?></em>
                    </span>
                    <span class="bh-quran-card__meta"><?php echo (int) $su['ayah_count']; ?> <?php echo esc_html(bh_l('versets', 'آيات')); ?> · <?php echo esc_html(bh_l($su['type'], $su['type'] === 'mecquoise' ? 'مكية' : 'مدنية')); ?></span>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<?php get_footer(); ?>
