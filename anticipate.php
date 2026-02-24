<?php
/*
Plugin Name:  Anticipate Plugin
Plugin URI:   https://anticipate.nl
Description:  Extra functionaliteiten
Version:      4.0.7
Author:       Anticipate / Aart Jan
Author URI:   https://anticipate.nl/
License:      GPL2
License URI:  https://www.gnu.org/licenses/gpl-2.0.html
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

use YahnisElsts\PluginUpdateChecker\v5\PucFactory;

$updateChecker = PucFactory::buildUpdateChecker(
    'https://github.com/Anticipate-Media/WP-Anticipate',
    __FILE__,
    'WP-Anticipate'
);

// Optioneel: alleen releases gebruiken
$updateChecker->getVcsApi()->enableReleaseAssets();

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
