<?php
/**
 * Defines widgets.
 */

class Custom_Default_User_Avatar_Profile_Widget extends WP_Widget {
	/**
	 * Constructor
	 * @since 1.9.4
	 */
	public function __construct() {
		$widget_ops = array(
			'classname'   => 'widget_custom_default_user_avatar',
			'description' => sprintf(
				/* translators: [avatar_upload] shortcode */
				__( 'Insert %s', 'custom-user-avatar' ),
				'[avatar_upload]'
			),
		);

		parent::__construct( 'custom_default_user_avatar_profile', __( 'WP User Avatar 2.0', 'custom-user-avatar' ), $widget_ops );
	}

	
	public function widget($args, $instance) {
		global $custom_default_user_avatar, $wpua_allow_upload, $wpua_shortcode;

		extract( $args );

		$instance = apply_filters( 'wpua_widget_instance', $instance );
		$title    = apply_filters( 'widget_title', empty( $instance['title'] ) ? '' : $instance['title'], $instance, $this->id_base );
		$text     = apply_filters( 'widget_text',  empty( $instance['text'] )  ? '' : $instance['text'],  $instance );

		// Show widget only for users with permission
		if ( $custom_default_user_avatar->wpua_is_author_or_above() || ( 1 == (bool) $wpua_allow_upload && is_user_logged_in() ) ) {
			echo $before_widget;

			if ( ! empty( $title ) ) {
				echo $before_title . $title . $after_title;
			}

			if ( ! empty( $text ) ) {
				echo '<div class="textwidget">';
				echo ! empty( $instance['filter'] ) ? wpautop($text) : $text;
				echo '</div>';
			}

			// Remove profile title
			add_filter( 'wpua_profile_title', '__return_null' );

			// Get [avatar_upload] shortcode
			echo $wpua_shortcode->wpua_edit_shortcode( '' );

			// Add back profile title
			remove_filter('wpua_profile_title', '__return_null');
		}
	}

	/**
	 * Set title
	 * @since 1.9.4
	 * @param array $instance
	 * @uses wp_parse_args()
	 */
	public function form( $instance ) {
		$instance = wp_parse_args( (array) $instance, array(
			'title' => '',
			'text'  => '',
		) );
		?>

		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>">
				<?php _e( 'Title:', 'custom-user-avatar' ); ?>
			</label>

			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name('title') ); ?>" type="text" value="<?php echo esc_attr( wp_kses( $instance['title'], 'data' ) ); ?>" />
		</p>

		<label for="<?php echo esc_attr( $this->get_field_id( 'filter' ) ); ?>"><?php _e( 'Description:', 'custom-user-avatar' ); ?></label>

		<textarea class="widefat" rows="3" cols="20" id="<?php echo esc_attr( $this->get_field_id( 'text' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'text' ) ); ?>"><?php echo esc_textarea( $instance['text'] ); ?></textarea>

		<p>
			<input id="<?php echo esc_attr( $this->get_field_id( 'filter' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'filter' ) ); ?>" type="checkbox" <?php checked( isset( $instance['filter'] ) ? $instance['filter'] : 0 ); ?> />

			<label for="<?php echo esc_attr( $this->get_field_id( 'filter' ) ); ?>">
				<?php _e( 'Automatically add paragraphs', 'custom-user-avatar' ); ?>
			</label>
		</p>
		<?php
	}

	/**
	 * Update widget
	 * @since 1.9.4
	 * @param array $new_instance
	 * @param array $old_instance
	 * @uses current_user_can()
	 * @return array
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = $old_instance;

		$instance['title'] = wp_kses( $new_instance['title'], 'data' );

		if ( current_user_can( 'unfiltered_html' ) ) {
			$instance['text'] =	$new_instance['text'];
		} else {
			$instance['text'] = stripslashes( wp_filter_post_kses( addslashes( $new_instance['text'] ) ) );
		}

		$instance['filter'] = isset( $new_instance['filter'] );

		return $instance;
	}
}
