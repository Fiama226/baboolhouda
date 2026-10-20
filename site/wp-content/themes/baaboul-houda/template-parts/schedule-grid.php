<?php
/**
 * Full weekly schedule grid (7 columns, EPG-style like nordubai.com).
 */
defined('ABSPATH') || exit;
$byDay = bh_schedule_all();
$today = bh_schedule_weekday();
$dayNamesFr = ['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi', 'Dimanche'];
$dayNamesAr = ['الاثنين', 'الثلاثاء', 'الأربعاء', 'الخميس', 'الجمعة', 'السبت', 'الأحد'];
?>
<div class="bh-week" x-data="{ day: <?php echo $today; ?> }">
    <div class="bh-week__days" role="tablist" aria-label="<?php echo esc_attr(bh_l('Choisir un jour', 'اختر يومًا')); ?>">
        <?php for ($d = 0; $d < 7; $d++) : ?>
            <button type="button" role="tab" class="bh-week__day" :class="{ 'is-active': day === <?php echo $d; ?> }"
                    @click="day = <?php echo $d; ?>" :aria-selected="day === <?php echo $d; ?> ? 'true' : 'false'">
                <?php echo esc_html(bh_l($dayNamesFr[$d], $dayNamesAr[$d])); ?>
            </button>
        <?php endfor; ?>
    </div>
    <div class="bh-week__panel">
        <?php for ($d = 0; $d < 7; $d++) : ?>
            <div class="bh-week__col" x-show="day === <?php echo $d; ?>" x-cloak>
                <h3><?php echo esc_html(bh_l($dayNamesFr[$d], $dayNamesAr[$d])); ?>
                    <?php if ($d === $today) : ?><span class="bh-badge-today"><?php echo esc_html(bh_l('Aujourd’hui', 'اليوم')); ?></span><?php endif; ?>
                </h3>
                <?php if ($byDay[$d]) : ?>
                    <ul class="bh-week__slots">
                        <?php foreach ($byDay[$d] as $slot) : ?>
                            <li class="bh-week__slot">
                                <span class="bh-time-range"><?php echo esc_html($slot['start'] . '–' . $slot['end']); ?></span>
                                <?php if ($slot['programme_id']) : ?>
                                    <a href="<?php echo esc_url(get_permalink($slot['programme_id'])); ?>"><?php echo esc_html(bh_slot_title($slot)); ?></a>
                                <?php else : ?>
                                    <span><?php echo esc_html(bh_slot_title($slot)); ?></span>
                                <?php endif; ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else : ?>
                    <p class="bh-empty"><?php echo esc_html(bh_l('Aucune diffusion programmée.', 'لا توجد برامج مبرمجة.')); ?></p>
                <?php endif; ?>
            </div>
        <?php endfor; ?>
    </div>
</div>
