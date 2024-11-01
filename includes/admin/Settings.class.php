<?php

/**
 * Settings class.
*
 * @package WoowBox
 * @author  Thomas Griffin
 */
class WoowBox_Settings {

	/**
	 * Holds the class object.
	 *
	 * @since 1.0.0
	 *
	 * @var object
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
	 * @var object
	 */
	public $base;

	/**
	 * Holds the menu pagehook.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	public $hook;

	/**
	 * Primary class constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		// Load the base class object.
		$this->base = WoowBox::get_instance();

		// Add custom settings menu.
		add_action( 'admin_menu', array( $this, 'admin_menu' ), 11 );

		// Add the settings menu item to the Plugins table.
		add_filter( 'plugin_action_links_' . plugin_basename( plugin_dir_path( $this->base->file ) . 'woowbox.php' ), array( $this, 'settings_link' ) );
	}

	/**
	 * Returns the singleton instance of the class.
	 *
	 * @since 1.0.0
	 *
	 * @return WoowBox_Settings object.
	 */
	public static function get_instance() {
		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof WoowBox_Settings ) ) {
			self::$instance = new WoowBox_Settings();
		}

		return self::$instance;
	}

	/**
	 * Register the Settings menu item for WoowBox.
	 *
	 * @since 1.0.0
	 */
	public function admin_menu() {
		// Register the submenu.
		$this->hook = add_menu_page(
			__( 'WoowBox Settings', 'woowbox' ),
			__( 'WoowBox', 'woowbox' ),
			apply_filters( 'woowbox_menu_cap', 'manage_options' ),
			$this->base->plugin_slug . '-settings',
			array( $this, 'settings_page' ),
			plugin_dir_url( $this->base->file ) . 'assets/images/woowbox-icon.png'
		);

		// If successful, load admin assets only on that page and check for addons refresh.
		if ( $this->hook ) {
			add_action( 'load-' . $this->hook, array( $this, 'update_settings' ) );
			add_action( 'load-' . $this->hook, array( $this, 'settings_page_assets' ) );
		}
	}

	/**
	 * Saves Settings:
	 *
	 * @since 1.0.0
	 */
	public function update_settings() {
		// Check if user pressed the 'Update' button and nonce is valid.
		if ( ! ( isset( $_POST['woowbox-settings-submit'] ) || isset( $_POST['woowbox-settings-reset'] ) ) ) {
			return;
		}
		if ( ! wp_verify_nonce( $_POST['_nonce_woowbox_settings_save'], 'settings_save' ) ) {
			return;
		}

		if ( isset( $_POST['woowbox-settings-submit'] ) ) {
			// Update settings.
			$this->update_setting( 'settings', $_POST['_woowbox'] );

			// Output an admin notice so the user knows what happened.
			add_action( 'woowbox_settings_notice', array( $this, 'updated_settings' ) );
		}

		if ( isset( $_POST['woowbox-settings-reset'] ) ) {
			// Update settings.
			$this->update_setting( 'settings', array() );
			delete_option( 'woow_skins' );

			// Output an admin notice so the user knows what happened.
			add_action( 'woowbox_settings_notice', array( $this, 'settings_reset' ) );
		}
	}

	/**
	 * Helper method for updating a setting's value.
	 *
	 * @since 1.0.0
	 *
	 * @param string $key   The setting key.
	 * @param mixed  $value The value to set for the key.
	 */
	public function update_setting( $key, $value ) {
		// Prefix the key.
		$key = 'woowbox_' . $key;

		// Allow devs to filter.
		$value = apply_filters( 'woowbox_get_setting', $value, $key );

		// Update option.
		update_option( $key, $value );
	}

	/**
	 * Helper method for getting a setting's value. Falls back to the default
	 * setting value if none exists in the options table.
	 *
	 * @since 1.0.0
	 *
	 * @param string $key The setting key to retrieve.
	 *
	 * @return string|array Key value on success, false on failure.
	 */
	public function get_setting( $key ) {
		// Prefix the key.
		$prefixed_key = 'woowbox_' . $key;

		// Get the option value.
		$value = get_option( $prefixed_key );

		$default_value = $this->get_setting_default( $key );
		// If no value exists, fallback to the default.
		if ( ! isset( $value ) || false === $value ) {
			$value = $default_value;
		} elseif ( is_array( $value ) ) {
			$value = array_merge( (array) $default_value, $value );
		}

		// Allow devs to filter.
		$value = apply_filters( 'woowbox_get_setting', $value, $key, $prefixed_key );

		return $value;
	}

	/**
	 * Helper method for getting a setting's default value
	 *
	 * @since 1.0.0
	 *
	 * @param string $key The default setting key to retrieve.
	 *
	 * @return string       Key value on success, false on failure.
	 */
	public function get_setting_default( $key ) {

		// Prepare default values.
		$defaults = $this->get_setting_defaults();

		// Return the key specified.
		return isset( $defaults[ $key ] ) ? $defaults[ $key ] : false;
	}

	/**
	 * Retrieves the default settings
	 *
	 * @since 1.0.0
	 *
	 * @return array       Array of default settings.
	 */
	public function get_setting_defaults() {

		// Prepare default values.
		$defaults = array(
			'settings' => array(
				'license'      => '',
				'default_skin' => 'masonry',
				'custom_css'   => '',
			),
		);

		// Allow devs to filter the defaults.
		$defaults = apply_filters( 'woowbox_settings_defaults', $defaults );

		return $defaults;
	}

	/**
	 * Outputs a WordPress style notification to tell the user settings were saved
	 *
	 * @since 1.0.0
	 */
	public function updated_settings() {
		?>
		<div class="notice updated below-h2">
			<p><strong><?php _e( 'Settings saved successfully.', 'woowbox' ); ?></strong></p>
		</div>
		<?php
	}

	/**
	 * Outputs a WordPress style notification to tell the user settings were reset
	 *
	 * @since 1.0.0
	 */
	public function settings_reset() {
		?>
		<div class="notice updated below-h2">
			<p><strong><?php _e( 'Settings reset successfully.', 'woowbox' ); ?></strong></p>
		</div>
		<?php
	}

	/**
	 * Loads assets for the settings page.
	 *
	 * @since 1.0.0
	 */
	public function settings_page_assets() {
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_styles' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );
	}

	/**
	 * Register and enqueue settings page specific CSS.
	 *
	 * @since 1.0.0
	 */
	public function enqueue_admin_styles() {
		wp_register_style( $this->base->plugin_slug . '-settings-style', plugins_url( 'assets/css/settings.css', $this->base->file ),
			array(
				'spectrum',
			), $this->base->version
		);
		wp_enqueue_style( $this->base->plugin_slug . '-settings-style' );

		// Run a hook to load in custom styles.
		do_action( 'woowbox_settings_styles' );
	}

	/**
	 * Register and enqueue settings page specific JS.
	 *
	 * @since 1.0.0
	 */
	public function enqueue_admin_scripts() {
		// Settings.
		wp_register_script( $this->base->plugin_slug . '-settings-script', plugins_url( 'assets/js/settings.min.js', $this->base->file ),
			array(
				'jquery',
				'vuejs',
				'vue-toasted',
				'backbone',
				'filtrex',
				'spectrum',
			), $this->base->version, true
		);
		wp_enqueue_script( $this->base->plugin_slug . '-settings-script' );

		add_filter( 'woowbox_admin_scripts_l10n', array( $this, 'l10n' ) );

		// Code Editor.
		if ( ! defined( 'IFRAME_REQUEST' ) && function_exists( 'wp_enqueue_code_editor' ) ) {
			$settings_css = wp_enqueue_code_editor( array( 'type' => 'text/css' ) );

			// do nothing if CodeMirror disabled.
			if ( false !== $settings_css ) {
				// initialization.
				wp_add_inline_script(
					'code-editor',
					sprintf( 'jQuery( function($) { wp.codeEditor.initialize( "woowbox-custom-css", %s ); } );', wp_json_encode( $settings_css ) )
				);
			}
		}

		// Run a hook to load in custom scripts.
		do_action( 'woowbox_settings_scripts' );
	}

	/**
	 * Callback for l10n filter.
	 *
	 * @param array $l10n Localization.
	 *
	 * @return array
	 */
	public function l10n( $l10n ) {
		$data = $this->get_setting( 'settings' );

		return array_merge( $l10n,
			array(
				'siteurl'                     => site_url(),
				'fill_preset_name'            => __( 'Fill the Preset Name', 'woowbox' ),
				'delete_default_preset_error' => __( 'You can\'t delete skin/preset chosen by default', 'woowbox' ),
				'default_skin'                => $data['default_skin'],
				'txt_default_skin_sign'       => ' ' . __( '(default)', 'woowbox' ),
				'txt_default'                 => __( 'Default', 'woowbox' ),
			)
		);
	}

	/**
	 * Callback to output the WoowBox settings page.
	 *
	 * @since 1.0.0
	 */
	public function settings_page() {
		do_action( 'woowbox_head' );

		// Get the settings data.
		$data            = $this->get_setting( 'settings' );
		$data['license'] = WoowBox_CommonGlobal::get_instance()->premium();

		?>
		<div class="wrap woowbox-wrap">
			<h1 class="wp-heading-inline">
				<?php _e( 'Settings', 'woowbox' ); ?>
			</h1>
			<?php
			do_action( 'woowbox_settings_notice' );

			// Load view.
			$this->base->load_admin_partial(
				'settings',
				$data
			);

			// Load view.
			$this->base->load_admin_partial(
				'skins',
				$data
			);
			?>
		</div>
		<?php
	}

	/**
	 * Add Settings page to plugin action links in the Plugins table.
	 *
	 * @since 1.0.0
	 *
	 * @param array $links Default plugin action links.
	 *
	 * @return array $links Amended plugin action links.
	 */
	public function settings_link( $links ) {
		$settings_link = sprintf(
			'<a href="%s">%s</a>',
			esc_url(
				add_query_arg(
					array( 'page' => 'woowbox-settings' ),
					admin_url( 'admin.php' )
				)
			),
			__( 'Settings', 'woowbox' )
		);
		array_unshift( $links, $settings_link );

		return $links;

	}
}

// Load the settings class.
$woowbox_settings = WoowBox_Settings::get_instance();
