<?php
/**
 * Class css
 *
 * Used as a simpler interface to add WP Customize API elements, with a lower chance of fatal errors due to missing settings or controllers
 */
    class css
    {
        /**
         * @var array $sections Used for storing our added sections before displaying them
         * @var array $settings The settings we wish to implement
         */
        private $sections = array();
        public $settings = array();

        /**
         * Class constructor
         * Initiates various WP hooks that we need for this to actually work
         */
        function __construct()
        {
            add_action( 'customize_register', array( $this, 'build' ) );
            add_action( 'wp_enqueue_scripts', array( $this, 'style' ) );
            add_action( 'customize_preview_init', array( $this, 'style_customize' ) );
            add_action( 'init', array( $this, 'init_build' ) );
        }

        /**
         * Add a section or setting to the Customize screen
         *
         * @param string $name Name your section or setting (should be unique)
         * @param string $type The type of the option being added (section or setting)
         * @param array $args Arguments accepted are the ones normally accepted by the WP Customize API
         * @return bool
         */
        function add( $name, $type, $args = array() )
        {
            //  First we set the name, this is an always existent constant, and it's nice to have it as the first data point
            $array = array(
                'name' => $name
            );

            //  Next, iterate over the arguments array and insert them accordingly
            foreach ( $args AS $item => $data )
            {
                $array[$item] = $data;
            }

            //  Finally, enter the data into the appropriate data container
            switch ( $type )
            {
                case 'section':
                    $this->sections[] = $array;
                    break;
                default:
                    $this->settings[] = $array;
            }

            return true;
        }

        /**
         * The build function generates our customize screen
         *
         * @param mixed $custom WP Customize class
         */
        function build( $custom )
        {
            //  Loop through the defined sections, sections hold our settings fields so it makes sense to define these first
            foreach( $this->sections AS $section )
            {
                $custom->add_section(
                    $section['name'],
                    array(
                        'title'    => __( $section['title'] ),
                        'priority' => ( ! isset( $section['priority'] ) || empty( $section['priority'] ) ? 30 : $section['priority'] )
                    )
                );
            }

            //  Next, generate the actual settings
            foreach( $this->settings AS $setting )
            {
                $custom->add_setting(
                    $setting['name'],
                    array(
                        'default'   => $setting['default'],
                        'transport' => 'postMessage'
                    )
                );

                //  Since a setting also requires a controller, we initiate the controller straight away using the setting name as identifier.
                //  This means we won't get fatal errors for missing setting for a controller which may happen if we do this manually per setting!
                switch( $setting['type'] )
                {
                    case 'header':
                        $custom->add_control(
                            new WP_Customize_Header_Image_Control(
                                $custom,
                                $setting['name'],
                                array(
                                    'label'    => __( $setting['label'] ),
                                    'section'  => $setting['section'],
                                    'settings' => $setting['name']
                                )
                            )
                        );
                        break;
                    case 'background':
                        $custom->add_control(
                            new WP_Customize_Background_Image_Control(
                                $custom,
                                $setting['name'],
                                array(
                                    'label'    => __( $setting['label'] ),
                                    'section'  => $setting['section'],
                                    'settings' => $setting['name']
                                )
                            )
                        );
                        break;
                    case 'image':
                        $custom->add_control(
                            new WP_Customize_Image_Control(
                                $custom,
                                $setting['name'],
                                array(
                                    'label'    => __( $setting['label'] ),
                                    'section'  => $setting['section'],
                                    'settings' => $setting['name']
                                )
                            )
                        );
                        break;
                    case 'upload':
                        $custom->add_control(
                            new WP_Customize_Upload_Control(
                                $custom,
                                $setting['name'],
                                array(
                                    'label'    => __( $setting['label'] ),
                                    'section'  => $setting['section'],
                                    'settings' => $setting['name']
                                )
                            )
                        );
                        break;
                    case 'color':
                        $custom->add_control(
                            new WP_Customize_Color_Control(
                                $custom,
                                $setting['name'],
                                array(
                                    'label'    => __( $setting['label'] ),
                                    'section'  => $setting['section'],
                                    'settings' => $setting['name']
                                )
                            )
                        );
                        break;
                    default:
                        $custom->add_control(
                            new WP_Customize_Control(
                                $custom,
                                $setting['name'],
                                array(
                                    'label'    => __( $setting['label'] ),
                                    'section'  => $setting['section'],
                                    'settings' => $setting['name']
                                )
                            )
                        );
                }
            }
        }

        /**
         * Queue the stylesheet for our primary website
         */
        function style() {
            $theme = wp_get_theme();

            wp_register_style( $theme->stylesheet . '-custom-css', home_url( '/' . $theme->stylesheet . '-custom-css.css' ), false, '1.0.0' );

            wp_enqueue_style( $theme->stylesheet . '-custom-css' );
        }

        /**
         * Queue the javascript file allowing for real time previews without reloading the frame
         */
        function style_customize() {
            $theme = wp_get_theme();

            wp_register_script( $theme->stylesheet . '-custom-js', home_url( '/' . $theme->stylesheet . '-custom-css.js' ), array( 'jquery', 'customize-preview' ), '1.0.0', true );

            wp_enqueue_script( $theme->stylesheet . '-custom-js' );
        }

        /**
         * Our build function for init, this is kind of magical
         *
         * We load our php scripts (that generate the javascript and css file) in using this function.
         *
         * Older browsers often define the type of file by file extension and ignores MIME type, this will help them understand what data they should display.
         */
        function init_build() {
            $theme = wp_get_theme();

            //  If the current URL requested is our customized css one, serve it up nicely with an include then kill any further output so WP doesn't also load twice
            if ( 'http://' . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI']  == home_url( '/' . $theme->stylesheet . '-custom-css.css?ver=1.0.0', 'http' ) )
            {
                header( 'Content-Type: text/css' );
                $this->generate_css();

                die();
            }

            //  If the current URL requested is our customized js one, serve it up nicely with an include then kill any further output so WP doesn't also load twice
            if ( 'http://' . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI']  == home_url( '/' . $theme->stylesheet . '-custom-css.js?ver=1.0.0', 'http' ) )
            {
                header( 'Content-Type: text/javascript' );
                $this->generate_js();

                die();
            }
        }

        /**
         * CSS File generator code
         * Used in <themename>-custom-css.css
         */
        function generate_css()
        {
            foreach( $this->settings AS $setting )
            {
                echo $setting['object'] . " { " . $setting['selector'] . ": " . get_theme_mod( $setting['name'] ) . "; }\n";
            }
        }

        /**
         * JavaScript file generator code
         * Used in <themename>-custom-css.js for the responsive live previews
         */
        function generate_js()
        {
            echo 'jQuery(document).ready(function ($) {';

            foreach( $this->settings AS $setting )
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

            echo '});';
        }
    }