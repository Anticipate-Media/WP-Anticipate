<?php
add_action('wp_ajax_edbm_export', function () {

    check_ajax_referer('edbm_nonce');

    $offset = intval($_POST['offset']);

    $more = EDBM_Export::process($offset);

    wp_send_json([
        'more' => $more,
        'next_offset' => $offset + 300
    ]);
});

add_action('wp_ajax_edbm_export_zip', function () {

    check_ajax_referer('edbm_nonce');

    $url = EDBM_Export::zip();

    wp_send_json(['url' => $url]);
});

add_action('wp_ajax_edbm_restore', function () {

    check_ajax_referer('edbm_nonce');

    $file = $_FILES['file']['tmp_name'];
    $search = sanitize_text_field($_POST['search']);
    $replace = sanitize_text_field($_POST['replace']);

    EDBM_Restore::process($file, $search, $replace);

    wp_send_json(['done' => true]);
});
