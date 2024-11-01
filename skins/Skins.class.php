<?php
/**
 * WoowBox Skins class
 */
defined( 'ABSPATH' ) || die( 'No script kiddies please!' );

/**
 * Main skin class.
 *
 * @author  Sergey Pasyuk
 */
class WoowBox_Skins {

	/**
	 * @var WoowBox_Skins object Holds the class object.
	 */
	public static $instance;

	/**
	 * @var string Skin file.
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
	 * Path to the skins directory.
	 *
	 * @var string
	 */
	private $include_path = '';

	/**
	 * Primary class constructor.
	 */
	public function __construct() {
		// Load the base class object.
		$this->base = WoowBox::get_instance();

		$this->include_path = realpath( dirname( __FILE__ ) );

		$skins_folders = glob( $this->include_path . '/*', GLOB_ONLYDIR | GLOB_NOSORT );
		foreach ( $skins_folders as $path ) {
			$this->load_file( $path . '/' . basename( $path ) . '.php' );
		}
	}

	/**
	 * Returns the singleton instance of the class.
	 *
	 * @return WoowBox_Skins object
	 */
	public static function get_instance() {
		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof WoowBox_Skins ) ) {
			self::$instance = new WoowBox_Skins();
		}

		return self::$instance;
	}

	/**
	 * Get available skins
	 */
	public function get_skins() {
		$skins = apply_filters( 'woow_skins', array() );
		ksort( $skins );

		return $skins;
	}

	/**
	 * Get skin
	 *
	 * @param string $skin Skin slug.
	 *
	 * @return object|bool Skin
	 */
	public function get_skin( $skin = '' ) {
		$skins = $this->get_skins();

		if ( empty( $skin ) || ! isset( $skins[ $skin ] ) ) {
			$settings = WoowBox_Settings::get_instance()->get_setting( 'settings' );
			if ( $settings['default_skin'] && $settings['default_skin'] !== $skin ) {
				return $this->get_skin( $settings['default_skin'] );
			} else {
				return false;
			}
		}

		return $skins[ $skin ];
	}

	/**
	 * Get skin model
	 *
	 * @param string $skin Skin slug.
	 * @param string $preset
	 * @param array  $overwrites
	 *
	 * @return array Skin model
	 */
	public function get_skin_model( $skin, $preset = 'default', $overwrites = array() ) {
		$defaults     = $this->get_skin_defaults( $skin );
		$skins_models = get_option( 'woow_skins', array() );
		if ( 'default' !== $preset && ! isset( $skins_models[ $skin ][ $preset ] ) ) {
			$preset = 'default';
		}
		$model = isset( $skins_models[ $skin ][ $preset ] ) ? $skins_models[ $skin ][ $preset ] : array();
		if ( empty( $model ) ) {
			$model = array_merge( $defaults, (array) $overwrites );
		} else {
			$model = array_merge( $defaults, (array) $model, (array) $overwrites );
		}

		return $model;
	}

	/**
	 * Get skin defaults
	 *
	 * @param string $skin Skin slug.
	 *
	 * @return array Skin default settings
	 */
	public function get_skin_defaults( $skin ) {
		$skin_object = $this->get_skin( $skin );
		$schema      = $skin_object->settings();
		$defaults    = $this->_get_schema_defaluts( $schema );

		return $defaults;
	}

	/**
	 * Include a class file.
	 *
	 * @param string $path
	 *
	 * @return bool successful or not
	 */
	private function load_file( $path ) {
		if ( $path && is_readable( $path ) ) {
			include_once $path;

			return true;
		}

		return false;
	}

	/**
	 * Recursive function to get default values from skin schema
	 *
	 * @param array $schema
	 * @param array $defaults
	 *
	 * @return array Schema default settings
	 */
	private function _get_schema_defaluts( $schema, $defaults = array() ) {
		foreach ( $schema as $key => $val ) {
			if ( isset( $val['default'] ) ) {
				$defaults[ $key ] = $val['default'];
			} elseif ( is_array( $val ) ) {
				$defaults = $this->_get_schema_defaluts( $val, $defaults );
			}
		}

		return $defaults;
	}
}

// Load the skin class.
$woowbox_skins = WoowBox_Skins::get_instance();
