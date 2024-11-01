<?php
/**
 * Elementor Widget class.
 *
 * @since   1.2.0
 *
 * @package WoowBox
 * @author  Sergey Pasyuk
 */

namespace Elementor;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly.

/**
 * Class Widget_Woowbox_Gallery
 *
 * @since   1.2.0
 *
 * @package Elementor
 */
class Widget_Woowbox_Gallery extends Widget_Base {

	/**
	 * Get widget categories.
	 *
	 * @since 1.2.0
	 *
	 * @return array Widget categories.
	 */
	public function get_categories() {
		return array( 'woowbox' );
	}

	/**
	 * Get widget icon.
	 *
	 * @since 1.2.0
	 *
	 * @return string Widget icon.
	 */
	public function get_icon() {
		return 'eicon-gallery-masonry';
	}

	/**
	 * Get element name.
	 *
	 * @since 1.2.0
	 *
	 * @return string The name.
	 */
	public function get_name() {
		return 'woowbox-gallery';
	}

	/**
	 * Get element title.
	 *
	 * @since 1.2.0
	 *
	 * @return string Element title.
	 */
	public function get_title() {
		return __( 'WoowBox Gallery', 'woowbox' );
	}

	protected function _content_template() {}

	/**
	 * Register Controls.
	 *
	 * @since 1.2.0
	 */
	protected function _register_controls() {
		$this->start_controls_section(
			'section_gallery',
			array(
				'label' => __( 'WoowBox Gallery', 'woowbox' ),
			)
		);

		$this->add_control(
			'wp_gallery',
			array(
				'label' => __( 'Add Images', 'woowbox' ),
				'type'  => Controls_Manager::GALLERY,
			)
		);

		$woowbox_skins = \WoowBox_Skins::get_instance();
		// Get Skins models from database.
		$skins_models = get_option( 'woow_skins', array() );
		// Get the settings data.
		$data = \WoowBox_Settings::get_instance()->get_setting( 'settings' );

		$options = array();
		foreach ( $woowbox_skins->get_skins() as $skin_obj ) {
			$value   = $skin_obj->slug;
			$default = ( $data['default_skin'] === $value ) ? ' ' . __( '(default)', 'woowbox' ) : '';

			$options[ $value ] = esc_html( $skin_obj->name ) . $default;

			if ( ! empty( $skins_models[ $skin_obj->slug ] ) ) {
				foreach ( $skins_models[ $skin_obj->slug ] as $preset_name => $preset_data ) {
					if ( 'default' === $preset_name ) {
						continue;
					}
					$value   = $skin_obj->slug . ': ' . $preset_name;
					$default = ( $data['default_skin'] === $value ) ? ' ' . __( '(default)', 'woowbox' ) : '';

					$options[ $value ] = esc_html( $skin_obj->name . ': ' . $preset_name ) . $default;
				}
			}
		}
		reset( $options );
		$default_option = key( $options );

		$this->add_control(
			'skin',
			array(
				'label'   => __( 'Set Preset', 'woowbox' ),
				'type'    => Controls_Manager::SELECT,
				'default' => $default_option,
				'options' => $options,
			)
		);

		$this->add_control(
			'change_skin_settings',
			array(
				'label'       => __( 'Preset Settings', 'woowbox' ),
				'type'        => Controls_Manager::BUTTON,
				'button_type' => 'success',
				'text'        => __( 'Modify Preset', 'woowbox' ),
				'event'       => 'woowbox:module:settings',
			)
		);

		$this->add_control(
			'change_skin_settings_trigger',
			array(
				'label'   => 'trigger',
				'type'    => Controls_Manager::HIDDEN,
				'default' => '',
			)
		);

		$this->add_control(
			'randomize',
			array(
				'label'        => __( 'Randomize', 'woowbox' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => false,
				'label_on'     => __( 'On', 'woowbox' ),
				'label_off'    => __( 'Off', 'woowbox' ),
				'return_value' => true,
				'separator'    => 'before',
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Render element.
	 * Generates the final HTML on the frontend.
	 *
	 * @since 1.2.0
	 */
	protected function render() {
		$settings = $this->get_settings();
		if ( ! $settings['wp_gallery'] ) {
			return;
		}

		$ids = wp_list_pluck( $settings['wp_gallery'], 'id' );
		//$this->add_render_attribute( 'shortcode', 'ids', implode( ',', $ids ) );
		//$skins = \WoowBox_Skins::get_instance();
		//$skin  = $skins->get_skin( $settings['skin'] );

		static $woowbox_shortcode_instance = 0;
		$woowbox_shortcode_instance ++;

		echo \WoowBox_Shortcode::get_instance()->post_gallery( 'Testing', array(
			'ids'          => $ids,
			'woowbox-skin' => $settings['skin'],
			'orderby'      => $settings['randomize'] ? 'rand' : 'post__in',
			'align'        => '',
			'width'        => '',
			'margin'       => '',
		), $woowbox_shortcode_instance );
	}
}

/**
 * Fires after Elementor widgets are registered.
 *
 * @since 1.2.0
 */
add_action( 'elementor/widgets/widgets_registered',
	function ( $widgets_manager ) {
		/**
		 * @var Widgets_Manager $widgets_manager The widgets manager.
		 */
		$widgets_manager->register_widget_type( new Widget_Woowbox_Gallery() );
	}
);
