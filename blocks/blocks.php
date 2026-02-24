<?php

/** 
 * Laposta block
 */
 add_filter( 'rwmb_meta_boxes', function( $meta_boxes ) {
    $lijsten = LapostaImplementatie::getLapostaLists();
    $select = [];
    foreach($lijsten as $lijst){
        $select[$lijst['list']['list_id']] = $lijst['list']['name'].' ('.$lijst['list']['members']['active'].' leden)';
    }
    

	$meta_boxes[] = [
        'title'           => 'Laposta block',
        'id'              => 'laposta',
        'description'     => 'Insert a Laposta form',
        'type'            => 'block',
        'icon'            => 'awards',
        'category'        => 'layout',
		'enqueue_script'  => plugin_dir_url(dirname(__FILE__)) . '/lib/laposta/laposta.js',
        'enqueue_style'   => plugin_dir_url(dirname(__FILE__)) . '/lib/laposta/laposta.css',
        'render_template' => dirname(__FILE__) . '/laposta/template.php',
        'supports' => [
            'align' => ['wide', 'full'],
        ],

        // Block fields.
        'fields'          => [
            [
                'name'            => 'Laposta Lijst',
                'id'              => 'lijst',
                'type'            => 'select',
                'multiple'        => false,
                'placeholder'     => 'Kies een lijst',
                'select_all_none' => false,
                'options'         => $select
            ],
            [
                'name'            => 'Labels of placeholders',
                'id'              => 'display',
                'type'            => 'select',
                'multiple'        => false,
                'placeholder'     => 'Kies een weergave',
                'select_all_none' => false,
                'options'         => [
                    'labels' => 'Labels',
                    'placeholders' => 'Placeholders ',
                ]
            ],
            [
                'name'            => 'Submit tekst',
                'id'              => 'submit',
                'type'            => 'text',
            ],
		],
    ];
    return $meta_boxes;
} );
