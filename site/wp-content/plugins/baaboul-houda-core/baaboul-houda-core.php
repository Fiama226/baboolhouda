<?php
/**
 * Plugin Name:       Baaboul Houda Core
 * Description:       Site officiel de TV Baaboul Houda / قناة باب الهدى — TV en direct, radio, programmes, coran, prières.
 * Version:           1.0.0
 * Requires at least: 6.0
 * Requires PHP:      8.1
 * Author:            Agence digitale (build interne)
 * License:           GPL-2.0-or-later
 * Text Domain:       baaboul-houda-core
 */

defined('ABSPATH') || exit;

define('BH_VERSION', '1.0.1');
define('BH_CORE_FILE', __FILE__);
define('BH_CORE_DIR', plugin_dir_path(__FILE__));
define('BH_CORE_URL', plugin_dir_url(__FILE__));
define('BH_DATA_DIR', BH_CORE_DIR . 'data/');

require BH_CORE_DIR . 'includes/i18n.php';
require BH_CORE_DIR . 'includes/post-types.php';
require BH_CORE_DIR . 'includes/settings.php';
require BH_CORE_DIR . 'includes/live.php';
require BH_CORE_DIR . 'includes/schedule.php';
require BH_CORE_DIR . 'includes/prayer.php';
require BH_CORE_DIR . 'includes/quran.php';
require BH_CORE_DIR . 'includes/islamic-data.php';
require BH_CORE_DIR . 'includes/contact.php';
require BH_CORE_DIR . 'includes/tools.php';
require BH_CORE_DIR . 'includes/feeds.php';
require BH_CORE_DIR . 'includes/community.php';
require BH_CORE_DIR . 'includes/seo.php';
require BH_CORE_DIR . 'includes/pwa.php';
require BH_CORE_DIR . 'includes/admin-ui.php';
require BH_CORE_DIR . 'includes/activation.php';

/** Convenience: one bilingual string — "the current language decides". */
function bh_l(string $fr, string $ar): string {
    return bh_is_ar() ? $ar : $fr;
}
