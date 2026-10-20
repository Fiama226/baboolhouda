<?php
/** Template Name: Heures de prière */
defined('ABSPATH') || exit;
get_header();
$s = bh_settings();
?>

<section class="bh-page-hero">
    <div class="bh-container">
        <h1><?php echo esc_html(bh_post_title(get_queried_object_id())); ?></h1>
        <p class="bh-page-hero__sub"><?php echo esc_html(bh_l('Horaires de prière et date hégirienne.', 'مواقيت الصلاة والتاريخ الهجري.')); ?></p>
    </div>
</section>

<section class="bh-section">
    <div class="bh-container">
        <div class="bh-card bh-card--prayer-lg" x-data="bhPrayer(true)">
            <div class="bh-prayer-lg__head">
                <label class="bh-prayer-lg__city">
                    <span><?php echo esc_html(bh_l('Ville', 'المدينة')); ?></span>
                    <select x-model="city" @change="load()" class="bh-select" aria-label="<?php echo esc_attr(bh_l('Ville', 'المدينة')); ?>">
                        <template x-for="c in cities" :key="c"><option :value="c" x-text="c"></option></template>
                    </select>
                </label>
                <div class="bh-prayer-lg__date">
                    <strong data-bh-hijri-lg x-text="hijri()"></strong>
                    <span><?php echo esc_html(date_i18n('l j F Y')); ?></span>
                </div>
            </div>

            <div class="bh-prayer-lg__grid">
                <template x-for="p in order" :key="p.key">
                    <div class="bh-prayer-lg__cell" :class="{ 'is-next': p.key === nextKey }">
                        <span class="bh-prayer-lg__name" x-text="names[p.key]"></span>
                        <strong class="bh-prayer-lg__time" x-text="times[p.key] || '—:—'"></strong>
                        <small x-show="p.key === nextKey && nextIn" x-text="'<?php echo esc_js(bh_l('dans', 'بعد')); ?> ' + nextIn" data-x-cloak></small>
                    </div>
                </template>
            </div>

            <p class="bh-prayer-lg__src" x-show="source === 'fallback'">
                <?php echo esc_html(bh_l('⚠ Horaires indicatifs (service hors-ligne) — vérifiez auprès de votre mosquée.', '⚠ أوقات تقريبية (دون اتصال) — يرجى التأكيد لدى مسجدمكم.')); ?>
            </p>
        </div>

        <div class="bh-grid bh-grid--3 bh-prayer-tools">
            <a class="bh-card bh-card--tool" href="<?php echo esc_url(bh_nav_url('qibla')); ?>">
                <span class="bh-card__icon" aria-hidden="true">🧭</span>
                <span class="bh-card__body"><strong><?php echo esc_html(bh_l('Direction de la Qibla', 'اتجاه القبلة')); ?></strong>
                <small><?php echo esc_html(bh_l('Boussole & angle', 'بوصلة وزاوية')); ?></small></span>
            </a>
            <a class="bh-card bh-card--tool" href="<?php echo esc_url(bh_nav_url('dates')); ?>">
                <span class="bh-card__icon" aria-hidden="true">📅</span>
                <span class="bh-card__body"><strong><?php echo esc_html(bh_l('Jeûnes & calendrier', 'الصيام والتقويم')); ?></strong>
                <small><?php echo esc_html(bh_l('Hégirien, jours de jeûne, fêtes', 'هجري، أيام صيام، أعياد')); ?></small></span>
            </a>
            <?php if (!empty($s['adhan_enabled']) && !empty($s['adhan_url'])) : ?>
                <div class="bh-card bh-card--tool bh-card--adhan">
                    <span class="bh-card__icon" aria-hidden="true">🔔</span>
                    <span class="bh-card__body"><strong><?php echo esc_html(bh_l('Adhan', 'الأذان')); ?></strong>
                    <small><?php echo esc_html(bh_l('L’adhan sonne à chaque appel (dans votre navigateur).', 'يُسمع الأذان مع كل أذان (في متصفحكم).')); ?></small></span>
                    <audio class="bh-adhan" data-bh-adhan preload="none" src="<?php echo esc_url($s['adhan_url']); ?>"></audio>
                </div>
            <?php endif; ?>
        </div>

        <div class="bh-prayer-month">
            <h2><?php echo esc_html(bh_l('Calendrier du mois', 'تقويم الشهر')); ?> — <?php echo esc_html($s['prayer_city']); ?></h2>
            <div class="bh-table-wrap">
                <table class="bh-table">
                    <thead>
                        <tr>
                            <th><?php echo esc_html(bh_l('Jour', 'اليوم')); ?></th>
                            <th>Fajr</th><th><?php echo esc_html(bh_l('Lever', 'الشروق')); ?></th><th>Dhuhr</th><th>Asr</th><th><?php echo esc_html(bh_l('Maghrib', 'المغرب')); ?></th><th><?php echo esc_html(bh_l('Isha', 'العشاء')); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $monthDays = bh_prayer_month((int) date('n'), (int) date('Y'), $s['prayer_city']);
                        foreach ($monthDays as $day => $times) :
                            $isToday = $day === (int) date('j');
                            ?>
                            <tr <?php echo $isToday ? 'class="is-today"' : ''; ?>>
                                <td><?php echo esc_html(date_i18n('D j', mktime(12, 0, 0, (int) date('n'), (int) $day, (int) date('Y')))); ?></td>
                                <td><?php echo esc_html($times['times']['fajr']); ?></td>
                                <td><?php echo esc_html($times['times']['sunrise']); ?></td>
                                <td><?php echo esc_html($times['times']['dhuhr']); ?></td>
                                <td><?php echo esc_html($times['times']['asr']); ?></td>
                                <td><?php echo esc_html($times['times']['maghrib']); ?></td>
                                <td><?php echo esc_html($times['times']['isha']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</section>

<?php get_footer(); ?>
