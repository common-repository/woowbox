<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly.

/**
 * Elementor class.
 *
 * @since   1.2.0
 *
 * @package WoowBox
 * @author  Sergey Pasyuk
 */
class WoowBox_Elementor {

	/**
	 * Holds the class object.
	 *
	 * @since 1.2.0
	 *
	 * @var WoowBox_Elementor object
	 */
	public static $instance;

	/**
	 * Path to the file.
	 *
	 * @since 1.2.0
	 *
	 * @var string
	 */
	public $file = __FILE__;

	/**
	 * Holds the base class object.
	 *
	 * @since 1.2.0
	 *
	 * @var WoowBox object
	 */
	public $base;

	/**
	 * Primary class constructor.
	 *
	 * @since 1.2.0
	 */
	public function __construct() {
		// Load the base class object.
		$this->base = WoowBox::get_instance();

		if ( ! defined( 'ELEMENTOR_VERSION' ) ) {
			return;
		}
		add_action( 'elementor/init', array( $this, 'elementor_category' ) );
		add_action( 'elementor/init', array( $this, 'elementor_add_elements' ) );

		add_action( 'elementor/editor/before_enqueue_styles', array( $this, 'elementor_before_enqueue_styles' ) );
		add_action( 'elementor/editor/before_enqueue_scripts', array( $this, 'elementor_before_enqueue_scripts' ) );
	}

	/**
	 * Returns the singleton instance of the class.
	 *
	 * @since 1.2.0
	 *
	 * @return WoowBox_Elementor object
	 */
	public static function get_instance() {
		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof WoowBox_Elementor ) ) {
			self::$instance = new WoowBox_Elementor();
		}

		return self::$instance;
	}

	/**
	 * Enqueue styles
	 *
	 * @since 1.2.0
	 */
	public function elementor_before_enqueue_styles() {
		wp_enqueue_style( $this->base->plugin_slug . '-editor-modal-style', plugins_url( 'assets/css/editor-modal.css', $this->base->file ), array(), $this->base->version );
	}

	/**
	 * Enqueue scripts
	 *
	 * @since 1.2.0
	 */
	public function elementor_before_enqueue_scripts() {
		wp_enqueue_script( 'elementor-preview-script', plugins_url( '/assets/js/elementor.js', __FILE__ ), array( 'jquery' ), '1.0.0', true );
		wp_localize_script( 'elementor-preview-script', 'WoowBoxElementor',
			array(
				'modal_title' => __( 'WoowBox Skin Preset Settings', 'woowbox' ),
				'modal_src'   => admin_url( 'admin.php?woowboxiframe=1&skin=default' ),
			)
		);
	}

	/**
	 * Add elements to Elementor.
	 *
	 * @since 1.2.0
	 */
	public function elementor_add_elements() {
		require_once dirname( __FILE__ ) . '/elements/woowbox-gallery.php';
	}

	/**
	 * Add category to Elementor.
	 *
	 * @since 1.2.0
	 */
	public function elementor_category() {
		\Elementor\Plugin::instance()->elements_manager->add_category( 'woowbox', array(
			'title' => __( 'WoowBox', 'woowbox' ),
			'icon'  => 'eicon-gallery-masonry',
		), 1 );
	}
}

// Load the common class.
$woowbox_elementor = WoowBox_Elementor::get_instance();
