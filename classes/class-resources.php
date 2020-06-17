<?php
/**
 * PageSpeed_Resources class file.
 *
 * @package kagg_pagespeed_optimization
 */

namespace KAGG\PageSpeed\Optimization;

/**
 * Class PageSpeed_Resources
 *
 * Move scripts and styles from header to footer.
 * Make scripts defer.
 * Add display=swap to fonts.
 */
class Resources {

	/**
	 * Scripts to move from header to footer.
	 *
	 * @var string[]
	 */
	private $scripts = [
		'admin-bar',
		'font-awesome-4-shim',
		'jquery-core',
		'jquery-migrate',
	];

	/**
	 * Scripts to block.
	 *
	 * @var string[]
	 */
	private $block_scripts = [];

	/**
	 * Scripts moved to footer.
	 *
	 * @var string[]
	 */
	private $moved_scripts = [];

	/**
	 * Styles to move from header to footer.
	 *
	 * @var string[]
	 */
	private $styles = [
		'ads-for-wp-front-css',
		'cvdw_cards_css',
		'cvdw_tooltip_style',
		'elementor-animations',
		'elementor-extras-frontend',
		'elementor-frontend',
		'elementor-global',
		'elementor-icons',
		'elementor-icons-fa-brands',
		'elementor-icons-fa-regular',
		'elementor-icons-fa-solid',
		'elementor-icons-shared-0',
		'elementor-pro',
		'font-awesome-4-shim',
		'font-awesome-5-all',
		'google-fonts-1',
		'hello-elementor',
		'hello-elementor-theme-style',
		'jet-blog',
		'jet-search',
		'jquery-chosen',
		'namogo-icons',
		'swiper-css-library',
		'swiper-css-main',
		'uael-frontend',
		'wp-block-library',
		'wp-polls',
		'wtr-css',
	];

	/**
	 * Styles to block.
	 *
	 * @var string[]
	 */
	private $block_styles = [];

	/**
	 * Styles moved to footer.
	 *
	 * @var string[]
	 */
	private $moved_styles = [];

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
			add_action( 'wp_enqueue_scripts', [ $this, 'remove_scripts_from_header' ], PHP_INT_MAX );
			add_action( 'wp_print_scripts', [ $this, 'remove_scripts_from_header' ], PHP_INT_MAX );
			add_action( 'get_footer', [ $this, 'add_scripts_to_footer' ] );

			add_action( 'wp_enqueue_scripts', [ $this, 'remove_styles_from_header' ], PHP_INT_MAX );
			add_action( 'wp_print_styles', [ $this, 'remove_styles_from_header' ], PHP_INT_MAX );
			add_action( 'get_footer', [ $this, 'add_styles_to_footer' ] );

			// Make some scripts defer.
			add_filter( 'script_loader_tag', [ $this, 'script_loader_tag_filter' ], 10, 2 );

			// Add display=swap to Google fonts.
			add_filter( 'style_loader_tag', [ $this, 'style_loader_tag_filter' ], 10, 4 );

			// Print preload links.
			add_action( 'wp_head', [ $this, 'head' ], - PHP_INT_MAX );

			// Modify display=swap.
//			add_action( 'wp_head', [ $this, 'font_display_swap' ], PHP_INT_MAX );
		}
	}

	/**
	 * Remove scripts from header.
	 */
	public function remove_scripts_from_header() {
		$scripts = array_unique( array_merge( $this->scripts, $this->block_scripts ) );
		$scripts = array_unique( array_merge( $scripts, $this->parent_scripts( $scripts ) ) );

		foreach ( $scripts as $script ) {
			if ( wp_script_is( $script, 'enqueued' ) ) {
				$this->moved_scripts[] = $script;
				wp_dequeue_script( $script );
			}
		}
	}

	/**
	 * Find all parent scripts, which need $scripts as dependencies.
	 *
	 * @param string[] $scripts Scripts.
	 *
	 * @return string[]
	 */
	private function parent_scripts( $scripts ) {
		global $wp_scripts;

		$parents = [];

		foreach ( $wp_scripts->registered as $handle => $style ) {
			$deps = $style->deps;
			if ( array_intersect( $scripts, $deps ) ) {
				$parents[] = $handle;
			}
		}

		return array_unique( $parents );
	}

	/**
	 * Add scripts to footer.
	 */
	public function add_scripts_to_footer() {
		foreach ( $this->moved_scripts as $script ) {
			if ( wp_script_is( $script, 'registered' ) ) {
				wp_enqueue_script( $script );
			}
		}
	}

	/**
	 * Remove styles from header.
	 */
	public function remove_styles_from_header() {
		global $wp_styles;

		$styles = array_unique( array_merge( $this->styles, $this->block_styles ) );
		$styles = array_unique( array_merge( $styles, $this->parent_styles( $styles ) ) );

		foreach ( $styles as $style ) {
			if ( in_array( $style, $wp_styles->queue, true ) ) {
				$this->moved_styles[] = $style;
				wp_dequeue_style( $style );
			}
		}
	}

	/**
	 * Find all parent styles, which need $styles as dependencies.
	 *
	 * @param string[] $styles Styles.
	 *
	 * @return string[]
	 */
	private function parent_styles( $styles ) {
		global $wp_styles;

		$parents = [];

		foreach ( $wp_styles->registered as $handle => $style ) {
			$deps = $style->deps;
			if ( array_intersect( $styles, $deps ) ) {
				$parents[] = $handle;
			}
		}

		return array_unique( $parents );
	}

	/**
	 * Add styles to footer.
	 */
	public function add_styles_to_footer() {
		global $wp_styles;

		foreach ( $this->moved_styles as $style ) {
			if ( isset( $wp_styles->registered[ $style ] ) ) {
				wp_enqueue_style( $style );
			}
		}
	}

	/**
	 * Filter script tag and add defer.
	 *
	 * @param string $tag    Script tag.
	 * @param string $handle Script handle.
	 *
	 * @return mixed
	 */
	public function script_loader_tag_filter( $tag, $handle ) {
		$defer = [];

		if ( in_array( $handle, $defer, true ) ) {
			$tag = str_replace( '></script>', ' defer></script>', $tag );
		}

		return $tag;
	}

	/**
	 * Print preload links
	 */
	public function head() {
		$content_types = [
			'eot'   => [ 'font', 'application/vnd.ms-fontobject' ],
			'otf'   => [ 'font', 'font/otf' ],
			'ttf'   => [ 'font', 'font/ttf' ],
			'woff'  => [ 'font', 'font/woff' ],
			'woff2' => [ 'font', 'font/woff2' ],
			'gif'   => [ 'image', 'image/gif' ],
			'ico'   => [ 'image', 'image/x-icon' ],
			'jpeg'  => [ 'image', 'image/jpeg' ],
			'jpg'   => [ 'image', 'image/jpeg' ],
			'png'   => [ 'image', 'image/png' ],
			'svg'   => [ 'image', 'image/svg+xml' ],
			'tif'   => [ 'image', 'image/tiff' ],
			'tiff'  => [ 'image', 'image/tiff' ],
			'js'    => [ 'script', 'application/javascript' ],
			'css'   => [ 'style', 'text/css' ],
			'htm'   => [ 'document', 'text/html' ],
			'html'  => [ 'document', 'text/html' ],
		];

		$links = [
			'/wp-includes/js/jquery/jquery.js',
			'/wp-includes/js/jquery/jquery-migrate.min.js',
			'/wp-includes/js/wp-embed.min.js',
			'/wp-content/plugins/kagg-pagespeed-optimization/cache/ya_an.js',
			'https://mc.yandex.ru/metrika/tag.js',

			'/wp-content/plugins/elementor/assets/lib/font-awesome/css/brands.min.css',
			'/wp-content/plugins/elementor/assets/lib/font-awesome/css/fontawesome.min.css',
			'/wp-content/plugins/elementor/assets/lib/font-awesome/css/regular.min.css',
			'/wp-content/plugins/elementor/assets/lib/font-awesome/css/solid.min.css?ver=5.12.0',

			'/wp-content/plugins/elementor/assets/lib/eicons/fonts/eicons.woff2',
			'/wp-content/plugins/elementor/assets/lib/font-awesome/webfonts/fa-brands-400.woff2',
			'/wp-content/plugins/elementor/assets/lib/font-awesome/webfonts/fa-regular-400.woff2',
			'/wp-content/plugins/elementor/assets/lib/font-awesome/webfonts/fa-solid-900.woff2',
			'https://fonts.gstatic.com/s/exo2/v8/7cHmv4okm5zmbtYoK-4.woff2',
			'https://fonts.gstatic.com/s/exo2/v8/7cHmv4okm5zmbtYsK-4E4Q.woff2',
			'https://fonts.gstatic.com/s/ptserif/v11/EJRSQgYoZZY2vCFuvAnt66qSVys.woff2',
			'https://fonts.gstatic.com/s/ptserif/v11/EJRSQgYoZZY2vCFuvAnt66qWVyvHpA.woff2',
			'https://fonts.gstatic.com/s/ptserif/v11/EJRTQgYoZZY2vCFuvAFT_r21cg.woff2',
			'https://fonts.gstatic.com/s/ptserif/v11/EJRVQgYoZZY2vCFuvAFSzr-tdg.woff2',
			'https://fonts.gstatic.com/s/robotocondensed/v18/ieVi2ZhZI2eCN5jzbjEETS9weq8-32meGCAYb8td.woff2',
			'https://fonts.gstatic.com/s/robotocondensed/v18/ieVi2ZhZI2eCN5jzbjEETS9weq8-32meGCQYbw.woff2',
			'https://fonts.gstatic.com/s/robotocondensed/v18/ieVi2ZhZI2eCN5jzbjEETS9weq8-33mZGCAYb8td.woff2',
			'https://fonts.gstatic.com/s/robotocondensed/v18/ieVi2ZhZI2eCN5jzbjEETS9weq8-33mZGCQYbw.woff2',
			'https://fonts.gstatic.com/s/robotocondensed/v18/ieVl2ZhZI2eCN5jzbjEETS9weq8-19K7DQ.woff2',
			'https://fonts.gstatic.com/s/robotocondensed/v18/ieVl2ZhZI2eCN5jzbjEETS9weq8-19a7DRs5.woff2',
		];

		foreach ( $links as $link ) {
			$ext = pathinfo( $link, PATHINFO_EXTENSION );
			if ( isset( $content_types[ $ext ] ) ) {
				$as   = $content_types[ $ext ][0];
				$type = $content_types[ $ext ][1];

				$crossorigin = '';
				if ( 'font' === $as ) {
					$crossorigin = ' crossorigin="anonymous"';
				}

				$onload = '';
				if ( 'style' === $as ) {
					// phpcs:disable WordPress.WP.EnqueuedResources.NonEnqueuedStylesheet
					$onload = ' onload="this.rel=\'stylesheet\'"';
					// phpcs:enable WordPress.WP.EnqueuedResources.NonEnqueuedStylesheet
				}

				$output =
					'<link rel="preload" href="' . esc_url( $link ) . '" as="' . $as . '" type="' . $type .
					'"' . $crossorigin . $onload . ">\n";
				// phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped
				echo $output;
				// phpcs:enable WordPress.Security.EscapeOutput.OutputNotEscaped
			}
		}
	}

	/**
	 * Add display: swap to fonts.
	 */
	public function font_display_swap() {
		echo '<style id="kagg-pagespeed-optimizaion-font-swap" type="text/css">@font-face{font-family:"Font Awesome 5 Brands";font-display:swap}</style>';
	}

	/**
	 * Filter style tag and add display=swap to Google fonts.
	 *
	 * @param string $tag    The link tag for the enqueued style.
	 * @param string $handle The style's registered handle.
	 * @param string $href   The stylesheet's source URL.
	 * @param string $media  The stylesheet's media attribute.
	 *
	 * @return string
	 */
	public function style_loader_tag_filter( $tag, $handle, $href, $media ) {
		if ( 0 === strpos( $href, 'https://fonts.googleapis.com' ) ) {
			return str_replace( $href, $href . '&display=swap', $tag );
		}

		return $tag;
	}
}
