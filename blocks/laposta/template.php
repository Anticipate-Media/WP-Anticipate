<?php

//require_once('../../lib/laposta/lapostaimplementatie.php');


// Unique HTML ID if available.
$id = 'laposta-' . ( $attributes['id'] ?? '' );
if ( ! empty( $attributes['anchor'] ) ) {
    $id = $attributes['anchor'];
}

// Custom CSS class name.
$class = 'lapostablock ' . ( $attributes['className'] ?? '' );
if ( ! empty( $attributes['align'] ) ) {
    $class .= " align{$attributes['align']}";
}
?>
<div id="<?= $id ?>" class="<?= $class ?>" style="background-color: <?= mb_get_block_field( 'background_color' ) ?>">
<?php 

echo LapostaImplementatie::form($attributes['data']['lijst'], $attributes['data']);
?>
</div>


<?php /*

API:
e2INanC6pPrMfhqecXf4




*/