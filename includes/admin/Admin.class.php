<?php

/**
 * Admin class.
*
 * @package WoowBox
 * @author  Sergey Pasyuk
 */
class WoowBox_Admin {

	/**
	 * Holds the class object.
	 *
	 * @since 1.0.0
	 *
	 * @var WoowBox_Admin object
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
		global $pagenow;

		// Load the base class object.
		$this->base = WoowBox::get_instance();

		// Handle any necessary upgrades.
		add_action( 'admin_init', array( $this, 'upgrade' ) );

		// Load admin assets.
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts_l10n' ), 999 );

		// Add the nice header.
		add_action( 'in_admin_header', array( $this, 'admin_header' ), 100 );
		add_filter( 'admin_body_class', array( $this, 'admin_body_class' ) );

		// Force the menu icon to be scaled to proper size (for Retina displays).
		add_action( 'admin_head', array( $this, 'menu_icon' ) );

		if ( ( 'admin.php' === $pagenow ) && isset( $_GET['woowboxiframe'] ) ) {
			add_action( 'admin_init', array( $this, 'woowboxiframe' ) );
		}
	}

	/**
	 * Returns the singleton instance of the class.
	 *
	 * @since 1.0.0
	 *
	 * @return WoowBox_Admin object
	 */
	public static function get_instance() {
		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof WoowBox_Admin ) ) {
			self::$instance = new WoowBox_Admin();
		}

		return self::$instance;
	}

	/**
	 * Handles any necessary upgrades for WOOW.
	 *
	 * @since 1.0.0
	 */
	public function upgrade() {
		$version = get_option( 'woowbox_version' );

		if ( $version && version_compare( $this->base->version, $version, '>' ) ) {
			do_action( 'woowbox_upgrade' );

			update_option( 'woowbox_version', $this->base->version );
		}
	}

	/**
	 * Register and Loads styles / scripts for all WOOW-based Administration Screens.
	 *
	 * @since 1.0.0
	 *
	 * @param string $hook Page hook.
	 */
	public function admin_scripts( $hook ) {
		$suffix = SCRIPT_DEBUG ? '' : '.min';

		// Register vendor scripts.
		wp_register_script( 'vuejs', plugins_url( "assets/vendor/vue{$suffix}.js", $this->base->file ), array(), '2.4.2', true );

		// Register vendor scripts.
		wp_register_script( 'vue-toasted', plugins_url( "assets/vendor/vue-toasted/vue-toasted{$suffix}.js", $this->base->file ), array( 'vuejs' ), '1.1.24', true );

		wp_register_script( 'clipboard', plugins_url( "assets/vendor/clipboard{$suffix}.js", $this->base->file ), array(), '1.6.0', true );

		wp_register_script( 'filtrex', plugins_url( 'assets/vendor/filtrex.js', $this->base->file ), array(), '20150306', true );

		wp_register_style( 'spectrum', plugins_url( "assets/vendor/spectrum/spectrum{$suffix}.css", $this->base->file ), array(), '1.8.0' );
		wp_register_script( 'spectrum', plugins_url( "assets/vendor/spectrum/spectrum{$suffix}.js", $this->base->file ), array( 'jquery' ), '1.8.0', true );

		// Register admin scripts.
		wp_register_style( $this->base->plugin_slug . '-admin-style', plugins_url( 'assets/css/admin.css', $this->base->file ), array(), $this->base->version );
		wp_register_script( $this->base->plugin_slug . '-admin-script', plugins_url( 'assets/js/admin.js', $this->base->file ),
			array(
				'jquery',
				'clipboard',
			), $this->base->version, true
		);

		// Bail if we're not on the WOOW Post Type screen.
		if ( strpos( woow_GET( 'page', '' ), 'woowbox' ) === false && woow_GET( 'woowboxiframe' ) === false ) {
			return;
		}

		// Load necessary admin scripts.
		wp_enqueue_style( $this->base->plugin_slug . '-admin-style' );
		wp_enqueue_script( $this->base->plugin_slug . '-admin-script' );

		// Fire a hook to load in custom admin scripts.
		do_action( 'woowbox_admin_scripts' );
	}

	/**
	 * Global Scripts Localization.
	 *
	 * @since 1.0.0
	 */
	public function admin_scripts_l10n() {
		$l10n = apply_filters( 'woowbox_admin_scripts_l10n', array() );
		wp_localize_script( $this->base->plugin_slug . '-admin-script', 'WoowBox',
			array(
				'l10n' => $l10n,
			)
		);
	}

	/**
	 * Outputs the WoowBox Header in the wp-admin.
	 *
	 * @since 1.0.0
	 */
	public function admin_header() {
		if ( strpos( woow_GET( 'page', '' ), 'woowbox' ) === false ) {
			return;
		}

		// If here, we're on an WoowBox or Collection screen, so output the header.
		$this->base->load_admin_partial( 'header',
			array(
				'logo'    => plugins_url( 'assets/images/woowbox-logo.png', $this->base->file ),
				'license' => WoowBox_CommonGlobal::get_instance()->premium(),
			)
		);
	}

	/**
	 * Add class to admin body.
	 *
	 * @since 1.0.0
	 *
	 * @param string $classes Admin body classes.
	 *
	 * @return string
	 */
	public function admin_body_class( $classes ) {
		if ( strpos( woow_GET( 'page', '' ), 'woowbox' ) === false ) {
			return $classes;
		}

		$classes .= ' screen-woowbox';

		return $classes;
	}

	/**
	 * Forces the WoowBox menu icon width/height for Retina devices.
	 *
	 * @since 1.0.0
	 */
	public function menu_icon() {
		?>
		<style type="text/css">#toplevel_page_woowbox-settings .wp-menu-image img { width: 16px; height: auto; }

			#toplevel_page_woowbox-settings.current .wp-menu-image img { opacity: 1; }</style>
		<?php
	}

	/**
	 * Load WoowBox pages in wpless interface
	 */
	public function woowboxiframe() {
		define( 'IFRAME_REQUEST', true );

		set_current_screen( 'toplevel_page_woowbox-settings' );

		do_action( 'load-toplevel_page_woowbox-settings' );

		iframe_header( 'WoowBox Skin Settings' );

		// Get the settings data.
		$data            = WoowBox_Settings::get_instance()->get_setting( 'settings' );
		$data['license'] = WoowBox_CommonGlobal::get_instance()->premium();

		$skin = woow_GET( 'skin' );
		if ( $skin && 'default' !== $skin ) {
			$data['default_skin'] = 'none' === $skin ? '' : $skin;
		}
		?>
		<div id="woowbox-iframe-content" class="woowbox-wrap<?php echo ! $data['default_skin'] ? ' choose-skin' : ''; ?>">
			<input type="hidden" id="woowbox-license" value="<?php echo esc_attr( $data['license']['key'] ); ?>"/>
			<input type="hidden" id="woowbox-license-plugin" value="<?php echo esc_attr( $data['license']['plugin'] ); ?>"/>
			<input type="hidden" id="woowbox-default-skin" value="<?php echo esc_attr( $data['default_skin'] ); ?>"/>
			<?php
			// Load view.
			$this->base->load_admin_partial(
				'skins',
				$data
			);
			?>
		</div>
		<?php

		iframe_footer();
		exit;
	}
}

// Load the common admin class.
$woowbox_admin = WoowBox_Admin::get_instance();
