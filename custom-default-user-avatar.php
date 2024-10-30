<?php
/*
Plugin Name:  WP User Avatar 2.0
Plugin URI:   https://wordpress.org/plugins/custom-default-user-avatar/
Description:  Use any image from your WordPress Media Library as a custom user avatar. Add your own Default Avatar.
Author:       chrismoretti
Author URI:   https://profiles.wordpress.org/chrismoretti/
Version:      1.1
Text Domain:  custom-user-avatar
Domain Path:  /languages/
*/

if ( ! defined( 'ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

class Custom_User_Avatar {
	/**
	 * Check for conflicts and load plugin
	 *
	 * 
	 */
	public function __construct() {
	
		// Load plugin
		require_once( plugin_dir_path( self::plugin_file_path() ) . 'includes/class-custom-default-user-avatar-setup.php' );
	}

	/**
	 * Access plugin file path globally
	 *
	 * 
	 */
	public static function plugin_file_path() {
		return __FILE__;
	}

	/**
	 * Access plugin directory path globally
	 *
	 * 
	 */
	public static function plugin_dir_path() {
		return plugin_dir_path( __FILE__ );
	}

	/**
	 * Print admin notice error in case of plugin conflict
	 *
	 * @since 2.3.1
	 */
	
}

/**
 * Load Plugin
 *
 * 
 */
function custom_user_avatar() {
	global $custom_user_avatar;

	if ( ! isset( $custom_user_avatar ) ) {
		$custom_user_avatar = new Custom_User_Avatar();
	}

	return $custom_user_avatar;
}
add_action( 'plugins_loaded', 'custom_user_avatar', 0 );
