<?php

namespace WOOW\Skins;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );
if ( ! class_exists( 'WOOW\Skins\Masonry' ) ) {
	class Masonry {
		public static $instance;
		public $version = '2.4.0';
		public $name = 'Masonry';
		public $slug = 'masonry';
		public $file = __FILE__;
		public $screenshots = array();
		public $description = '';
		public $info = '';

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
			//wp_register_style( 'woow-skin-' . $this->slug . '-style', plugins_url( 'assets/style.css', $this->file ), array(), $this->version );
			wp_register_script( 'woow-skin-' . $this->slug . '-script', plugins_url( 'assets/script.js', $this->file ), array(), $this->version, true );
		}

		public function enqueue_scripts() {
			//wp_enqueue_style( 'woow-skin-' . $this->slug . '-style' );
			wp_enqueue_script( 'woow-skin-' . $this->slug . '-script' );
		}

		public function skin( $gallery ) {
			$uid    = $gallery['uid'];
			$model  = $gallery['config'][ $this->slug ];
			$data   = $gallery['data'];
			$output = '<script type="text/javascript">var ' . $uid . ' = {settings: ' . json_encode( $model ) . ', data: ' . json_encode( $data ) . '};</script>';

			return $output;
		}

		public function settings() {
			$schema = array(
				'common' => array(

					'label'  => __( 'Captions Settings', 'woowbox' ),
					'fields' =>
						array(
							'itemTitleFrom'       => array(
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
								),
							),
							'itemDescriptionFrom' => array(
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
								),
							),
						),
				),
				'thumbs' => array(
					'label'  => __( 'Thumbnails Grid General', 'woowbox' ),
					'fields' =>
						array(
							'collectionThumbRecomendedWidth'     => array(
								'label'   => __( 'Thumbnail desired Width', 'woowbox' ),
								'tag'     => 'input',
								'default' => 280,
								'attr'    => array(
									'type' => 'number',
									'min'  => 150,
									'max'  => 400,
								),
							),
							'collectionThumbInPadding'           => array(
								'label'   => __( 'Space between thumbnails', 'woowbox' ),
								'tag'     => 'input',
								'default' => 10,
								'attr'    => array(
									'type' => 'number',
									'min'  => 0,
									'max'  => 20,
								),
							),
							'collectionThumbTitleEnable'         => array(
								'label'   => __( 'Show thumbnails title', 'woowbox' ),
								'tag'     => 'checkbox',
								'default' => 1,
							),
							'collectionthumbHoverTitleFontSize'  => array(
								'label'   => __( 'Thumbnails Title - font size scale', 'woowbox' ),
								'tag'     => 'input',
								'default' => 18,
								'visible' => 'collectionThumbTitleEnable',
								'attr'    => array(
									'type' => 'number',
									'min'  => 10,
									'max'  => 36,
								),
							),
							'collectionthumbHoverTitleTextColor' => array(
								'label'   => __( 'Thumbnails Title - text color', 'woowbox' ),
								'tag'     => 'input',
								'default' => '#ffffff',
								'visible' => 'collectionThumbTitleEnable',
								'attr'    => array(
									'type' => 'color',
								),
							),
							'collectionThumbSkipLightbox'        => array(
								'label'   => __( 'Skip Lightbox', 'woowbox' ),
								'tag'     => 'checkbox',
								'default' => 0,
								'text'    => __( 'Open link directly instead lightbox', 'woowbox' ),
							),
							'collectionThumbSubMenuVisibility'   => array(
								'label'   => __( 'Show Link Button', 'woowbox' ),
								'tag'     => 'checkbox',
								'default' => 1,
							),
							'collectionThumbSubMenuInfo'   => array(
								'label'   => __( 'Show Info Button', 'woowbox' ),
								'tag'     => 'checkbox',
								'premium' => 1,
								'default' => 1,
							),
							'collectionThumbSubMenuShare'   => array(
								'label'   => __( 'Show Share Button', 'woowbox' ),
								'tag'     => 'checkbox',
								'premium' => 1,
								'default' => 1,
							),
							'collectionThumbSubMenuDownload'   => array(
								'label'   => __( 'Show Download Button', 'woowbox' ),
								'tag'     => 'checkbox',
								'premium' => 1,
								'default' => 1,
							),
							'collectionThumbSubMenuComents'   => array(
								'label'   => __( 'Show Coments Button', 'woowbox' ),
								'tag'     => 'checkbox',
								'premium' => 1,
								'default' => 1,
							),
							'collectionthumbHoverBgColor'        => array(
								'label'   => __( 'Thumbnails hover color', 'woowbox' ),
								'tag'     => 'input',
								'default' => 'rgba(0,0,0,0.5)',
								'options' => array(
									'showAlpha' => true,
								),
								'attr'    => array(
									'type' => 'color',
								),
							),
							'collectionThumbSubMenuIconColor'    => array(
								'label'   => __( 'Sub-menu button icon color', 'woowbox' ),
								'tag'     => 'input',
								'default' => '#ffffff',
								'options' => array(
									'showAlpha' => true,
								),
								'visible' => 'collectionThumbSubMenuVisibility',
								'attr'    => array(
									'type' => 'color',
								),
							),
							'collectionThumbSubMenuIconColorHover'    => array(
								'label'   => __( 'Sub-menu button icon color - Hover', 'woowbox' ),
								'tag'     => 'input',
								'default' => '#000000',
								'options' => array(
									'showAlpha' => true,
								),
								'visible' => 'collectionThumbSubMenuVisibility',
								'attr'    => array(
									'type' => 'color',
								),
							),
							'collectionThumbSubMenuBgColor'    => array(
								'label'   => __( 'Sub-menu button background color', 'woowbox' ),
								'tag'     => 'input',
								'default' => '#000000',
								'options' => array(
									'showAlpha' => true,
								),
								'visible' => 'collectionThumbSubMenuVisibility',
								'attr'    => array(
									'type' => 'color',
								),
							),
							'collectionThumbSubMenuBgColorHover'    => array(
								'label'   => __( 'Sub-menu button background color - Hover', 'woowbox' ),
								'tag'     => 'input',
								'default' => '#ffffff',
								'options' => array(
									'showAlpha' => true,
								),
								'visible' => 'collectionThumbSubMenuVisibility',
								'attr'    => array(
									'type' => 'color',
								),
							),
						),
				),
				'modalWindow' => array(
					'label' => __('Modal Window Settings (Item Info Bar)', 'woowbox'),
					'fields' =>
						array(
							'modaBgColor' => array(
								'label' => __('Overlap Color', 'woowbox'),
								'tag' => 'input',
								'default' => 'rgba(0,0,0,0.9)',
								'attr' => array(
									'type' => 'color',
								),
								'options' => array(
									'showAlpha' => true
								)
							),
							'modalInfoBoxBgColor' => array(
								'label' => __('Info Bar Color', 'woowbox'),
								'tag' => 'input',
								'default' => 'rgba(255,255,255,1)',
								'attr' => array(
									'type' => 'color',
								),
								'options' => array(
									'showAlpha' => true
								)
							),
							'modalInfoBoxTitleTextColor' => array(
								'label' => __('Info Bar Title text Color', 'woowbox'),
								'tag' => 'input',
								'default' => 'rgba(0,0,0,1)',
								'attr' => array(
									'type' => 'color',
								),
								'options' => array(
									'showAlpha' => true
								)
							),
							'modalInfoBoxTextColor' => array(
								'label' => __('Info Bar Text Color', 'woowbox'),
								'tag' => 'input',
								'default' => 'rgba(90,90,90,1)',
								'attr' => array(
									'type' => 'color',
								),
								'options' => array(
									'showAlpha' => true
								)
							),
							'infoBarExifEnable' => array(
								'label' => __('Show Item EXIF Data', 'woowbox'),
								'tag' => 'checkbox',
								'default' => 1,
							),
							'infoBarDateInfoEnable' => array(
								'label' => __('Show Item Upload Date', 'woowbox'),
								'tag' => 'checkbox',
								'default' => 1,
							),
						)
				),
				'slider' => array(
					'label' => __('Lightbox Settings', 'woowbox'),
					'fields' =>
						array(
							'copyR_Alert' => array(
								'label' => __('Copyright Alert (right mouse click)', 'woowbox'),
								'tag'   => 'input',
								'default' => 'Hello, this photo is mine!',
								'text'    => __('Alert about the ban on downloading photo', 'woowbox'),
								'attr'    => array(
									'type' => 'text',
								)
							),
							'sliderBgColor' => array(
								'label' => __('Lightbox  background color', 'woowbox'),
								'tag' => 'input',
								'default' => 'rgba(0,0,0,0.9)',
								'attr' => array(
									'type' => 'color',
								),
								'options' => array(
									'showAlpha' => true
								),
								'text' => __('Set the background color for lightbox', 'woowbox'),
							),
							'sliderPreloaderColor' => array(
								'label' => __('Preloader Color', 'woowbox'),
								'tag' => 'input',
								'default' => 'rgba(255,255,255,1)',
								'attr' => array(
									'type' => 'color',
								),
								'options' => array(
									'showAlpha' => true
								),
								'text' => __('Set custom color for gallery', 'woowbox'),
							),
							'sliderHeaderFooterBgColor' => array(
								'label' => __('Lightbox Header & Footer color', 'woowbox'),
								'tag' => 'input',
								'default' => 'rgba(0,0,0,0.4)',
								'options' => array(
									'showAlpha' => true
								),
								'attr' => array(
									'type' => 'color',
								),
								'text' => __('Set the background color for header and footer (gradient)', 'woowbox'),
							),
							'sliderNavigationColor' => array(
								'label' => __('Main Controls Color', 'woowbox'),
								'tag' => 'input',
								'default' => 'rgba(0,0,0,1)',
								'attr' => array(
									'type' => 'color',
								),
								'options' => array(
									'showAlpha' => true
								),
								'text' => __('Buttons Background Color', 'woowbox'),
							),
							'sliderNavigationIconColor' => array(
								'label' => __('Main Controls Icon Color', 'woowbox'),
								'tag' => 'input',
								'default' => 'rgba(255,255,255,1)',
								'attr' => array(
									'type' => 'color',
								),
								'options' => array(
									'showAlpha' => true
								),
								'text' => __('Icon Color', 'woowbox'),
							),
							'itemCounterColor' => array(
								'label' => __('Items Counter Color', 'woowbox'),
								'tag' => 'input',
								'default' => 'rgba(255,255,255,1)',
								'attr' => array(
									'type' => 'color',
								),
								'options' => array(
									'showAlpha' => true
								)
							),
							'sliderDescriptionShow' => array(
								'label' => __('Show Description Bar (Title + Description)', 'woowbox'),
								'tag' => 'checkbox',
								'default' => 1,
							),
							'sliderItemTitleEnable' => array(
								'label' => __('Show Title', 'woowbox'),
								'tag' => 'checkbox',
								'visible' => 'sliderDescriptionShow=="1"',
								'default' => 1,
							),
							'sliderItemTitleFontSize' => array(
								'label' => __('Item Title - font size', 'woowbox'),
								'visible' => 'sliderItemTitleEnable == "1" and sliderDescriptionShow=="1"',
								'tag' => 'input',
								'default' => 18,
								'attr' => array(
									'type' => 'number',
									'min' => 18,
									'max' => 36,
								),
							),
							'sliderItemTitleTextColor' => array(
								'label' => __('Item Title - text color', 'woowbox'),
								'visible' => 'sliderItemTitleEnable == "1" and sliderDescriptionShow=="1"',
								'tag' => 'input',
								'default' => 'rgba(255,255,255,1)',
								'attr' => array(
									'type' => 'color',
								),
								'options' => array(
									'showAlpha' => true
								)
							),
							'sliderItemDescriptionEnable' => array(
								'label' => __('Show Description', 'woowbox'),
								'tag' => 'checkbox',
								'visible' => 'sliderDescriptionShow=="1"',
								'default' => 1,
							),
							'sliderItemDescriptionFontSize' => array(
								'label' => __('Item Description - font size', 'woowbox'),
								'visible' => 'sliderItemDescriptionEnable == "1" and sliderDescriptionShow=="1"',
								'tag' => 'input',
								'default' => 16,
								'attr' => array(
									'type' => 'number',
									'min' => 12,
									'max' => 36,
								),
							),
							'sliderItemDescriptionTextColor' => array(
								'label' => __('Item Description - text color', 'woowbox'),
								'visible' => 'sliderItemDescriptionEnable == "1" and sliderDescriptionShow=="1"',
								'tag' => 'input',
								'default' => 'rgba(255,255,255,0.8)',
								'attr' => array(
									'type' => 'color',
								),
								'options' => array(
									'showAlpha' => true
								)
							),
							'sliderZoomEnable' => array(
								'label' => __('Enable Zooom ', 'woowbox'),
								'tag' => 'checkbox',
								'default' => 1
							),
							'sliderSlideshow' => array(
								'label' => __('Show Slideshow Button', 'woowbox'),
								'tag' => 'checkbox',
								'default' => 1
							),
							'slideshowIndicatorColor' => array(
								'label' => __('Slideshow Indicator Color', 'woowbox'),
								'visible' => 'sliderSlideshow == "1"',
								'tag' => 'input',
								'default' => 'rgba(255,255,255,1)',
								'attr' => array(
									'type' => 'color',
								),
								'options' => array(
									'showAlpha' => true
								)
							),
							'slideshowIndicatorColorBg' => array(
								'label' => __('Slideshow Indicator Bg Color', 'woowbox'),
								'visible' => 'sliderSlideshow == "1"',
								'tag' => 'input',
								'default' => 'rgba(255,255,255,0.6)',
								'attr' => array(
									'type' => 'color',
								),
								'options' => array(
									'showAlpha' => true
								)
							),
							'sliderSlideshowDelay' => array(
								'label' => __('Slideshows Timer', 'woowbox'),
								'visible' => 'sliderSlideshow == "1"',
								'tag' => 'input',
								'default' => 8,
								'attr' => array(
									'type' => 'number',
									'min' => 2,
									'max' => 30,
								),
							),
							'sliderThumbSubMenuBackgroundColor' => array(
								'label' => __('Submenu button color', 'woowbox'),
								'tag' => 'input',
								'default' => 'rgba(0, 0, 0, 0)',
								'options' => array(
									'showAlpha' => true
								),
								'attr' => array(
									'type' => 'color',
								),
							),
							'sliderThumbSubMenuIconColor' => array(
								'label' => __('Submenu button Icon color', 'woowbox'),
								'tag' => 'input',
								'default' => 'rgba(255, 255, 255, 1)',
								'options' => array(
									'showAlpha' => true
								),
								'attr' => array(
									'type' => 'color',
								),
							),
							'sliderThumbSubMenuBackgroundColorOver' => array(
								'label' => __('Submenu button Hover color', 'woowbox'),
								'tag' => 'input',
								'default' => 'rgba(255, 255, 255, 1)',
								'options' => array(
									'showAlpha' => true
								),
								'attr' => array(
									'type' => 'color',
								),
							),
							'sliderThumbSubMenuIconHoverColor' => array(
								'label' => __('Submenu button Icon Hover color', 'woowbox'),
								'tag' => 'input',
								'default' => 'rgba(0, 0, 0, 1)',
								'options' => array(
									'showAlpha' => true
								),
								'attr' => array(
									'type' => 'color',
								),
							),
							'sliderThumbBarEnable' => array(
								'label' => __('Show Thumbnails Bar', 'woowbox'),
								'tag' => 'checkbox',
								'default' => 1,
								'premium' => 1
							),
							'sliderThumbBarHoverColor' => array(
								'label' => __('Thumbnails Border Color (select mode)', 'woowbox'),
								'visible' => 'sliderThumbBarEnable == "1"',
								'tag' => 'input',
								'default' => 'rgba(255,255,255,1)',
								'premium' => 1,
								'attr' => array(
									'type' => 'color',
								),
								'options' => array(
									'showAlpha' => true
								)
							),
							'sliderInfoEnable' => array(
								'label' => __('Show Info Button', 'woowbox'),
								'tag' => 'checkbox',
								'default' => 1,
								'text' => __('Enable description bar for item', 'woowbox'),
								'premium' => 1
							),
							'sliderSocialShareEnabled' => array(
								'label' => __('Show Share Buttons', 'woowbox'),
								'tag' => 'checkbox',
								'default' => 1,
								'premium' => 1
							),
							'sliderItemDownload' => array(
								'label' => __('Show Download Button', 'woowbox'),
								'tag' => 'checkbox',
								'default' => 1,
								'text' => __('Download original file', 'woowbox'),
								'premium' => 1
							),
							'sliderItemDiscuss' => array(
								'label' => __('Show Comments Button', 'woowbox'),
								'tag' => 'checkbox',
								'default' => 1,
								'premium' => 1
							)
						)
				),
			);

			return apply_filters( 'woow_skin_default_settings', $schema, $this->slug );
		}

		public static function get_instance() {
			if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Masonry ) ) {
				self::$instance = new Masonry();
			}

			return self::$instance;
		}
	}
}

// Load the skin class.
Masonry::get_instance();
