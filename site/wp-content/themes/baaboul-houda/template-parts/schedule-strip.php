<?php
/**
 * Today's schedule — horizontal strip.
 * Expects optional $args['day'] (weekday int, 0=Monday).
 */
defined('ABSPATH') || exit;
$day = isset($args['day']) ? (int) $args['day'] : bh_schedule_weekday();
$slots = bh_schedule_by_day($day);
$dayNamesFr = ['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi', 'Dimanche'];
$dayNamesAr = ['الاثنين', 'الثلاثاء', 'الأربعاء', 'الخميس', 'الجمعة', 'السبت', 'الأحد'];

if ($slots) :
    ?>
    <div class="bh-strip" role="list">
        <?php foreach ($slots as $slot) : ?>
            <div class="bh-strip__item" role="listitem" <?php echo !empty($slot['_now']) ? 'data-now="1"' : ''; ?>>
                <span class="bh-time-range"><?php echo esc_html($slot['start'] . '–' . $slot['end']); ?></span>
                <?php if ($slot['programme_id']) : ?>
                    <a href="<?php echo esc_url(get_permalink($slot['programme_id'])); ?>"><?php echo esc_html(bh_slot_title($slot)); ?></a>
                <?php else : ?>
                    <span><?php echo esc_html(bh_slot_title($slot)); ?></span>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
    <p class="bh-strip__label"><?php echo esc_html(bh_l($dayNamesFr[$day], $dayNamesAr[$day])); ?></p>
    <?php
else :
    echo '<p class="bh-empty">' . esc_html(bh_l('Aucun créneau renseigné pour aujourd’hui.', 'لا توجد حلقات مبرمجة لهذا اليوم.')) . '</p>';
endif;
