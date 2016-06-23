<?php
/**
 * Plugin Name: WP For Android Application API
 * Plugin URI: http://www.thunderbeardesign.com/
 * Description: A Connection between your Android App and WordPress.
 * Version: 1.0
 * Author: Torbjorn Zetterlund
 * Author URI: http://www.torbjornzetterlund.com/
 * Requires at least: 3.7
 * Tested up to: 4.1.1
 *
 * @package WP Android
 * @category Core
 * @author Torbjorn Zetterlund
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

define ('WP2ANDROID_PLUGIN_VERSION'  , '1.0');
define ('WP2ANDROID_REQUIRED_VERSION', '3.7');
define ('WP2ANDROID_PLUGIN_FILE'	   , __FILE__);

final class WP2ANDROID {

	/**
	 * @var string
	 */
	public $version = '1.0';

	/**
	 * WordPress2Android Constructor.
	 * @access public
	 */
	public function __construct() {
		
		//Load Requried Files
		$this->include_required_functions();
		
		// Hooks
		add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this, 'action_links' ) );
		add_action( 'after_setup_theme', array( $this, 'setup_environment' ) );
		register_activation_hook( __FILE__, array( $this, 'install' ) );
		
		//Initial API Class
		$wp2android_api = new WP2ANDROID_API();
		$wp2android_settings = new WP2ANDROID_Settings();
		add_action( 'init', array( $this, 'wp2android_flush_rewrite_rules_maybe'), 20 );
	}

	/**
	 * WordPress2Android Installer/Flush Rewrite Rules.
	 * @access public
	 */
	public function install() {
		if ( ! get_option( 'wp2android_flush_rewrite_rules_flag' ) ) 
			add_option( 'wp2android_flush_rewrite_rules_flag', true );
	}
	
	function wp2android_flush_rewrite_rules_maybe() {
		if ( get_option( 'wp2android_flush_rewrite_rules_flag' ) ) {
			flush_rewrite_rules();
			delete_option( 'wp2android_flush_rewrite_rules_flag' );
		}
	}
	
	
	/**
	 * WordPress2Android Plugin Action Links.
	 * @access public
	 */
	public function action_links( $links ) {
		return array_merge( array(
			'<a href="' . admin_url( 'options-general.php?page=wp2android-settings' ) . '">' . __( 'Settings', 'wp2android' ) . '</a>',
		), $links );
	}
	/**
	 * Function used to Init WordPress2Android Template Functions.
	 */
	public function include_required_functions() {
		require_once( 'includes/functions.php' );
		require_once( 'includes/class-api.php' );
		require_once( 'includes/class-api-server-instance.php' );
		require_once( 'includes/class-api-server.php' );
		require_once( 'includes/class-settings.php' );
	}
	
	/**
	 * Ensure theme and server variable compatibility and setup image sizes..
	 */
	public function setup_environment() {
		// Post thumbnail support
		if ( ! current_theme_supports( 'post-thumbnails' ) ) {
			add_theme_support( 'post-thumbnails' );
		}

		// Add image sizes
		$app_thumbnail = wp2android_get_image_size( 'app_thumbnail' );
		$app_image     = wp2android_get_image_size( 'app_image' );

		add_image_size( 'wp2android_thumbnail', $app_thumbnail['width'], $app_thumbnail['height'], $app_thumbnail['crop'] );
		add_image_size( 'wp2android_image',     $app_image['width']    , $app_image['height']    , $app_image['crop'] );

		// IIS
		if ( ! isset($_SERVER['REQUEST_URI'] ) ) {
			$_SERVER['REQUEST_URI'] = substr( $_SERVER['PHP_SELF'], 1 );
			if ( isset( $_SERVER['QUERY_STRING'] ) ) {
				$_SERVER['REQUEST_URI'].='?'.$_SERVER['QUERY_STRING'];
			}
		}

		// NGINX Proxy
		if ( ! isset( $_SERVER['REMOTE_ADDR'] ) && isset( $_SERVER['HTTP_REMOTE_ADDR'] ) ) {
			$_SERVER['REMOTE_ADDR'] = $_SERVER['HTTP_REMOTE_ADDR'];
		}

		if ( ! isset( $_SERVER['HTTPS'] ) && ! empty( $_SERVER['HTTP_HTTPS'] ) ) {
			$_SERVER['HTTPS'] = $_SERVER['HTTP_HTTPS'];
		}

		// Support for hosts which don't use HTTPS, and use HTTP_X_FORWARDED_PROTO
		if ( ! isset( $_SERVER['HTTPS'] ) && ! empty( $_SERVER['HTTP_X_FORWARDED_PROTO'] ) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https' ) {
			$_SERVER['HTTPS'] = '1';
		}
	}
}

new WP2ANDROID();