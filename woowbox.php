<?php
/**
 * Plugin Name: WoowBox - Gallery & Lightbox
 * Plugin URI:  https://wordpress.org/plugins/woowbox/
 * Description: Skins for your WP galleries
 * Author:      pasyuk
 * Author URI:  https://woowgallery.com/woowbox/
 * Version:     1.5.2
 * License:     GPL2
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: woowbox
 * Domain Path: /languages
 *
 * @package WOOW
 */

defined( 'ABSPATH' ) || die( 'No script kiddies please!' );

/**
 * Main plugin class.
*
 * @package WoowBox
 * @author  Sergey Pasyuk
 */
class WoowBox {

	/**
	 * Holds the class object.
	 *
	 * @since 1.0.0
	 *
	 * @var WoowBox object
	 */
	public static $instance;

	/**
	 * Plugin version, used for cache-busting of style and script file references.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	public $version = '1.5.2';

	/**
	 * The name of the plugin.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	public $plugin_name = 'WoowBox';

	/**
	 * Unique plugin slug identifier.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	public $plugin_slug = 'woowbox';

	/**
	 * Plugin file.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	public $file = __FILE__;

	/**
	 * Primary class constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {

		// Fire a hook before the class is setup.
		do_action( 'woowbox_pre_init' );

		// Load the plugin textdomain.
		add_action( 'plugins_loaded', array( $this, 'load_plugin_textdomain' ) );

		add_action( 'plugins_loaded', array( $this, 'elementor_addons' ) );

		// Load the plugin.
		add_action( 'init', array( $this, 'init' ), 0 );

		// Fire a hook for plugin activation.
		register_activation_hook( $this->file, array( &$this, 'WoowBox::activation_hook' ) );
		// Fire a hook for plugin deactivation.
		register_deactivation_hook( $this->file, array( &$this, 'WoowBox::deactivation_hook' ) );

	}

	/**
	 * Fired when the plugin is deactivated to clear flushed permalinks flag and flush the permalinks.
	 *
	 * @since 1.0.0
	 *
	 * @param boolean $network_wide True if WPMU superadmin uses "Network Activate" action, false otherwise.
	 */
	public static function deactivation_hook( $network_wide ) {

		// Flush rewrite rules.
		flush_rewrite_rules();

	}

	/**
	 * Returns the singleton instance of the class.
	 *
	 * @since 1.0.0
	 *
	 * @return WoowBox object
	 */
	public static function get_instance() {

		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof WoowBox ) ) {
			self::$instance = new WoowBox();
		}

		return self::$instance;

	}

	/**
	 * Loads the plugin textdomain for translation.
	 *
	 * @since 1.0.0
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain( $this->plugin_slug, false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );

	}

	/**
	 * Loads the plugin into WordPress.
	 *
	 * @since 1.0.0
	 */
	public function init() {

		// Run hook once WOOW has been initialized.
		do_action( 'woowbox_init' );

		// Load global components.
		$this->require_global();

		// Load admin only components.
		if ( is_admin() ) {
			$this->check_installation();
			$this->require_admin();
		}

		// Add hook for when WOOW has loaded.
		do_action( 'woowbox_loaded' );

	}

	/**
	 * Display a nag notice if the server's configuration doesn't match requirements
	 *
	 * @since 1.0.0
	 */
	public function check_installation() {

		// Output a notice if PHP version less than 5.3.
		if ( (float) phpversion() < 5.3 ) {
			add_action( 'admin_notices', array( $this, 'notice_php_version' ) );
		}

		// Output a notice if missing cropping extensions because WOOW needs them.
		if ( ! ( extension_loaded( 'gd' ) && function_exists( 'gd_info' ) ) && ! extension_loaded( 'imagick' )
		) {
			add_action( 'admin_notices', array( $this, 'notice_missing_extensions' ) );
		}
	}

	/**
	 * Output a nag notice if the user has a PHP version older than 5.3
	 *
	 * @since 1.0.0
	 */
	public function notice_php_version() {
		?>
		<div class="error">
			<p><?php _e( 'WoowBox requires PHP 5.3 or greater for some specific functionality. Please have your web host resolve this.', 'woowbox' ); ?></p>
		</div>
		<?php
	}

	/**
	 * Outputs a notice when the GD and Imagick PHP extensions aren't installed.
	 *
	 * @since 1.0.0
	 */
	public function notice_missing_extensions() {

		?>
		<div class="error">
			<p>
				<strong><?php _e( 'The GD or Imagick libraries are not installed on your server. WoowBox requires at least one (preferably Imagick) in order to crop images and may not work properly without it. Please contact your webhost and ask them to compile GD or Imagick for your PHP install.', 'woowbox' ); ?></strong>
			</p>
		</div>
		<?php

	}

	/**
	 * Loads all global files into scope.
	 *
	 * @since 1.0.0
	 */
	public function require_global() {

		require dirname( __FILE__ ) . '/includes/helper.functions.php';
		require dirname( __FILE__ ) . '/includes/CommonGlobal.class.php';
		require dirname( __FILE__ ) . '/includes/admin/Settings.class.php';

		// Load WoowBox Skins.
		require dirname( __FILE__ ) . '/skins/Skins.class.php';

		// Load WoowBox Ajax functions.
		require dirname( __FILE__ ) . '/includes/Ajax.class.php';

		require dirname( __FILE__ ) . '/includes/Shortcode.class.php';

	}

	/**
	 * Loads all admin related files into scope.
	 *
	 * @since 1.0.0
	 */
	public function require_admin() {

		require dirname( __FILE__ ) . '/includes/admin/Admin.class.php';
		require dirname( __FILE__ ) . '/includes/admin/Notice.class.php';
		require dirname( __FILE__ ) . '/includes/admin/EditorModal.class.php';

	}

	/**
	 * Loads the plugin textdomain for translation.
	 *
	 * @since 1.2.0
	 */
	public function elementor_addons() {

		// Load Elementor widgets.
		require dirname( __FILE__ ) . '/elementor/Elementor.class.php';

	}

	/**
	 * Loads a partial view for the Administration screen
	 *
	 * @since 1.0.0
	 *
	 * @param string $view PHP file at includes/admin/partials, excluding file extension.
	 * @param array  $data Any data to pass to the view.
	 *
	 * @return void
	 */
	public function load_admin_partial( $view, $data = array() ) {

		$dir = trailingslashit( plugin_dir_path( __FILE__ ) . 'includes/admin/partials' );

		if ( file_exists( $dir . $view . '.php' ) ) {
			require_once( $dir . $view . '.php' );
		}

	}

	/**
	 * Loads the default plugin options.
	 *
	 * @since 1.0.0
	 *
	 * @return array Array of default plugin options.
	 */
	public function default_options() {

		$options = array(
			'key' => '',
		);

		return apply_filters( 'woowbox_default_options', $options );

	}

	/**
	 * Fired when the plugin is activated.
	 *
	 * @since 1.0.0
	 *
	 * @global int     $wp_version   The version of WordPress for this install.
	 * @global object  $wpdb         The WordPress database object.
	 *
	 * @param  boolean $network_wide True if WPMU superadmin uses "Network Activate" action, false otherwise.
	 */
	public function activation_hook( $network_wide ) {

		global $wp_version;
		if ( version_compare( $wp_version, '4.4.0', '<' ) ) {
			deactivate_plugins( plugin_basename( __FILE__ ) );
			wp_die( sprintf( __( 'Sorry, but your version of WordPress does not meet WoowBox\'s required version of <strong>4.0.0</strong> or higher to run properly. The plugin has been deactivated. <a href="%s">Click here to return to the Dashboard</a>.', 'woowbox' ), get_admin_url() ) );
		}

		if ( is_multisite() && $network_wide ) {
			$site_list = version_compare( $wp_version, '4.6.0', '<' ) ? wp_get_sites() : get_sites();
			foreach ( (array) $site_list as $site ) {
				switch_to_blog( $site['blog_id'] );

				// Set default options.
				$this->setup();

				restore_current_blog();
			}
		} else {
			// Set default options.
			$this->setup();
		}

	}

	/**
	 * Setup the plugin settings in DB
	 *
	 * @since 1.0.0
	 */
	public function setup() {

		// Set default options.
		$version = get_option( 'woowbox_version' );
		if ( empty( $version ) ) {
			update_option( 'woowbox_version', $this->version );
			update_option( 'woowbox_install_date', time() );
		}
	}

}

// Load the main plugin class.
$woowbox = WoowBox::get_instance();
