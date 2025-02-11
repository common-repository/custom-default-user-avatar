<?php
/**
 * Admin page to change plugin options.
 */

global $show_avatars,
       $upload_size_limit_with_units,
	   $wpua_admin,
	   $wpua_allow_upload,
	   $wpua_force_file_uploader,
	   $wpua_disable_gravatar,
	   $wpua_edit_avatar,
	   $wpua_resize_crop,
	   $wpua_resize_h,
	   $wpua_resize_upload,
	   $wpua_resize_w,
	   $wpua_subscriber,
	   $wpua_tinymce,
	   $wpua_upload_size_limit,
	   $wpua_upload_size_limit_with_units;

$updated = false;
$settingsupdated = filter_input(INPUT_GET, 'settings-updated');
if ( isset($settingsupdated  ) && 'true' == $settingsupdated ) {
	$updated = true;
}

$hide_size   = true !== (bool) $wpua_allow_upload  ? ' style="display:none;"' : '';
$hide_resize = true !== (bool) $wpua_resize_upload ? ' style="display:none;"' : '';

$wpua_options_page_title = __( 'WP User Avatar 2.0', 'custom-user-avatar' );

/**
 * Filter admin page title
 * @since 1.9
 * @param string $wpua_options_page_title
 */
$wpua_options_page_title = apply_filters( 'wpua_options_page_title', $wpua_options_page_title );
?>

<div class="wrap">
	<h2><?php echo esc_html($wpua_options_page_title); ?></h2>

	<table>
		<tr valign="top">
			<td align="top">
				<form method="post" action="<?php echo admin_url('options.php'); ?>">

					<?php settings_fields('wpua-settings-group'); ?>

					<?php do_settings_fields('wpua-settings-group', ""); ?>

					<table class="form-table">
						<?php
							// Format settings in table rows
							$wpua_before_settings = array();

							/**
							 * Filter settings at beginning of table
							 * @since 1.9
							 * @param array $wpua_before_settings
							 */
							$wpua_before_settings = apply_filters( 'wpua_before_settings', $wpua_before_settings );

							echo implode( '', $wpua_before_settings );
						?>

						<tr valign="top">
							<th scope="row"><?php _e( 'Settings', 'custom-user-avatar' ); ?></th>

							<td>
								<?php
									// Format settings in fieldsets
									$wpua_settings = array();

									$wpua_settings['tinymce'] = sprintf(
										'<fieldset>
											<label for="wp_user_avatar_tinymce">
												<input name="wp_user_avatar_tinymce" type="checkbox" id="wp_user_avatar_tinymce" value="1" %s />
												%s
											</label>
										</fieldset>',
										checked( $wpua_tinymce, true, false ),
										__( 'Add avatar button to Visual Editor', 'custom-user-avatar' )
									);

									$wpua_settings['upload'] = sprintf(
										'<fieldset>
											<label for="wp_user_avatar_allow_upload">
												<input name="wp_user_avatar_allow_upload" type="checkbox" id="wp_user_avatar_allow_upload" value="1" %s />
												%s
											</label>
										</fieldset>',
										checked( $wpua_allow_upload, true, false ),
										__( 'Allow Contributors & Subscribers to upload avatars', 'custom-user-avatar' )
									);

									$wpua_settings['gravatar'] = sprintf(
										'<fieldset>
											<label for="wp_user_avatar_disable_gravatar">
												<input name="wp_user_avatar_disable_gravatar" type="checkbox" id="wp_user_avatar_disable_gravatar" value="1" %s />
												%s
											</label>
										</fieldset>',
										checked( $wpua_disable_gravatar, true, false ),
										__( 'Disable Gravatar and use only local avatars', 'custom-user-avatar' )
									);

									$wpua_settings['force_file_uploader'] = sprintf(
										'<fieldset>
											<label for="wp_user_avatar_force_file_uploader">
												<input name="wp_user_avatar_force_file_uploader" type="checkbox" id="wp_user_avatar_force_file_uploader" value="1" %s />
												%s
											</label>
											<p class="description">%s</p>
										</fieldset>',
										checked( $wpua_force_file_uploader, true, false ),
										__( 'Always use the browser file uploader to upload avatars', 'custom-user-avatar' ),
										__( 'Check this if another plugin is conflicting with the WordPress Media Uploader.', 'custom-user-avatar' )
									);

									/**
									 * Filter main settings
									 * @since 1.9
									 * @param array $wpua_settings
									 */
									$wpua_settings = apply_filters( 'wpua_settings', $wpua_settings );

									echo implode( '', $wpua_settings );
								?>
							</td>
						</tr>
					</table>

					<?php
						// Format settings in table
						$wpua_subscriber_settings = array();

						ob_start();
					?>

					<div id="wpua-contributors-subscribers"<?php echo $hide_size; ?>>
						<table class="form-table">
							<tr valign="top">
								<th scope="row">
									<label for="wp_user_avatar_upload_size_limit">'
										<?php _e( 'Upload Size Limit', 'custom-user-avatar' ); ?>
										<?php _e( '(only for Contributors & Subscribers)', 'custom-user-avatar' ); ?>
									</label>
								</th>

								<td>
									<fieldset>
										<legend class="screen-reader-text">
											<span>
												<?php _e( 'Upload Size Limit', 'custom-user-avatar' ); ?>
												<?php _e( '(only for Contributors & Subscribers)', 'custom-user-avatar' ); ?>
											</span>
										</legend>

										<input name="wp_user_avatar_upload_size_limit" type="range" id="wp_user_avatar_upload_size_limit" value="<?php echo esc_attr( $wpua_upload_size_limit ); ?>" min="0" max="<?php echo esc_attr( wp_max_upload_size() ); ?>" class="regular-text" />

										<span id="wpua-readable-size"><?php echo esc_html($wpua_upload_size_limit_with_units); ?></span>

										<span id="wpua-readable-size-error"><?php printf(
											/* translators: file name */
											__( '%s exceeds the maximum upload size for this site.', 'custom-user-avatar' ),
											''
										); ?></span>

										<p class="description">
											<?php
												printf(
													/* translators: file size in KB */
													__( 'Maximum upload file size: %s.', 'custom-user-avatar' ),
													esc_html( wp_max_upload_size() ) . esc_html( sprintf( ' bytes (%s)', $upload_size_limit_with_units ) )
												);
											?>
										</p>
									</fieldset>

									<fieldset>
										<label for="wp_user_avatar_edit_avatar">
											<input name="wp_user_avatar_edit_avatar" type="checkbox" id="wp_user_avatar_edit_avatar" value="1" <?php checked( $wpua_edit_avatar ); ?> />

											<?php _e( 'Allow users to edit avatars', 'custom-user-avatar' ); ?>
										</label>
									</fieldset>

									<fieldset>
										<label for="wp_user_avatar_resize_upload">
											<input name="wp_user_avatar_resize_upload" type="checkbox" id="wp_user_avatar_resize_upload" value="1" <?php checked( $wpua_resize_upload ); ?> />

											<?php _e( 'Resize avatars on upload', 'custom-user-avatar' ); ?>
										</label>
									</fieldset>

									<fieldset id="wpua-resize-sizes"'.$hide_resize.'>
										<label for="wp_user_avatar_resize_w"><?php _e( 'Width', 'custom-user-avatar' ); ?></label>

										<input name="wp_user_avatar_resize_w" type="number" step="1" min="0" id="wp_user_avatar_resize_w" value="<?php echo esc_attr( get_option( 'wp_user_avatar_resize_w' ) ); ?>" class="small-text" />

										<label for="wp_user_avatar_resize_h"><?php _e( 'Height', 'custom-user-avatar' ); ?></label>

										<input name="wp_user_avatar_resize_h" type="number" step="1" min="0" id="wp_user_avatar_resize_h" value="<?php echo esc_attr( get_option( 'wp_user_avatar_resize_h' ) ); ?>" class="small-text" />

										<br />

										<input name="wp_user_avatar_resize_crop" type="checkbox" id="wp_user_avatar_resize_crop" value="1" <?php checked( '1', $wpua_resize_crop ); ?> />

										<label for="wp_user_avatar_resize_crop"><?php _e( 'Crop avatars to exact dimensions', 'custom-user-avatar' ); ?></label>
									</fieldset>
								</td>
							</tr>
						</table>
					</div>

					<?php
						$wpua_subscriber_settings['subscriber-settings'] = ob_get_clean();

						/**
						 * Filter Subscriber settings
						 * @since 1.9
						 * @param array $wpua_subscriber_settings
						 */
						$wpua_subscriber_settings = apply_filters( 'wpua_subscriber_settings', $wpua_subscriber_settings );

						echo implode( '', $wpua_subscriber_settings );
					?>

					<table class="form-table">
						<tr valign="top">
							<th scope="row"><?php _e( 'Avatar Display', 'custom-user-avatar' ); ?></th>

							<td>
								<fieldset>
									<legend class="screen-reader-text">
										<span>
											<?php _e( 'Avatar Display', 'custom-user-avatar' ); ?>
										</span>
									</legend>

									<label for="show_avatars">
										<input type="checkbox" id="show_avatars" name="show_avatars" value="1" <?php checked( $show_avatars ); ?> />

										<?php _e( 'Show Avatars', 'custom-user-avatar' ); ?>
									</label>
								</fieldset>
							</td>
						</tr>
                                               
						<tr valign="top" id="avatar-rating" <?php echo ( 1 == $wpua_disable_gravatar ) ? ' style="display:none"' : '' ?>>
							<th scope="row"><?php _e( 'Maximum Rating', 'custom-user-avatar' ); ?></th>

							<td>
								<fieldset>
									<legend class="screen-reader-text">
										<span>
											<?php _e( 'Maximum Rating', 'custom-user-avatar' ); ?>
										</span>
									</legend>

									<?php
										$ratings = array(
											'G'  => __( 'G &#8212; Suitable for all audiences', 'custom-user-avatar' ),
											'PG' => __( 'PG &#8212; Possibly offensive, usually for audiences 13 and above', 'custom-user-avatar' ),
											'R'  => __( 'R &#8212; Intended for adult audiences above 17', 'custom-user-avatar' ),
											'X'  => __( 'X &#8212; Even more mature than above', 'custom-user-avatar' ),
										);

										foreach ( $ratings as $key => $rating ) :
											?>
											<label>
												<input type="radio" name="avatar_rating" value="<?php echo esc_attr( $key ); ?>" <?php checked( $key, get_option( 'avatar_rating' ) ); ?> />
												<?php echo esc_html($rating); ?>
											</label>

											<br />
											<?php
										endforeach;
									?>
								</fieldset>
							</td>
						</tr>

						<tr valign="top">
							<th scope="row"><?php _e( 'Default Avatar', 'custom-user-avatar' ); ?></th>

							<td class="defaultavatarpicker">
								<fieldset>
									<legend class="screen-reader-text">
										<span>
											<?php _e( 'Default Avatar', 'custom-user-avatar' ); ?>
										</span>
									</legend>

									<?php _e( 'For users without a custom avatar of their own, you can either display a generic logo or a generated one based on their e-mail address.', 'custom-user-avatar' ); ?>

									<br />

									<?php echo $wpua_admin->wpua_add_default_avatar(); ?>
								</fieldset>
							</td>
						</tr>
					</table>

					<?php submit_button(); ?>
				</form>
			</td>
		</tr>
	</table>
</div>
