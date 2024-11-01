<?php
namespace WOOW\Skins;

defined('ABSPATH') or die('No script kiddies please!');
if( !class_exists('WOOW\Skins\SlidersBox')){
	class SlidersBox {
		public static $instance;
		public $version = '1.1.0';
		public $name = 'SlidersBox';
		public $slug = 'slidersbox';
		public $file = __FILE__;
		public $screenshots = array();
		public $description = '';
		public $info = '';

		public function __construct(){
			$this->screenshots[] = plugins_url('/screenshot.png', $this->file);
			add_filter('woow_skins', array($this, 'skin_info'));
			add_action('init', array($this, 'register_scripts'));
			add_action('woow_' . $this->slug . '_skin', array($this, 'enqueue_scripts'));
		}

		public function skin_info($skins){
			$skins[ $this->slug ] = $this;

			return $skins;
		}

		public function register_scripts(){
			//wp_register_style('woow-skin-' . $this->slug . '-style', plugins_url('assets/style.css', $this->file), array(), $this->version);
			wp_register_script('woow-skin-' . $this->slug . '-script', plugins_url('assets/script.js', $this->file), array(), $this->version, true);
		}

		public function enqueue_scripts(){
			//wp_enqueue_style('woow-skin-' . $this->slug . '-style');
			wp_enqueue_script('woow-skin-' . $this->slug . '-script');
		}

		public function skin($gallery){
			$uid    = $gallery['uid'];
			$model  = $gallery['config'][ $this->slug ];
			$data   = $gallery['data'];
			$output = '<script type="text/javascript">var ' . $uid . ' = {settings: ' . json_encode($model) . ', data: ' . json_encode($data) . '};</script>';

			return $output;
		}

		public function settings(){
			$schema = array(
				'common' => array(
					'label'  => __('Captions Settings', 'woowbox'),
					'fields' =>
						array(
							'itemTitleFrom'       => array(
								'label'   => __('Populate title from', 'woowbox'),
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
								'label'   => __('Populate description from', 'woowbox'),
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
							'linkTargetWindow' => array(
								'label'   => __('Link target', 'woowbox'),
								'tag'     => 'select',
								'default' => '_blank',
								'options' => array(
									array(
										'name'  => '_blank',
										'value' => '_blank',
									),
									array(
										'name'  => '_self',
										'value' => '_self',
									),
								),
							),
						),
				),
				'thumbs' => array(
					'label'  => __('Slider & Thumbnails General', 'woowbox'),
					'fields' =>
						array(
							'sliderType'       => array(
								'label'   => __('Slider layout', 'woowbox'),
								'tag'     => 'select',
								'default' => 'posts',
								'options' => array(
									array(
										'name'  => 'Posts Style',
										'value' => 'posts',
									),
									array(
										'name'  => 'Multi Rows',
										'value' => 'multi',
									),
									array(
										'name'  => 'One Row',
										'value' => 'oneRow',
									),
								),
							),
							'collectionSliderBgColor' => array(
								'label'   => __('Slider Background Color', 'woowbox'),
								'tag'     => 'input',
								'default' => 'rgba(0, 0, 0, 0)',
								'options' => array(
									'showAlpha' => true
								),
								'attr'    => array(
									'type' => 'color',
								),
							),
							'collectionBulletsShow'         => array(
								'label'   => __('Bullets Navigation', 'woowbox'),
								'tag'     => 'checkbox',
								'default' => 1,
								'text'    => __('Show Bullets Navigation', 'woowbox')
							),
							'bulletsBgColor' => array(
								'label'   => __('Bullets Navigation bar BG Color', 'woowbox'),
								'tag'     => 'input',
								'visible' => 'collectionBulletsShow',
								'default' => 'rgba(0, 0, 0, 0)',
								'options' => array(
									'showAlpha' => true
								),
								'attr'    => array(
									'type' => 'color',
								),
								'text'    => __('Navigation bar background color', 'woowbox')
							),
							'bulletsColor' => array(
								'label'   => __('Bullets navigation Color', 'woowbox'),
								'tag'     => 'input',
								'visible' => 'collectionBulletsShow',
								'default' => 'rgba(229, 229, 229, 1)',
								'options' => array(
									'showAlpha' => true
								),
								'attr'    => array(
									'type' => 'color',
								),
							),
							'nextPprevNavigation'         => array(
								'label'   => __('Show Next/Prev navigation', 'woowbox'),
								'tag'     => 'checkbox',
								'default' => 1,
							),
							'nextPprevBgColor' => array(
								'label'   => __('Next/Prev Color', 'woowbox'),
								'tag'     => 'input',
								'visible' => 'nextPprevNavigation',
								'default' => 'rgba(240, 240, 240, 1)',
								'options' => array(
									'showAlpha' => true
								),
								'attr'    => array(
									'type' => 'color',
								),
								'text'    => __('Next/Prev Buttons background color', 'woowbox')
							),
							'nextPprevColor' => array(
								'label'   => __('Next/Prev Icon Color', 'woowbox'),
								'tag'     => 'input',
								'visible' => 'nextPprevNavigation',
								'default' => 'rgba(0, 0, 0, 1)',
								'options' => array(
									'showAlpha' => true
								),
								'attr'    => array(
									'type' => 'color',
								),
								'text'    => __('Next/Prev Buttons icon color', 'woowbox')
							),
							'sliderRows'  => array(
								'label'   => __('Desired number of rows', 'woowbox'),
								'tag'     => 'input',
								'premium' => 1,
								'default' => 2,
								'visible' => 'sliderType == "multi"',
								'attr'    => array(
									'type' => 'number',
									'min'  => 1,
									'max'  => 10,
								),
							),
							'thumbHieghtRatio'     => array(
								'label'   => __('Thumbnail Size ratio', 'woowbox'),
								'visible' => 'sliderType != "posts"',
								'tag'     => 'input',
								'default' => 1.32,
								'attr'    => array(
									'type' => 'number',
									'min'  => 0.1,
									'max'  => 2,
									'step' =>0.1,
								),
								'text'    => __('Height / Width = Ratio. Determines the height of the thumbnail', 'woowbox')
							),
							'thumbMinWidth'     => array(
								'label'   => __('Thumbnail desired Width', 'woowbox'),
								'tag'     => 'input',
								'default' => 280,
								'attr'    => array(
									'type' => 'number',
									'min'  => 150,
									'max'  => 400,
								),
							),
							'thumbSpacing'           => array(
								'label'   => __('Space between thumbnails', 'woowbox'),
								'tag'     => 'input',
								'default' => 10,
								'attr'    => array(
									'type' => 'number',
									'min'  => 0,
									'max'  => 20,
								),
							),
							'collectionThumbContentBGColor' => array(
								'label'   => __('Thumbnails Info bar - background colorr', 'woowbox'),
								'tag'     => 'input',
								'default' => '#f5f5f5',
								'options' => array(
									'showAlpha' => true
								),
								'visible' => 'sliderType == "posts"',
								'attr'    => array(
									'type' => 'color',
								),
							),
							'collectionThumbTitleShow'         => array(
								'label'   => __('Show thumbnails title', 'woowbox'),
								'tag'     => 'checkbox',
								'default' => 1,
							),
							'collectionThumbFontSize'  => array(
								'label'   => __('Thumbnails Title - font size scale', 'woowbox'),
								'tag'     => 'input',
								'default' => 18,
								'visible' => 'collectionThumbTitleShow',
								'attr'    => array(
									'type' => 'number',
									'min'  => 10,
									'max'  => 36,
								),
							),
							'collectionThumbTitleColor' => array(
								'label'   => __('Thumbnails Title - text color', 'woowbox'),
								'tag'     => 'input',
								'default' => '#414141',
								'visible' => 'collectionThumbTitleShow',
								'attr'    => array(
									'type' => 'color',
								),
							),
							'collectionThumbDateShow'         => array(
								'label'   => __('Show item upload date', 'woowbox'),
								'tag'     => 'checkbox',
								'default' => 1,
							),
							'collectionThumbDateFontSize'  => array(
								'label'   => __('Date - font size', 'woowbox'),
								'tag'     => 'input',
								'default' => 13,
								'visible' => 'collectionThumbDateShow',
								'attr'    => array(
									'type' => 'number',
									'min'  => 10,
									'max'  => 36,
								),
							),
							'collectionThumbDateColor' => array(
								'label'   => __('Date - text color', 'woowbox'),
								'tag'     => 'input',
								'default' => '#414141',
								'visible' => 'collectionThumbDateShow',
								'attr'    => array(
									'type' => 'color',
								),
							),
							'collectionThumbDescriptionShow'         => array(
								'label'   => __('Show item description', 'woowbox'),
								'tag'     => 'checkbox',
								'visible' => 'sliderType == "posts"',
								'default' => 1,
								'text'    => __('Description is only available for Posts Style', 'woowbox')
							),
							'collectionThumbDescriptionFontSize'  => array(
								'label'   => __('Description - font size', 'woowbox'),
								'tag'     => 'input',
								'default' => 13,
								'visible' => 'sliderType == "posts" and collectionThumbDescriptionShow',
								'attr'    => array(
									'type' => 'number',
									'min'  => 10,
									'max'  => 36,
								),
							),
							'collectionThumbDescriptionColor' => array(
								'label'   => __('Description - text color', 'woowbox'),
								'tag'     => 'input',
								'default' => '#414141',
								'visible' => 'sliderType == "posts" and collectionThumbDescriptionShow',
								'attr'    => array(
									'type' => 'color',
								),
							),
							'lightBoxDisable'   => array(
								'label'   => __('Skip Lightbox', 'woowbox'),
								'tag'     => 'checkbox',
								'default' => 0,
								'premium' => 0,
								'text'    => __('Open link directly instead lightbox', 'woowbox'),
							),
							'collectionThumbHoverColor'        => array(
								'label'   => __('Thumbnails hover color', 'woowbox'),
								'tag'     => 'input',
								'default' => 'rgba(0,0,0,0.5)',
								'options' => array(
									'showAlpha' => true
								),
								'attr'    => array(
									'type' => 'color',
								),
							),
							'collectionReadMoreButtonLabel' => array(
								'label' => __('Read More Button', 'woowbox'),
								'tag'   => 'input',
								'default' => 'Learn more',
								'visible' => 'sliderType != "multi"',
								'premium' => 1,
								'text'    => __('Editable button name', 'woowbox'),
								'attr'    => array(
									'type' => 'text',
								)
							),
							'collectionReadMoreButtonLabelColor' => array(
								'label'   => __('Read More button label color', 'woowbox'),
								'tag'     => 'input',
								'visible' => 'sliderType != "multi"',
								'default' => '#ffffff',
								'options' => array(
									'showAlpha' => true
								),
								'attr'    => array(
									'type' => 'color',
								)
							),
							'collectionReadMoreButtonLabelColorHover' => array(
								'label'   => __('Read More button label hover color', 'woowbox'),
								'tag'     => 'input',
								'visible' => 'sliderType != "multi"',
								'default' => '#ffffff',
								'options' => array(
									'showAlpha' => true
								),
								'attr'    => array(
									'type' => 'color',
								)
							),
							'collectionReadMoreButtonBGColor' => array(
								'label'   => __('Read More button background color', 'woowbox'),
								'tag'     => 'input',
								'visible' => 'sliderType != "multi"',
								'default' => '#000000',
								'options' => array(
									'showAlpha' => true
								),
								'attr'    => array(
									'type' => 'color',
								)
							),
							'collectionReadMoreButtonBGColorHover' => array(
								'label'   => __('Read More button background hover color', 'woowbox'),
								'tag'     => 'input',
								'visible' => 'sliderType != "multi"',
								'default' => 'rgba(111, 111, 111, 1)',
								'options' => array(
									'showAlpha' => true
								),
								'attr'    => array(
									'type' => 'color',
								)
							),
							'collectionSocialShareEnabled'   => array(
								'label'   => __('Share Button', 'woowbox'),
								'visible' => 'sliderType == "posts"',
								'tag'     => 'checkbox',
								'default' => 1,
								'premium' => 1,
								'text'    => __('Available for Posts Style', 'woowbox')
							),
							'collectionItemDiscuss'   => array(
								'label'   => __('Show Comments Button', 'woowbox'),
								'visible' => 'sliderType == "posts"',
								'tag'     => 'checkbox',
								'default' => 1,
								'premium' => 1,
								'text'    => __('Available for Posts Style', 'woowbox')
							),
							'collectionThumbSubMenuBackgroundColor'        => array(
								'label'   => __('Submenu button color', 'woowbox'),
								'tag'     => 'input',
								'visible' => 'sliderType == "posts"',
								'default' => 'rgba(0, 0, 0, 0)',
								'options' => array(
									'showAlpha' => true
								),
								'attr'    => array(
									'type' => 'color',
								),
							),
							'collectionThumbSubMenuIconColor'        => array(
								'label'   => __('Submenu button Icon color', 'woowbox'),
								'tag'     => 'input',
								'visible' => 'sliderType == "posts"',
								'default' => 'rgba(111, 111, 111, 1)',
								'options' => array(
									'showAlpha' => true
								),
								'attr'    => array(
									'type' => 'color',
								),
							),
							'collectionThumbSubMenuBackgroundColorOver'        => array(
								'label'   => __('Submenu button Hover color', 'woowbox'),
								'tag'     => 'input',
								'visible' => 'sliderType == "posts"',
								'default' => 'rgba(0, 0, 0, 0)',
								'options' => array(
									'showAlpha' => true
								),
								'attr'    => array(
									'type' => 'color',
								),
							),
							'collectionThumbSubMenuIconHoverColor'        => array(
								'label'   => __('Submenu button Icon Hover color', 'woowbox'),
								'tag'     => 'input',
								'visible' => 'sliderType == "posts"',
								'default' => 'rgba(0, 0, 0, 1)',
								'options' => array(
									'showAlpha' => true
								),
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
								'premium' => 1,
							),
							'infoBarDateInfoEnable' => array(
								'label' => __('Show Item Upload Date', 'woowbox'),
								'tag' => 'checkbox',
								'default' => 1,
								'premium' => 1,
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
							'sliderNavigationColorOver' => array(
								'label' => __('Main Controls Hover color', 'woowbox'),
								'tag' => 'input',
								'default' => 'rgba(255,255,255,1)',
								'attr' => array(
									'type' => 'color',
								),
								'options' => array(
									'showAlpha' => true
								),
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
							'sliderNavigationIconColorOver' => array(
								'label' => __('Main Controls Icon Hover Color', 'woowbox'),
								'tag' => 'input',
								'default' => 'rgba(0,0,0,1)',
								'attr' => array(
									'type' => 'color',
								),
								'options' => array(
									'showAlpha' => true
								),
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

			return apply_filters('woow_skin_default_settings', $schema, $this->slug);
		}

		public static function get_instance(){

			if( !isset(self::$instance) && !(self::$instance instanceof SlidersBox)){
				self::$instance = new SlidersBox();
			}

			return self::$instance;

		}

	}
}
// Load the skin class.
SlidersBox::get_instance();
