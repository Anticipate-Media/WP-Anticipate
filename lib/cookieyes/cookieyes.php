<?php
defined('ABSPATH') or die('Error: this file is not to be called separately.');

add_action(
    'wp_head', 
    function () {
        $anticipate_settings = get_option('anticipate_settings');
        if (isset($anticipate_settings['cookieyes_id']) && $anticipate_settings['cookieyes_id'] !== '') {
            echo '<!-- Start cookieyes banner --> <script id="cookieyes" type="text/javascript" src="https://cdn-cookieyes.com/client_data/'.$anticipate_settings['cookieyes_id'].'/script.js"></script> <!-- End cookieyes banner -->';          
        }
    },
    -1 // early execution
);

/**
 * Exclude cookieyes script from Rocket Loader (Cloudflare) to prevent conflicts with the cookieyes banner.
 */
add_filter( 'rocket_defer_inline_exclusions', function( $inline_exclusions_list ) {
  if ( ! is_array( $inline_exclusions_list ) ) {
    $inline_exclusions_list = array();
  }

  $inline_exclusions_list[] = 'cookieyes';
  return $inline_exclusions_list;
} );

add_filter( 'pre_get_rocket_option_delay_js_exclusions', function( $exclusions_list ) {
  if ( ! is_array( $exclusions_list ) ) {
    $exclusions_list = array();
  }
  $exclusions_list[] = 'maps.googleapis.com';
  $exclusions_list[] = 'cookieyes';
  $exclusions_list[] = 'cookie-law-info';
  $exclusions_list[] = 'cdn-cookieyes.com';
  return $exclusions_list;
},10,1 );