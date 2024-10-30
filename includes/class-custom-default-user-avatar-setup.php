<?php
/**
 * Let's get started!
 */

class Custom_Default_User_Avatar_Setup {
	/**
	 * Constructor
	 * @since 1.9.2
	 */
	public function __construct() {
		$this->_define_constants();
		$this->_load_wp_includes();
		$this->_load_wpua();
	}

	/**
	 * Define paths
	 * @since 1.9.2
	 */
	private function _define_constants() {
		define( 'WPUA_VERSION',    '2.3.5' );
		define( 'WPUA_FOLDER',     basename( dirname( Custom_User_Avatar::plugin_file_path() ) ) );
		define( 'WPUA_DIR',        Custom_User_Avatar::plugin_dir_path() );
		define( 'WPUA_INC',        WPUA_DIR . 'includes' . '/' );
		define( 'WPUA_URL',        plugin_dir_url( WPUA_FOLDER ) . WPUA_FOLDER . '/' );
		define( 'WPUA_ASSETS_URL', WPUA_URL . 'assets'.'/' );
		define( 'WPUA_CSS_URL',    WPUA_ASSETS_URL . 'css'.'/' );
		define( 'WPUA_JS_URL',     WPUA_ASSETS_URL . 'js'.'/' );
		define( 'WPUA_IMG_URL',    WPUA_ASSETS_URL . 'images'.'/' );
		define( 'WPUA_INC_URL',    WPUA_URL . 'includes'.'/' );
	}

	/**
	 * WordPress includes used in plugin
	 * @since 1.9.2
	 * @uses is_admin()
	 */
	private function _load_wp_includes() {
		if ( ! is_admin() ) {
			// wp_handle_upload
			require_once( ABSPATH . 'wp-admin/includes/file.php' );

			// wp_generate_attachment_metadata
			require_once( ABSPATH . 'wp-admin/includes/image.php' );

			// image_add_caption
			require_once( ABSPATH . 'wp-admin/includes/media.php' );

			// submit_button
			require_once( ABSPATH . 'wp-admin/includes/template.php' );
		}

		// add_screen_option
		require_once( ABSPATH . 'wp-admin/includes/screen.php' );
	}

	/**
	 * Load WP User Avatar 2.0
	 * @since 1.9.2
	 * @uses bool $wpua_tinymce
	 * @uses is_admin()
	 */
	private function _load_wpua() {
		global $wpua_tinymce;

		require_once( WPUA_INC . 'wpua-globals.php' );
		require_once( WPUA_INC . 'wpua-functions.php' );
		require_once( WPUA_INC . 'class-custom-default-user-avatar-admin.php' );
		require_once( WPUA_INC . 'class-custom-default-user-avatar.php' );
		require_once( WPUA_INC . 'class-custom-default-user-avatar-functions.php' );
		require_once( WPUA_INC . 'class-custom-default-user-avatar-shortcode.php' );
		require_once( WPUA_INC . 'class-custom-default-user-avatar-subscriber.php' );
		require_once( WPUA_INC . 'class-custom-default-user-avatar-update.php' );
		require_once( WPUA_INC . 'class-custom-default-user-avatar-widget.php' );

		// Load TinyMCE only if enabled
		if ( 1 == (bool) $wpua_tinymce ) {
			require_once( WPUA_INC.'wpua-tinymce.php' );
		}
	}
}

function custom_default_user_avatar_setup() {
	global $custom_default_user_avatar_setup;

	if ( ! isset( $custom_default_user_avatar_setup ) ) {
		$custom_default_user_avatar_setup = new Custom_Default_User_Avatar_Setup();
	}

	return $custom_default_user_avatar_setup;
}

/**
 * Initialize
 */
custom_default_user_avatar_setup();
