<?php
/**
 * Template Name: Tasbih numérique
 */
defined('ABSPATH') || exit;
get_header();
?>
<div class="bh-container bh-page" x-data="bhTasbih()">
    <nav class="bh-breadcrumb" aria-label="Fil d’Ariane">
        <a href="<?php echo esc_url(bh_nav_url('direct')); ?>"><?php echo esc_html(bh_l('Accueil', 'الرئيسية')); ?></a>
        <span aria-hidden="true">/</span>
        <a href="<?php echo esc_url(bh_nav_url('outils')); ?>"><?php echo esc_html(bh_l('Outils', 'الأدوات')); ?></a>
        <span aria-hidden="true">/</span> <span><?php echo esc_html(bh_l('Tasbih', 'السبحة')); ?></span>
    </nav>
    <header class="bh-page__head">
        <h1><?php echo esc_html(bh_post_title(get_queried_object_id())); ?></h1>
        <p class="bh-page__lead"><?php echo esc_html(bh_l('Compteur de dhikr — votre total est sauvegardé sur cet appareil.', 'عداد الذكر — مجموعك محفوظ على هذا الجهاز.')); ?></p>
    </header>

    <div class="bh-tasbih">
        <div class="bh-tasbih__top">
            <select x-model="phrase" @change="onPhraseChange()">
                <option value="subhanallah"><?php echo esc_html(bh_l('SubhanAllah — Gloria à Allah', 'سبحان الله')); ?></option>
                <option value="alhamdulillah"><?php echo esc_html(bh_l('Alhamdulillah — Louange à Allah', 'الحمد لله')); ?></option>
                <option value="allahuakbar"><?php echo esc_html(bh_l('Allahu Akbar — Allah est le Plus Grand', 'الله أكبر')); ?></option>
                <option value="la-ilaha-illa-llah"><?php echo esc_html(bh_l('Lâ ilâha illa-llah — Nul dieu sauf Allah', 'لا إله إلا الله')); ?></option>
                <option value="astaghfirullah"><?php echo esc_html(bh_l('Astaghfiru-llah — Je cherche pardon auprès d’Allah', 'أستغفر الله')); ?></option>
            </select>
            <label><?php echo esc_html(bh_l('Objectif', 'الهدف')); ?>
                <select x-model.number="goal">
                    <option value="33">33</option>
                    <option value="100">100</option>
                    <option value="500">500</option>
                    <option value="1000">1000</option>
                </select>
            </label>
        </div>
        <p class="bh-tasbih__phrase" dir="rtl" x-text="phraseAr()" style="font-size:28px;font-family:var(--bh-font-quran);text-align:center"></p>
        <button type="button" class="bh-tasbih__btn" @click="tick()" aria-label="<?php echo esc_attr(bh_l('Compter', 'عد')); ?>">
            <span x-text="count"></span>
        </button>
        <div class="bh-tasbih__meta">
            <span x-text="roundsText()"></span>
            <button type="button" class="bh-btn bh-btn--ghost" @click="reset()"><?php echo esc_html(bh_l('Réinitialiser', 'إعادة تعيين')); ?></button>
        </div>
    </div>
</div>
<?php get_footer();
