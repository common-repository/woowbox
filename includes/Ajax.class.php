<?php
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

class WoowBox_Ajax {
	/**
	 * Holds the class object.
	 *
	 * @since 1.0.0
	 *
	 * @var WoowBox_Ajax object
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
		add_action( 'wp_ajax_woowbox_license', array( $this, 'woowbox_license' ) );
		add_action( 'wp_ajax_woow_save_skin_data', array( $this, 'save_skin_data' ) );
		add_action( 'wp_ajax_woow_delete_skin_preset', array( $this, 'delete_skin_preset' ) );
	}

	/**
	 * Returns the singleton instance of the class.
	 *
	 * @since 1.0.0
	 *
	 * @return WoowBox_Ajax object
	 */
	public static function get_instance() {
		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof WoowBox_Ajax ) ) {
			self::$instance = new WoowBox_Ajax();
		}

		return self::$instance;
	}

	/**
	 * WOOW Save License.
	 *
	 * @since 1.1.0
	 */
	public function woowbox_license() {
		// Bail out if we fail a security check.
		woowbox_verify_nonce( 'settings_save' );

		$license_action      = woow_POST( 'license_action', '' );
		$license             = trim( woow_POST( 'license', '' ) );
		$settings            = WoowBox_Settings::get_instance()->get_setting( 'settings' );
		$settings['license'] = $license;

		WoowBox_Settings::get_instance()->update_setting( 'settings', $settings );

		if ( 'check' === $license_action && ! $license ) {
			$message = '';
		} else {
			$message = $license ? __( 'Saved successfuly', 'woowbox' ) : '';
		}
		wp_send_json_success( $message );
	}

	/**
	 * WOOW Save Skin Data.
	 *
	 * @since 1.0.0
	 */
	public function save_skin_data() {
		// Bail out if we fail a security check.
		woow_verify_nonce( 'skin_settings_save' );

		$skin          = woow_POST( 'skin' );
		$preset        = trim( woow_POST( 'preset', 'default' ) );
		$data          = woow_POST( 'data', '{}' );
		$default_reset = woow_POST( 'default_reset' );

		if ( ! $skin || ! $preset ) {
			wp_send_json_error( __( 'Something goes wrong.', 'woowbox' ) );
		}

		$skins_data = get_option( 'woow_skins', array() );
		if ( $default_reset ) {
			unset( $skins_data[ $skin ]['default'] );
		} else {
			$skins_data[ $skin ][ $preset ] = json_decode( $data );
			ksort( $skins_data[ $skin ] );
			ksort( $skins_data );
		}

		update_option( 'woow_skins', $skins_data );

		wp_send_json_success( sprintf( __( 'Settings saved (`%s` preset)', 'woowbox' ), $preset ) );
	}

	/**
	 * WOOW Save Skin Data.
	 *
	 * @since 1.0.0
	 */
	public function delete_skin_preset() {
		// Bail out if we fail a security check.
		woow_verify_nonce( 'skin_settings_save' );

		$skin   = woow_POST( 'skin' );
		$preset = woow_POST( 'preset', 'default' );

		if ( ! $skin || 'default' === $preset ) {
			wp_send_json_error( __( 'Something goes wrong.', 'woowbox' ) );
		}

		$settings       = WoowBox_Settings::get_instance()->get_setting( 'settings' );
		$settings_skin  = explode( ':', $settings['default_skin'], 2 );
		$default_skin   = $settings_skin[0];
		$default_preset = isset( $settings_skin[1] ) ? trim( $settings_skin[1] ) : 'default';

		if ( $skin === $default_skin && $preset === $default_preset ) {
			wp_send_json_error( __( 'You can\'t delete skin/preset chosen by default', 'woowbox' ) );
		}

		$skins_data = get_option( 'woow_skins', array() );
		unset( $skins_data[ $skin ][ $preset ] );

		update_option( 'woow_skins', $skins_data );

		wp_send_json_success( sprintf( __( '`%s` preset was deleted', 'woowbox' ), $preset ) );
	}
}

// Load the common admin class.
$woowbox_ajax = WoowBox_Ajax::get_instance();
