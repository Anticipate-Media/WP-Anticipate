<?php

add_shortcode(
    'ant_lapostaform',
    function($atts){
        $atts = shortcode_atts( array(
            'list' => ''
        ), $atts, 'ant_lapostaform' );
        if($atts['list']!==''){
            wp_enqueue_style( 'laposta', plugins_url( '../..//lib/laposta/laposta.css' , __FILE__ ) );
            wp_enqueue_script( 'laposta', plugins_url( '../../lib/laposta/laposta.js' , __FILE__ ),['jquery'] );
           
    
            return LapostaImplementatie::form($atts['list']);
        }
    }
);