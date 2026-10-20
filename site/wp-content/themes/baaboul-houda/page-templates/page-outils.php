<?php
/**
 * Template Name: Outils & calculs
 *
 * Hub of the Islamic tools: zakat, qibla, dates, tasbih, wudu, adhkar, 99 names, mosques.
 */
defined('ABSPATH') || exit;
get_header();
$tools = [
    ['slug' => 'zakat',   'icon' => '⚖️', 'fr' => 'Calculateur Zakat', 'ar' => 'حاسبة الزكاة', 'desc_fr' => 'Calculez votre zakat (2,5 %) selon votre patrimoine.', 'desc_ar' => 'احسب زكاتك (2,5%) حسب أملاكك.'],
    ['slug' => 'qibla',   'icon' => '🧭', 'fr' => 'Direction de la Qibla', 'ar' => 'اتجاه القبلة', 'desc_fr' => 'Trouvez la direction de la Kaaba (boussole ou carte).', 'desc_ar' => 'اعرف اتجاه الكعبة (بوصلة أو خريطة).'],
    ['slug' => 'dates',   'icon' => '📅', 'fr' => 'Dates & calendrier', 'ar' => 'التواريخ والتقويم', 'desc_fr' => 'Convertisseur hégirien, jours de jeûne, prochaines fêtes.', 'desc_ar' => 'محول التقويم الهجري، أيام الصوم، الأعياد.'],
    ['slug' => 'tasbih',  'icon' => '📿', 'fr' => 'Tasbih numérique', 'ar' => 'سبحة إلكترونية', 'desc_fr' => 'Compteur de dhikr avec objectifs, sauvegardé sur votre appareil.', 'desc_ar' => 'عداد الذكر مع الأهداف، محفوظ على جهازك.'],
    ['slug' => 'wudu',    'icon' => '💧', 'fr' => 'Guide du Wudu', 'ar' => 'دليل الوضوء', 'desc_fr' => 'Les étapes de la petite ablution, pas à pas.', 'desc_ar' => 'خطوات الوضوء خطوة بخطوة.'],
    ['slug' => 'adhkar',  'icon' => '🤲', 'fr' => 'Invocations (Adhkar)', 'ar' => 'الأذكار', 'desc_fr' => 'Hisn al-Muslim : invocations avec texte arabe et répétitions.', 'desc_ar' => 'حصن المسلم: أذكار بالنص العربي وعدد التكرار.'],
    ['slug' => 'noms-allah', 'icon' => '✨', 'fr' => 'Les 99 noms d’Allah', 'ar' => 'أسماء الله الحسنى', 'desc_fr' => 'Les 99 noms : arabe, translittération et signification.', 'desc_ar' => 'أسماء الله الحسنى: بالعربية ومعانيها.'],
    ['slug' => 'mosquees','icon' => '🕌', 'fr' => 'Mosquées', 'ar' => 'الجمع', 'desc_fr' => 'Annuaire des mosquées (adresse, contact, carte).', 'desc_ar' => 'دليل الجمع (العنوان، الاتصال، الخريطة).'],
];
?>
<div class="bh-container bh-page">
    <nav class="bh-breadcrumb" aria-label="Fil d’Ariane">
        <a href="<?php echo esc_url(bh_nav_url('direct')); ?>"><?php echo esc_html(bh_l('Accueil', 'الرئيسية')); ?></a>
        <span aria-hidden="true">/</span> <span><?php echo esc_html(bh_l('Outils & calculs', 'الأدوات والحسابات')); ?></span>
    </nav>
    <header class="bh-page__head">
        <h1><?php echo esc_html(bh_post_title(get_queried_object_id())); ?></h1>
        <p class="bh-page__lead"><?php echo esc_html(bh_l('Des outils simples pour votre pratique quotidienne.', 'أدوات بسيطة لممارستكم اليومية.')); ?></p>
    </header>
    <div class="bh-grid bh-grid--3 bh-tools-grid">
        <?php foreach ($tools as $t) : ?>
            <a class="bh-card bh-card--tool" href="<?php echo esc_url(bh_nav_url($t['slug'])); ?>">
                <span class="bh-card__icon" aria-hidden="true"><?php echo esc_html($t['icon']); ?></span>
                <span class="bh-card__body">
                    <strong><?php echo esc_html(bh_l($t['fr'], $t['ar'])); ?></strong>
                    <small><?php echo esc_html(bh_l($t['desc_fr'], $t['desc_ar'])); ?></small>
                </span>
            </a>
        <?php endforeach; ?>
    </div>
</div>
<?php get_footer();
