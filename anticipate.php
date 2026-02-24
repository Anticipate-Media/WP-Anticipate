<?php
/*
Plugin Name:  Anticipate Plugin
Plugin URI:   https://anticipate.nl
Description:  Extra functionaliteiten
Version:      4.0.2
Author:       Anticipate / Aart Jan
Author URI:   https://anticipate.nl/
License:      GPL2
License URI:  https://www.gnu.org/licenses/gpl-2.0.html
Text Domain:  anticipate
*/

defined('ABSPATH') or die('Error: this file is not to be called separately.');
require __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/lib/eduardovillao/class-updater-checker.php';

include_once('lib/laposta/lapostaimplementatie.php');
include_once('lib/cookieyes/cookieyes.php');

include_once('lib/database/edbm.php');

include_once 'adminpages/extracode.php';
include_once 'blocks/blocks.php';
include_once 'shortcodes/shortcodes.php';
// include_once 'lib/iframechanger.php';

// UPDATER CHECKER TEstje
use Updater_Checker; // Use your namespace

$github_username = 'Anticipate-Media'; // Use your gitbub username
$github_repository = 'WP-Anticipate'; // Use your repository name
$plugin_basename = plugin_basename( __FILE__ ); // Check note below
$plugin_current_version = '4.0.2'; // Use the current version of the plugin

$updater = new Updater_Checker(
    $github_username,
    $github_repository,
    $plugin_basename,
    $plugin_current_version
);
$updater->set_hooks();


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
