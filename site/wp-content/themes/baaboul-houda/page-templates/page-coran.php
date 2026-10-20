<?php
/** Template Name: Coran (index des 114 sourates + recherche) */
defined('ABSPATH') || exit;
get_header();
$quranMeta = bh_quran_meta();
$quranData = bh_quran_data();
?>

<section class="bh-page-hero bh-page-hero--quran">
    <div class="bh-container">
        <h1><?php echo esc_html(bh_post_title(get_the_ID())); ?></h1>
        <p class="bh-page-hero__sub" dir="rtl"><?php echo esc_html($quranMeta['note_ar'] ?? ''); ?></p>
        <p class="bh-page-hero__sub"><?php echo esc_html($quranMeta['note_fr'] ?? ''); ?></p>
    </div>
</section>

<section class="bh-section">
    <div class="bh-container" x-data="bhQuranIndex()">
        <div class="bh-quran-tools">
            <input type="search" x-model.debounce.200ms="q" @input="match()" class="bh-input bh-quran-search"
                   placeholder="<?php echo esc_attr(bh_l('Rechercher une sourate (nom arabe ou français)…', 'ابحث عن سورة (بالاسم العربي أو الفرنسي)…')); ?>"
                   aria-label="<?php echo esc_attr(bh_l('Rechercher une sourate', 'ابحث عن سورة')); ?>">
            <span class="bh-quran-count" x-text="count()"></span>
        </div>
        <div class="bh-quran-grid">
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
