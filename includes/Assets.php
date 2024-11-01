<?php
namespace SupportMonitor\App;

/**
 * Scripts and Styles Class
 */
class Assets {


    public function __construct() {
        if ( is_admin() ) {
            add_action( 'admin_enqueue_scripts', [ $this, 'register' ], 5 );
        } else {
            add_action( 'wp_enqueue_scripts', [ $this, 'register' ], 5 );
        }
    }

    /**
     * Register our app scripts and styles
     *
     * @return void
     */
    public function register() {         $this->register_scripts( $this->get_scripts() );
        $this->register_styles( $this->get_styles() );
    }

    /**
     * Register scripts
     *
     * @param array $scripts
     *
     * @return void
     */
    private function register_scripts( $scripts ) {         foreach ( $scripts as $handle => $script ) {
            $deps      = isset( $script['deps'] ) ? $script['deps'] : false;
            $in_footer = isset( $script['in_footer'] ) ? $script['in_footer'] : false;
            $version   = isset( $script['version'] ) ? $script['version'] : SUPPORTMONITOR_VERSION;

            wp_register_script( $handle, $script['src'], $deps, $version, $in_footer );
	}
    }

    /**
     * Register styles
     *
     * @param array $styles
     *
     * @return void
     */
    public function register_styles( $styles ) {         foreach ( $styles as $handle => $style ) {
            $deps = isset( $style['deps'] ) ? $style['deps'] : false;

            wp_register_style( $handle, $style['src'], $deps, SUPPORTMONITOR_VERSION );
	}
    }

    /**
     * Get all registered scripts
     *
     * @return array
     */
    public function get_scripts() {         $prefix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '.min' : '';

        $scripts = [
            'supportmonitor-runtime' => [
                'src'       => SUPPORTMONITOR_ASSETS . '/js/runtime.js',
                'version'   => filemtime( SUPPORTMONITOR_PATH . '/assets/js/runtime.js' ),
                'in_footer' => true,
            ],
            'supportmonitor-vendor' => [
                'src'       => SUPPORTMONITOR_ASSETS . '/js/vendors.js',
                'version'   => filemtime( SUPPORTMONITOR_PATH . '/assets/js/vendors.js' ),
                'in_footer' => true,
            ],
            'supportmonitor-frontend' => [
                'src'       => SUPPORTMONITOR_ASSETS . '/js/frontend.js',
                'deps'      => [ 'jquery', 'supportmonitor-vendor', 'supportmonitor-runtime' ],
                'version'   => filemtime( SUPPORTMONITOR_PATH . '/assets/js/frontend.js' ),
                'in_footer' => true,
            ],
            'supportmonitor-admin' => [
                'src'       => SUPPORTMONITOR_ASSETS . '/js/admin.js',
                'deps'      => [ 'jquery', 'supportmonitor-vendor', 'supportmonitor-runtime' ],
                'version'   => filemtime( SUPPORTMONITOR_PATH . '/assets/js/admin.js' ),
                'in_footer' => true,
            ],
        ];

        return $scripts;
    }

    /**
     * Get registered styles
     *
     * @return array
     */
    public function get_styles() {
        $styles = [
            'supportmonitor-style' => [
                'src' => SUPPORTMONITOR_ASSETS . '/css/style.css',
            ],
            'supportmonitor-frontend' => [
                'src' => SUPPORTMONITOR_ASSETS . '/css/frontend.css',
            ],
            'supportmonitor-admin' => [
                'src' => SUPPORTMONITOR_ASSETS . '/css/admin.css',
            ],
            'tailwind' => [
                'src' => SUPPORTMONITOR_ASSETS . '/css/tailwind.css',
            ],

        ];

        return $styles;
    }
}
