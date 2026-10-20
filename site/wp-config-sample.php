<?php
/**
 * Baaboul Houda — wp-config.php sample.
 *
 * cPanel : copier ce fichier en `wp-config.php`, remplir les 3 constantes DB
 * et remplacer les clés SALT par celles de
 * <https://api.wordpress.org/secret-key/1.1/salt/>
 *
 * @package baaboul-houda
 */

// Nom / utilisateur / mot de passe / hôte de la base (cPanel > MySQL).
define( 'DB_NAME', 'CHANGE_ME_db' );
define( 'DB_USER', 'CHANGE_ME_user' );
define( 'DB_PASSWORD', 'CHANGE_ME_password' );
define( 'DB_HOST', 'localhost' );
define( 'DB_CHARSET', 'utf8mb4' );
define( 'DB_COLLATE', '' );

// Clés de sécurité — GÉNÉRER sur https://api.wordpress.org/secret-key/1.1/salt/
define( 'AUTH_KEY', 'CHANGE_ME' );
define( 'SECURE_AUTH_KEY', 'CHANGE_ME' );
define( 'LOGGED_IN_KEY', 'CHANGE_ME' );
define( 'NONCE_KEY', 'CHANGE_ME' );
define( 'AUTH_SALT', 'CHANGE_ME' );
define( 'SECURE_AUTH_SALT', 'CHANGE_ME' );
define( 'LOGGED_IN_SALT', 'CHANGE_ME' );
define( 'NONCE_SALT', 'CHANGE_ME' );

$table_prefix = 'wp_';

// Sécurité : interdire la modification des fichiers depuis l'admin.
define( 'DISALLOW_FILE_EDIT', true );
define( 'DISALLOW_FILE_MODS', true );
// Interdire l'inscription publique.
define( 'WP_ALLOW_MULTISITE', false );
// Journal d'erreurs : hors du web root par défaut ; désactiver en production
// une fois le site stable (laisser true pendant le déploiement).
define( 'WP_DEBUG', false );
define( 'WP_DEBUG_LOG', false );
define( 'WP_DEBUG_DISPLAY', false );

// Mises à jour : autoriser mineures + traductions uniquement.
define( 'AUTOMATIC_UPDATER_DISABLED', false );
add_action( 'option_update_auto_update', function ( $value ) {
	$plugins = is_array( $value ) ? $value : array();
	// Ne jamais désactiver la mise à jour du cœur si tout est épinglé.
	return $plugins;
}, 20 );

// Maintenance : créer un fichier `maintenance.php` à la racine pour forcer
// la page de maintenance pendant les mises à jour manuelles.
if ( file_exists( __DIR__ . '/maintenance.php' ) ) {
	wp_die(
		esc_html__( 'Le site est en maintenance. Revenez dans quelques instants.', 'baaboul-houda' ),
		'Maintenance',
		array( 'response' => 503 )
	);
}

/* That's all, stop editing! */
require_once ABSPATH . 'wp-settings.php';
