<?php

class Custom_Default_User_Avatar_Functions {
	/**
	 * Constructor
	 * @since 1.8
	 * @uses add_filter()
	 * @uses register_activation_hook()
	 * @uses register_deactivation_hook()
	 */
	public function __construct() {
		add_filter( 'get_avatar',               array( $this, 'wpua_get_avatar_filter' ),               10, 6 );

		add_filter( 'get_avatar_url',           array( $this, 'wpua_get_avatar_url' ),                  10, 3 );

		// Filter to display WP User Avatar 2.0 at Buddypress
		add_filter( 'bp_core_fetch_avatar',     array( $this, 'wpua_bp_core_fetch_avatar_filter' ),     10, 5 );

		// Filter to display WP User Avatar 2.0 by URL at Buddypress
		add_filter( 'bp_core_fetch_avatar_url', array( $this, 'wpua_bp_core_fetch_avatar_url_filter' ), 10, 5 );

	}



	function wpua_get_avatar_url( $url, $id_or_email, $args ){
		global $wpua_disable_gravatar;

		$user_id = null;

		if ( is_object( $id_or_email ) ) {
			 if ( ! empty( $id_or_email->comment_author_email ) ) {
				$user_id = $id_or_email->user_id;
			}
		} else {
			if ( is_email( $id_or_email ) ) {
				$user = get_user_by( 'email', $id_or_email );

				if ( $user ) {
					$user_id = $user->ID;
				}
			} else {
				$user_id = $id_or_email;
			}
		}

		// First checking custom avatar.
		if ( has_custom_default_user_avatar( $user_id ) ) {
			$url = $this->get_custom_default_user_avatar_src( $user_id );
		} else if( $wpua_disable_gravatar ) {
			$url = $this->wpua_get_default_avatar_url( $url, $id_or_email, $args );
		} else {
			$has_valid_url = $this->wpua_has_gravatar( $id_or_email );

			if ( ! $has_valid_url ) {
				$url = $this->wpua_get_default_avatar_url( $url, $id_or_email, $args );
			}

		}
		/**
		 * Filter get_avatar_url filter
		 * @since 4.1.9
		 * @param string $url
		 * @param int|string $id_or_email
		 * @param array $args
		 */
		return apply_filters( 'wpua_get_avatar_filter_url', $url, $id_or_email );
	}


	function wpua_get_default_avatar_url( $url, $id_or_email, $args ){
		global  $avatar_default,
				$mustache_admin,
				$mustache_avatar,
				$mustache_medium,
				$mustache_original,
				$mustache_thumbnail,
				$post,
				$wpua_avatar_default,
				$wpua_disable_gravatar,
				$wpua_functions;

		$default_image_details = array();

		$size = ! empty( $args['size'] ) ? $args['size'] : 96;

		// Show custom Default Avatar
		if ( ! empty( $wpua_avatar_default ) && $wpua_functions->wpua_attachment_is_image( $wpua_avatar_default ) ) {
			// Get image
			$wpua_avatar_default_image = $wpua_functions->wpua_get_attachment_image_src(
				$wpua_avatar_default,
				array( $size, $size )
			);

			// Image src
			$url = $wpua_avatar_default_image[0];
		} else {
			// Get mustache image based on numeric size comparison
			if ( $size > get_option( 'medium_size_w' ) ) {
				$url = $mustache_original;
			} elseif ( $size <= get_option( 'medium_size_w' ) && $size > get_option( 'thumbnail_size_w' ) ) {
				$url = $mustache_medium;
			} elseif ( $size <= get_option( 'thumbnail_size_w' ) && 96 < $size ) {
				$url = $mustache_thumbnail;
			} elseif ( 96 >= $size && 32 < $size ) {
				$url = $mustache_avatar;
			} elseif ( 32 >= $size ) {
				$url = $mustache_admin;
			}
		}

		return $url;
	}

	/**
	 * Returns WP User Avatar 2.0 or Gravatar-hosted image if user doesn't have Buddypress-uploaded image
	 * @param string $avatar
	 * @param array $params
	 * @param int $item_id
	 * @param string $avatar_dir
	 * @param string $css_id
	 * @param int $html_width
	 * @param int $html_height
	 * @param string $avatar_folder_url
	 * @param string $avatar_folder_dir
	 * @uses object $wpua_functions
	 * @uses wpua_get_avatar_filter()
	 */
	public function wpua_bp_core_fetch_avatar_filter( $gravatar, $params, $item_id = '', $avatar_dir = '', $css_id = '', $html_width = '', $html_height =' ', $avatar_folder_url = '', $avatar_folder_dir = '') {
		global $wpua_functions;

		if ( -1 < strpos( $gravatar, 'gravatar.com' , 0 ) ) {
			$avatar = $wpua_functions->wpua_get_avatar_filter(
				$gravatar,
				( 'user' == $params['object'] ) ? $params['item_id'] : '',
				( 'user' == $params['object'] ) ? ( ( 'thumb' == $params['type'] ) ? 50 :150 ) : 50,
				'',
				''
			);

			return $avatar;
		}

		return $gravatar;
	}

	/**
	 * Returns WP user default local avatar URL or Gravatar-hosted image URL if user doesn't have Buddypress-uploaded image
	 * @param string $avatar
	 * @param array $params
	 * @uses object $wpua_functions
	 * @uses wpua_get_avatar_filter()
	*/
	public function wpua_bp_core_fetch_avatar_url_filter( $gravatar, $params ) {
		global $wpua_functions;

		if ( -1 < strpos( $gravatar, 'gravatar.com', 0 ) ) {
			$avatar = $this->wpua_get_avatar_url( $gravatar, $params['email'], $params );

			return $avatar;
		}

		return $gravatar;
	}

	/**
	 * Returns true if user has Gravatar-hosted image
	 * @since 1.4
	 * @param int|string $id_or_email
	 * @param bool $has_gravatar
	 * @param int|string $user
	 * @param string $email
	 * @uses get_user_by()
	 * @uses is_wp_error()
	 * @uses wp_cache_get()
	 * @uses wp_cache_set()
	 * @uses wp_remote_head()
	 * @return bool $has_gravatar
	 */
	public function wpua_has_gravatar( $id_or_email = '', $has_gravatar = 0, $user = '', $email = '') {
		global  $wpua_hash_gravatar,
				$avatar_default,
				$mustache_admin,
				$mustache_avatar,
				$mustache_medium,
				$mustache_original,
				$mustache_thumbnail,
				$post,
				$wpua_avatar_default,
				$wpua_disable_gravatar,
				$wpua_functions;

		// User has WPUA
		// Decide if check gravatar required or not.
		if ( 'custom_default_user_avatar' != trim( $avatar_default ) ) {
			return true;
		}

		if ( ! is_object( $id_or_email ) && ! empty( $id_or_email ) ) {
			// Find user by ID or e-mail address
			$user = is_numeric( $id_or_email ) ? get_user_by( 'id', $id_or_email ) : get_user_by( 'email', $id_or_email );

			// Get registered user e-mail address
			$email = ! empty( $user ) ? $user->user_email : '';
		}

		if ( '' == $email ) {
			if ( ! is_numeric( $id_or_email ) && ! is_object( $id_or_email ) ) {
				$email = $id_or_email;
			} elseif ( ! is_numeric( $id_or_email ) && is_object( $id_or_email ) ) {
				$email = $id_or_email->comment_author_email;
			}
		}

		if ( '' != $email ) {
			$hash = md5( strtolower( trim( $email ) ) );

			//check if gravatar exists for hashtag using options
			if ( is_array( $wpua_hash_gravatar ) ) {
				$date = date( 'm-d-Y' );

				if (
					array_key_exists( $hash, $wpua_hash_gravatar )          &&
					is_array( $wpua_hash_gravatar[$hash] )                  &&
					array_key_exists( $date, $wpua_hash_gravatar[ $hash ] )
				) {
					return (bool) $wpua_hash_gravatar[ $hash ][ $date ];
				}
			}

			if (
				isset( $_SERVER['HTTPS'] )                              &&
				( 'on' == $_SERVER['HTTPS'] || 1 == $_SERVER['HTTPS'] ) ||
				isset( $_SERVER['HTTP_X_FORWARDED_PROTO'] )             &&
				'https' == $_SERVER['HTTP_X_FORWARDED_PROTO']
			) {
				$http = 'https';
			} else {
				$http = 'http';
			}

			$gravatar = $http . '://www.gravatar.com/avatar/' . $hash . '?d=404';
			$data     = wp_cache_get( $hash );

			if ( false === $data ) {
				$response = wp_remote_head( $gravatar );
				$data     = is_wp_error( $response ) ? 'not200' : $response['response']['code'];

				wp_cache_set( $hash, $data, $group = '', $expire = 60 * 5 );

				$has_gravatar = ( '200' == $data ) ? true : false;

				if ( false == $wpua_hash_gravatar ) {
					$date = date( 'm-d-Y' );

					$wpua_hash_gravatar = [];
					$wpua_hash_gravatar[ $hash ][ $date ] = (bool) $has_gravatar;

					add_option( 'wpua_hash_gravatar', serialize( $wpua_hash_gravatar ), '', false );
				} else {
					if ( is_array( $wpua_hash_gravatar ) && ! empty( $wpua_hash_gravatar ) ) {
						$date = date( 'm-d-Y' );

						if ( array_key_exists( $hash, $wpua_hash_gravatar ) ){
							unset( $wpua_hash_gravatar[ $hash ] );

							$wpua_hash_gravatar[ $hash ][ $date ] = (bool) $has_gravatar;

							update_option( 'wpua_hash_gravatar', serialize( $wpua_hash_gravatar ), false );
						}
						else {
							$wpua_hash_gravatar[ $hash ][ $date ] = (bool) $has_gravatar;

							update_option( 'wpua_hash_gravatar', serialize( $wpua_hash_gravatar ), false );
						}
					}
				}
			}

			$has_gravatar = ( '200' == $data ) ? true : false;
		} else {
			$has_gravatar = false;
		}

		// Check if Gravatar image returns 200 (OK) or 404 (Not Found)
		return (bool) $has_gravatar;
	}


	/**
	 * Check if local image
	 * @since 1.9.2
	 * @param int $attachment_id
	 * @uses apply_filters()
	 * @uses wp_attachment_is_image()
	 * @return bool
	 */
	public function wpua_attachment_is_image( $attachment_id ) {
		$is_image = wp_attachment_is_image( $attachment_id );

		/**
		 * Filter local image check
		 * @since 1.9.2
		 * @param bool $is_image
		 * @param int $attachment_id
		 */
		$is_image = apply_filters( 'wpua_attachment_is_image', $is_image, $attachment_id );

		return (bool) $is_image;
	}

	/**
	 * Get local image tag
	 * @since 1.9.2
	 * @param int $attachment_id
	 * @param int|string $size
	 * @param bool $icon
	 * @param string $attr
	 * @uses apply_filters()
	 * @uses wp_get_attachment_image()
	 * @return string
	 */
	public function wpua_get_attachment_image( $attachment_id, $size = 'thumbnail', $icon = 0, $attr = '' ) {
		$image = wp_get_attachment_image( $attachment_id, $size, $icon, $attr );

		/**
		 * Filter local image tag
		 * @since 1.9.2
		 * @param string $image
		 * @param int $attachment_id
		 * @param int|string $size
		 * @param bool $icon
		 * @param string $attr
		 */
		return apply_filters( 'wpua_get_attachment_image', $image, $attachment_id, $size, $icon, $attr );
	}

	/**
	 * Get local image src
	 * @since 1.9.2
	 * @param int $attachment_id
	 * @param int|string $size
	 * @param bool $icon
	 * @uses apply_filters()
	 * @uses wp_get_attachment_image_src()
	 * @return array
	 */
	public function wpua_get_attachment_image_src( $attachment_id, $size = 'thumbnail', $icon = 0 ) {
		$image_src_array = wp_get_attachment_image_src( $attachment_id, $size, $icon );

		/**
		 * Filter local image src
		 * @since 1.9.2
		 * @param array $image_src_array
		 * @param int $attachment_id
		 * @param int|string $size
		 * @param bool $icon
		 */
		return apply_filters( 'wpua_get_attachment_image_src', $image_src_array, $attachment_id, $size, $icon );
	}

	/**
	 * Returns true if user has custom_default_user_avatar
	 * @since 1.1
	 * @param int|string $id_or_email
	 * @param bool $has_wpua
	 * @param object $user
	 * @param int $user_id
	 * @uses int $blog_id
	 * @uses object $wpdb
	 * @uses int $wpua_avatar_default
	 * @uses object $wpua_functions
	 * @uses get_user_by()
	 * @uses get_user_meta()
	 * @uses get_blog_prefix()
	 * @uses wpua_attachment_is_image()
	 * @return bool
	 */
	public function has_custom_default_user_avatar($id_or_email = '', $has_wpua = 0, $user = '', $user_id = '') {
		global $blog_id, $wpdb, $wpua_avatar_default, $wpua_functions, $avatar_default;

		if ( ! is_object ( $id_or_email ) && ! empty( $id_or_email ) ) {
			// Find user by ID or e-mail address
			$user = is_numeric( $id_or_email ) ? get_user_by( 'id', $id_or_email ) : get_user_by( 'email', $id_or_email );

			// Get registered user ID
			$user_id = ! empty( $user ) ? $user->ID : '';
		}

		$wpua = get_user_meta( $user_id, $wpdb->get_blog_prefix( $blog_id ) . 'user_avatar', true );

		// Check if avatar is same as default avatar or on excluded list
		$has_wpua = ! empty( $wpua ) && ( 'custom_default_user_avatar' != $avatar_default || $wpua != $wpua_avatar_default ) && $wpua_functions->wpua_attachment_is_image( $wpua ) ? true : false;

		return (bool) $has_wpua;
	}
	/**
	 * Retrive default image url set by admin.
	 */
	public function wpua_default_image( $size ) {
				global  $avatar_default,
						$mustache_admin,
						$mustache_avatar,
						$mustache_medium,
						$mustache_original,
						$mustache_thumbnail,
						$post,
						$wpua_avatar_default,
						$wpua_disable_gravatar,
						$wpua_functions;

				$default_image_details = array();

				// Show custom Default Avatar
				if ( !empty( $wpua_avatar_default ) && $wpua_functions->wpua_attachment_is_image( $wpua_avatar_default ) ) {
					// Get image
					$wpua_avatar_default_image = $wpua_functions->wpua_get_attachment_image_src(
						$wpua_avatar_default,
						array( $size, $size )
					);

					// Image src
					$default = $wpua_avatar_default_image[0];

					// Add dimensions if numeric size
					$default_image_details['dimensions'] = sprintf(
						' width="%s" height="%s"',
						esc_attr( $wpua_avatar_default_image[1] ),
						esc_attr( $wpua_avatar_default_image[2] )
					);
				} else {
					// Get mustache image based on numeric size comparison
					if ( $size > get_option( 'medium_size_w' ) ) {
						$default = $mustache_original;
					} elseif( $size <= get_option( 'medium_size_w' ) && $size > get_option( 'thumbnail_size_w' ) ) {
						$default = $mustache_medium;
					} elseif( $size <= get_option( 'thumbnail_size_w' ) && 96 < $size ) {
						$default = $mustache_thumbnail;
					} elseif( 96 >= $size && 32 < $size ) {
						$default = $mustache_avatar;
					} elseif( 32 >= $size ) {
						$default = $mustache_admin;
					}

					// Add dimensions if numeric size
					$default_image_details['dimensions'] = sprintf( ' width="%1$s" height="%1$s"', esc_attr( $size ) );
				}

				// Construct the img tag
				$default_image_details['size'] = $size;
				$default_image_details['src']  = $default;

				return $default_image_details;

	}
	/**
	 * Replace get_avatar only in get_custom_default_user_avatar
	 *
	 * @param string $avatar
	 * @param int|string $id_or_email
	 * @param int|string $size
	 * @param string $default
	 * @param string $alt
	 * @param array $args
	 *
	 * @return string $avatar
	 * @since 1.4
	 * @uses string $avatar_default
	 * @uses string $mustache_admin
	 * @uses string $mustache_avatar
	 * @uses string $mustache_medium
	 * @uses string $mustache_original
	 * @uses string $mustache_thumbnail
	 * @uses object $post
	 * @uses int $wpua_avatar_default
	 * @uses bool $wpua_disable_gravatar
	 * @uses object $wpua_functions
	 * @uses apply_filters()
	 * @uses get_custom_default_user_avatar()
	 * @uses has_custom_default_user_avatar()
	 * @uses wpua_has_gravatar()
	 * @uses wpua_attachment_is_image()
	 * @uses wpua_get_attachment_image_src()
	 * @uses get_option()
	 * @return string $avatar
	 */
	public function wpua_get_avatar_filter( $avatar, $id_or_email = '', $size = '', $default = '', $alt = '') {
		global  $avatar_default,
				$mustache_admin,
				$mustache_avatar,
				$mustache_medium,
				$mustache_original,
				$mustache_thumbnail,
				$post,
				$wpua_avatar_default,
				$wpua_disable_gravatar,
				$wpua_functions;

		// User has WPUA
		if ( '' == $alt ) {
			 $alt = apply_filters( 'wpua_default_alt_tag', __( 'Avatar', 'custom-user-avatar' ) );
		}

		$alt   = esc_attr($alt);
		$size  = esc_attr($size);
		$class = [];

		if ( isset( $args['class'] ) ) {
			if ( is_array( $args['class'] ) ) {
				$class = array_merge( $class, $args['class'] );
			} else {
				$class[] = $args['class'];
			}
		}

		$avatar = str_replace( 'gravatar_default', '', $avatar );

		if ( is_object( $id_or_email ) ) {
			if ( ! empty( $id_or_email->comment_author_email ) ) {
				$avatar = get_custom_default_user_avatar( $id_or_email, $size, $default, $alt, $class );
			} else {
				$avatar = get_custom_default_user_avatar( 'unknown@gravatar.com', $size, $default, $alt, $class );
			}
		} else {
			if ( has_custom_default_user_avatar( $id_or_email ) ) {
				$avatar = get_custom_default_user_avatar( $id_or_email, $size, $default, $alt, $class );
			// User has Gravatar and Gravatar is not disabled
			} elseif( 1 != (bool) $wpua_disable_gravatar && $wpua_functions->wpua_has_gravatar( $id_or_email ) ) {
				// find our src
				if ( ! empty( $avatar ) ) {
					preg_match_all( '/<img.+src=[\'"]([^\'"]+)[\'"].*>/i', $avatar, $matches, PREG_SET_ORDER );

					$wpua_avatar_image_src         = ! empty( $matches ) ? $matches[0][1] : '';
					$default_image_details         = $this->wpua_default_image( $size );
					$wpua_default_avatar_image_src = $default_image_details['src'];
					$wpua_final_avatar_image_src   = str_replace(
						'd=custom_default_user_avatar',
						'd=' . urlencode( $wpua_default_avatar_image_src ),
						$wpua_avatar_image_src
					);
				}

				$class_string = ! empty( $class ) ? ' ' . esc_attr( implode( ' ', $class ) ) : '';
				$avatar       = sprintf(
					'<img src="%1$s"%2$s alt="%3$s" class="avatar avatar-%4$s custom-default-user-avatar custom-default-user-avatar-%4$s photo avatar-default" />',
					esc_url( $wpua_final_avatar_image_src ),
					$default_image_details['dimensions'],
					esc_attr( $alt ),
					esc_attr( $size )
				);
			// User doesn't have WPUA or Gravatar and Default Avatar is custom_default_user_avatar, show custom Default Avatar
			} elseif ( 'custom_default_user_avatar' == $avatar_default ) {
				$default_image_details = $this->wpua_default_image( $size );
				$class_string          = ! empty( $class ) ? ' ' . esc_attr( implode( ' ', $class ) ) : '';
				$avatar                = sprintf(
					'<img src="%1$s"%2$s alt="%3$s" class="avatar avatar-%4$s custom-default-user-avatar custom-default-user-avatar-%4$s photo avatar-default" />',
					esc_url( $default_image_details['src'] ),
					$default_image_details['dimensions'],
					esc_attr( $alt ),
					esc_attr( $size )
				);

				return $avatar;
			}
		}

		/**
		 * Filter get_avatar filter
		 * @since 1.9
		 * @param string $avatar
		 * @param int|string $id_or_email
		 * @param int|string $size
		 * @param string $default
		 * @param string $alt
		 */
		return apply_filters( 'wpua_get_avatar_filter', $avatar, $id_or_email, $size, $default, $alt );
	}

	/**
	 * Get original avatar, for when user removes custom_default_user_avatar
	 * @since 1.4
	 * @param int|string $id_or_email
	 * @param int|string $size
	 * @param string $default
	 * @param string $alt
	 * @uses string $avatar_default
	 * @uses string $mustache_avatar
	 * @uses int $wpua_avatar_default
	 * @uses bool $wpua_disable_gravatar
	 * @uses object $wpua_functions
	 * @uses wpua_attachment_is_image()
	 * @uses wpua_get_attachment_image_src()
	 * @uses wpua_has_gravatar()
	 * @uses add_filter()
	 * @uses apply_filters()
	 * @uses get_avatar()
	 * @uses remove_filter()
	 * @return string $default
	 */
	public function wpua_get_avatar_original( $id_or_email = '', $size = '', $default = '', $alt = '' ) {
		global  $avatar_default,
				$mustache_avatar,
				$wpua_avatar_default,
				$wpua_disable_gravatar,
				$wpua_functions;

		// Remove get_avatar filter
		remove_filter( 'get_avatar',     array( $this, 'wpua_get_avatar_filter' ) );
		remove_filter( 'get_avatar_url', array( $this, 'wpua_get_avatar_url' ) );

		if ( 1 != (bool) $wpua_disable_gravatar ) {
			// User doesn't have Gravatar and Default Avatar is custom_default_user_avatar, show custom Default Avatar
			if ( ! $wpua_functions->wpua_has_gravatar( $id_or_email ) && 'custom_default_user_avatar' == $avatar_default) {
				// Show custom Default Avatar
				if( ! empty( $wpua_avatar_default ) && $wpua_functions->wpua_attachment_is_image( $wpua_avatar_default ) ) {
					$size_numeric_w_x_h        = array( get_option( $size . '_size_w' ), get_option( $size . '_size_h' ) );
					$wpua_avatar_default_image = $wpua_functions->wpua_get_attachment_image_src(
						$wpua_avatar_default,
						$size_numeric_w_x_h
					);

					$default = $wpua_avatar_default_image[0];
				} else {
					$default = $mustache_avatar;
				}
			} else {
				// Get image from Gravatar, whether it's the user's image or default image
				$default = get_avatar_url( $id_or_email, array( 'size' => $size ) );
			}
		} else {
			if ( ! empty( $wpua_avatar_default ) && $wpua_functions->wpua_attachment_is_image( $wpua_avatar_default ) ) {
				$size_numeric_w_x_h        = array( get_option( $size . '_size_w' ), get_option( $size . '_size_h' ) );
				$wpua_avatar_default_image = $wpua_functions->wpua_get_attachment_image_src(
					$wpua_avatar_default,
					$size_numeric_w_x_h
				);

				$default = $wpua_avatar_default_image[0];
			} else {
				$default = $mustache_avatar;
			}
		}

		// Enable get_avatar filter
		add_filter( 'get_avatar',     array( $this, 'wpua_get_avatar_filter' ), 10, 5 );
		add_filter( 'get_avatar_url', array( $this, 'wpua_get_avatar_url' ),    10, 3 );

		/**
		 * Filter original avatar src
		 * @since 1.9
		 * @param string $default
		 */
		return apply_filters( 'wpua_get_avatar_original', $default );
	}


	/**
	 * Find WPUA, show get_avatar if empty
	 *
	 * @param int|string $id_or_email
	 * @param int|string $size
	 * @param string $align
	 * @param string $alt
	 * @param array $class
	 *
	 * @return string $avatar
	 * @since 1.0
	 * @uses array $_wp_additional_image_sizes
	 * @uses array $all_sizes
	 * @uses string $avatar_default
	 * @uses int $blog_id
	 * @uses object $post
	 * @uses object $wpdb
	 * @uses int $wpua_avatar_default
	 * @uses object $wpua_functions
	 * @uses apply_filters()
	 * @uses get_the_author_meta()
	 * @uses get_blog_prefix()
	 * @uses get_user_by()
	 * @uses get_query_var()
	 * @uses is_author()
	 * @uses wpua_attachment_is_image()
	 * @uses wpua_get_attachment_image_src()
	 * @uses get_option()
	 * @uses get_avatar()
	 * @return string $avatar
	 */
	public function get_custom_default_user_avatar( $id_or_email = '', $size = '96', $align = '', $alt = '', $class = [] ) {
		global  $all_sizes,
				$avatar_default,
				$blog_id,
				$post,
				$wpdb,
				$wpua_avatar_default,
				$wpua_functions,
				$_wp_additional_image_sizes;

		$email = 'unknown@gravatar.com';

		// Checks if comment
		if ( '' == $alt ) {
			 $alt = apply_filters( 'wpua_default_alt_tag', __( 'Avatar', 'custom-user-avatar' ) );
		}

		if ( is_object( $id_or_email ) ) {
			// Checks if comment author is registered user by user ID
			if ( 0 != $id_or_email->user_id ) {
				$email = $id_or_email->user_id;
			// Checks that comment author isn't anonymous
			} elseif ( ! empty( $id_or_email->comment_author_email ) ) {
				// Checks if comment author is registered user by e-mail address
				$user = get_user_by( 'email', $id_or_email->comment_author_email );
				// Get registered user info from profile, otherwise e-mail address should be value
				$email = ! empty( $user ) ? $user->ID : $id_or_email->comment_author_email;
			}

			$alt = $id_or_email->comment_author;
		} else {
			if ( ! empty( $id_or_email ) ) {
				// Find user by ID or e-mail address
				$user = is_numeric( $id_or_email ) ? get_user_by( 'id', $id_or_email ) : get_user_by( 'email', $id_or_email );
			} else {
				// Find author's name if id_or_email is empty
				$author_name = get_query_var( 'author_name' );

				if ( is_author() ) {
					// On author page, get user by page slug
					$user = get_user_by( 'slug', $author_name );
				} else {
					// On post, get user by author meta
					$user_id = get_the_author_meta( 'ID' );
					$user    = get_user_by( 'id', $user_id );
				}
			}

			// Set user's ID and name
			if ( ! empty ( $user ) ) {
				$email = $user->ID;
				$alt   = $user->display_name;
			}
		}

		$alt   = esc_attr( $alt );
		$size  = esc_attr( $size );
		$class = esc_attr( implode( ' ', $class ) );

		// Checks if user has WPUA
		$wpua_meta  = get_the_author_meta( $wpdb->get_blog_prefix( $blog_id ) . 'user_avatar', $email );

		// Add alignment class
		$alignclass = ! empty( $align ) && ( 'left' == $align || 'right' == $align || 'center' == $align ) ? ' align' . $align : ' alignnone';

		// User has WPUA, check if on excluded list and bypass get_avatar
		if ( ! empty( $wpua_meta ) && $wpua_functions->wpua_attachment_is_image( $wpua_meta ) ) {
			// Numeric size use size array
			$get_size = is_numeric( $size ) ? array( $size, $size ) : $size;

			// Get image src
			$wpua_image = $wpua_functions->wpua_get_attachment_image_src( $wpua_meta, $get_size );

			// Add dimensions to img only if numeric size was specified
			$dimensions = is_numeric( $size ) ? sprintf(
				' width="%s" height="%s"',
				esc_attr( $wpua_image[1] ),
				esc_attr( $wpua_image[2] )
			) : '';

			$class_string = ! empty( $class ) ? ' ' . $class : '';

			// Construct the img tag
			$avatar = sprintf(
				'<img src="%1$s"%2$s alt="%3$s" class="avatar avatar-%4$s custom-default-user-avatar custom-default-user-avatar-%4$s%5$s photo" />',
				esc_url( $wpua_image[0] ),
				$dimensions,
				esc_attr( $alt ),
				esc_attr( $size ),
				esc_attr( $alignclass )
			);
		} else {
			// Check for custom image sizes
			if ( in_array( $size, $all_sizes ) ) {
				if ( in_array( $size, array( 'original', 'large', 'medium', 'thumbnail' ) ) ) {
					$get_size = ( 'original' == $size ) ? get_option( 'large_size_w' ) : get_option( $size . '_size_w' );
				} else {
					$get_size = $_wp_additional_image_sizes[ $size ]['width'];
				}
			} else {
				// Numeric sizes leave as-is
				$get_size = $size;
			}

			// User with no WPUA uses get_avatar
			$avatar = get_avatar( $email, $get_size, $default = '', $alt = '', [ 'class' => $class ] );

			// Remove width and height for non-numeric sizes
			if ( in_array( $size, array( 'original', 'large', 'medium', 'thumbnail' ) ) ) {
				$avatar = preg_replace( '/(width|height)=\"\d*\"\s/', '', $avatar );
				$avatar = preg_replace( "/(width|height)=\'\d*\'\s/", '', $avatar );
			}

			$replace = array(
				'custom-default-user-avatar ',
				'custom-default-user-avatar-' . $get_size . ' ',
				'custom-default-user-avatar-' . $size . ' ',
				'avatar-'.$get_size,
				' photo'
			);

			$replacements = array(
				'',
				'',
				'',
				'avatar-' . $size,
				'custom-default-user-avatar custom-default-user-avatar-' . $size . $alignclass . ' photo'
			);
			$avatar = str_replace( $replace, $replacements, $avatar );
		}
		/**
		 * Filter get_custom_default_user_avatar
		 * @since 1.9
		 * @param string $avatar
		 * @param int|string $id_or_email
		 * @param int|string $size
		 * @param string $align
		 * @param string $alt
		 */
		return apply_filters( 'get_custom_default_user_avatar', $avatar, $id_or_email, $size, $align, $alt );
	}

	/**
	 * Return just the image src
	 * @since 1.1
	 * @param int|string $id_or_email
	 * @param int|string $size
	 * @param string $align
	 * @uses get_custom_default_user_avatar()
	 * @return string
	 */
	public function get_custom_default_user_avatar_src( $id_or_email = '', $size = '', $align = '' ) {
		$wpua_image_src = '';

		// Gets the avatar img tag
		$wpua_image = get_custom_default_user_avatar( $id_or_email, $size, $align );

		// Takes the img tag, extracts the src
		if ( ! empty( $wpua_image ) ) {
			preg_match_all( '/<img.+src=[\'"]([^\'"]+)[\'"].*>/i', $wpua_image, $matches, PREG_SET_ORDER );

			$wpua_image_src = ! empty ( $matches ) ? $matches[0][1] : '';
		}

		return $wpua_image_src;
	}
}

/**
 * Initialize
 * @since 1.9.2
 */
function wpua_functions_init() {
	global $wpua_functions;

	if ( ! isset( $wpua_functions ) ) {
		$wpua_functions = new Custom_Default_User_Avatar_Functions();
	}

	return $wpua_functions;
}
add_action( 'plugins_loaded', 'wpua_functions_init' );
