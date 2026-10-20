<?php
/**
 * Auto-activate the Baaboul Houda core plugin on first admin load (idempotent).
 * Remove this file (or the Dockerfile) if you prefer manual activation.
 */

defined('ABSPATH') || exit;

add_action('admin_init', function () {
    if (defined('DOING_AJAX') && DOING_AJAX) {
        return;
    }
    $plugin = 'baaboul-houda-core/baaboul-houda-core.php';
    if (!file_exists(WP_PLUGIN_DIR . '/' . $plugin)) {
        return;
    }
    if (is_multisite()) {
        if (!is_plugin_active_for_network($plugin)) {
            activate_plugin($plugin, '', true);
        }
    } elseif (!is_plugin_active($plugin)) {
        activate_plugin($plugin);
    }
}, 1);
