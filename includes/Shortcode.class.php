<?php

/**
 * Shortcode class.
 *
 * @since     1.0.0
 *
 * @package   WoowBox
 * @author    Sergey Pasyuk
 */
class WoowBox_Shortcode {

	/**
	 * Holds the class object.
	 *
	 * @since 1.0.0
	 *
	 * @var WoowBox_Shortcode object
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
	 * Holds the Skins class object.
	 *
	 * @since 1.0.0
	 *
	 * @var WoowBox_Skins object
	 */
	public $skins;

	/**
	 * Iterator for shortcodes on the page.
	 *
	 * @since 1.0.0
	 *
	 * @var int
	 */
	public $counter = 1;

	/**
	 * Array of gallery ids on the page.
	 *
	 * @since 1.0.0
	 *
	 * @var array
	 */
	public $gallery_ids = array();

	/**
	 * Array of galleries with data
	 *
	 * @since 1.0.0
	 *
	 * @var array
	 */
	public $galleries = array();

	/**
	 * Gallery output HTML
	 *
	 * @var mixed
	 * @access public
	 */
	public $gallery_markup;

	/**
	 * Is mobile?
	 *
	 * @var mixed
	 * @access public
	 */
	public $is_mobile;

	/**
	 * Primary class constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		// Load the base class object.
		$this->base      = WoowBox::get_instance();
		$this->skins     = WoowBox_Skins::get_instance();
		$this->is_mobile = woow_mobile_detect()->isMobile();

		// Load hooks and filters.
		add_filter( 'style_loader_tag', array( $this, 'add_stylesheet_property_attribute' ) );
		add_filter( 'post_gallery', array( $this, 'post_gallery' ), 10, 3 );

		add_filter( 'wp_footer', array( $this, 'add_inline_styles' ), 20 );

	}

	/**
	 * Returns the singleton instance of the class.
	 *
	 * @return WoowBox_Shortcode object.
	 * @since 1.0.0
	 *
	 */
	public static function get_instance() {
		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof WoowBox_Shortcode ) ) {
			self::$instance = new WoowBox_Shortcode();
		}

		return self::$instance;
	}

	/**
	 * Add inline custom styles to the footer
	 *
	 * @return void
	 * @since 1.0.0
	 *
	 */
	public function add_inline_styles() {
		// Do nothing if there is no gallery on the page.
		if ( ! $this->gallery_ids ) {
			return;
		}

		$settings = WoowBox_Settings::get_instance()->get_setting( 'settings' );
		// Build out the custom CSS.
		$style = '<style type="text/css" id="woowbox-custom-styles">' . WoowBox_CommonGlobal::get_instance()->minify( html_entity_decode( stripslashes( $settings['custom_css'] ), ENT_QUOTES ), false ) . '</style>';

		echo $style;
	}

	/**
	 * Add the 'property' tag to stylesheets enqueued in the body
	 *
	 * @param string $tag '<link>' tag.
	 *
	 * @return string
	 * @since 1.0.0
	 *
	 */
	public function add_stylesheet_property_attribute( $tag ) {
		// If the <link> stylesheet is any WOOW-based stylesheet, add the property attribute.
		if ( strpos( $tag, "id='woow-" ) !== false && strpos( $tag, 'property="stylesheet"' ) === false ) {
			$tag = str_replace( '/>', 'property="stylesheet" />', $tag );
		}

		return $tag;
	}

	/**
	 * Creates the shortcode for the plugin.
	 *
	 * @param string   $output   The gallery output. Default empty.
	 * @param array    $attr     Attributes of the gallery shortcode.
	 * @param int      $instance Unique numeric ID of this gallery shortcode instance.
	 *
	 * @return string The gallery output.
	 * @since 1.0.0
	 *
	 * @global WP_Post $post     The current post object.
	 */
	public function post_gallery( $output, $attr, $instance ) {
		global $post;

		$return = $output; // fallback.

		$atts = shortcode_atts(
			apply_filters(
				'woowbox_allowed_shortcode_atts',
				array(
					'order'        => 'ASC',
					'exclude'      => '',
					'id'           => $post ? $post->ID : 0,
					'ids'          => '',
					'woowbox-skin' => '',
					'link'         => 'post',
					'size'         => 'thumbnail',
					'columns'      => '3',
					'orderby'      => 'post__in',
					'align'        => '',
					'width'        => '',
					'margin'       => '',
				)
			), $attr, 'gallery'
		);
		$atts = apply_filters( 'woowbox_atts', $atts );

		$settings = WoowBox_Settings::get_instance()->get_setting( 'settings' );

		if ( ! $atts['woowbox-skin'] || 'none' === $atts['woowbox-skin'] ) {
			if ( empty( $settings['default_skin'] ) || 'none' === $atts['woowbox-skin'] ) {
				return $return;
			} else {
				$atts['woowbox-skin'] = $settings['default_skin'];
			}
		}

		$set_skin = explode( ':', $atts['woowbox-skin'], 2 );
		$new_skin = $set_skin[0];

		$preset = 'default';
		if ( isset( $set_skin[1] ) ) {
			$preset = trim( $set_skin[1] );
		}

		// Check if gallery's skin exists and fallback to default skin or false.
		$skin = $this->skins->get_skin( $new_skin );
		if ( ! $skin ) {
			return $new_skin;
		}

		if ( $new_skin !== $skin->slug ) {
			$preset = 'default';
		}

		if ( ! empty( $atts['ids'] ) ) {
			$_attachments = get_posts(
				array(
					'include'        => $atts['ids'],
					'post_status'    => 'inherit',
					'post_type'      => 'attachment',
					'post_mime_type' => 'image',
					'order'          => $atts['order'],
					'orderby'        => $atts['orderby'],
				)
			);
			$attachments  = array();
			foreach ( $_attachments as $key => $val ) {
				$attachments[ $val->ID ] = $_attachments[ $key ];
			}
		} elseif ( ! empty( $atts['exclude'] ) ) {
			$attachments = get_children(
				array(
					'post_parent'    => $atts['id'],
					'exclude'        => $atts['exclude'],
					'post_status'    => 'inherit',
					'post_type'      => 'attachment',
					'post_mime_type' => 'image',
					'order'          => $atts['order'],
					'orderby'        => $atts['orderby'],
				)
			);
		} else {
			$attachments = get_children(
				array(
					'post_parent'    => $atts['id'],
					'post_status'    => 'inherit',
					'post_type'      => 'attachment',
					'post_mime_type' => 'image',
					'order'          => $atts['order'],
					'orderby'        => $atts['orderby'],
				)
			);
		}
		if ( empty( $attachments ) ) {
			return '';
		}

		if ( is_feed() ) {
			$output = "\n";
			foreach ( $attachments as $att_id => $attachment ) {
				$output .= wp_get_attachment_link( $att_id, $atts['size'], true ) . "\n";
			}

			return $output;
		}

		$attachments_ids = array_keys( $attachments );
		sort( $attachments_ids );

		$gallery_id = 'wb_' . md5( join( '', $attachments_ids ) );

		$request_type = ! empty( $_SERVER['HTTP_X_REQUESTED_WITH'] ) && strtolower( $_SERVER['HTTP_X_REQUESTED_WITH'] ) === 'xmlhttprequest' ? 'ajax' : 'direct';
		if ( 'ajax' === $request_type ) {
			$gallery_id = $gallery_id . '_' . current_time( 'timestamp' );
		}

		// Lets check if this gallery has already been output on the page.
		if ( ! in_array( $gallery_id, $this->gallery_ids, true ) ) {
			$this->gallery_ids[] = $gallery_id;
		} else {
			$gallery_id = $gallery_id . '_' . $this->counter;
		}
		$gallery_id     = sanitize_html_class( $gallery_id );
		$gallery['uid'] = $gallery_id;

		// Limit the number of images returned, if specified.
		//if(isset($atts['limit']) && is_numeric($atts['limit'])){
		//    $items           = array_slice($gallery['data'], 0, absint($atts['limit']), true);
		//    $gallery['data'] = $items;
		//}

		$model = $this->skins->get_skin_model( $skin->slug, $preset );

		$gallery['config']['skin']        = $skin->slug;
		$gallery['config'][ $skin->slug ] = $model;

		$gallery['data'] = array();
		foreach ( $attachments as $attachment ) {
			$gallery['data'][] = woow_prepare_attachment_data( $attachment );
		}

		// Allow the gallery data to be filtered before it is used to create the gallery output.
		$gallery = apply_filters( 'woowbox_pre_data', $gallery, $this->counter );

		// If there is no data to output or the gallery is inactive, do nothing.
		if ( ! $gallery || empty( $gallery['data'] ) ) {
			return '';
		}

		$this->galleries[ $gallery_id ] = $gallery;

		// Prepare variables.
		$this->gallery_markup = '';

		// Run a skin specific hook for the skin.
		do_action( 'woow_' . $skin->slug . '_skin', $gallery );

		// Run a hook before the gallery output begins but after scripts and inits have been set.
		do_action( 'woowbox_before_output', $gallery );

		// Apply a filter before starting the gallery HTML.
		$this->gallery_markup = apply_filters( 'woowbox_output_start', $this->gallery_markup, $gallery );

		// Schema.org microdata ( Itemscope, etc. ) interferes with Google+ Sharing... so we are adding this via filter rather than hardcoding.
		$schema_microdata = apply_filters( 'woowbox_output_shortcode_schema_microdata', 'itemscope itemtype="http://schema.org/ImageGallery"', $gallery );

		// Build out the gallery HTML.
		$this->gallery_markup .= '<div id="' . $gallery_id . '" class="' . $this->get_gallery_classes( $gallery ) . '" ' . $schema_microdata . '>';

		$this->gallery_markup = apply_filters( 'woowbox_output_before_container', $this->gallery_markup, $gallery );

		$this->gallery_markup .= $skin->skin( $gallery );

		$this->gallery_markup = apply_filters( 'woowbox_output_after_container', $this->gallery_markup, $gallery );

		// Add no JS fallback support.
		$no_js                = '<noscript>';
		$no_js                .= $this->get_indexable_content( $gallery );
		$no_js                .= '</noscript>';
		$this->gallery_markup .= apply_filters( 'woowbox_output_noscript', $no_js, $gallery );

		$assets_links = array();
		if ( file_exists( realpath( dirname( $skin->file ) . '/assets/style.css' ) ) ) {
			$assets_links[] = add_query_arg( array( 'v' => $skin->version ), plugins_url( 'assets/style.css', $skin->file ) );
		}
		if ( file_exists( realpath( dirname( $skin->file ) . '/assets/script.js' ) ) ) {
			$assets_links[] = add_query_arg( array( 'v' => $skin->version ), plugins_url( 'assets/script.js', $skin->file ) );
		}
		$request_type = ! empty( $_SERVER['HTTP_X_REQUESTED_WITH'] ) && strtolower( $_SERVER['HTTP_X_REQUESTED_WITH'] ) === 'xmlhttprequest' ? 'ajax' : 'direct';
		$callback     = esc_attr( $skin->slug . '_skin' );

		$this->gallery_markup .= "<script type=\"text/javascript\">function waitforglobal_{$gallery_id}(){if(window.WoowBox && window.WoowBox.woowboxRequiredAssets){window.WoowBox.woowboxRequiredAssets({links:" . str_replace( '\/', '/', wp_json_encode( $assets_links ) ) . ",request_type:'{$request_type}',callback:'{$callback}',gallery_id:'{$gallery_id}'});}else{setTimeout(waitforglobal_{$gallery_id},500);}}waitforglobal_{$gallery_id}();</script>";
		$this->gallery_markup .= '</div>';
		$this->gallery_markup = apply_filters( 'woowbox_output_end', $this->gallery_markup, $gallery );

		// Increment the counter.
		$this->counter ++;

		// Return the gallery HTML.
		return apply_filters( 'woowbox_output', $this->gallery_markup, $gallery );
	}

	/**
	 * Helper method for adding custom gallery classes.
	 *
	 * @param array $data The gallery data to use for retrieval.
	 *
	 * @return string String of space separated gallery classes.
	 * @since 1.0.0
	 *
	 */
	public function get_gallery_classes( $data ) {
		// Set default class.
		$classes   = array();
		$classes[] = 'woowbox-gallery-wrap';

		// Allow filtering of classes and then return what's left.
		$classes = apply_filters( 'woowbox_output_classes', $classes, $data );

		return trim( implode( ' ', array_unique( array_map( 'sanitize_html_class', $classes ) ) ) );
	}

	/**
	 * Returns a set of indexable image links to allow SEO indexing for preloaded images.
	 *
	 * @param mixed $gallery Gallery Data.
	 *
	 * @return string String of indexable content HTML.
	 * @since 1.0.0
	 *
	 */
	public function get_indexable_content( $gallery ) {
		$data = apply_filters( 'woowbox_indexable_data', $gallery['data'], $gallery );

		// If there are no images, don't do anything.
		$output = '';
		foreach ( $data as $item ) {

			$imagesrc = apply_filters( 'woowbox_default_image_src', wp_get_attachment_image_src( $item['id'], 'large', true ), $item, $gallery, $this->is_mobile );
			if ( ! empty( $imagesrc ) ) {
				$output .= "\n" . '<img class="skip-lazy" data-lazy-src="" src ="' . esc_url( $imagesrc[0] ) . '" width="' . absint( $imagesrc[1] ) . '" height="' . absint( $imagesrc[2] ) . '" title="' . trim( esc_html( $item['title'] ) ) . '" alt="' . trim( esc_html( $item['alt'] ) ) . '" />';
			}
		}

		return $output;
	}
}

// Load the shortcode class.
$woowbox_shortcode = WoowBox_Shortcode::get_instance();
