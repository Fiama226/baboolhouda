<?php
/**
 * Template Name: Direction de la Qibla
 */
defined('ABSPATH') || exit;
get_header();
$s      = function_exists('bh_settings') ? bh_settings() : [];
$city   = (string) ($s['prayer_city'] ?? 'Ouagadougou');
$bearing = function_exists('bh_qibla_for_city') ? bh_qibla_for_city($city) : null;
$cities = function_exists('bh_known_cities') ? array_keys(bh_known_cities()) : [$city];
?>
<div class="bh-container bh-page" x-data="bhQibla(<?php echo wp_json_encode([
    'city'    => $city,
    'bearing' => $bearing !== null ? round((float) $bearing, 1) : null,
    'cities'  => array_map('strval', $cities),
]); ?>)">
    <nav class="bh-breadcrumb" aria-label="Fil d’Ariane">
        <a href="<?php echo esc_url(bh_nav_url('direct')); ?>"><?php echo esc_html(bh_l('Accueil', 'الرئيسية')); ?></a>
        <span aria-hidden="true">/</span>
        <a href="<?php echo esc_url(bh_nav_url('outils')); ?>"><?php echo esc_html(bh_l('Outils', 'الأدوات')); ?></a>
        <span aria-hidden="true">/</span> <span><?php echo esc_html(bh_l('Qibla', 'القبلة')); ?></span>
    </nav>
    <header class="bh-page__head">
        <h1><?php echo esc_html(bh_post_title(get_queried_object_id())); ?></h1>
        <p class="bh-page__lead"><?php echo esc_html(bh_l('Orientez-vous vers la Kaaba avant la prière.', 'اتجه نحو الكعبة قبل الصلاة.')); ?></p>
    </header>

    <div class="bh-qibla">
        <div class="bh-qibla__dial" role="img" aria-label="<?php echo esc_attr(bh_l('Boussole', 'بوصلة')); ?>">
            <span class="bh-qibla__needle" x-bind:style="'transform: rotate(' + needleDeg() + 'deg)'"></span>
            <span class="bh-qibla__kaaba" aria-hidden="true">🕋</span>
            <span class="bh-qibla__north" aria-hidden="true">N</span>
        </div>
        <div class="bh-qibla__info">
            <label><?php echo esc_html(bh_l('Ville', 'المدينة')); ?>
                <select x-model="citySel" @change="applyCity()">
                    <template x-for="c in cities" :key="c">
                        <option :value="c" x-text="c"></option>
                    </template>
                </select>
            </label>
            <p>
                <strong><?php echo esc_html(bh_l('Angle depuis le nord :', 'الزاوية من الشمال:')); ?></strong>
                <span class="bh-qibla__deg" x-text="bearingText()"></span>
            </p>
            <p class="bh-note" x-show="deviceReady === false">
                <?php echo esc_html(bh_l('Astuce : sur téléphone, activez la boussole (bouton ci-dessous) pour l’orientation en temps réel.', 'تلميح: على الهاتف فعّل البوصلة (الزر أدناه) للتوجيه الفوري.')); ?>
            </p>
            <button type="button" class="bh-btn" @click="startCompass()" x-show="!deviceReady">
                <?php echo esc_html(bh_l('Activer la boussole', 'تشغيل البوصلة')); ?>
            </button>
            <template x-if="deviceReady">
                <p class="bh-qibla__live">
                    <?php echo esc_html(bh_l('Tenez votre téléphone à plat et tournez-vous jusqu’à ce que l’aiguille pointe vers 🕋.', 'ضع هاتفك مائلاً ثم دوّر حتى تشير الإبرة إلى الكعبة.')); ?>
                </p>
            </template>
        </div>
    </div>
</div>
<?php get_footer();
