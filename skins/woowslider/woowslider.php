<?php

namespace WOOW\Skins;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );
if ( ! class_exists( 'WOOW\Skins\WoowSlider' ) ) {
	class WoowSlider {
		public static $instance;
		public $version = '1.0.0';
		public $name = 'WoowSlider';
		public $slug = 'woowslider';
		public $file = __FILE__;
		public $screenshots = array();

		public function __construct() {
			$this->screenshots[] = plugins_url( '/screenshot.png', $this->file );
			add_filter( 'woow_skins', array( $this, 'skin_info' ) );
			add_action( 'init', array( $this, 'register_scripts' ) );
			add_action( 'woow_' . $this->slug . '_skin', array( $this, 'enqueue_scripts' ) );
		}

		public function skin_info( $skins ) {
			$skins[ $this->slug ] = $this;

			return $skins;
		}

		public function register_scripts() {
			wp_register_style( 'woow-skin-' . $this->slug . '-style', plugins_url( 'assets/style.css', $this->file ), array(), $this->version );
			wp_register_script( 'woow-skin-' . $this->slug . '-script', plugins_url( 'assets/script.js', $this->file ), array(), $this->version, true );
		}

		public function enqueue_scripts() {
			wp_enqueue_style( 'woow-skin-' . $this->slug . '-style' );
			wp_enqueue_script( 'woow-skin-' . $this->slug . '-script' );
		}

		public function skin( $gallery ) {
			$uid    = $gallery['uid'];
			$model  = $gallery['config'][ $this->slug ];
			$data   = $gallery['data'];
			$output = '
            <div app-id="' . $uid . '" class="woow-skin-' . $this->slug . '">
            <script type="text/javascript">var ' . $uid . ' = {settings: ' . json_encode( $model ) . ', data: ' . json_encode( $data ) . '};</script>
            </div>';

			return $output;
		}

		public function settings() {
			$schema = array(
				'common'  => array(
					'label'  => __( 'Common Settings', 'woowbox' ),
					'fields' =>
						array(
							'itemTitleFrom'             => array(
								'label'   => __( 'Populate title from', 'woowbox' ),
								'tag'     => 'select',
								'default' => 'title',
								'options' => array(
									array(
										'name'  => 'WP Image Title',
										'value' => 'title',
									),
									array(
										'name'  => 'WP Image Caption',
										'value' => 'caption',
									),
									array(
										'name'  => 'None',
										'value' => 'none',
									),
								),
							),
							'itemDescriptionFrom'       => array(
								'label'   => __( 'Populate description from', 'woowbox' ),
								'tag'     => 'select',
								'default' => 'description',
								'options' => array(
									array(
										'name'  => 'WP Image Description',
										'value' => 'description',
									),
									array(
										'name'  => 'WP Image Caption',
										'value' => 'caption',
									),
									array(
										'name'  => 'WP Image Title',
										'value' => 'title',
									),
									array(
										'name'  => 'None',
										'value' => 'none',
									),
								),
							),
							'sliderHeightRetio'         => array(
								'label'   => __( 'Slider height ratio', 'woowbox' ),
								'tag'     => 'input',
								'default' => 0.6,
								'attr'    => array(
									'type' => 'range',
									'min'  => 0.1,
									'max'  => 2,
									'step' => 0.1,
								),
								'text'    => __( 'Height / Width = Ratio', 'woowbox' ),
							),
							'sliderFillMode'            => array(
								'label'   => __( 'Image Scale Mode', 'woowbox' ),
								'tag'     => 'select',
								'default' => '1',
								'premium' => '1',
								'options' => array(
									array(
										'name'  => 'Fill',
										'value' => '1',
									),
									array(
										'name'  => 'Fit',
										'value' => '0',
									),
								),
								'text'    => __( 'Default value: Fill. Note \'Fit\'', 'woowbox' ),
							),
							'sliderZoomEnable'          => array(
								'label'   => __( 'Activate ZoomIn by click', 'woowbox' ),
								'tag'     => 'checkbox',
								'default' => 1,
								'premium' => '1',
								'text'    => __( 'Works only with scale mode FIT', 'woowbox' ),
							),
							'sliderPreloaderColor'      => array(
								'label'   => __( 'Slider Preloader color', 'woowbox' ),
								'tag'     => 'input',
								'default' => '#000000',
								'attr'    => array(
									'type' => 'color',
								),
							),
							'sliderBackgroundColor'     => array(
								'label'   => __( 'Slider background color', 'woowbox' ),
								'tag'     => 'input',
								'default' => '#ffffff',
								'attr'    => array(
									'type' => 'color',
								),
							),
							'sliderSlideshowEneble'     => array(
								'label'   => __( 'Autoplay On Load', 'woowbox' ),
								'tag'     => 'checkbox',
								'default' => 1,
								'text'    => __( 'Start slideshow automatically on gallery load', 'woowbox' ),
							),
							'slideshowDelay'            => array(
								'label'   => __( 'Slideshow Delay', 'woowbox' ),
								'tag'     => 'input',
								'visible' => 'sliderSlideshowEneble',
								'default' => 8,
								'attr'    => array(
									'type' => 'number',
									'min'  => 1,
								),
								'text'    => __( 'Delay between change slides in seconds', 'woowbox' ),
							),
							'slideshowProgressBarColor' => array(
								'label'   => __( 'Slideshow progress bar color', 'woowbox' ),
								'tag'     => 'input',
								'visible' => 'sliderSlideshowEneble',
								'default' => '#ffffff',
								'attr'    => array(
									'type' => 'color',
								),
							),
						),
				),
				'infoBar' => array(
					'label'  => __( 'Slide Information Bar Settings', 'woowbox' ),
					'fields' => array(
						'sliderShowItemInformation'                 => array(
							'label'   => __( 'Show slide information', 'woowbox' ),
							'tag'     => 'checkbox',
							'default' => 1,
						),
						'sliderCounterColor'                        => array(
							'label'   => __( 'Сounter color', 'woowbox' ),
							'tag'     => 'input',
							'visible' => 'sliderShowItemInformation',
							'default' => '#ffffff',
							'attr'    => array(
								'type' => 'color',
							),
						),
						'sliderTitleColor'                          => array(
							'label'   => __( 'Title color', 'woowbox' ),
							'tag'     => 'input',
							'visible' => 'sliderShowItemInformation',
							'default' => '#ffffff',
							'attr'    => array(
								'type' => 'color',
							),
						),
						'sliderDescriptionTextColor'                => array(
							'label'   => __( 'Description color', 'woowbox' ),
							'tag'     => 'input',
							'visible' => 'sliderShowItemInformation',
							'default' => '#ffffff',
							'attr'    => array(
								'type' => 'color',
							),
						),
						'sliderDescriptionBgColor'                  => array(
							'label'   => __( 'Description background color', 'woowbox' ),
							'tag'     => 'input',
							'visible' => 'sliderShowItemInformation',
							'default' => 'rgba(0,0,0,0.7)',
							'attr'    => array(
								'type' => 'color',
							),
						),
						'sliderDescriptionReadMoreButtonLabel'      => array(
							'label'   => __( 'Read More Button', 'woowbox' ),
							'tag'     => 'input',
							'default' => 'Learn more',
							'visible' => 'sliderShowItemInformation',
							'premium' => 1,
							'text'    => __( 'Editable button name', 'woowbox' ),
							'attr'    => array(
								'type' => 'text',
							),
						),
						'sliderDescriptionReadMoreButtonLabelColor' => array(
							'label'   => __( 'Read More button text color', 'woowbox' ),
							'tag'     => 'input',
							'visible' => 'sliderShowItemInformation',
							'default' => '#ffffff',
							'attr'    => array(
								'type' => 'color',
							),
						),
						'sliderDescriptionReadMoreButtonBGColor'    => array(
							'label'   => __( 'Read More button background color', 'woowbox' ),
							'tag'     => 'input',
							'visible' => 'sliderShowItemInformation',
							'default' => '#000000',
							'attr'    => array(
								'type' => 'color',
							),
						),
					),
				),
				'naviBar' => array(
					'label'  => __( 'Prev/Next Navigation Bar Settings', 'woowbox' ),
					'fields' => array(
						'arrowsNaviEnable'         => array(
							'label'   => __( 'Show Prev/Next Navigation', 'woowbox' ),
							'tag'     => 'checkbox',
							'default' => 1,
						),
						'arrowsNaviLabelColor'     => array(
							'label'   => __( 'Buttons icon color', 'woowbox' ),
							'visible' => 'arrowsNaviEnable',
							'tag'     => 'input',
							'default' => '#000000',
							'attr'    => array(
								'type' => 'color',
							),
						),
						'arrowsNaviBgColor'        => array(
							'label'   => __( 'Buttons background color', 'woowbox' ),
							'visible' => 'arrowsNaviEnable',
							'tag'     => 'input',
							'default' => '#ffffff',
							'attr'    => array(
								'type' => 'color',
							),
						),
						'arrowsNaviThumbnailsShow' => array(
							'label'   => __( 'Show Prev/Next HoverBar', 'woowbox' ),
							'visible' => 'arrowsNaviEnable',
							'tag'     => 'checkbox',
							'default' => 1,
							'premium' => '1',
							'text'    => __( 'Show HoverBar with Thumbnails', 'woowbox' ),
						),
						'arrowsNaviHoverBgColor'   => array(
							'label'   => __( 'HoverBar background color', 'woowbox' ),
							'visible' => 'arrowsNaviEnable',
							'tag'     => 'input',
							'default' => '#000000',
							'attr'    => array(
								'type' => 'color',
							),
						),
						'arrowsNaviTitleColor'     => array(
							'label'   => __( 'HoverBar title color', 'woowbox' ),
							'visible' => 'arrowsNaviEnable',
							'tag'     => 'input',
							'default' => '#ffffff',
							'attr'    => array(
								'type' => 'color',
							),
						),
					),
				),
				'thumbs'  => array(
					'label'  => 'Thumbnails Navigation Bar Settings',
					'fields' => array(
						'thumbsNaviEnable'       => array(
							'label'   => __( 'Show Thumbnails Navigation', 'woowbox' ),
							'tag'     => 'checkbox',
							'premium' => '1',
							'default' => 0,
						),
						'thumbsNaviButtonsColor' => array(
							'label'   => __( 'Thumbnails Navigation Buttons color', 'woowbox' ),
							'visible' => 'thumbsNaviEnable',
							'tag'     => 'input',
							'default' => '#ffffff',
							'attr'    => array(
								'type' => 'color',
							),
						),
					),
				),
			);

			return apply_filters( 'woow_skin_default_settings', $schema, $this->slug );
		}

		public static function get_instance() {
			if ( ! isset( self::$instance ) && ! ( self::$instance instanceof WoowSlider ) ) {
				self::$instance = new WoowSlider();
			}

			return self::$instance;
		}
	}
}
// Load the skin class.
WoowSlider::get_instance();
