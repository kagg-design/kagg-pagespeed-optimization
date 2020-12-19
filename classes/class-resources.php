<?php
/**
 * Resources class file.
 *
 * @package kagg_pagespeed_optimization
 */

namespace KAGG\PageSpeed\Optimization;

/**
 * Class Resources
 *
 * Move scripts and styles from header to footer.
 * Make scripts defer.
 * Add display=swap to fonts.
 */
class Resources {

	/**
	 * Main class instance.
	 *
	 * @var Main
	 */
	private $main;

	/**
	 * Scripts to move from header to footer.
	 *
	 * @var string[]
	 */
	private $scripts_to_footer = [];

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
	private $styles_to_footer = [];

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
	 * Resources constructor.
	 *
	 * @param Main $main Main class instance.
	 */
	public function __construct( $main ) {
		$this->main = $main;

		$this->init();
	}

	/**
	 * Init class hooks.
	 */
	public function init() {
		if ( is_admin() ) {
			return;
		}

		$this->scripts_to_footer = $this->main->get_option( 'scripts_to_footer', [] );
		$this->block_scripts     = $this->main->get_option( 'block_scripts', [] );
		$this->styles_to_footer  = $this->main->get_option( 'styles_to_footer', [] );
		$this->block_styles      = $this->main->get_option( 'block_styles', [] );

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
	}

	/**
	 * Remove scripts from header.
	 */
	public function remove_scripts_from_header() {
		$scripts = array_unique( array_merge( $this->scripts_to_footer, $this->block_scripts ) );
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

		$styles = array_unique( array_merge( $this->styles_to_footer, $this->block_styles ) );
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
	 * Print preload links.
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

		$fonts_to_preload = json_decode( $this->main->get_option( 'fonts_to_preload', '[]' ) );

		$links_to_preload = $this->main->get_option( 'links_to_preload', [] );

		$this->font_display_swap( $fonts_to_preload );

		foreach ( $fonts_to_preload as $font_links ) {
			$links_to_preload = array_merge( $links_to_preload, (array) $font_links );
		}

		foreach ( $links_to_preload as $link ) {
			$ext = pathinfo( preg_replace( '/(.+)\?.+$/', '$1', $link ), PATHINFO_EXTENSION );

			if ( ! isset( $content_types[ $ext ] ) ) {
				continue;
			}

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

	/**
	 * Add display: swap to fonts.
	 *
	 * @param array $fonts Fonts.
	 */
	private function font_display_swap( $fonts ) {
		?>
		<style id="kagg-pagespeed-optimization-font-swap" type="text/css">
			<?php
			foreach ( $fonts as $font_family => $font_links ) {
				$font_links = (array) $font_links;
				$urls       = [];

				foreach ( $font_links as $font_link ) {
					// @todo: Allow to specify format, not only woff2.
					$urls[] = 'url(' . esc_html( $font_link ) . ') format("woff2")';
				}

				// phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped
				?>
				@font-face {
					font-family: <?php echo "'" . $font_family . "'"; ?>;
					src: <?php echo implode( ', ', $urls ); ?>;
					font-display: swap;
				}

				<?php
				// phpcs:enable WordPress.Security.EscapeOutput.OutputNotEscaped
			}
			?>
		</style>
		<?php
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
