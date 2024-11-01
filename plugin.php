<?php
/*
Plugin Name: Support Monitor
Plugin URI: https://github.com/emtiazzahid/wp-support-monitor
Description: WordPress Support Monitoring Plugin
Version: 1.0.3
Author: Emtiaz Zahid
Author URI: https://emtiazzahid.com/
License: GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: supportmonitor
Domain Path: /languages
*/

/**
 * Copyright (c) 2021 Emtiaz Zahid (email: emtiazzahid@gmail.com). All rights reserved.
 *
 * Released under the GPL license
 * http://www.opensource.org/licenses/gpl-license.php
 *
 * This is an add-on for WordPress
 * http://wordpress.org/
 *
 * **********************************************************************
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 * **********************************************************************
 */

// don't call the file directly

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once __DIR__ . '/vendor/autoload.php';

/**
 * Support_Monitor class
 *
 * @class Support_Monitor The class that holds the entire Support_Monitor plugin
 */
final class Support_Monitor {

    /**
     * Plugin version
     *
     * @var string
     */
    public $version = '1.0.3';

    /**
     * Holds various class instances
     *
     * @var array
     */
    private $container = array();

    /**
     * Constructor for the Support_Monitor class
     *
     * Sets up all the appropriate hooks and actions
     * within our plugin.
     */
    public function __construct() {
        $this->define_constants();

        register_activation_hook( __FILE__, array( $this, 'activate' ) );
        register_deactivation_hook( __FILE__, array( $this, 'deactivate' ) );

        add_action( 'plugins_loaded', array( $this, 'init_plugin' ) );
    }

    /**
     * Initializes the Support_Monitor() class
     *
     * Checks for an existing Support_Monitor() instance
     * and if it doesn't find one, creates it.
     */
    public static function init() {
        static $instance = false;

        if ( ! $instance ) {
            $instance = new Support_Monitor();
        }

        return $instance;
    }

    /**
     * Magic getter to bypass referencing plugin.
     *
     * @param $prop
     *
     * @return mixed
     */
    public function __get( $prop ) {
        if ( array_key_exists( $prop, $this->container ) ) {
            return $this->container[ $prop ];
        }

        return $this->{$prop};
    }

    /**
     * Magic isset to bypass referencing plugin.
     *
     * @param $prop
     *
     * @return mixed
     */
    public function __isset( $prop ) {
        return isset( $this->{$prop} ) || isset( $this->container[ $prop ] );
    }

    /**
     * Define the constants
     *
     * @return void
     */
    public function define_constants() {
        define( 'SUPPORTMONITOR_VERSION', $this->version );
        define( 'SUPPORTMONITOR_FILE', __FILE__ );
        define( 'SUPPORTMONITOR_PATH', dirname( SUPPORTMONITOR_FILE ) );
        define( 'SUPPORTMONITOR_INCLUDES', SUPPORTMONITOR_PATH . '/includes' );
        define( 'SUPPORTMONITOR_URL', plugins_url( '', SUPPORTMONITOR_FILE ) );
        define( 'SUPPORTMONITOR_ASSETS', SUPPORTMONITOR_URL . '/assets' );
    }

    /**
     * Load the plugin after all plugis are loaded
     *
     * @return void
     */
    public function init_plugin() {
        $this->includes();
        $this->init_hooks();
    }

    /**
     * Placeholder for activation function
     *
     * Nothing being called here yet.
     */
    public function activate() {
        $installed = get_option( 'supportmonitor_installed' );

        if ( ! $installed ) {
            update_option( 'supportmonitor_installed', time() );
        }

        $this->create_table_if_not_exist();

        update_option( 'supportmonitor_version', SUPPORTMONITOR_VERSION );
    }

    /**
     * Placeholder for deactivation function
     *
     * Nothing being called here yet.
     */
    public function deactivate() {
    }

    /**
     * Include the required files
     *
     * @return void
     */
    public function includes() {
        require_once SUPPORTMONITOR_INCLUDES . '/Assets.php';

        if ( $this->is_request( 'admin' ) ) {
            require_once SUPPORTMONITOR_INCLUDES . '/Admin.php';
        }

        if ( $this->is_request( 'frontend' ) ) {
            require_once SUPPORTMONITOR_INCLUDES . '/Frontend.php';
        }

        if ( $this->is_request( 'ajax' ) ) { // phpcs:ignore
            // require_once SUPPORTMONITOR_INCLUDES . '/class-ajax.php';
        }

        require_once SUPPORTMONITOR_INCLUDES . '/Api.php';
    }

    /**
     * Initialize the hooks
     *
     * @return void
     */
    public function init_hooks() {
        add_action( 'init', array( $this, 'init_classes' ) );

        // Localize our plugin
        add_action( 'init', array( $this, 'localization_setup' ) );
    }

    /**
     * Instantiate the required classes
     *
     * @return void
     */
    public function init_classes() {
        if ( $this->is_request( 'admin' ) ) {
            $this->container['admin'] = new SupportMonitor\App\Admin();
        }

        if ( $this->is_request( 'frontend' ) ) {
            $this->container['frontend'] = new SupportMonitor\App\Frontend();
        }

        $this->container['api'] = new SupportMonitor\App\Api();
        $this->container['assets'] = new SupportMonitor\App\Assets();
    }

    /**
     * Initialize plugin for localization
     *
     * @uses load_plugin_textdomain()
     */
    public function localization_setup() {
        load_plugin_textdomain( 'supportmonitor', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
    }

    /**
     * What type of request is this?
     *
     * @param  string $type admin, ajax, cron or frontend.
     *
     * @return bool
     */
    private function is_request( $type ) {
        switch ( $type ) {
            case 'admin':
                return is_admin();

            case 'ajax':
                return defined( 'DOING_AJAX' );

            case 'rest':
                return defined( 'REST_REQUEST' );

            case 'cron':
                return defined( 'DOING_CRON' );

            case 'frontend':
                return ( ! is_admin() || defined( 'DOING_AJAX' ) ) && ! defined( 'DOING_CRON' );
        }
    }

    /**
     * Create required table if not exists
     */
    private function create_table_if_not_exist() {
        global $table_prefix, $wpdb;

        $tblname = 'sm_plugins';
        $wp_track_table = $table_prefix . "$tblname ";
        $charset_collate = $wpdb->get_charset_collate();

        #Check to see if the table exists already, if not, then create it
        if ( $wpdb->get_var( "show tables like '$wp_track_table'" ) !== $wp_track_table ) { // phpcs:ignore
            $sql = "CREATE TABLE $wp_track_table (
            ID mediumint(9) NOT NULL AUTO_INCREMENT,
            `slug` varchar(255) NOT NULL,
            PRIMARY KEY  (ID)
            ) $charset_collate;"; // phpcs:ignore

            require_once ABSPATH . '/wp-admin/includes/upgrade.php';
            dbDelta( $sql );
        }
    }

} // Support_Monitor

$supportmonitor = Support_Monitor::init();
