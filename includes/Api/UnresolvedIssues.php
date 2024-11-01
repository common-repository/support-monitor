<?php
namespace SupportMonitor\App\Api;

use SupportMonitor\App\Models\SMPlugins;
use SupportMonitor\App\Services\SupportService;
use WP_REST_Controller;

/**
 * REST_API Handler
 */
class UnresolvedIssues extends WP_REST_Controller {


    /**
     * [__construct description]
     */
    public function __construct() {
        $this->namespace = 'supportmonitor/v1/api';
        $this->rest_base = '/issues';
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
                    'callback'            => array( $this, 'get_issues' ),
                    'permission_callback' => array( $this, 'get_items_permissions_check' ),
                    'args'                => $this->get_collection_params(),
                ),
            )
        );
    }

    /**
     * Retrieves a collection of items.
     *
     * @param WP_REST_Request $request Full details about the request.
     *
     * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
     */
    public function get_issues( $request ) {
        try {
            $service = new SupportService();
            if ( ! isset( $request['slug'] ) || $request['slug'] === '' ) {
                global $wpdb;

                // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
                $plugins = $wpdb->get_results( "SELECT * FROM {$this->get_table()}", ARRAY_A );

                foreach ( $plugins as $plugin ) {
                    $plugins_info[] = $service->get_unresolved_issues( $plugin['slug'], $request['hour'] );
                }
            } else {
                $plugins_info[] = $service->get_unresolved_issues( $request['slug'], $request['hour'] );
            }

            return rest_ensure_response( $plugins_info );
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
     */
    public function get_collection_params() {
        return [
            'slug' => array(
                'required' => false,
                'type' => 'string',
                'description' => 'Plugin Slug',
                'minLength' => 1,
                'maxLength' => 255,
            ),
            'hour' => array(
                'required' => true,
                'type' => 'integer',
                'description' => 'Plugin unresolved since',
                'minLength' => 0,
            ),
	    ];
    }

    protected function get_table() {
        global $wpdb;

        return $wpdb->prefix . 'sm_plugins';
    }
}
