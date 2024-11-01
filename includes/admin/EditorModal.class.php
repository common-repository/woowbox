<?php

/**
 * WOOW Editor Modal class.
*
 * @package WoowBox
 * @author  Sergey Pasyuk
 */
class WoowBox_EditorModal {

	/**
	 * Holds the class object.
	 *
	 * @since 1.0.0
	 *
	 * @var WoowBox_EditorModal object
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

		// Scripts and styles.
		add_action( 'admin_enqueue_scripts', array( $this, 'styles' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'scripts' ) );

		add_action( 'print_media_templates', array( $this, 'woowbox_gallery_settings' ) );

		// Add the filter for editing the custom url field.
		add_filter( 'attachment_fields_to_edit', array( $this, 'apply_filter_attachment_fields_to_edit' ), null, 2 );

		// Add the filter for saving the custom url field.
		add_filter( 'attachment_fields_to_save', array( $this, 'apply_filter_attachment_fields_to_save' ), null, 2 );

		// Add modal template to the Edit Post page.
		add_action( 'admin_footer', array( $this, 'add_post_modal_tpl' ) );
		add_action( 'elementor/editor/footer', array( $this, 'modal_tpl' ) );
	}

	/**
	 * Returns the singleton instance of the class.
	 *
	 * @since 1.0.0
	 *
	 * @return WoowBox_EditorModal object.
	 */
	public static function get_instance() {
		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof WoowBox_EditorModal ) ) {
			self::$instance = new WoowBox_EditorModal();
		}

		return self::$instance;
	}

	/**
	 * The filter for editing the custom url field
	 *
	 * @param $form_fields
	 * @param $post
	 *
	 * @return mixed
	 */
	public function apply_filter_attachment_fields_to_edit( $form_fields, $post ) {
		$_form_fields = array();

		$_form_fields['woowbox_attachment_custom_fields_start'] = array(
			'tr' => '
                <tr style="border-top:1px solid #ccc;"><th class="textleft">' . __( 'Woowbox Gallery', 'woowbox' ) . ':</th><td></td></tr>
            ',
		);

		// Gallery Link URL field.
		$_form_fields['gallery_link_url'] = array(
			'label'       => __( 'Link URL', 'woowbox' ),
			'input'       => 'text',
			'value'       => get_post_meta( $post->ID, '_gallery_link_url', true ),
			'application' => 'image',
		);

		// Gallery Link Target field
		$target_value                        = get_post_meta( $post->ID, '_gallery_link_target', true );
		$_form_fields['gallery_link_target'] = array(
			'label'       => __( 'Link Target', 'woowbox' ),
			'input'       => 'html',
			'application' => 'image',
			'html'        => '
				<select name="attachments[' . $post->ID . '][gallery_link_target]" id="attachments[' . $post->ID . '][gallery_link_target]">
					<option value="">' . __( 'Do Not Change', 'woowbox' ) . '</option>
					<option value="_self"' . ( $target_value == '_self' ? ' selected="selected"' : '' ) . '>' . __( 'Same Window', 'woowbox' ) . '</option>
					<option value="_blank"' . ( $target_value == '_blank' ? ' selected="selected"' : '' ) . '>' . __( 'New Window', 'woowbox' ) . '</option>
				</select>',
		);

		$_form_fields['woowbox_attachment_custom_fields_end'] = array(
			'tr' => '
                <tr style="border-bottom:1px solid #ccc;"><th></th><td></td></tr>
                <tr><th></th><td></td></tr>
            ',
		);

		return $_form_fields + $form_fields;
	}

	/**
	 * The filter for saving the custom url field
	 *
	 * @param array $post       The Post array.
	 * @param array $attachment Attachment array.
	 *
	 * @return mixed
	 */
	public function apply_filter_attachment_fields_to_save( $post, $attachment ) {
		// Save our custom meta fields.
		if ( isset( $attachment['gallery_link_url'] ) ) {
			update_post_meta( $post['ID'], '_gallery_link_url', $attachment['gallery_link_url'] );
		}

		if ( isset( $attachment['gallery_link_target'] ) ) {
			update_post_meta( $post['ID'], '_gallery_link_target', $attachment['gallery_link_target'] );
		}

		return $post;
	}

	/**
	 * Loads styles
	 *
	 * @since 1.0.0
	 *
	 * @param string $hook Page hook.
	 */
	public function styles( $hook ) {
		// Get current screen.
		$screen = get_current_screen();

		// Bail if we're not on the edit WOOW Post Type screen.
		if ( 'post' !== $screen->base ) {
			return;
		}

		// Enqueue styles.
		wp_register_style( $this->base->plugin_slug . '-editor-modal-style', plugins_url( 'assets/css/editor-modal.css', $this->base->file ), array(), $this->base->version );
		wp_enqueue_style( $this->base->plugin_slug . '-editor-modal-style' );

		// Fire a hook to load in custom styles.
		do_action( 'woowbox_editor_modal_styles' );
	}

	/**
	 * Loads scripts for our metaboxes.
	 *
	 * @since 1.0.0
	 *
	 * @param string $hook Page hook.
	 */
	public function scripts( $hook ) {
		// Get current screen.
		$screen = get_current_screen();

		// Bail if we're not on the edit WOOW Post Type screen.
		if ( 'post' !== $screen->base ) {
			return;
		}

		$suffix = SCRIPT_DEBUG ? '' : '.min';
		// Enqueue the script that will trigger the editor button.
		wp_enqueue_script( $this->base->plugin_slug . '-editor-modal-script', plugins_url( "assets/js/editor{$suffix}.js", $this->base->file ), array( 'jquery' ), $this->base->version, true );

		// Fire a hook to load custom metabox scripts.
		do_action( 'woowbox_editor_modal_scripts' );
	}

	/**
	 * Adds a custom gallery settings to Media Uploader popup.
	 *
	 * @since 1.0.0
	 */
	public function woowbox_gallery_settings() {
		$woowbox_skins = WoowBox_Skins::get_instance();

		// Get Skins models from database.
		$skins_models = get_option( 'woow_skins', array() );

		// Get the settings data.
		$data    = WoowBox_Settings::get_instance()->get_setting( 'settings' );
		$default = ! $data['default_skin'] ? ' ' . __( '(default)', 'woowbox' ) : '';
		?>
		<script type="text/html" id="tmpl-woowbox-gallery-settings">

			<div class="woowbox-gallery-settings">
				<h2><?php _e( 'WoowBox Settings', 'woowbox' ); ?></h2>
				<label class="setting">
					<span><?php _e( 'Skin', 'woowbox' ); ?></span>
					<select name="type" data-setting="woowbox-skin" data-default_skin="<?php esc_attr_e( $data['default_skin'] ) ?>" id="woowbox-skin">
						<option value="<?php echo $data['default_skin'] ? 'default' : ''; ?>" selected="selected"><?php _e( 'Default', 'woowbox' ); ?></option>
						<option value="none"><?php echo __( 'None', 'woowbox' ) . $default; ?></option>
						<?php
						// Iterate through the available skins, outputting them in a list.
						foreach ( $woowbox_skins->get_skins() as $skin_obj ) {
							$value   = $skin_obj->slug;
							$default = ( $data['default_skin'] === $value ) ? ' ' . __( '(default)', 'woowbox' ) : '';
							?>
							<option value="<?php echo esc_attr( $value ); ?>"><?php echo esc_html( $skin_obj->name ) . $default; ?></option>
							<?php
							if ( ! empty( $skins_models[ $skin_obj->slug ] ) ) {
								foreach ( $skins_models[ $skin_obj->slug ] as $preset_name => $preset_data ) {
									if ( 'default' === $preset_name ) {
										continue;
									}
									$value   = $skin_obj->slug . ': ' . $preset_name;
									$default = ( $data['default_skin'] === $value ) ? ' ' . __( '(default)', 'woowbox' ) : '';
									?>
									<option value="<?php echo esc_attr( $value ); ?>"><?php echo esc_html( $skin_obj->name . ': ' . $preset_name ) . $default; ?></option>
									<?php
								}
							}
						}
						?>
					</select>
				</label>
				<button type="button" class="button button-small button-secondary" id="woowbox-skin-config" data-title="<?php _e( 'WoowBox Skin Settings', 'woowbox' ); ?>" data-src="<?php echo admin_url( 'admin.php?woowboxiframe=1&skin=' ) ?>" data-skin="default"><?php _e( 'Change Settings', 'woowbox' ); ?></button>
			</div>

		</script>
		<?php
	}

	/**
	 * Adds Modal Template
	 */
	public function add_post_modal_tpl() {
		// Get current screen.
		$screen = get_current_screen();

		// Bail if we're not on the edit WOOW Post Type screen.
		if ( 'post' !== $screen->base ) {
			return;
		}
		$this->modal_tpl();
	}

	/**
	 * Modal Template
	 */
	public function modal_tpl() {
		?>
		<script type="text/html" id="tmpl-woowbox-modal">
			<div id="woowbox-modal" tabindex="0">
				<div class="media-modal wp-core-ui">
					<a class="media-modal-close" href="javascript:void(0)"><span class="media-modal-icon"></span></a>

					<div class="media-modal-content">
						<div class="media-frame wp-core-ui hide-router hide-toolbar hide-menu">
							<div class="media-frame-title"><h1>{{ data.title }}</h1></div>
							<div class="media-frame-menu">
								<div class="media-menu"></div>
							</div>
							<div class="media-frame-content">
							</div>
						</div>
					</div>
				</div>
				<div class="media-modal-backdrop"></div>
			</div>
		</script>
		<?php
	}
}

// Load the editor modal class.
$woowbox_editor_modal = WoowBox_EditorModal::get_instance();
