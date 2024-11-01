<?php
namespace SupportMonitor\App\Api;

use SupportMonitor\App\Models\SMPlugins;
use WP_REST_Controller;

/**
 * REST_API Handler
 */
class Plugins extends WP_REST_Controller {


    /**
     * Plugins constructor.
     */
    public function __construct() {         $this->namespace = 'supportmonitor/v1/api';
        $this->rest_base = '/plugins';
    }

    /**
     * Register the routes
     *
     * @return void
     */
    public function register_routes() {
        register_rest_route(
            $this->namespace,
            '/' . $this->rest_base,
            array(
                array(
                    'methods'             => \WP_REST_Server::READABLE,
                    'callback'            => array( $this, 'get_plugins' ),
                    'permission_callback' => array( $this, 'get_items_permissions_check' ),
                ),
            )
        );

        register_rest_route(
            $this->namespace,
            '/' . $this->rest_base,
            array(
                array(
                    'methods'             => \WP_REST_Server::CREATABLE,
                    'callback'            => array( $this, 'store_plugin' ),
                    'permission_callback' => array( $this, 'get_items_permissions_check' ),
                    'args'                => $this->validate_plugin_store_request(),
                ),
            )
        );

        register_rest_route(
            $this->namespace,
            '/' . $this->rest_base,
            array(
                array(
                    'methods'             => \WP_REST_Server::DELETABLE,
                    'callback'            => array( $this, 'delete_plugin' ),
                    'permission_callback' => array( $this, 'get_items_permissions_check' ),
                    'args'                => $this->validate_plugin_get_request(),
                ),
            )
        );
    }

    /**
     * Retrieves a collection of items.
     * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
     */
    public function get_plugins() {
        try {
            global $wpdb;

            // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
            $plugins = $wpdb->get_results( "SELECT * FROM {$this->get_table()}", ARRAY_A );

            return rest_ensure_response( $plugins );
        } catch ( \Exception $e ) {
            return new \WP_Error(
                'record-fetch-error',
                $e->getMessage(),
                array( 'status' => $e->getCode() )
            );
        }
    }

    /**
     * Retrieves a collection of items.
     *
     * @param WP_REST_Request $request Full details about the request.
     *
     * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
     */
    public function store_plugin( $request ) {
        try {
            global $wpdb;

            $plugin = $wpdb->insert(
                $this->get_table(), [
					'slug' => $request['slug'],
				]
            );

            return rest_ensure_response( $plugin );
        } catch ( \Exception $e ) {
            return new \WP_Error(
                'record-fetch-error',
                $e->getMessage(),
                array( 'status' => $e->getCode() )
            );
        }
    }

    /**
     * Retrieves a collection of items.
     *
     * @param WP_REST_Request $request Full details about the request.
     *
     * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
     */
    public function delete_plugin( $request ) {
        try {
            global $wpdb;
            // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
            $wpdb->query( "DELETE FROM {$this->get_table()} WHERE `id` IN ({$request['id']})" );

            return rest_ensure_response( 'Deleted Successfully' );
        } catch ( \Exception $e ) {
            return new \WP_Error(
                'record-fetch-error',
                $e->getMessage(),
                array( 'status' => $e->getCode() )
            );
        }
    }

    /**
     * Checks if a given request has access to read the items.
     *
     * @param WP_REST_Request $request Full details about the request.
     *
     * @return true|WP_Error True if the request has read access, WP_Error object otherwise.
     */
    public function get_items_permissions_check( $request ) {
        return true;
    }

    /**
     * Retrieves the query params for the items collection.
     *
     * @return array Collection parameters.
     * // TODO:: need unique validation check
     */
    public function validate_plugin_store_request() {
        return [
            'slug' => array(
                'required' => true,
                'type' => 'string',
                'description' => 'Plugin Slug',
                'minLength' => 1,
                'maxLength' => 255,
            ),
	    ];
    }

    /**
     * Retrieves the query params for the items collection.
     */
    public function validate_plugin_get_request() {
        return [
            'id' => array(
                'required' => true,
                'type' => 'integer',
                'description' => 'Plugin ID',
                'minLength' => 1,
                'maxLength' => 9,
            ),
	    ];
    }

    protected function get_table() {
        global $wpdb;

        return $wpdb->prefix . 'sm_plugins';
    }
}
