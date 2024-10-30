<?php
/**
 * TinyMCE modal window.
 */

/**
 * @uses get_users()
 */

if ( ! defined('ABSPATH' ) ) {
	die( 'You are not allowed to call this page directly.' );
}

$hook_suffix = 'custom-user-avatar_tinymce-window';

?><!DOCTYPE html>
<html>
<head>
	<title><?php _e( 'WP User Avatar 2.0', 'custom-user-avatar' ); ?></title>
	<meta http-equiv="Content-Type" content="<?php bloginfo( 'html_type' ); ?>; charset=<?php echo get_option( 'blog_charset' ); ?>" />
	<base target="_self" />
	<?php
	/**
	 * Enqueue scripts.
	 *
	 * 
	 *
	 * @param string $hook_suffix The current admin page.
	 */
	do_action( 'admin_enqueue_scripts', $hook_suffix );

	/**
	 * Fires when styles are printed for this specific page based on $hook_suffix.
	 *
	 * 
	 */
	do_action( "admin_print_styles-{$hook_suffix}" ); // phpcs:ignore WordPress.NamingConventions.ValidHookName.UseUnderscores

	/**
	 * Fires when styles are printed for all admin pages.
	 *
	 * 
	 */
	do_action( 'admin_print_styles' );

	/**
	 * Fires when scripts are printed for this specific page based on $hook_suffix.
	 *
	 * 
	 */
	do_action( "admin_print_scripts-{$hook_suffix}" ); // phpcs:ignore WordPress.NamingConventions.ValidHookName.UseUnderscores

	/**
	 * Fires when scripts are printed for all admin pages.
	 *
	 * 
	 */
	do_action( 'admin_print_scripts' );

	/**
	 * Fires in head section for this specific admin page.
	 *
	 * The dynamic portion of the hook, `$hook_suffix`, refers to the hook suffix
	 * for the admin page.
	 *
	 * 
	 */
	do_action( "admin_head-{$hook_suffix}" ); // phpcs:ignore WordPress.NamingConventions.ValidHookName.UseUnderscores

	/**
	 * Fires in head section for all admin pages.
	 *
	 * 
	 */
	do_action( 'admin_head' );
	?>
</head>

<body id="link" class="wp-core-ui" onload="document.body.style.display='';" style="display:none;">
	<div id="wpua-tabs">
		<ul>
			<li><a href="#wpua"><?php _e( 'Profile Picture', 'custom-user-avatar' ); ?></a></li>
			<li><a href="#wpua-upload"><?php _e( 'Upload', 'custom-user-avatar' ); ?></a></li>
		</ul>

		<form name="wpUserAvatar" action="#">
			<div id="wpua">
				<p>
					<label for="<?php echo esc_attr( 'custom_default_user_avatar_user' ); ?>"><strong><?php _e( 'User Name', 'custom-user-avatar' ); ?>:</strong></label>

					<select id="<?php echo esc_attr( 'custom_default_user_avatar_user' ); ?>" name="<?php echo esc_attr( 'custom_default_user_avatar_user' ); ?>">
						<option value=""></option>

						<?php
						$users = get_users();

						foreach($users as $user) :
							?>

							<option value="<?php echo esc_attr( $user->user_login ); ?>"><?php echo esc_html( $user->display_name ); ?></option>

							<?php
						endforeach;
						?>
					</select>
				</p>

				<p>
					<label for="<?php echo esc_attr( 'custom_default_user_avatar_size' ); ?>"><strong><?php _e( 'Size:', 'custom-user-avatar' ); ?></strong></label>

					<select id="<?php echo esc_attr( 'custom_default_user_avatar_size' ); ?>" name="<?php echo esc_attr('custom_default_user_avatar_size'); ?>">
						<option value=""></option>
						<option value="original"><?php  _e( 'Original Size', 'custom-user-avatar' ); ?></option>
						<option value="large"><?php     _e( 'Large',         'custom-user-avatar' ); ?></option>
						<option value="medium"><?php    _e( 'Medium',        'custom-user-avatar' ); ?></option>
						<option value="thumbnail"><?php _e( 'Thumbnail',     'custom-user-avatar' ); ?></option>
						<option value="custom"><?php    _e( 'Custom',        'custom-user-avatar' ); ?></option>
					</select>
				</p>

				<p id="<?php echo esc_attr( 'custom_default_user_avatar_size_number_section' ); ?>">
					<label for="<?php echo esc_attr( 'custom_default_user_avatar_size_number' ); ?>"><?php _e( 'Size:', 'custom-user-avatar' ); ?></label>

					<input type="text" size="8" id="<?php echo esc_attr( 'custom_default_user_avatar_size_number' ); ?>" name="<?php echo esc_attr ( 'custom_default_user_avatar_size' ); ?>" value="" />
				</p>

				<p>
					<label for="<?php echo esc_attr( 'custom_default_user_avatar_align' ); ?>"><strong><?php _e( 'Alignment:', 'custom-user-avatar' ); ?></strong></label>

					<select id="<?php echo esc_attr( 'custom_default_user_avatar_align' ); ?>" name="<?php echo esc_attr( 'custom_default_user_avatar_align' ); ?>">
						<option value=""></option>
						<option value="center"><?php _e( 'Center','custom-user-avatar' ); ?></option>
						<option value="left"><?php   _e( 'Left',  'custom-user-avatar' ); ?></option>
						<option value="right"><?php  _e( 'Right', 'custom-user-avatar' ); ?></option>
					</select>
				</p>

				<p>
					<label for="<?php echo esc_attr( 'custom_default_user_avatar_link' ); ?>"><strong><?php _e( 'Link To:', 'custom-user-avatar' ); ?></strong></label>

					<select id="<?php echo esc_attr( 'custom_default_user_avatar_link' ); ?>" name="<?php echo esc_attr( 'custom_default_user_avatar_link' ); ?>">
						<option value=""></option>
						<option value="file"><?php       _e('Image File',     'custom-user-avatar'); ?></option>
						<option value="attachment"><?php _e('Attachment Page','custom-user-avatar'); ?></option>
						<option value="custom-url"><?php _e('Custom URL',     'custom-user-avatar'); ?></option>
					</select>
				</p>

				<p id="<?php echo esc_attr( 'custom_default_user_avatar_link_external_section' ); ?>">
					<label for="<?php echo esc_attr( 'custom_default_user_avatar_link_external' ); ?>"><?php _e( 'URL:', 'custom-user-avatar' ); ?></label>

					<input type="text" size="36" id="<?php echo esc_attr( 'custom_default_user_avatar_link_external' ); ?>" name="<?php echo esc_attr( 'custom_default_user_avatar_link_external' ); ?>" value="" />
				</p>

				<p>
					<label for="<?php echo esc_attr( 'custom_default_user_avatar_target' ); ?>"></label>

					<input type="checkbox" id="<?php echo esc_attr( 'custom_default_user_avatar_target' ); ?>" name="<?php echo esc_attr( 'custom_default_user_avatar_target' ); ?>" value="_blank" /> <strong><?php _e( 'Open link in a new window', 'custom-user-avatar' ); ?></strong>
				</p>

				<p>
					<label for="<?php echo esc_attr( 'custom_default_user_avatar_caption' ); ?>"><strong><?php _e( 'Caption', 'custom-user-avatar' ); ?>:</strong></label>

					<textarea cols="36" rows="2" id="<?php echo esc_attr( 'custom_default_user_avatar_caption' ); ?>" name="<?php echo esc_attr( 'custom_default_user_avatar_size' ); ?>"></textarea>
				</p>

				<div class="mceActionPanel">
					<input type="submit" id="insert" class="button-primary" name="insert" value="<?php _e( 'Insert into Post', 'custom-user-avatar' ); ?>" onclick="wpuaInsertAvatar();" />
				</div>
			</div>

			<div id="wpua-upload" style="display:none;">
				<p id="<?php echo esc_attr( 'custom_default_user_avatar_upload' ); ?>">
					<label for="<?php echo esc_attr( 'custom_default_user_avatar_upload' ); ?>"><strong><?php _e( 'Upload', 'custom-user-avatar' ); ?>:</strong></label>

					<input type="text" size="36" id="<?php echo esc_attr( 'custom_default_user_avatar_upload' ); ?>" name="<?php echo esc_attr( 'custom_default_user_avatar_upload' ); ?>" value="<?php echo esc_attr( '[avatar_upload]' ); ?>" readonly="readonly" />
				</p>

				<div class="mceActionPanel">
					<input type="submit" id="insert" class="button-primary" name="insert" value="<?php _e( 'Insert into Post', 'custom-user-avatar' ); ?>" onclick="wpuaInsertAvatarUpload();" />
				</div>
			</div>
		</form>
	</div>
</body>
</html>
