<?php
/**
 * Template Name: Calculateur Zakat
 */
defined('ABSPATH') || exit;
get_header();
$zakat = function_exists('bh_zakat_reference') ? bh_zakat_reference() : ['rate' => 2.5, 'nisab_amount' => 0, 'currency' => 'FCFA'];
?>
<div class="bh-container bh-page" x-data="bhZakat(<?php echo wp_json_encode([
    'rate'       => (float) $zakat['rate'],
    'nisab'      => (float) $zakat['nisab_amount'],
    'currency'   => (string) $zakat['currency'],
    'nisabNote'  => function_exists('bh_l') ? bh_l(
        '' !== (string) $zakat['nisab_fr'] ? $zakat['nisab_fr'] : $zakat['nisab_note'],
        '' !== (string) $zakat['nisab_ar'] ? $zakat['nisab_ar'] : 'nisab = قيمة 85 غ من الذهب.'
    ) : '',
]); ?>)">
    <nav class="bh-breadcrumb" aria-label="Fil d’Ariane">
        <a href="<?php echo esc_url(bh_nav_url('direct')); ?>"><?php echo esc_html(bh_l('Accueil', 'الرئيسية')); ?></a>
        <span aria-hidden="true">/</span>
        <a href="<?php echo esc_url(bh_nav_url('outils')); ?>"><?php echo esc_html(bh_l('Outils', 'الأدوات')); ?></a>
        <span aria-hidden="true">/</span> <span><?php echo esc_html(bh_l('Zakat', 'الزكاة')); ?></span>
    </nav>
    <header class="bh-page__head">
        <h1><?php echo esc_html(bh_post_title(get_queried_object_id())); ?></h1>
        <p class="bh-page__lead">
            <?php echo esc_html(bh_l('Le nisab : ', 'النصاب: ')); ?>
            <span x-text="nisabText()"></span>
            <?php echo esc_html(bh_l(" — au-dessus de ce seuil, la zakat est de " . (string) $zakat['rate'] . " %.", ' — فوق هذا الحد تكون الزكاة ' . (string) $zakat['rate'] . '%.')); ?>
        </p>
    </header>
    <div class="bh-card bh-zakat">
        <div class="bh-zakat__inputs">
            <label><?php echo esc_html(bh_l('Argent liquide (comptes, cash)', 'النقد (الحسابات، الكاش)')); ?>
                <input type="number" min="0" step="1" x-model.number="cash">
            </label>
            <label><?php echo esc_html(bh_l('Or & bijoux (valeur)', 'الذهب والمجوهرات (القيمة)')); ?>
                <input type="number" min="0" step="1" x-model.number="gold">
            </label>
            <label><?php echo esc_html(bh_l('Biens commerciaux (valeur)', 'البضاعة التجارية (القيمة)')); ?>
                <input type="number" min="0" step="1" x-model.number="business">
            </label>
            <label><?php echo esc_html(bh_l('Dettes dues (à déduire)', 'الديون (تُخصم)')); ?>
                <input type="number" min="0" step="1" x-model.number="debts">
            </label>
        </div>
        <div class="bh-zakat__result">
            <template x-if="total() < nisab && nisab > 0">
                <p class="bh-zakat__below">
                    <?php echo esc_html(bh_l('Vos biens sont sous le nisab : pas de zakat due cette année.', 'أملاككم دون النصاب: لا زكاة هذه السنة.')); ?>
                </p>
            </template>
            <template x-if="total() >= nisab || nisab <= 0">
                <div>
                    <strong><?php echo esc_html(bh_l('Zakat due :', 'الزكاة المستحقة:')); ?></strong>
                    <span class="bh-zakat__amount" x-text="zakatDue()"></span>
                    <small><?php echo esc_html(bh_l('(2,5 % des biens assujettis après déduction des dettes)', '(2,5% من الصافي بعد خصم الديون)')); ?></small>
                </div>
            </template>
        </div>
    </div>
    <p class="bh-note"><?php echo esc_html(bh_l('Outil indicatif — pour un cas précis, consultez un savant de confiance.', 'أداة استرشادية — للحالات الخاصة راجع عالماً موثوقاً.')); ?></p>
</div>
<?php get_footer();
