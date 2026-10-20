<?php
/**
 * Audio replay row (radio / podcast lists): round play button, title,
 * programme · date, progress bar with time, download. Expects $args['post'].
 * One sound at a time: starting a row pauses the others and the live radio.
 */
defined('ABSPATH') || exit;

$e     = $args['post'] ?? get_post();
$audio = function_exists('bh_episode_audio_url') ? bh_episode_audio_url($e->ID) : '';
if ('' === $audio) {
    return;
}
$prog   = bh_episode_programme($e->ID);
$epDate = get_post_meta($e->ID, '_bh_date', true);
$cfg    = [
    'src'   => $audio,
    'play'  => bh_l('Écouter', 'استمع'),
    'pause' => bh_l('Pause', 'إيقاف مؤقت'),
];
$ico = static function (string $path): string {
    return '<svg viewBox="0 0 24 24" width="22" height="22" fill="currentColor" aria-hidden="true">' . $path . '</svg>';
};
?>
<article class="bh-audio-row" :class="{ 'is-playing': playing }" x-data="bhAudioRow(<?php echo esc_attr(wp_json_encode($cfg)); ?>)">
    <button type="button" class="bh-audio-row__play" :class="{ 'is-loading': loading }" @click="toggle()"
            :aria-label="playing ? <?php echo esc_attr(wp_json_encode($cfg['pause'])); ?> : <?php echo esc_attr(wp_json_encode($cfg['play'])); ?>">
        <span x-show="!playing"><?php echo $ico('<path d="M8 5.5v13a1 1 0 0 0 1.5.86l10.5-6.5a1 1 0 0 0 0-1.72L9.5 4.64A1 1 0 0 0 8 5.5z"/>'); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
        <span x-show="playing" x-cloak><?php echo $ico('<path d="M7 5h3.5v14H7zM13.5 5H17v14h-3.5z"/>'); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
    </button>

    <div class="bh-audio-row__main">
        <a class="bh-audio-row__title" href="<?php echo esc_url(get_permalink($e)); ?>"><?php echo esc_html(bh_post_title($e->ID)); ?></a>
        <span class="bh-audio-row__meta">
            <?php if ($prog) : ?><?php echo esc_html(bh_post_title($prog->ID)); ?><?php endif; ?>
            <?php if ($prog && $epDate) : ?> · <?php endif; ?>
            <?php if ($epDate) : ?><?php echo esc_html(date_i18n('j F Y', strtotime($epDate))); ?><?php endif; ?>
        </span>
        <div class="bh-audio-row__bar" dir="ltr">
            <input type="range" min="0" max="1000" step="1" :value="pct()" :style="'--p:' + (pct() / 10) + '%'" :disabled="!dur"
                   @input="seek($event)" aria-label="<?php echo esc_attr(bh_l('Position de lecture', 'موضع التشغيل')); ?>">
            <span class="bh-audio-row__time" x-text="timeText()">0:00 / --:--</span>
        </div>
        <small class="bh-audio-row__err" x-show="error" x-cloak><?php echo esc_html(bh_l('Fichier audio indisponible.', 'الملف الصوتي غير متاح.')); ?></small>
    </div>

    <a class="bh-audio-row__dl" href="<?php echo esc_url($audio); ?>" download target="_blank" rel="noopener"
       aria-label="<?php echo esc_attr(bh_l('Télécharger l’audio', 'تحميل الصوت')); ?>" title="<?php echo esc_attr(bh_l('Télécharger', 'تحميل')); ?>">
        <?php echo $ico('<path d="M12 3v11.2l3.6-3.6 1.4 1.4-6 6-6-6 1.4-1.4 3.6 3.6V3h2zM5 19h14v2H5z"/>'); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
    </a>
</article>
