<?php

/**
 * Common class.
*
 * @package WoowBox
 * @author  Sergey Pasyuk
 */
class WoowBox_CommonGlobal {

	/**
	 * Holds the class object.
	 *
	 * @since 1.0.0
	 *
	 * @var WoowBox_CommonGlobal object
	 */
	public static $instance;

	/**
	 * Path to the file.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	public $file = __FILE__;

	/**
	 * Holds the base class object.
	 *
	 * @since 1.0.0
	 *
	 * @var WoowBox object
	 */
	public $base;

	/**
	 * Primary class constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		// Load the base class object.
		$this->base = WoowBox::get_instance();

		// If the wp_generate_attachment_metadata function does not exist, load it into memory because we will need it.
		$this->load_metadata_function();

		// Load frontend assets.
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
	}

	/**
	 * Returns the singleton instance of the class.
	 *
	 * @since 1.0.0
	 *
	 * @return WoowBox_CommonGlobal object
	 */
	public static function get_instance() {
		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof WoowBox_CommonGlobal ) ) {
			self::$instance = new WoowBox_CommonGlobal();
		}

		return self::$instance;
	}

	/**
	 * Register global scripts.
	 *
	 * @since 1.0.0
	 */
	public function enqueue_scripts() {
		// $suffix = SCRIPT_DEBUG ? '' : '.min';
		// Register necessary vendor scripts.
		// Register frontend scripts.
		wp_register_style( $this->base->plugin_slug . '-style', plugins_url( 'assets/css/woowbox.css', $this->base->file ), array(), $this->base->version );
		wp_register_script( $this->base->plugin_slug . '-script', plugins_url( 'assets/js/woowbox.js', $this->base->file ), array(), $this->base->version, false );

		$license      = $this->premium();
		$license_part = explode( ':', $license['key'] );
		$license_key  = isset( $license_part[1] ) ? $license_part[1] : '';
		wp_localize_script( $this->base->plugin_slug . '-script', 'WoowBox',
			array(
				'galleries' => (object) array(),
				'ajaxurl'   => admin_url( 'admin-ajax.php' ),
				'l10n'      => apply_filters( 'woowbox_scripts_l10n', array() ),
				'key'       => $license_key,
			)
		);

		// Load necessary frontend scripts.
		wp_enqueue_style( $this->base->plugin_slug . '-style' );
		wp_enqueue_script( $this->base->plugin_slug . '-script' );

		// Fire a hook to load in custom frontend scripts.
		do_action( 'woowbox_scripts' );
	}

	/**
	 * Load the wp_generate_attachment_metadata function if necessary.
	 *
	 * @since 1.0.0
	 */
	public function load_metadata_function() {
		if ( ! function_exists( 'wp_generate_attachment_metadata' ) ) {
			require_once ABSPATH . 'wp-admin/includes/image.php';
		}
	}

	/**
	 * API method for cropping images.
	 *
	 * @since 1.0.0
	 *
	 * @param string  $url             The URL of the image to resize.
	 * @param int     $width           The width for cropping the image.
	 * @param int     $height          The height for cropping the image.
	 * @param bool    $crop            Whether or not to crop the image (default yes).
	 * @param string  $align           The crop position alignment.
	 * @param int     $quality         Quality of the resulting image.
	 * @param bool    $retina          Whether or not to make a retina copy of image.
	 * @param array   $data            Array of gallery data (optional).
	 * @param bool    $force_overwrite Forces an overwrite even if the thumbnail already exists (useful for applying watermarks).
	 *
	 * @global object $wpdb            The $wpdb database object.
	 *
	 * @return string|WP_Error Return WP_Error on error, URL of resized image on success.
	 */
	public function resize_image( $url, $width = null, $height = null, $crop = true, $align = 'c', $quality = 100, $retina = false, $data = array(), $force_overwrite = false ) {
		global $wpdb;

		// Get common vars.
		$args = array( $url, $width, $height, $crop, $align, $quality, $retina, $data );

		// Filter args.
		$args = apply_filters( 'woowbox_resize_image_args', $args );

		// Don't resize images that don't belong to this site's URL.
		// Strip ?lang=fr from blog's URL - WPML adds this on and means our next statement fails.
		if ( is_multisite() ) {
			$blog_id = get_current_blog_id();
			// Doesn't use network_site_url because this will be incorrect for remapped domains.
			if ( is_main_site( $blog_id ) ) {
				$site_url = preg_replace( '/\?.*/', '', network_site_url() );
			} else {
				$site_url = preg_replace( '/\?.*/', '', site_url() );
			}
		} else {
			$site_url = preg_replace( '/\?.*/', '', get_bloginfo( 'url' ) );
		}

		// WPML check - if there is a /fr or any domain in the url, then remove that from the $site_url.
		if ( defined( 'ICL_LANGUAGE_CODE' ) ) {
			if ( strpos( $site_url, '/' . ICL_LANGUAGE_CODE ) !== false ) {
				$site_url = str_replace( '/' . ICL_LANGUAGE_CODE, '', $site_url );
			}
		}

		if ( function_exists( 'qtrans_getLanguage' ) ) {

			$lang = qtrans_getLanguage();

			if ( ! empty( $lang ) ) {
				if ( strpos( $site_url, '/' . $lang ) !== false ) {
					$site_url = str_replace( '/' . $lang, '', $site_url );
				}
			}
		}

		if ( strpos( $url, $site_url ) === false ) {
			return $url;
		}

		// Get image info.
		$common = $this->get_image_info( $args );

		// Unpack variables if an array, otherwise return WP_Error.
		if ( is_wp_error( $common ) ) {
			return $common;
		} else {
			/**
			 * @var $orig_width
			 * @var $orig_height
			 * @var $dest_width
			 * @var $dest_height
			 * @var $dest_file_name
			 * @var $file_path
			 */
			extract( $common );
		}

		// If the destination width/height values are the same as the original, don't do anything.
		if ( ! $force_overwrite && $orig_width === $dest_width && $orig_height === $dest_height ) {
			return $url;
		}

		// If the file doesn't exist yet, we need to create it.
		if ( ! file_exists( $dest_file_name ) || ( file_exists( $dest_file_name ) && $force_overwrite ) ) {
			// We only want to resize Media Library images, so we can be sure they get deleted correctly when appropriate.
			$get_attachment = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $wpdb->posts WHERE guid='%s'", $url ) );

			// Load the WordPress image editor.
			$editor = wp_get_image_editor( $file_path );

			// If an editor cannot be found, the user needs to have GD or Imagick installed.
			if ( is_wp_error( $editor ) ) {
				return new WP_Error( 'woowbox-error-no-editor', __( 'No image editor could be selected. Please verify with your webhost that you have either the GD or Imagick image library compiled with your PHP install on your server.', 'woowbox' ) );
			}

			// Set the image editor quality.
			$editor->set_quality( $quality );

			// If cropping, process cropping.
			if ( $crop ) {
				$src_x = $src_y = 0;
				$src_w = $orig_width;
				$src_h = $orig_height;

				$cmp_x = $orig_width / $dest_width;
				$cmp_y = $orig_height / $dest_height;

				// Calculate x or y coordinate and width or height of source.
				if ( $cmp_x > $cmp_y ) {
					$src_w = round( $orig_width / $cmp_x * $cmp_y );
					$src_x = round( ( $orig_width - ( $orig_width / $cmp_x * $cmp_y ) ) / 2 );
				} elseif ( $cmp_y > $cmp_x ) {
					$src_h = round( $orig_height / $cmp_y * $cmp_x );
					$src_y = round( ( $orig_height - ( $orig_height / $cmp_y * $cmp_x ) ) / 2 );
				}

				// Positional cropping.
				if ( $align && $align != 'c' ) {
					if ( strpos( $align, 't' ) !== false || strpos( $align, 'tr' ) !== false || strpos( $align, 'tl' ) !== false ) {
						$src_y = 0;
					}

					if ( strpos( $align, 'b' ) !== false || strpos( $align, 'br' ) !== false || strpos( $align, 'bl' ) !== false ) {
						$src_y = $orig_height - $src_h;
					}

					if ( strpos( $align, 'l' ) !== false ) {
						$src_x = 0;
					}

					if ( strpos( $align, 'r' ) !== false ) {
						$src_x = $orig_width - $src_w;
					}
				}

				// Crop the image.
				$editor->crop( $src_x, $src_y, $src_w, $src_h, $dest_width, $dest_height );
			} else {
				// Just resize the image.
				$editor->resize( $dest_width, $dest_height );
			}

			// Save the image.
			$saved = $editor->save( $dest_file_name );

			// Print possible out of memory errors.
			if ( is_wp_error( $saved ) ) {
				@unlink( $dest_file_name );

				return $saved;
			}

			// Add the resized dimensions and alignment to original image metadata, so the images
			// can be deleted when the original image is delete from the Media Library.
			if ( $get_attachment ) {
				$metadata = wp_get_attachment_metadata( $get_attachment[0]->ID );

				if ( isset( $metadata['image_meta'] ) ) {
					$md = $saved['width'] . 'x' . $saved['height'];

					if ( $crop ) {
						$md .= $align ? "_${align}" : '_c';
					}

					$metadata['image_meta']['resized_images'][] = $md;
					wp_update_attachment_metadata( $get_attachment[0]->ID, $metadata );
				}
			}

			// Set the resized image URL.
			$resized_url = str_replace( basename( $url ), basename( $saved['path'] ), $url );
		} else {
			// Set the resized image URL.
			$resized_url = str_replace( basename( $url ), basename( $dest_file_name ), $url );
		}

		// Return the resized image URL.
		return apply_filters( 'woowbox_resize_image_resized_url', $resized_url );
	}

	/**
	 * Helper method to return common information about an image.
	 *
	 * @since 1.0.0
	 *
	 * @param array $args List of resizing args to expand for gathering info.
	 *
	 * @return WP_Error|array Return WP_Error on error, array of data on success.
	 */
	public function get_image_info( $args ) {
		// Unpack arguments.
		list( $url, $width, $height, $crop, $align, $quality, $retina, $data ) = $args;

		// Return an error if no URL is present.
		if ( empty( $url ) ) {
			return new WP_Error( 'woowbox-error-no-url', __( 'No image URL specified.', 'woowbox' ) );
		}

		// Get the image file path.
		$urlinfo       = wp_parse_url( $url );
		$wp_upload_dir = wp_upload_dir();

		// Interpret the file path of the image.
		if ( preg_match( '/\/[0-9]{4}\/[0-9]{2}\/.+$/', $urlinfo['path'], $matches ) ) {
			$file_path = $wp_upload_dir['basedir'] . $matches[0];
		} else {
			$pathinfo    = wp_parse_url( $url );
			$uploads_dir = is_multisite() ? '/files/' : '/wp-content/';
			$file_path   = ABSPATH . str_replace( dirname( $_SERVER['SCRIPT_NAME'] ) . '/', '', strstr( $pathinfo['path'], $uploads_dir ) );
			$file_path   = preg_replace( '/(\/\/)/', '/', $file_path );
		}

		// Attempt to stream and import the image if it does not exist based on URL provided.
		if ( ! file_exists( $file_path ) ) {
			return new WP_Error( 'woowbox-error-no-file', __( 'No file could be found for the image URL specified.', 'woowbox' ) );
		}

		// Get original image size.
		$size = @getimagesize( $file_path );

		// If no size data obtained, return an error.
		if ( ! $size ) {
			return new WP_Error( 'woowbox-error-no-size', __( 'The dimensions of the original image could not be retrieved.', 'woowbox' ) );
		}

		// Set original width and height.
		list( $orig_width, $orig_height, $orig_type ) = $size;

		// Generate width or height if not provided.
		if ( $width && ! $height ) {
			$height = floor( $orig_height * ( $width / $orig_width ) );
		} elseif ( $height && ! $width ) {
			$width = floor( $orig_width * ( $height / $orig_height ) );
		} elseif ( ! $width && ! $height ) {
			return new WP_Error( 'woowbox-error-no-size', __( 'The dimensions of the original image could not be retrieved.', 'woowbox' ) );
		}

		// Allow for different retina image sizes.
		$retina = $retina ? ( true === $retina ? 2 : $retina ) : 1;

		// Destination width and height variables.
		$dest_width  = $width * $retina;
		$dest_height = $height * $retina;

		// Some additional info about the image.
		$info = pathinfo( $file_path );
		$dir  = $info['dirname'];
		$ext  = $info['extension'];
		$name = wp_basename( $file_path, ".$ext" );

		// Suffix applied to filename.
		$suffix = "${dest_width}x${dest_height}";

		// Set alignment information on the file.
		if ( $crop ) {
			$suffix .= ( $align ) ? "_${align}" : '_c';
		}

		// Get the destination file name.
		$dest_file_name = "${dir}/${name}-${suffix}.${ext}";

		// Return the info.
		$info = array(
			'dir'            => $dir,
			'name'           => $name,
			'ext'            => $ext,
			'suffix'         => $suffix,
			'orig_width'     => $orig_width,
			'orig_height'    => $orig_height,
			'orig_type'      => $orig_type,
			'dest_width'     => $dest_width,
			'dest_height'    => $dest_height,
			'file_path'      => $file_path,
			'dest_file_name' => $dest_file_name,
		);

		return $info;

	}

	/**
	 * Helper method to return difference between two multidimentional arrays
	 *
	 * @author  Gajus Kuizinas <g.kuizinas@anuary.com>
	*
	 * @param array $arr1
	 * @param array $arr2
	 *
	 * @return array
	 */
	public function array_diff_key_recursive( array $arr1, array $arr2 ) {
		$diff      = array_diff_key( $arr1, $arr2 );
		$intersect = array_intersect_key( $arr1, $arr2 );

		foreach ( $intersect as $k => $v ) {
			if ( is_array( $arr1[ $k ] ) && is_array( $arr2[ $k ] ) ) {
				$d = self::array_diff_key_recursive( $arr1[ $k ], $arr2[ $k ] );

				if ( ! empty( $d ) ) {
					$diff[ $k ] = $d;
				}
			}
		}

		return $diff;
	}

	/**
	 * Get array of image sizes function.
	 *
	 * @access public
	 * @return array
	 */
	public function get_image_sizes() {
		global $_wp_additional_image_sizes;

		$sizes = array();
		foreach ( get_intermediate_image_sizes() as $_size ) {

			if ( in_array( $_size, array( 'thumbnail', 'medium', 'medium_large', 'large' ), true ) ) {

				if ( (bool) get_option( "{$_size}_crop" ) === true ) {

					continue;

				}
				$sizes[ $_size ]['name']   = $_size;
				$sizes[ $_size ]['width']  = get_option( "{$_size}_size_w" );
				$sizes[ $_size ]['height'] = get_option( "{$_size}_size_h" );
				$sizes[ $_size ]['crop']   = (bool) get_option( "{$_size}_crop" );

			} elseif ( isset( $_wp_additional_image_sizes[ $_size ] ) ) {

				if ( true === $_wp_additional_image_sizes[ $_size ]['crop'] ) {

					continue;
				}

				$sizes[ $_size ] = array(
					'name'   => $_size,
					'width'  => $_wp_additional_image_sizes[ $_size ]['width'],
					'height' => $_wp_additional_image_sizes[ $_size ]['height'],
					'crop'   => $_wp_additional_image_sizes[ $_size ]['crop'],
				);
			}
		}

		return $sizes;
	}

	/**
	 * Helper method to minify a string of data.
	 *
	 * @since 1.0.0
	 *
	 * @param string $string                       String of data to minify.
	 * @param bool   $strip_double_forward_slashes Strip double forwardslashes.
	 *
	 * @return string $string Minified string of data.
	 */
	public function minify( $string, $strip_double_forward_slashes = true ) {
		// Added a switch for stripping double forwardslashes.
		// This can be disabled when using URLs in JS, to ensure http:// doesn't get removed.
		// All other comment removal and minification will take place.
		$strip_double_forward_slashes = apply_filters( 'woow_minify_strip_double_forward_slashes', $strip_double_forward_slashes );

		if ( $strip_double_forward_slashes ) {
			$clean = preg_replace( '/((?:\/\*(?:[^*]|(?:\*+[^*\/]))*\*+\/)|(?:\/\/.*))/', '', $string );
		} else {
			// Use less aggressive method.
			$clean = preg_replace( '!/\*.*?\*/!s', '', $string );
			$clean = preg_replace( '/\n\s*\n/', "\n", $clean );
		}

		$clean = str_replace( array( "\r\n", "\r", "\t", "\n", '  ', '	  ', '	   ' ), '', $clean );

		return apply_filters( 'woowbox_minified_string', $clean, $string );
	}

	/**
	 * Check if url has image extention
	 *
	 * @param string $url
	 *
	 * @return bool
	 */
	public function is_image( $url ) {
		$p = strrpos( $url, '.' );

		if ( false === $p ) {
			return false;
		}

		$extension = strtolower( trim( substr( $url, $p ) ) );

		$img_extensions = array( '.gif', '.jpg', '.jpeg', '.png', '.tiff', '.tif' );

		if ( in_array( $extension, $img_extensions, true ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Check Premium
	 *
	 * @since 1.1.0
	 *
	 * @return array
	 */
	public function premium() {
		include_once ABSPATH . 'wp-admin/includes/plugin.php';

		$settings = WoowBox_Settings::get_instance()->get_setting( 'settings' );
		$license  = $settings['license'];

		$return = array(
			'key'    => $license,
			'plugin' => 'WoowBox',
		);
		if ( ! $license && is_plugin_active( 'grand-media/grand-media.php' ) ) {
			global $gmGallery;
			if ( ! empty( $gmGallery->options['license_key'] ) ) {
				$return['key']    = $gmGallery->options['license_key'];
				$return['plugin'] = 'GmediaGallery';
			}
		}
		if ( ! $license && is_plugin_active( 'flash-album-gallery/flag.php' ) ) {
			$flag_options = get_option( 'flag_options' );
			if ( ! empty( $flag_options['license_key'] ) && ! empty( $flag_options['license_name'] ) && 'GRANDPackPlus' === $flag_options['license_name'] ) {
				$return['key']    = $flag_options['license_name'] . ':' . $flag_options['license_key'];
				$return['plugin'] = 'Flagallery';
			}
		}

		return $return;
	}
}

// Load the common class.
$woowbox_common = WoowBox_CommonGlobal::get_instance();
