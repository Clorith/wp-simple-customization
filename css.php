<?php
/**
 * User: Marius Jensen
 * Date: 26.04.13
 * Time: 10:44
 */

    class css
    {
        private $sections = array();
        public $settings = array();

        function add( $name, $type, $args = array() )
        {
            //  First we set the name, this is an always existent constant, and it's nice ot ahve it as the first data point
            $array = array(
                'name' => $name
            );

            //  Next, iterate over the arguments array and insert them accordingly
            foreach ( $args AS $item => $data )
            {
                $array[$item] = $data;
            }

            //  Finally, enter the data into the apropriate data container
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

        function build( $custom )
        {
            //  Loop through the defined sections, sections hold our settings so it makes sense to define these first
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

                //  Since a setting also requires a controller, we initiate the controller straight away
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

        function style() {
            $theme = wp_get_theme();

            wp_register_style( $theme->stylesheet . '-custom-css', home_url( '/' . $theme->stylesheet . '-custom-css.css' ), false, '1.0.0' );

            wp_enqueue_style( $theme->stylesheet . '-custom-css' );
        }
        function style_customize() {
            $theme = wp_get_theme();

            wp_register_script( $theme->stylesheet . '-custom-js', home_url( '/' . $theme->stylesheet . '-custom-css.js' ), array( 'jquery', 'customize-preview' ), '1.0.0', true );

            wp_enqueue_script( $theme->stylesheet . '-custom-js' );
        }
        function init_build() {
            $theme = wp_get_theme();

            //  If the current URL requested is our customized css one, serve it up nicely with an include then kill any further output so WP doens't also load twice
            if ( 'http://' . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI']  == home_url( '/' . $theme->stylesheet . '-custom-css.css?ver=1.0.0', 'http' ) )
            {
                header( 'Content-Type: text/css' );
                include_once( dirname( __FILE__ ) . '/style.css.php' );

                die();
            }

            //  If the current URL requested is our customized css one, serve it up nicely with an include then kill any further output so WP doens't also load twice
            if ( 'http://' . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI']  == home_url( '/' . $theme->stylesheet . '-custom-css.js?ver=1.0.0', 'http' ) )
            {
                header( 'Content-Type: text/javascript' );
                include_once( dirname( __FILE__ ) . '/style.js.php' );

                die();
            }
        }
    }

    $css = new css();

    add_action( 'customize_register', array( $css, 'build' ) );
    add_action( 'wp_enqueue_scripts', array( $css, 'style' ) );
    add_action( 'customize_preview_init', array( $css, 'style_customize' ) );
    add_action( 'init', array( $css, 'init_build' ) );
