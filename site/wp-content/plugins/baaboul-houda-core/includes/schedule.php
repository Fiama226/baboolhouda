<?php
/**
 * Weekly program schedule ("grille des programmes").
 */

defined('ABSPATH') || exit;

/** PHP date('w') uses 0=Sunday..6=Saturday; settings use 0=Monday..6=Sunday. */
function bh_schedule_weekday(): int {
    return (int) ((int) date('w') + 6) % 7;
}

function bh_schedule(): array {
    $s = bh_settings();
    return is_array($s['schedule']) ? $s['schedule'] : [];
}

function bh_schedule_by_day(int $weekday): array {
    $slots = [];
    foreach (bh_schedule() as $row) {
        if ((int) $row['day'] === $weekday) {
            $slots[] = $row;
        }
    }
    usort($slots, static fn ($a, $b) => strcmp($a['start'], $b['start']));
    return $slots;
}

function bh_schedule_all(): array {
    $byDay = [];
    for ($d = 0; $d < 7; $d++) {
        $byDay[$d] = bh_schedule_by_day($d);
    }
    return $byDay;
}

/** Current + next slot for "à l'antenne maintenant". */
function bh_now_next(): array {
    $weekday = bh_schedule_weekday();
    $slots   = bh_schedule_by_day($weekday);
    $now     = date('H:i');
    $current = null;
    $next    = null;
    foreach ($slots as $slot) {
        if ($now >= $slot['start'] && $now < $slot['end']) {
            $current = $slot;
        }
        if ($slot['start'] > $now && null === $next) {
            $next = $slot;
        }
    }
    if (null === $next && $slots) {
        $next = $slots[0]; // wraps to tomorrow's first slot
    }
    return ['current' => $current, 'next' => $next, 'slots' => $slots];
}

function bh_slot_title(array $slot): string {
    $post = $slot['programme_id'] ? get_post($slot['programme_id']) : null;
    return $post ? bh_post_title($post->ID) : bh_l('Antenne', 'العرض');
}
