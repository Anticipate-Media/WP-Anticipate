<?php

if (!defined('ABSPATH')) exit;

define('EDBM_PATH', plugin_dir_path(__FILE__));
define('EDBM_URL', plugin_dir_url(__FILE__));

require_once EDBM_PATH . 'class-edbm-logger.php';
require_once EDBM_PATH . 'class-edbm-export.php';
require_once EDBM_PATH . 'class-edbm-restore.php';
require_once EDBM_PATH . 'class-edbm-ajax.php';

add_action('admin_menu', function () {
    add_options_page(
        'Anticipate DB',
        'Anticipate DB',
        'manage_options',
        'anticipate-db-manager',
        'edbm_admin_page'
    );
});

function edbm_admin_page() {
    if (!current_user_can('manage_options')) wp_die('Geen toegang.');

    wp_enqueue_script('edbm-js', EDBM_URL . 'dbadmin.js', ['jquery'], '1.0', true);
    wp_localize_script('edbm-js', 'edbm_ajax', [
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('edbm_nonce')
    ]);
    ?>
    <div class="wrap">
        <h1>Anticipate DB</h1>
        <h2>Export</h2>
        <button id="edbm-export" class="button button-primary">Start Export</button>
        <div id="edbm-export-progress"></div>

        <hr>

        <h2>Import (overschrijft huidige database, maak eerst een backup!)</h2>
        <input type="file" id="edbm-file" accept=".sql">
        <br><br>
        <!-- <label>Search/Replace URL (optioneel)</label><br>
        <input type="text" id="edbm-search" placeholder="https://staging.site">
        <input type="text" id="edbm-replace" placeholder="https://live.site"> -->
        <br><br>
        <button id="edbm-restore" class="button button-primary">Start Import</button>
        <div id="edbm-restore-progress"></div>
    </div>
    <?php
}
