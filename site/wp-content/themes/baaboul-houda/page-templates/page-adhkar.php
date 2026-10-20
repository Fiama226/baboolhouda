<?php
/**
 * Template Name: Invocations (Adhkar)
 *
 * Hisn al-Muslim — sections of invocations with the Arabic text and the
 * number of repetitions. Search is client-side (Alpine, inline expressions).
 */
defined('ABSPATH') || exit;
get_header();
$sections = function_exists('bh_adhkar') ? bh_adhkar() : [];
$meta     = function_exists('bh_adhkar_meta') ? bh_adhkar_meta() : [];
$total    = 0;
foreach ($sections as $sec) {
    $total += count($sec['items'] ?? []);
}
// Normaliser shared by every filter expression: lower-case, no tashkeel / accents.
$norm = "(s)=>(s||'').normalize('NFD').replace(/[\\u064B-\\u065F\\u0670\\u06D6-\\u06ED\\u0640\\u0300-\\u036F]/g,'').toLowerCase()";
$xdata = "{ q: '', none: false, n: " . $norm . ",
    hit(el, k) { const t = this.n(this.q.trim()); return !t || this.n(el.dataset[k]).includes(t); } }";
?>
<div class="bh-container bh-page" x-data="<?php echo esc_attr($xdata); ?>"
     x-effect="q; $nextTick(() => { none = !!q.trim() && !$el.querySelector('.bh-adhkar__sec:not([style*=&quot;display: none&quot;])'); })">
    <nav class="bh-breadcrumb" aria-label="Fil d’Ariane">
        <a href="<?php echo esc_url(bh_nav_url('direct')); ?>"><?php echo esc_html(bh_l('Accueil', 'الرئيسية')); ?></a>
        <span aria-hidden="true">/</span>
        <a href="<?php echo esc_url(bh_nav_url('outils')); ?>"><?php echo esc_html(bh_l('Outils', 'الأدوات')); ?></a>
        <span aria-hidden="true">/</span> <span><?php echo esc_html(bh_l('Invocations (Adhkar)', 'الأذكار')); ?></span>
    </nav>
    <header class="bh-page__head">
        <h1><?php echo esc_html(bh_post_title(get_queried_object_id())); ?></h1>
        <p class="bh-page__lead"><?php echo esc_html(bh_l(
            'Invocations du Château du Musulman (Hisn al-Muslim) : texte arabe et nombre de répétitions.',
            'أذكار من كتاب حصن المسلم: النص العربي وعدد التكرار.'
        )); ?></p>
        <label class="screen-reader-text" for="bh-adhkar-q" style="position:absolute;width:1px;height:1px;overflow:hidden;clip:rect(0 0 0 0);white-space:nowrap"><?php echo esc_html(bh_l('Rechercher une invocation', 'ابحث عن ذكر')); ?></label>
        <input id="bh-adhkar-q" type="search" class="bh-input" x-model.debounce.200ms="q" autocomplete="off"
               placeholder="<?php echo esc_attr(bh_l('Rechercher (matin, sommeil, voyage… ou en arabe)', 'ابحث (الصباح، النوم، السفر…)')); ?>">
        <p class="bh-note" style="margin-top:8px"><?php echo esc_html(sprintf(
            bh_l('%1$d sections · %2$d invocations', '%1$d باباً · %2$d ذكراً'),
            count($sections),
            $total
        )); ?></p>
    </header>

    <?php if (!$sections) : ?>
        <p class="bh-note"><?php echo esc_html(bh_l('Les invocations ne sont pas disponibles pour le moment.', 'الأذكار غير متاحة حالياً.')); ?></p>
    <?php else : ?>
        <div class="bh-adhkar" style="display:grid;gap:12px">
            <?php foreach ($sections as $i => $sec) :
                $title_fr = $sec['title_fr'] ?? '';
                $title_ar = $sec['title_ar'] ?? '';
                $items    = $sec['items'] ?? [];
                $blob     = $title_fr . ' ' . $title_ar . ' ' . implode(' ', array_column($items, 'text'));
                $titles   = $title_fr . ' ' . $title_ar;
                ?>
                <details class="bh-card bh-adhkar__sec" id="adhkar-<?php echo (int) ($sec['id'] ?? $i); ?>"
                         data-s="<?php echo esc_attr($blob); ?>" data-t="<?php echo esc_attr($titles); ?>"
                         x-show="hit($el, 's')" :open="q.trim() !== ''">
                    <summary style="cursor:pointer;display:flex;justify-content:space-between;gap:12px;align-items:center;font-weight:700">
                        <span><?php echo esc_html(bh_l($title_fr ?: $title_ar, $title_ar ?: $title_fr)); ?>
                            <?php if (!bh_is_ar() && $title_ar && $title_ar !== $title_fr) : ?>
                                <small dir="rtl" lang="ar" style="display:block;font-family:var(--bh-font-ar);font-weight:400;color:var(--bh-ink-soft)"><?php echo esc_html($title_ar); ?></small>
                            <?php endif; ?>
                        </span>
                        <small style="color:var(--bh-ink-soft);white-space:nowrap"><?php echo (int) count($items); ?></small>
                    </summary>
                    <div style="display:grid;gap:14px;margin-top:14px">
                        <?php foreach ($items as $it) :
                            $text  = (string) ($it['text'] ?? '');
                            $count = (int) ($it['count'] ?? 1);
                            ?>
                            <div class="bh-adhkar__item" data-s="<?php echo esc_attr($text); ?>"
                                 x-data="{ ok: false }"
                                 x-show="!q.trim() || hit($el, 's') || hit($el.closest('.bh-adhkar__sec'), 't')"
                                 style="border-top:1px solid var(--bh-line);padding-top:12px">
                                <p class="bh-ayah-ar" dir="rtl" lang="ar" x-ref="t"><?php echo esc_html($text); ?></p>
                                <div style="display:flex;justify-content:space-between;align-items:center;gap:10px;flex-wrap:wrap">
                                    <span class="bh-chip" style="cursor:default"><?php echo esc_html(
                                        $count > 1
                                            ? sprintf(bh_l('À répéter %d fois', 'يُكرَّر %d مرة'), $count)
                                            : bh_l('1 fois', 'مرة واحدة')
                                    ); ?></span>
                                    <button type="button" class="bh-btn bh-btn-ghost" style="padding:6px 14px;font-size:13px"
                                            @click="navigator.clipboard && navigator.clipboard.writeText($refs.t.textContent.trim()).then(() => { ok = true; setTimeout(() => ok = false, 1500); })"
                                            data-label-copy="<?php echo esc_attr(bh_l('Copier', 'نسخ')); ?>"
                                            data-label-ok="<?php echo esc_attr(bh_l('Copié', 'تم النسخ')); ?>"
                                            x-text="ok ? $el.dataset.labelOk : $el.dataset.labelCopy"><?php echo esc_html(bh_l('Copier', 'نسخ')); ?></button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </details>
            <?php endforeach; ?>
        </div>
        <p class="bh-note" x-show="none" x-cloak><?php echo esc_html(bh_l('Aucun résultat pour cette recherche.', 'لا توجد نتائج لهذا البحث.')); ?></p>
    <?php endif; ?>

    <footer class="bh-note" style="margin-top:28px">
        <?php if (!empty($meta['book'])) : ?>
            <?php echo esc_html(bh_l('Source :', 'المصدر:')); ?> <?php echo esc_html($meta['book']); ?>.
        <?php endif; ?>
        <?php if (!empty($meta['source'])) : ?>
            <?php echo esc_html(bh_l('Données :', 'البيانات:')); ?> <?php echo esc_html($meta['source']); ?>.
        <?php endif; ?>
        <?php echo esc_html(bh_l($meta['note_fr'] ?? '', $meta['note_ar'] ?? '')); ?>
    </footer>
</div>
<?php get_footer();
