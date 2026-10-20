<?php
/**
 * Template Name: Dates & calendrier
 *
 * Hijri ↔ Gregorian converter, fasting calendar, next-holiday countdown,
 * month view with events.
 */
defined('ABSPATH') || exit;
get_header();
$s = function_exists('bh_settings') ? bh_settings() : [];
?>
<div class="bh-container bh-page" x-data="bhDates(<?php echo wp_json_encode([
    'city'   => (string) ($s['prayer_city'] ?? 'Ouagadougou'),
]); ?>)">
    <nav class="bh-breadcrumb" aria-label="Fil d’Ariane">
        <a href="<?php echo esc_url(bh_nav_url('direct')); ?>"><?php echo esc_html(bh_l('Accueil', 'الرئيسية')); ?></a>
        <span aria-hidden="true">/</span>
        <a href="<?php echo esc_url(bh_nav_url('outils')); ?>"><?php echo esc_html(bh_l('Outils', 'الأدوات')); ?></a>
        <span aria-hidden="true">/</span> <span><?php echo esc_html(bh_l('Dates', 'التواريخ')); ?></span>
    </nav>
    <header class="bh-page__head">
        <h1><?php echo esc_html(bh_post_title(get_queried_object_id())); ?></h1>
        <p class="bh-page__lead"><?php echo esc_html(bh_l('Calendrier islamique : conversion, jeûnes et fêtes à venir.', 'التقويم الإسلامي: تحويل التواريخ والصيام والأعياد.')); ?></p>
    </header>

    <div class="bh-grid bh-grid--2">
        <div class="bh-card">
            <h2 class="bh-card__title"><?php echo esc_html(bh_l('Aujourd’hui', 'اليوم')); ?></h2>
            <p class="bh-dates__today">
                <span x-text="todayGreg()"></span>
                <span class="bh-dates__hijri" x-text="todayHijri()"></span>
            </p>
        </div>
        <div class="bh-card">
            <h2 class="bh-card__title"><?php echo esc_html(bh_l('Prochaine fête / événement', 'أقرب عيد/حدث')); ?></h2>
            <p class="bh-dates__next">
                <strong x-text="nextEvent().name"></strong>
                <span x-text="nextEvent().date"></span>
                <small><?php echo esc_html(bh_l('dans', 'بعد')); ?> <strong x-text="nextEvent().days"></strong> <?php echo esc_html(bh_l('jour(s)', 'يوم')); ?></small>
            </p>
        </div>
    </div>

    <div class="bh-card bh-dates-convert">
        <h2 class="bh-card__title"><?php echo esc_html(bh_l('Convertisseur de dates', 'محول التواريخ')); ?></h2>
        <div class="bh-dates__form">
            <label><?php echo esc_html(bh_l('Date grégorienne', 'التاريخ الميلادي')); ?>
                <input type="date" x-model="gregDate" @change="convertGregToHijri()">
            </label>
            <label><?php echo esc_html(bh_l('Date hégirienne', 'التاريخ الهجري')); ?>
                <input type="number" min="1" max="12" x-model.number="hijriMonth" @change="convertHijriToGreg()">
                <select x-model="hijriMonthName" hidden></select>
                <input type="number" min="1" max="30" x-model.number="hijriDay" @change="convertHijriToGreg()">
                <input type="number" min="1300" max="1600" x-model.number="hijriYear" @change="convertHijriToGreg()">
            </label>
            <p class="bh-dates__result" x-show="resultText()" x-text="resultText()"></p>
        </div>
    </div>

    <div class="bh-card">
        <h2 class="bh-card__title"><?php echo esc_html(bh_l('Jours de jeûne à venir', 'أيام الصيام القادمة')); ?></h2>
        <ul class="bh-fasts">
            <template x-for="f in upcomingFasts()" :key="f.id">
                <li>
                    <span class="bh-fasts__date" x-text="f.date"></span>
                    <strong x-text="f.name"></strong>
                    <small x-text="f.daysLeft"></small>
                </li>
            </template>
        </ul>
    </div>
</div>
<?php get_footer();
