<?php
/**
 * Media Library view of all avatars in use.
 */

/** WordPress Administration Bootstrap */
require_once( ABSPATH . 'wp-admin/admin.php' );

if ( ! current_user_can( 'upload_files' ) ) {
	wp_die( __( 'You do not have permission to upload files.', 'custom-user-avatar' ) );
}

global $wpua_admin;

$wp_list_table = $wpua_admin->_wpua_get_list_table( 'Custom_Default_User_Avatar_List_Table' );

$wp_list_table->prepare_items();

?>

<div class="wrap">
	<h2>
		<?php _e('WP User Avatar 2.0','custom-user-avatar'); ?>

		<?php if ( ! empty( $_REQUEST['s'] ) ) : ?>
			<span class="subtitle">
				<?php
				printf(
					/* translators: search query */
					__( 'Search results for %s','custom-user-avatar' ),
					sprintf( '&#8220;%s&#8221;', get_search_query() )
				);
				?>
			</span>
		<?php endif; ?>
	</h2>

	<?php
		$message = '';
                $deleted = filter_input(INPUT_GET, 'deleted');
             
		if ( ! empty($deleted) && $deleted = absint( $deleted ) ) {
			$message = sprintf(
				_n(
					'Media attachment permanently deleted.',
					'%d media attachments permanently deleted.',
					$deleted
				),
				number_format_i18n( $deleted )
			);

			$_SERVER['REQUEST_URI'] = remove_query_arg( array( 'deleted' ), $_SERVER['REQUEST_URI'] );
		}
	?>

	<?php if ( ! empty( $message ) ) : ?>
    <div id="message" class="updated"><p><?php echo esc_html($message); ?></p></div>
	<?php endif; ?>

	<?php $wp_list_table->views(); ?>

	<form id="posts-filter" action="" method="get">
		<?php $wp_list_table->search_box( __('Search','custom-user-avatar'), 'media' ); ?>

		<?php $wp_list_table->display(); ?>

		<div id="ajax-response"></div>

		<?php find_posts_div(); ?>

		<br class="clear" />
	</form>
</div>
