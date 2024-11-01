<?php

if ( ! function_exists( 'woow_mobile_detect' ) ) {
	/**
	 * Holder for mobile detect.
	 *
	 * @since    1.0.0
	 *
	 * @access   public
	 * @return object
	 */
	function woow_mobile_detect() {
		if ( ! class_exists( 'Mobile_Detect' ) ) {
			require_once dirname( __FILE__ ) . '/global/Mobile_Detect.php';
		}

		return new Mobile_Detect();
	}
}

if ( ! function_exists( 'woow_verify_nonce' ) ) {
	/**
	 * Verify that correct nonce was used with time limit.
	 *
	 * The user is given an amount of time to use the token, so therefore, since the
	 * UID and $action remain the same, the independent variable is the time.
	 *
	 * @since    1.0.0
	 *
	 * @param string $action          Action nonce.
	 * @param bool   $die             Optional. Whether to die early when the nonce cannot be verified.
	 *                                Default true.
	 *
	 * @return false|int False if the nonce is invalid, 1 if the nonce is valid and generated between
	 *                   0-12 hours ago, 2 if the nonce is valid and generated between 12-24 hours ago.
	 */
	function woow_verify_nonce( $action = '', $die = true ) {
		// Key to check for the nonce in `$_REQUEST`.
		$key = "_nonce_woow_{$action}";

		return _woowbox_verify_nonce( $key, $action, $die );
	}
}

if ( ! function_exists( 'woowbox_verify_nonce' ) ) {
	/**
	 * Verify that correct nonce was used with time limit.
	 *
	 * The user is given an amount of time to use the token, so therefore, since the
	 * UID and $action remain the same, the independent variable is the time.
	 *
	 * @since    1.1.0
	 *
	 * @param string $action          Action nonce.
	 * @param bool   $die             Optional. Whether to die early when the nonce cannot be verified.
	 *                                Default true.
	 *
	 * @return false|int False if the nonce is invalid, 1 if the nonce is valid and generated between
	 *                   0-12 hours ago, 2 if the nonce is valid and generated between 12-24 hours ago.
	 */
	function woowbox_verify_nonce( $action = '', $die = true ) {
		// Key to check for the nonce in `$_REQUEST`.
		$key = "_nonce_woowbox_{$action}";

		return _woowbox_verify_nonce( $key, $action, $die );
	}
}

if ( ! function_exists( '_woowbox_verify_nonce' ) ) {
	/**
	 * Verify that correct nonce was used with time limit.
	 *
	 * @since    1.1.0
	 *
	 * @param string $key             Nonce key.
	 * @param string $action          The nonce action.
	 * @param bool   $die             Optional. Whether to die early when the nonce cannot be verified.
	 *                                Default true.
	 *
	 * @return false|int False if the nonce is invalid, 1 if the nonce is valid and generated between
	 *                   0-12 hours ago, 2 if the nonce is valid and generated between 12-24 hours ago.
	 */
	function _woowbox_verify_nonce( $key, $action, $die = true ) {
		$nonce = woow_REQUEST( $key );

		if ( ! $nonce ) {
			return false;
		}

		$result = wp_verify_nonce( $nonce, $action );

		/**
		 * Fires once the request has been validated or not.
		 *
		 * @param string    $action The nonce action.
		 * @param false|int $result False if the nonce is invalid, 1 if the nonce is valid and generated between
		 *                          0-12 hours ago, 2 if the nonce is valid and generated between 12-24 hours ago.
		 */
		do_action( 'woow_verify_nonce', $action, $result );

		if ( $die && false === $result ) {
			if ( wp_doing_ajax() ) {
				wp_die( - 1, 403 );
			} else {
				die( '-1' );
			}
		}

		return $result;
	}
}

if ( ! function_exists( 'woow_GET' ) ) {
	/**
	 * Check GET data
	 *
	 * @since    1.0.0
	 *
	 * @param string $key
	 * @param mixed  $default
	 * @param bool   $empty_is_false
	 *
	 * @return mixed
	 */
	function woow_GET( $key, $default = false, $empty_is_false = false ) {
		return isset( $_GET[ $key ] ) ? ( ( $empty_is_false && woow_empty( $_GET[ $key ] ) ) ? false : stripslashes_from_strings_only( $_GET[ $key ] ) ) : $default;
	}
}

if ( ! function_exists( 'woow_empty' ) ) {
	/**
	 * Check if variable has empty value
	 *
	 * @since    1.0.0
	 *
	 * @param string $var
	 *
	 * @return bool
	 */
	function woow_empty( $var ) {
		return ! ( ! empty( $var ) && ! in_array( strtolower( $var ), array( 'null', 'false' ), true ) );
	}
}

if ( ! function_exists( 'woow_POST' ) ) {
	/**
	 * Check POST data
	 *
	 * @since    1.0.0
	 *
	 * @param string     $key
	 * @param bool|mixed $default
	 *
	 * @return mixed
	 */
	function woow_POST( $key, $default = false ) {
		return isset( $_POST[ $key ] ) ? stripslashes_from_strings_only( $_POST[ $key ] ) : $default;
	}
}

if ( ! function_exists( 'woow_REQUEST' ) ) {
	/**
	 * Check REQUEST data
	 *
	 * @since    1.0.0
	 *
	 * @param string $key
	 * @param mixed  $default
	 *
	 * @return mixed
	 */
	function woow_REQUEST( $key, $default = false ) {
		return isset( $_REQUEST[ $key ] ) ? stripslashes_from_strings_only( $_REQUEST[ $key ] ) : $default;
	}
}

if ( ! function_exists( 'woow_prepare_attachment_data' ) ) {
	/**
	 * Prepare Attachment Data Model
	 *
	 * @since    1.0.0
	 *
	 * @param WP_Post|int $attachment Attachment ID or object.
	 *
	 * @return array Array of attachment details.
	 */
	function woow_prepare_attachment_data( $attachment ) {
		$attachment = get_post( $attachment );
		if ( ! $attachment ) {
			return array();
		}

		if ( 'attachment' !== $attachment->post_type ) {
			return array();
		}

		$meta = wp_get_attachment_metadata( $attachment->ID );
		if ( false !== strpos( $attachment->post_mime_type, '/' ) ) {
			list( $type, $subtype ) = explode( '/', $attachment->post_mime_type );
		} else {
			list( $type, $subtype ) = array( $attachment->post_mime_type, '' );
		}

		$attachment_url = wp_get_attachment_url( $attachment->ID );
		$base_url       = str_replace( wp_basename( $attachment_url ), '', $attachment_url );

		$response = array(
			'id'             => $attachment->ID,
			'title'          => $attachment->post_title,
			'filename'       => wp_basename( get_attached_file( $attachment->ID ) ),
			'url'            => $attachment_url,
			'link'           => get_attachment_link( $attachment->ID ),
			'alt'            => get_post_meta( $attachment->ID, '_wp_attachment_image_alt', true ),
			'author'         => array(
				'id'   => $attachment->post_author,
				'name' => '',
				'url'  => '',
			),
			'description'    => $attachment->post_content,
			'caption'        => $attachment->post_excerpt,
			'name'           => $attachment->post_name,
			'status'         => $attachment->post_status,
			'comment_status' => $attachment->comment_status,
			'date'           => strtotime( $attachment->post_date_gmt ) * 1000,
			'modified'       => strtotime( $attachment->post_modified_gmt ) * 1000,
			'menuOrder'      => $attachment->menu_order,
			'mime'           => $attachment->post_mime_type,
			'type'           => $type,
			'subtype'        => $subtype,
			'icon'           => wp_mime_type_icon( $attachment->ID ),
			'dateFormatted'  => mysql2date( __( 'F j, Y' ), $attachment->post_date ),
			'editLink'       => false,
			'meta'           => false,
			'custom'         => false,
		);

		$author = new WP_User( $attachment->post_author );
		if ( $author->exists() ) {
			$response['author']['name'] = html_entity_decode( $author->display_name, ENT_QUOTES, get_bloginfo( 'charset' ) );
			$response['author']['url']  = get_the_author_meta( 'url', $author->ID );
		}

		$attached_file = get_attached_file( $attachment->ID );

		if ( isset( $meta['filesize'] ) ) {
			$bytes = $meta['filesize'];
		} elseif ( file_exists( $attached_file ) ) {
			$bytes = filesize( $attached_file );
		} else {
			$bytes = '';
		}

		if ( $bytes ) {
			$response['filesizeInBytes']       = $bytes;
			$response['filesizeHumanReadable'] = size_format( $bytes );
		}

		if ( current_user_can( 'edit_post', $attachment->ID ) ) {
			$response['editLink'] = get_edit_post_link( $attachment->ID, 'raw' );
		}

		if ( $meta && ( 'image' === $type || ! empty( $meta['sizes'] ) ) ) {
			$sizes = array();

			/** This filter is documented in wp-admin/includes/media.php */
			$possible_sizes = apply_filters( 'image_size_names_choose',
				array(
					'thumbnail' => __( 'Thumbnail' ),
					'medium'    => __( 'Medium' ),
					'large'     => __( 'Large' ),
					'full'      => __( 'Full Size' ),
				)
			);
			unset( $possible_sizes['full'] );

			// Loop through all potential sizes that may be chosen. Try to do this with some efficiency.
			// First: run the image_downsize filter. If it returns something, we can use its data.
			// If the filter does not return something, then consult image metadata.
			foreach ( $possible_sizes as $size => $label ) {
				/** This filter is documented in wp-includes/media.php */
				$downsize = apply_filters( 'image_downsize', false, $attachment->ID, $size );
				if ( $downsize ) {
					if ( empty( $downsize[3] ) ) {
						continue;
					}

					$sizes[ $size ] = array(
						'height'      => $downsize[2],
						'width'       => $downsize[1],
						'url'         => $downsize[0],
						'orientation' => $downsize[2] > $downsize[1] ? 'portrait' : 'landscape',
					);
				} elseif ( isset( $meta['sizes'][ $size ] ) ) {
					// Nothing from the filter, so consult image metadata if we have it.
					$size_meta = $meta['sizes'][ $size ];
					list( $width, $height ) = array( $size_meta['width'], $size_meta['height'] );
					$sizes[ $size ] = array(
						'height'      => $height,
						'width'       => $width,
						'url'         => $base_url . $size_meta['file'],
						'orientation' => $height > $width ? 'portrait' : 'landscape',
					);
				}
			}

			if ( 'image' === $type ) {
				$sizes['full'] = array( 'url' => $attachment_url );

				if ( isset( $meta['height'], $meta['width'] ) ) {
					$sizes['full']['height']      = $meta['height'];
					$sizes['full']['width']       = $meta['width'];
					$sizes['full']['orientation'] = $meta['height'] > $meta['width'] ? 'portrait' : 'landscape';
				}

				$response = array_merge( $response, $sizes['full'] );
			} elseif ( $meta['sizes']['full']['file'] ) {
				$sizes['full'] = array(
					'url'         => $base_url . $meta['sizes']['full']['file'],
					'height'      => $meta['sizes']['full']['height'],
					'width'       => $meta['sizes']['full']['width'],
					'orientation' => $meta['sizes']['full']['height'] > $meta['sizes']['full']['width'] ? 'portrait' : 'landscape',
				);
			}

			$response = array_merge( $response, array( 'sizes' => $sizes ) );

			$response['meta'] = wp_read_image_metadata( $attached_file );
		}

		if ( $meta && 'video' === $type ) {
			if ( isset( $meta['width'] ) ) {
				$response['width'] = (int) $meta['width'];
			}
			if ( isset( $meta['height'] ) ) {
				$response['height'] = (int) $meta['height'];
			}
		}

		if ( $meta && ( 'audio' === $type || 'video' === $type ) ) {
			if ( isset( $meta['length_formatted'] ) ) {
				$response['fileLength'] = $meta['length_formatted'];
			}

			$response['meta'] = array();
			foreach ( wp_get_attachment_id3_keys( $attachment, 'js' ) as $key => $label ) {
				$response['meta'][ $key ] = false;

				if ( ! empty( $meta[ $key ] ) ) {
					$response['meta'][ $key ] = $meta[ $key ];
				}
			}

			$id = get_post_thumbnail_id( $attachment->ID );
			if ( ! empty( $id ) ) {
				list( $src, $width, $height ) = wp_get_attachment_image_src( $id, 'full' );
				$response['image'] = compact( 'src', 'width', 'height' );
				list( $src, $width, $height ) = wp_get_attachment_image_src( $id, 'thumbnail' );
				$response['thumb'] = compact( 'src', 'width', 'height' );
			} else {
				$src               = wp_mime_type_icon( $attachment->ID );
				$width             = 48;
				$height            = 64;
				$response['image'] = compact( 'src', 'width', 'height' );
				$response['thumb'] = compact( 'src', 'width', 'height' );
			}
		}

		$custom = get_post_meta( $attachment->ID );
		foreach ( $custom as $key => $value ) {
			if ( '_wp_' === substr( $key, 0, 4 ) || is_serialized( $value ) ) {
				unset( $custom[ $key ] );
			}
		}
		$response['custom'] = $custom;

		$taxonomies             = get_object_taxonomies( 'attachment' );
		$response['taxonomies'] = wp_get_object_terms( $attachment->ID, $taxonomies );

		/**
		 * Filters the attachment data prepared.
		 *
		 * @param array      $response   Array of prepared attachment data.
		 * @param int|object $attachment Attachment ID or object.
		 * @param array      $meta       Array of attachment meta data.
		 */
		return apply_filters( 'woow_prepare_attachment_data', $response, $attachment, $meta );
	}
}

/**
 * Skip images for Jetpack lazy load.
 * @param bool $skip
 * @param array $attributes
 *
 * @return bool
 */
function jetpack_no_lazy_for_woowbox( $skip, $attributes ) {
	if ( isset( $attributes['class'] ) && strpos( $attributes['class'], 'skip-lazy' ) ) {
		return true;
	}

	return $skip;
}
add_filter( 'jetpack_lazy_images_skip_image_with_attributes', 'jetpack_no_lazy_for_woowbox', 10, 2 );

/**
 * Skip images for a3 Lazy Load.
 * @param string $classes
 *
 * @return string
 */
function a3_no_lazy_for_woowbox( $classes ) {
	return 'skip-lazy,' . $classes;
}
add_filter( 'a3_lazy_load_skip_images_classes', 'a3_no_lazy_for_woowbox', 10 );
