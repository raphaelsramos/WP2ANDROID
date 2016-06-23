<?php
/**
 * 
 *
 * Handles WP2ANDROID-API endpoint requests
 *
 * @author      ThunderBear Design
 * @category    API
 * @package     WPAndroid/API
 * @since       1.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class WP2ANDROID_API {

	/** This is the major version for the REST API and takes
	 * first-order position in endpoint URLs
	 */
	const VERSION = 1;

	/** @var BP3A_API_Server the REST API server */
	public $server;

	/**
	 * Setup class
	 *
	 * @access public
	 * @since 2.0
	 * @return WC_API
	 */
	public function __construct() {

		// add query vars
		add_filter( 'query_vars', array( $this, 'add_query_vars'), 0 );

		// register API endpoints
		add_action( 'init', array( $this, 'add_endpoint'), 0 );

		// handle REST/legacy API request
		add_action( 'parse_request', array( $this, 'handle_api_requests'), 0 );
		
		//Plugin Activation Request
		register_activation_hook( __FILE__, array( $this, 'install' ) );
	}
	
	// Flush rules after install
	public function install() {
		// Flush rules after install
		flush_rewrite_rules();
	}

	/**
	 * add_query_vars function.
	 *
	 * @access public
	 * @since 2.0
	 * @param $vars
	 * @return array
	 */
	public function add_query_vars( $vars ) {
		$vars[] = 'wp2android-api';
		$vars[] = 'wp2android-api-route';
		return $vars;
	}

	/**
	 * add_endpoint function.
	 *
	 * @access public
	 * @since 2.0
	 * @return void
	 */
	public function add_endpoint() {

		// REST API
		add_rewrite_rule( '^wp2android-api\/v' . self::VERSION . '/?$', 'index.php?wp2android-api-route=/', 'top' );
		add_rewrite_rule( '^wp2android-api\/v' . self::VERSION .'(.*)?', 'index.php?wp2android-api-route=$matches[1]', 'top' );

		// legacy API for payment gateway IPNs
		add_rewrite_endpoint( 'wp2android-api', EP_ALL );
	}

	/**
	 * API request - Trigger any API requests
	 *
	 * @access public
	 * @since 1.0
	 * @return void
	 */
	public function handle_api_requests() {
		global $wp;

		if ( ! empty( $_GET['wp2android-api'] ) )
			$wp->query_vars['wp2android-api'] = $_GET['wp2android-api'];

		if ( ! empty( $_GET['wp2android-api-route'] ) )
			$wp->query_vars['wp2android-api-route'] = $_GET['wp2android-api-route'];

		// REST API request
		if ( ! empty( $wp->query_vars['wp2android-api-route'] ) ) {

			define( 'WP2ANDROID_API_REQUEST', true );

			try {
				$API = new WP2ANDROID_API_Server($wp->query_vars['wp2android-api-route'], 'Hello');
				echo $API->processAPI();
			} catch (Exception $e) {
				echo json_encode(Array('error' => $e->getMessage()));
			}
			exit;
		}
	}
}
