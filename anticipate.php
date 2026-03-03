<?php
/*
Plugin Name:  Anticipate Plugin
Plugin URI:   https://anticipate.nl
Description:  Extra functionaliteiten
Version:      4.1.1
Author:       Anticipate / Aart Jan
Author URI:   https://anticipate.nl/
License:      GPL2
License URI:  https://www.gnu.org/licenses/gpl-2.0.html
Update URI:   https://github.com/Anticipate-Media/WP-Anticipate/

Text Domain:  anticipate
*/

defined('ABSPATH') or die('Error: this file is not to be called separately.');
require __DIR__ . '/vendor/autoload.php';

include_once('lib/laposta/lapostaimplementatie.php');
include_once('lib/cookieyes/cookieyes.php');

include_once('lib/database/edbm.php');

include_once 'adminpages/extracode.php';
include_once 'blocks/blocks.php';
include_once 'shortcodes/shortcodes.php';

//WEG include_once 'lib/iframechanger.php';

add_filter( 'update_plugins_github.com', 'self_update', 10, 4 );

/**
 * Check for updates to this plugin
 *
 * @param array  $update   Array of update data.
 * @param array  $plugin_data Array of plugin data.
 * @param string $plugin_file Path to plugin file.
 * @param string $locales    Locale code.
 *
 * @return array|bool Array of update data or false if no update available.
 */
function self_update( $update, array $plugin_data, string $plugin_file, $locales ) {

	// only check this plugin
	if ( 'WP-Anticipate/anticipate.php' !== $plugin_file ) {
		return $update;
	}

	// already completed update check elsewhere
	if ( ! empty( $update ) ) {
		return $update;
	}

	// let's go get the latest version number from GitHub
	$response = wp_remote_get(
		'https://api.github.com/repos/Anticipate-Media/WP-Anticipate/releases/latest',
		array(
			'user-agent' => 'aartjan',
		)
	);

	if ( is_wp_error( $response ) ) {
		return;
	} else {
		$output = json_decode( wp_remote_retrieve_body( $response ), true );
	}

	$new_version_number  = $output['tag_name'];
	$is_update_available = version_compare( $plugin_data['Version'], $new_version_number, '<' );

	if ( ! $is_update_available ) {
		return false;
	}

	$new_url     = $output['html_url'];
	$new_package = sprintf(
		'https://github.com/Anticipate-Media/WP-Anticipate/releases/download/%s/WP-Anticipate.zip',
		$new_version_number
	);
	error_log('$plugin_data: ' . print_r( $plugin_data, true ));
	error_log('$new_version_number: ' . $new_version_number );
	error_log('$new_url: ' . $new_url );
	error_log('$new_package: ' . $new_package );

	return array(
		'slug'    => 'WP-Anticipate',
		'version' => $new_version_number,
		'url'     => $new_url,
		'package' => $new_package,
		'plugin_file' => 'WP-Anticipate/anticipate.php',
	);
}




add_action(
    'wp_head', 
    function () {
        echo '<script type="text/javascript">
            window.anticipateajaxurl = "' . admin_url('admin-ajax.php') . '";
            </script>';
    }
);

add_action(
    'admin_head', 
    function () {
        echo '<script type="text/javascript">
            window.anticipateajaxurl = "' . admin_url('admin-ajax.php') . '";
            </script>';
    }
);


add_action(
    'wp_enqueue_scripts', 
    function () {
        wp_enqueue_style( 'anticipatestyle', plugins_url( '/anticipate.css' , __FILE__ ),[],202602181828 );
    }
);

/**
 * Verwijdert alle .zip en .sql bestanden uit de map lib/database van deze plugin.
 * Kan veilig via WP_Filesystem of direct via PHP worden uitgevoerd.
 */
function anticipate_cleanup_database_backups(): void {
	$base_dir = plugin_dir_path(__FILE__);
	$target_dir = $base_dir . 'lib/database/';

	// Beveiliging: zorg dat het pad binnen de plugin map ligt
	$real_base = realpath($base_dir);
	$real_target = realpath($target_dir) ?: $target_dir; // map kan bestaan zonder bestanden
	if ($real_base === false || strpos($real_target, $real_base) !== 0) {
		return; // onveilig pad, niets doen
	}

	$patterns = [
		$target_dir . '*.zip',
		$target_dir . '*.sql',
	];
	// die($target_dir . '*.sql');
	// Probeer WP_Filesystem indien beschikbaar
	global $wp_filesystem;
	if (! function_exists('WP_Filesystem')) {
		require_once ABSPATH . 'wp-admin/includes/file.php';
	}
	if (function_exists('WP_Filesystem') && WP_Filesystem() && is_object($wp_filesystem)) {
		foreach ($patterns as $pattern) {
			foreach (glob($pattern) ?: [] as $file) {
				// Dubbele check dat het bestand in de target map staat
				if (strpos(realpath($file) ?: $file, $real_target) === 0) {
					$wp_filesystem->delete($file);
				}
			}
		}
		return;
	}

	// Fallback: native PHP verwijdering
	foreach ($patterns as $pattern) {
		foreach (glob($pattern) ?: [] as $file) {
			if (is_file($file)) {
				@unlink($file);
			}
		}
	}
}


// Optioneel: koppel aan een admin-actie zodat het handmatig aangeroepen kan worden via admin-ajax
add_action('wp_ajax_anticipate_cleanup_database_backups', function () {
	// Alleen voor administrators
	if (! current_user_can('manage_options')) {
		wp_send_json_error('forbidden', 403);
	}
	anticipate_cleanup_database_backups();
	wp_send_json_success(['status' => 'done']);
});
