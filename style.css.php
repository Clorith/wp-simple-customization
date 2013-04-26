<?php
    global $css;

    foreach( $css->settings AS $setting )
    {
        echo $setting['object'] . " { " . $setting['selector'] . ": " . get_theme_mod( $setting['name'] ) . "; }\n";
    }
