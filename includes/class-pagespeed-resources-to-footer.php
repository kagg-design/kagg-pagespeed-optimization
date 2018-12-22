<?php

/**
 * Class PageSpeed_Resources_To_Footer
 *
 * Move scripts and styles from header to footer.
 */
class PageSpeed_Resources_To_Footer {
	/** @var array Scripts to move from header to footer. */
	private $scripts = array(
		'admin-bar',
		'comment-reply',
		'layerslider',
		'layerslider-transitions',
		'contact-form-7',
		'jquery-ui-ru',
		'jquery-ui-timepicker-ru',
		'jquery-ui-datepicker',
		'jquery-ui-timepicker',
		'jquery-ui-slider',
		'jquery-ui-slider-access',
		'sb_instagram_scripts',
		'jquery',
		'jquery-ui-core',
		'jquery-ui-widget',
		'jcrop',
		'jquery.iframe-transport',
		'jquery.fileupload',
		'jquery.fileupload-process',
		'jquery.fileupload-validate',
		'uni-avatar-modal',
		'wpml-legacy-dropdown-0',
		'jquery-masonry',
		'jquery-bxslider-min',
		'jquery-blackandwhite',
		'jquery-fancybox',
		'isotope-min',
		'jquery-mousewheel',
		'jquery-jscrollpane-min',
		'jquery-dotdotdot-min',
		'jquery-blockui',
		'uni-bauhaus-theme-script',
		'unitheme-script',
		'share-script',
		'masked_input',
		'fortezza_script',
		'social-likes',
		'load-more',
		'contact-form',
		'jquery.cookie',
		'fca_pc_client_js',
		'fca_pc_tooltipster_js',
		'fca_pc_deactivation_js',
		'cmb2-scripts',
		'jquery-parsley',
		'parsley-localization',
	);

	/** @var array Scripts to block. */
	private $block_scripts = array(
		'jquery-blackandwhite',
		'social-likes',
		'jquery-parsley',
		'parsley-localization',
		'comment-reply',
		'jquery-masonry',
		'jcrop',
		'jquery-ui-slider',
		'jquery-ui-slider-access',
		'jquery-ui-widget',
		'jquery-ui-core',
	);

	/** @var array Styles to move from header to footer. */
	private $styles = array(
		'layerslider',
		'contact-form-7',
		'sb_instagram_styles',
		'sb-font-awesome',
		'bodhi-svgs-attachment',
		'admin-css',
		'wpml-legacy-dropdown-0',
		'ball-clip-rotate-style',
		'bxslider-style',
		'fancybox-style',
		'jscrollpane-style',
		'unitheme-styles',
		'unitheme-adaptive',
		'unichild-styles',
		'font-awesome',
		'fancybox',
		'js_composer_custom_css',
		'jquery-ui-timepicker',
		'dashicons',
		'cmb2-styles',
	);

	/** @var array Styles to block. */
	private $block_styles = array(
		'sb-font-awesome',
		'font-awesome',
		'dashicons',
		'jscrollpane-style',
		'bodhi-svgs-attachment',
		'wpml-legacy-dropdown-0',
		'ball-clip-rotate-style',
	);

	/**
	 * PageSpeed_Resources_To_Footer constructor.
	 */
	public function __construct() {
		$this->init();
	}

	/**
	 * Init class hooks.
	 */
	public function init() {
		if ( ! is_admin() ) {
			add_action( 'wp_enqueue_scripts', array( $this, 'remove_scripts_from_header' ), PHP_INT_MAX );
			add_action( 'get_footer', array( $this, 'add_scripts_to_footer' ) );

			add_action( 'wp_enqueue_scripts', array( $this, 'remove_styles_from_header' ), PHP_INT_MAX );
			add_action( 'get_footer', array( $this, 'add_styles_to_footer' ) );
		}
	}

	/**
	 * Remove scripts from header.
	 */
	public function remove_scripts_from_header() {
		$scripts = array_unique( array_merge( $this->scripts, $this->block_scripts ) );

		foreach ( $scripts as $script ) {
			if ( wp_script_is( $script, 'enqueued' ) ) {
				wp_dequeue_script( $script );
			}
		}
	}

	/**
	 * Add scripts to footer.
	 */
	public function add_scripts_to_footer() {
		$scripts = array_diff( $this->scripts, $this->block_scripts );

		foreach ( $scripts as $script ) {
			if ( wp_script_is( $script, 'registered' ) ) {
				wp_enqueue_script( $script );
			}
		}
	}

	/**
	 * Remove styles from header.
	 */
	public function remove_styles_from_header() {
		/** @var $wp_styles WP_Styles */
		global $wp_styles;

		$styles = array_unique( array_merge( $this->styles, $this->block_styles ) );

		foreach ( $styles as $style ) {
			if ( in_array( $style, $wp_styles->queue, true ) ) {
				wp_dequeue_style( $style );
			}
		}
	}

	/**
	 * Add styles to footer.
	 */
	public function add_styles_to_footer() {
		/** @var $wp_styles WP_Styles */
		global $wp_styles;

		$styles = array_diff( $this->styles, $this->block_styles );

		foreach ( $styles as $style ) {
			if ( isset( $wp_styles->registered[ $style ] ) ) {
				wp_enqueue_style( $style );
			}
		}
	}
}
