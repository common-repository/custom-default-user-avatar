<?php
/**
 * TinyMCE button for Visual Editor.
 */

function wpua_add_buttons() {
	// Add only in Rich Editor mode
	if ( 'true' == get_user_option( 'rich_editing' ) ) {
		add_filter( 'mce_external_plugins', 'wpua_add_tinymce_plugin' );
		add_filter( 'mce_buttons', 'wpua_register_button' );
	}
}
add_action( 'init', 'wpua_add_buttons' );

/**
 * Register TinyMCE button
 * @param array $buttons
 * @return array
 */
function wpua_register_button( $buttons ) {
	array_push( $buttons, 'separator', 'wpUserAvatar' );

	return $buttons;
}

/**
 * Load TinyMCE plugin
 * @param array $plugin_array
 * @return array
 */
function wpua_add_tinymce_plugin( $plugins ) {
	$plugins['wpUserAvatar'] = WPUA_JS_URL . 'tinymce-editor_plugin.js';

	return $plugins;
}

function wpua_tinymce_enqueue_scripts( $hook_suffix ) {
	switch ( $hook_suffix ) {
		case 'custom-user-avatar_tinymce-window':
			wp_enqueue_style( 'custom-user-avatar-tinymce-window', WPUA_CSS_URL . 'tinymce-window.css' );

			wp_enqueue_script( 'jquery' );
                        
			wp_enqueue_script( 'custom-user-avatar-tinymce-popup',      includes_url( 'js/tinymce/tiny_mce_popup.js' ) );
			wp_enqueue_script( 'custom-user-avatar-tinymce-form-utils', includes_url( 'js/tinymce/utils/form_utils.js' ) );
			wp_enqueue_script( 'custom-user-avatar-tinymce-window',     WPUA_JS_URL . 'tinymce-window.js' );

			break;

		case 'post.php':
                    
			wp_localize_script( 'editor', 'custom_user_avatar_tinymce_editor_args', array(
				'insert_avatar' => __( 'Insert Avatar', 'custom-user-avatar' ),
			) );
                        
       

			break;
	}
}
add_action( 'admin_enqueue_scripts', 'wpua_tinymce_enqueue_scripts' );

/**
 * Call TinyMCE window content via admin-ajax
 * @since 1.4
 */
function wpua_ajax_tinymce() {
	include_once( WPUA_INC . 'wpua-tinymce-window.php' );

	die();
}
add_action( 'wp_ajax_wp_user_avatar_tinymce', 'wpua_ajax_tinymce' );

foreach ( array('post.php','post-new.php') as $hook ) {
     add_action( "admin_head-$hook", 'my_admin_head' );
}
 
/**
 * Localize Script
 */
function my_admin_head() {
    $plugin_url = plugins_url( '/', __FILE__ );
    ?>
<!-- TinyMCE Shortcode Plugin -->
<script type='text/javascript'>
var custom_user_avatar_tinymce_editor_args = {
    'insert_avatar': '<?php _e( 'Insert Avatar', 'custom-user-avatar' ); ?>',
};
</script>
<!-- TinyMCE Shortcode Plugin -->
    <?php
}