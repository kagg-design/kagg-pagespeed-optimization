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
	 * Scripts moved to footer.
	 *
	 * @var string[]
	 */
	private $moved_scripts = [];

	/**
	 * Scripts to block.
	 *
	 * @var string[]
	 */
	private $block_scripts = [];

	/**
	 * Scripts to delay.
	 *
	 * @var string[]
	 */
	private $delay_scripts = [];

	/**
	 * Styles to move from header to footer.
	 *
	 * @var string[]
	 */
	private $styles_to_footer = [];

	/**
	 * Styles moved to footer.
	 *
	 * @var string[]
	 */
	private $moved_styles = [];

	/**
	 * Styles to block.
	 *
	 * @var string[]
	 */
	private $block_styles = [];

	/**
	 * Generated inline css for fonts.
	 *
	 * @var array
	 */
	private $fonts_generated_css;

	/**
	 * Preload links for fonts.
	 *
	 * @var array
	 */
	private $fonts_preload_links;

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
	private function init() {
		$this->scripts_to_footer   = $this->get_option( 'scripts_to_footer' );
		$this->block_scripts       = $this->get_option( 'block_scripts' );
		$this->delay_scripts       = $this->get_option( 'delay_scripts' );
		$this->styles_to_footer    = $this->get_option( 'styles_to_footer' );
		$this->block_styles        = $this->get_option( 'block_styles' );
		$this->fonts_generated_css = $this->get_option( '_fonts_generated_css' );
		$this->fonts_preload_links = $this->get_option( '_fonts_preload_links' );

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

		// Delay some scripts.
		add_action( 'wp_print_footer_scripts', [ $this, 'delay_scripts' ], - PHP_INT_MAX );
	}

	/**
	 * Remove scripts from header.
	 */
	public function remove_scripts_from_header() {
		$scripts = $this->add_parent_scripts( $this->block_scripts );

		foreach ( $scripts as $script ) {
			if ( wp_script_is( $script, 'enqueued' ) ) {
				wp_deregister_script( $script );
			}
		}

		$scripts = $this->add_parent_scripts( $this->scripts_to_footer );

		foreach ( $scripts as $script ) {
			if ( wp_script_is( $script, 'enqueued' ) ) {
				wp_dequeue_script( $script );
				$this->moved_scripts[] = $script;
			}
		}
	}

	/**
	 * Find all parent scripts, which need $scripts as dependencies.
	 * Return all scripts with parents.
	 *
	 * @param string[] $scripts Scripts.
	 *
	 * @return string[]
	 */
	private function add_parent_scripts( $scripts ) {
		global $wp_scripts;

		$parents = [];

		foreach ( $wp_scripts->registered as $handle => $script ) {
			$deps = $script->deps;
			if ( array_intersect( $scripts, $deps ) && ! in_array( $handle, $parents, true ) ) {
				$handle_parents = $this->add_parent_scripts( [ $handle ] );
				$parents        = $handle_parents ? array_unique( array_merge( $parents, $handle_parents ) ) : $parents;
			}
		}

		return $parents ? array_unique( array_merge( $scripts, $parents ) ) : $scripts;
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

		$styles = $this->add_parent_styles( $this->block_styles );

		foreach ( $styles as $style ) {
			if ( in_array( $style, $wp_styles->queue, true ) ) {
				wp_deregister_style( $style );
			}
		}

		$styles = $this->add_parent_styles( $this->styles_to_footer );

		foreach ( $styles as $style ) {
			if ( in_array( $style, $wp_styles->queue, true ) ) {
				wp_dequeue_style( $style );
				$this->moved_styles[] = $style;
			}
		}
	}

	/**
	 * Find all parent styles, which need $styles as dependencies.
	 * Return all styles with parents.
	 *
	 * @param string[] $styles Styles.
	 *
	 * @return string[]
	 */
	private function add_parent_styles( $styles ) {
		global $wp_styles;

		$styles = array_unique( $styles );

		$parents = [];

		foreach ( $wp_styles->registered as $handle => $style ) {
			$deps = $style->deps;
			if ( array_intersect( $styles, $deps ) ) {
				if ( ! in_array( $handle, $parents, true ) ) {
					$parents = array_unique( array_merge( $parents, $this->add_parent_styles( [ $handle ] ) ) );
				}
			}
		}

		return array_unique( array_merge( $styles, $parents ) );
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

		$links_to_preload = $this->get_option( 'links_to_preload' );

		$links_to_preload = array_unique( array_merge( $links_to_preload ) );

		$links_to_preconnect = array_unique(
			array_map(
				static function ( $link ) {
					$parsed_url = wp_parse_url( $link );
					$scheme     = $parsed_url['scheme'] ?: 'http';
					$host       = $parsed_url['host'] ?: '';

					return $scheme . '://' . $host;
				},
				$links_to_preload
			)
		);

		foreach ( $links_to_preconnect as $link ) {
			echo '<link rel="preconnect" href="' . esc_url( $link ) . "\">\n";
		}

		foreach ( $this->fonts_preload_links as $link ) {
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo $link . "\n";
		}

		foreach ( $links_to_preload as $link ) {
			$ext = pathinfo( preg_replace( '/(.+)\?.+$/', '$1', $link ), PATHINFO_EXTENSION );

			if ( ! isset( $content_types[ $ext ] ) ) {
				continue;
			}

			list( $as, $type ) = $content_types[ $ext ];

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

		$this->font_display_swap();
	}

	/**
	 * Output generated css for fonts.
	 */
	private function font_display_swap() {
		?>
		<style id="kagg-pagespeed-optimization-fonts-generated-css" type="text/css">
			<?php
			foreach ( $this->fonts_generated_css as $generated_css ) {
				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				echo $generated_css . "\n";
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

	/**
	 * Delay some scripts.
	 */
	public function delay_scripts() {
		global $wp_scripts;

		foreach ( $this->delay_scripts as $handle ) {
			if ( wp_script_is( $handle, 'registered' ) && wp_script_is( $handle, 'enqueued' ) ) {
				$src = $wp_scripts->registered[ $handle ]->src;
				$src = str_replace( '#asyncload', '', $src );

				wp_dequeue_script( $handle );
				Delayed_Script::launch( [ 'src' => $src ] );
			}
		}
	}

	/**
	 * Get array or json option.
	 *
	 * @param string $name Option name.
	 * @param string $type Option type.
	 *
	 * @return array
	 */
	private function get_option( $name, $type = 'array' ) {
		$option = $this->main->get_option( $name );

		switch ( $type ) {
			case 'array':
				return array_unique( array_filter( array_map( 'trim', explode( "\n", $option ) ) ) );
			case 'json':
				return (array) json_decode( $option );
			default:
		}

		return [];
	}
}
