<?php
/**
 * Template Name: Les 99 noms d'Allah
 *
 * Asma ul-Husna — Arabic name, transliteration, French meaning and the
 * commentary bundled in data/names99.json. Search is client-side (Alpine).
 */
defined('ABSPATH') || exit;
get_header();
$data  = function_exists('bh_names99') ? bh_names99() : [];
$names = is_array($data) ? ($data['names'] ?? []) : [];
$meta  = function_exists('bh_names99_meta') ? bh_names99_meta() : [];
$norm  = "(s)=>(s||'').normalize('NFD').replace(/[\\u064B-\\u065F\\u0670\\u06D6-\\u06ED\\u0640\\u0300-\\u036F]/g,'').toLowerCase()";
$xdata = "{ q: '', none: false, n: " . $norm . ",
    hit(el) { const t = this.n(this.q.trim()); return !t || this.n(el.dataset.s).includes(t); } }";
?>
<div class="bh-container bh-page" x-data="<?php echo esc_attr($xdata); ?>"
     x-effect="q; $nextTick(() => { none = !!q.trim() && !$refs.grid.querySelector('.bh-names__item:not([style*=&quot;display: none&quot;])'); })">
    <nav class="bh-breadcrumb" aria-label="Fil d’Ariane">
        <a href="<?php echo esc_url(bh_nav_url('direct')); ?>"><?php echo esc_html(bh_l('Accueil', 'الرئيسية')); ?></a>
        <span aria-hidden="true">/</span>
        <a href="<?php echo esc_url(bh_nav_url('outils')); ?>"><?php echo esc_html(bh_l('Outils', 'الأدوات')); ?></a>
        <span aria-hidden="true">/</span> <span><?php echo esc_html(bh_l('Les 99 noms d’Allah', 'أسماء الله الحسنى')); ?></span>
    </nav>
    <header class="bh-page__head">
        <h1><?php echo esc_html(bh_post_title(get_queried_object_id())); ?></h1>
        <p class="bh-page__lead"><?php echo esc_html(bh_l(
            'Les plus beaux noms d’Allah (Asma ul-Husna) : nom arabe, translittération et signification.',
            'أسماء الله الحسنى: الاسم بالعربية، النطق بالحروف اللاتينية ومعناه.'
        )); ?></p>
        <label for="bh-noms-q" style="position:absolute;width:1px;height:1px;overflow:hidden;clip:rect(0 0 0 0);white-space:nowrap"><?php echo esc_html(bh_l('Rechercher un nom', 'ابحث عن اسم')); ?></label>
        <input id="bh-noms-q" type="search" class="bh-input" x-model.debounce.200ms="q" autocomplete="off"
               placeholder="<?php echo esc_attr(bh_l('Rechercher (Rahman, miséricordieux, 12… ou en arabe)', 'ابحث (الرحمن، رحيم، ١٢…)')); ?>">
    </header>

    <?php if (!$names) : ?>
        <p class="bh-note"><?php echo esc_html(bh_l('Les noms ne sont pas disponibles pour le moment.', 'الأسماء غير متاحة حالياً.')); ?></p>
    <?php else : ?>
        <div class="bh-grid bh-grid--3 bh-names" x-ref="grid">
            <?php foreach ($names as $nm) :
                $blob = ($nm['n'] ?? '') . ' ' . ($nm['ar'] ?? '') . ' ' . ($nm['translit'] ?? '') . ' ' . ($nm['fr'] ?? '') . ' ' . ($nm['fr_desc'] ?? '');
                ?>
                <article class="bh-card bh-names__item" data-s="<?php echo esc_attr($blob); ?>" x-show="hit($el)" x-data="{ open: false }">
                    <p style="margin:0;color:var(--bh-ink-soft);font-size:13px">#<?php echo (int) ($nm['n'] ?? 0); ?></p>
                    <h2 class="bh-ayah-ar" dir="rtl" lang="ar" style="text-align:center;font-size:34px;margin:4px 0"><?php echo esc_html($nm['ar'] ?? ''); ?></h2>
                    <p style="text-align:center;margin:0 0 6px"><strong><?php echo esc_html($nm['translit'] ?? ''); ?></strong></p>
                    <?php if (!empty($nm['fr'])) : ?>
                        <p class="bh-names__fr" lang="fr" dir="ltr" style="text-align:center;margin:0"><?php echo esc_html($nm['fr']); ?></p>
                    <?php endif; ?>
                    <?php if (!empty($nm['fr_desc'])) : ?>
                        <div style="margin-top:10px">
                            <p class="bh-note" lang="fr" dir="ltr" style="margin:0;line-height:1.6" :style="open ? '' : 'display:-webkit-box;-webkit-line-clamp:3;-webkit-box-orient:vertical;overflow:hidden'"><?php echo esc_html(trim($nm['fr_desc'])); ?></p>
                            <button type="button" class="bh-btn bh-btn-ghost" style="padding:4px 12px;font-size:12.5px;margin-top:8px"
                                    @click="open = !open" :aria-expanded="open"
                                    data-more="<?php echo esc_attr(bh_l('Lire la suite', 'اقرأ المزيد')); ?>"
                                    data-less="<?php echo esc_attr(bh_l('Réduire', 'إخفاء')); ?>"
                                    x-text="open ? $el.dataset.less : $el.dataset.more"><?php echo esc_html(bh_l('Lire la suite', 'اقرأ المزيد')); ?></button>
                        </div>
                    <?php endif; ?>
                    <?php if (!empty($nm['found'])) : ?>
                        <p class="bh-note" style="margin:8px 0 0;font-size:12.5px"><?php echo esc_html(bh_l('Dans le Coran :', 'في القرآن:')); ?> <bdi><?php echo esc_html($nm['found']); ?></bdi></p>
                    <?php endif; ?>
                </article>
            <?php endforeach; ?>
        </div>
        <p class="bh-note" x-show="none" x-cloak><?php echo esc_html(bh_l('Aucun nom ne correspond à cette recherche.', 'لا يوجد اسم مطابق لهذا البحث.')); ?></p>
    <?php endif; ?>

    <?php if (!empty($meta['source'])) : ?>
        <footer class="bh-note" style="margin-top:28px">
            <?php echo esc_html(bh_l('Source :', 'المصدر:')); ?> <?php echo esc_html($meta['source']); ?>.
            <?php echo esc_html($meta['note'] ?? ''); ?>
        </footer>
    <?php endif; ?>
</div>
<?php get_footer();
