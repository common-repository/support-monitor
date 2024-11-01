<?php
namespace SupportMonitor\App;

/**
 * Admin Pages Handler
 */
class Admin {


    public function __construct() {         add_action( 'admin_menu', [ $this, 'admin_menu' ] );
    }

    /**
     * Register our menu page
     *
     * @return void
     */
    public function admin_menu() {         global $submenu;

        $capability = 'manage_options';
        $slug       = 'support-monitor';

        $hook = add_menu_page( __( 'Support Monitor', 'supportmonitor' ), __( 'Support Monitor', 'supportmonitor' ), $capability, $slug, [ $this, 'plugin_page' ], 'dashicons-text' );

        if ( current_user_can( $capability ) ) {
            $submenu[ $slug ][] = array( __( 'Unresolved Issues', 'supportmonitor' ), $capability, 'admin.php?page=' . $slug . '#/' ); // phpcs:ignore
            $submenu[ $slug ][] = array( __( 'Plugin List', 'supportmonitor' ), $capability, 'admin.php?page=' . $slug . '#/plugins' ); // phpcs:ignore
        }

        add_action( 'load-' . $hook, [ $this, 'init_hooks' ] );
    }

    /**
     * Initialize our hooks for the admin page
     *
     * @return void
     */
    public function init_hooks() {         add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
    }

    /**
     * Load scripts and styles for the app
     *
     * @return void
     */
    public function enqueue_scripts() {         // Main
        wp_enqueue_style( 'supportmonitor-admin' );
        wp_enqueue_script( 'supportmonitor-admin' );

        // Plugins
        wp_enqueue_style( 'tailwind' );
    }

    /**
     * Render our admin page
     *
     * @return void
     */
    public function plugin_page() {         echo '<div class="wrap"><div id="vue-admin-app"></div></div>';
    }
}
