<?php

class Custom_Default_User_Avatar_Resource_Manager {
	static $instance = null;

	public $marker = '<!-- custom_default_user_avatar_resource_manager_marker -->';

	var $buffer        = '';
	var $styles        = '';
	var $other_output  = '';
	var $wrote_footer  = false;
	var $run_shutdown  = false;
	var $valid_request = true;

	/**
	 * Start buffering all generated output. We'll then do two things with the buffer
	 * 1) Find stylesheets lately enqueued and move them to the header
	 * 2) Ensure that wp_print_footer_scripts() is called
	 * @since 1.9.5
	 */
	function __construct() {
		// Validate the request
		$this->validate_request();

		add_action( 'init',      array( &$this, 'start_buffer' ),  1 );
		add_action( 'wp_footer', array( &$this, 'print_marker' ), -1 );
	}

	/**
	 * Created early as possible in the wp_footer action this is the string to which we
	 * will move JS resources after
	 * @since 1.9.8
	 */
	function print_marker() {
		print $this->marker;
	}

	/**
	 * Determines if the resource manager should perform its routines for this request
	 * @since 1.9.5
	 * @return bool
	 */
	function validate_request() {
		$retval = true;

		if ( is_admin() ) {
			if ( isset($_REQUEST['page'] ) && ! preg_match( "#^(custom_default_user_avatar)#", $_REQUEST['page'] ) ) {
				$retval = false;
			}
		}

		if ( false !== strpos( $_SERVER['REQUEST_URI'], 'wp-admin/update') ) {
			$retval = false;
		} elseif ( isset( $_GET['display_gallery_iframe'] ) ) {
			$retval = false;
		} elseif ( defined( 'WP_ADMIN' ) && WP_ADMIN && defined( 'DOING_AJAX' ) && DOING_AJAX ) {
			$retval = false;
		} elseif ( preg_match( "/(js|css|xsl|xml|kml)$/", $_SERVER['REQUEST_URI'] ) ) {
			$retval = false;
		} elseif ( preg_match( "/\\.(\\w{3,4})$/", $_SERVER['REQUEST_URI'], $match ) ) {
			if ( ! in_array( $match[1], array( 'htm', 'html', 'php' ) ) ) {
				$retval = false;
			}
		}

		$this->valid_request = $retval;
	}

	/**
	 * Start the output buffers
	 * @since 1.9.5
	 */
	function start_buffer() {
		if( defined( 'WPUA_DISABLE_RESOURCE_MANAGER' ) && WPUA_DISABLE_RESOURCE_MANAGER ) {
			return;
		}

		if ( apply_filters('run_wpua_resource_manager', $this->valid_request ) ) {
			ob_start( array( &$this, 'output_buffer_handler' ) );
			ob_start( array( &$this, 'get_buffer' ) );

			add_action( 'wp_print_footer_scripts',    array( &$this, 'get_resources'), 1 );
			add_action( 'admin_print_footer_scripts', array( &$this, 'get_resources'), 1 );

			add_action( 'shutdown', array( &$this, 'shutdown' ) );
		}
	}

	/**
		* @since 1.9.5
	 */
	function get_resources() {
		ob_start();

		wp_print_styles();

		print_admin_styles();

		$this->styles = ob_get_clean();

		if ( ! is_admin() ) {
			ob_start();

			wp_print_scripts();

			$this->scripts = ob_get_clean();
		}

		$this->wrote_footer = true;
	}

	/**
	 * Output the buffer after PHP execution has ended (but before shutdown)
	 * @since 1.9.5
	 * @param string $content
	 * @return string
	 */
	function output_buffer_handler($content) {
		return $this->output_buffer();
	}

	/**
	 * Removes the closing </html> tag from the output buffer. We'll then write our own closing tag
	 * in the shutdown function after running wp_print_footer_scripts()
	 * @since 1.9.5
	 * @param string $content
	 * @return mixed
	 */
	function get_buffer($content) {
		$this->buffer = $content;

		return '';
	}


	/**
	 * Moves resources to their appropriate place
	 * @since 1.9.5
	 */
	function move_resources() {
		if ( $this->valid_request ) {
			// Move stylesheets to head
			if ( $this->styles ) {
				$this->buffer = str_ireplace( '</head>', $this->styles . '</head>', $this->buffer );
			}

			// Move the scripts to the bottom of the page
			if ( $this->scripts ) {
				$this->buffer = str_ireplace( $this->marker, $this->marker.$this->scripts, $this->buffer );
			}

			if( $this->other_output ) {
				$this->buffer = str_replace( $this->marker, $this->marker.$this->other_output, $this->buffer );
			}
		}
	}

	/**
	 * When PHP has finished, we output the footer scripts and closing tags
	 * @since 1.9.5
	 */
	function output_buffer( $in_shutdown = false ) {
		// If the footer scripts haven't been outputted, then
		// we need to take action - as they're required
		if ( ! $this->wrote_footer ) {
			// If W3TC is installed and activated, we can't output the
			// scripts and manipulate the buffer, so we can only provide a warning
			if ( defined( 'W3TC' ) && defined( 'WP_DEBUG' ) && WP_DEBUG && ! is_admin() ) {
				if ( ! defined( 'DONOTCACHEPAGE' ) ) {
					define( 'DONOTCACHEPAGE', true );
				}

				error_log( sprintf(
					/* translators: either wp_footer() or wp_print_footer_scripts() hook. */
					__( "We're sorry, but your theme's page template didn't make a call to %s, which is required by WP User Avatar 2.0. Please add this call to your page templates.", 'custom-user-avatar' ),
					( did_action( 'wp_footer' ) ? 'wp_print_footer_scripts()' : 'wp_footer()' )
				) );
			// We don't want to manipulate the buffer if it doesn't contain HTML
			} elseif ( false === strpos( $this->buffer, '</body>' ) ) {
				$this->valid_request = false;
			}

			// The output_buffer() function has been called in the PHP shutdown callback
			// This will allow us to print the scripts ourselves and manipulate the buffer
			if ( true === $in_shutdown ) {
				ob_start();

				if ( ! did_action( 'wp_footer' ) ) {
					wp_footer();
				} else {
					wp_print_footer_scripts();
				}

				$this->other_output = ob_get_clean();
			}

			// W3TC isn't activated and we're not in the shutdown callback.
			// We'll therefore add a shutdown callback to print the scripts
			else {
				$this->run_shutdown = true;

				return '';
			}
		}

		// Once we have the footer scripts, we can modify the buffer and
		// move the resources around
		if ( $this->wrote_footer ) {
			$this->move_resources();
		}

		return $this->buffer;
	}

	/**
	 * PHP shutdown callback. Manipulate and output the buffer
	 * @since 1.9.5
	 */
	function shutdown() {
		if ( $this->run_shutdown ) {
			echo $this->output_buffer( true );
		}
	}

	/**
		* @since 1.9.5
	 */
	static function init() {
		$klass = get_class();

		return self::$instance = new $klass;
	}
}
