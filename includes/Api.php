<?php
namespace SupportMonitor\App;

require_once __DIR__ . './../vendor/autoload.php';

use WP_REST_Controller;

/**
 * REST_API Handler
 */
class Api extends WP_REST_Controller {


    /**
     * [__construct description]
     */
    public function __construct() {         $this->includes();

        add_action( 'rest_api_init', [ $this, 'register_routes' ] );
    }

    /**
     * Include the controller classes
     *
     * @return void
     */
    private function includes() {         if ( ! class_exists( __NAMESPACE__ . '\Api\Plugins' ) ) {
            include_once __DIR__ . '/Api/Plugins.php';
	}
	if ( ! class_exists( __NAMESPACE__ . '\Api\UnresolvedIssues' ) ) {
		include_once __DIR__ . '/Api/UnresolvedIssues.php';
	}
    }

    /**
     * Register the API routes
     *
     * @return void
     */
    public function register_routes() {         ( new Api\Plugins() )->register_routes();
        ( new Api\UnresolvedIssues() )->register_routes();
    }

}
