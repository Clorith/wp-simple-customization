jQuery(document).ready(function ($) {
<?php
    global $css;

    foreach( $css->settings AS $setting )
    {
        echo "
            wp.customize( '" . $setting['name'] . "', function( value ) {
                value.bind( function( newval ) {
                    $('" . $setting['object'] . "').css('" . $setting['selector'] . "', newval );
                } );
            } );
            \n\n
        ";
    }
?>
});
