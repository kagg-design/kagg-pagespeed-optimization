<?php
/**
 * Resources class file.
 *
 * @package kagg_pagespeed_optimization
 */

namespace KAGG\PageSpeed\Optimization;

use WP_Dependencies;

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
	 * Delayed scripts.
	 *
	 * @var string[]
	 */
	private $delayed_scripts = [];

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
		$this->fonts_generated_css = $this->get_option( '_fonts_generated_css', 'array' );
		$this->fonts_preload_links = $this->get_option( '_fonts_preload_links', 'array' );

		add_action( 'wp_enqueue_scripts', [ $this, 'remove_scripts_from_header' ], PHP_INT_MAX );
		add_action( 'wp_print_scripts', [ $this, 'remove_scripts_from_header' ], PHP_INT_MAX );
		add_action( 'get_footer', [ $this, 'add_scripts_to_footer' ] );

		add_action( 'wp_enqueue_scripts', [ $this, 'remove_styles_from_header' ], PHP_INT_MAX );
		add_action( 'wp_print_styles', [ $this, 'remove_styles_from_header' ], PHP_INT_MAX );
		add_action( 'get_footer', [ $this, 'add_styles_to_footer' ] );

		// Add display=swap to Google fonts.
		add_filter( 'style_loader_tag', [ $this, 'style_loader_tag_filter' ], 10, 4 );

		// Print preload links.
		add_action( 'wp_head', [ $this, 'head' ], - PHP_INT_MAX );

		// Delay some scripts.
		add_action( 'wp_print_footer_scripts', [ $this, 'delay_scripts' ], - PHP_INT_MAX );

		// Launch delayed scripts.
		add_action( 'wp_print_footer_scripts', [ DelayedScript::class, 'launch_stored_scripts' ], - PHP_INT_MAX + 1 );
	}

	/**
	 * Remove scripts from header.
	 */
	public function remove_scripts_from_header() {
		global $wp_scripts;

		$scripts_tree = $this->create_tree( $wp_scripts );

		$scripts = $this->find_parent_dependencies( $scripts_tree, $this->block_scripts );

		foreach ( $scripts as $script ) {
			if ( wp_script_is( $script ) ) {
				wp_deregister_script( $script );
			}
		}

		$scripts = $this->find_parent_dependencies( $scripts_tree, $this->scripts_to_footer );

		foreach ( $scripts as $script ) {
			if ( wp_script_is( $script ) ) {
				wp_dequeue_script( $script );
				$this->moved_scripts[] = $script;
			}
		}

		$scripts = $this->find_parent_dependencies( $scripts_tree, $this->delay_scripts );

		foreach ( $scripts as $script ) {
			if ( wp_script_is( $script ) ) {
				wp_dequeue_script( $script );
				$this->delayed_scripts[] = $script;
			}
		}
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

		$styles_tree = $this->create_tree( $wp_styles );

		$styles = $this->find_parent_dependencies( $styles_tree, $this->block_styles );

		foreach ( $styles as $style ) {
			if ( in_array( $style, $wp_styles->queue, true ) ) {
				wp_deregister_style( $style );
			}
		}

		$styles = $this->find_parent_dependencies( $styles_tree, $this->styles_to_footer );

		foreach ( $styles as $style ) {
			if ( in_array( $style, $wp_styles->queue, true ) ) {
				wp_dequeue_style( $style );
				$this->moved_styles[] = $style;
			}
		}
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

		$links_to_preload = array_unique( $links_to_preload );

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

		foreach ( $this->fonts_preload_links as $index => $link ) {
			if ( 0 === $index ) {
				echo "\n";
			}

			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo $link . "\n";
		}

		foreach ( $links_to_preload as $index => $link ) {
			if ( 0 === $index ) {
				echo "\n";
			}

			$ext = pathinfo( preg_replace( '/(.+)\?.+$/', '$1', $link ), PATHINFO_EXTENSION );

			if ( ! ( isset( $content_types[ $ext ] ) && is_array( $content_types[ $ext ] ) ) ) {
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
		if ( ! $this->fonts_generated_css ) {
			return;
		}

		echo "\n<style id=\"kagg-pagespeed-optimization-fonts-generated-css\">\n";
		foreach ( $this->fonts_generated_css as $generated_css ) {
			if ( 0 !== strpos( $generated_css, '@' ) && 0 !== strpos( $generated_css, '}' ) ) {
				$generated_css = "\t" . $generated_css;
			}

			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo "\t" . $generated_css . "\n";
		}
		echo "</style>\n";
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
	 * @noinspection PhpUnusedParameterInspection
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

		$scripts_tree = $this->create_tree( $wp_scripts );

		// We have to do it here again as some scripts can be enqueued from the content or footer.
		$scripts = $this->find_parent_dependencies( $scripts_tree, $this->delay_scripts );

		foreach ( $scripts as $script ) {
			if ( wp_script_is( $script ) ) {
				wp_dequeue_script( $script );
				$this->delayed_scripts[] = $script;
			}
		}

		$this->delayed_scripts = array_unique( $this->delayed_scripts );

		$saved_to_do = $wp_scripts->to_do;
		$wp_scripts->all_deps( $this->delayed_scripts, false, 1 );
		$this->delayed_scripts = $wp_scripts->to_do;
		$wp_scripts->to_do     = $saved_to_do;

		foreach ( $this->delayed_scripts as $handle ) {
			if ( ! wp_script_is( $handle, 'registered' ) ) {
				continue;
			}

			$src = $wp_scripts->registered[ $handle ]->src;

			$src = str_replace( '#asyncload', '', $src );

			if ( ! $src ) {
				continue;
			}

			// @todo: Print before, after and extra script properly inside of delayed script.
			// Currently, this cause a problem if after script, for instance, depends on main script.
			$wp_scripts->print_inline_script( $handle, 'before' );
			$wp_scripts->print_inline_script( $handle );
			$wp_scripts->print_extra_script( $handle );

			wp_dequeue_script( $handle );
			DelayedScript::store( [ 'src' => $src ] );
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
	private function get_option( $name, $type = 'unique_array' ) {
		$option = $this->main->get_option( $name );

		switch ( $type ) {
			case 'array':
				return array_filter( array_map( 'trim', explode( "\n", $option ) ) );
			case 'json':
				return (array) json_decode( $option, true );
			case 'unique_array':
				return array_unique( array_filter( array_map( 'trim', explode( "\n", $option ) ) ) );
			default:
		}

		return [];
	}

	/**
	 * Create flat tree of the dependencies.
	 *
	 * @param WP_Dependencies $dependencies Dependencies.
	 * @return array
	 */
	private function create_tree( $dependencies ) {
		$tree = [];

		foreach ( $dependencies->registered as $handle => $dependency ) {
			foreach ( $dependency->deps as $dep ) {
				$tree[ $dep ][] = $handle;
			}
		}

		return $tree;
	}

	/**
	 * Standard level order traversal using queue.
	 *
	 * @param array       $tree Flat tree.
	 * @param string|null $root Root index.
	 *
	 * @return array
	 */
	private function traverse_tree( $tree, $root = null ) {
		if ( ! isset( $tree[ $root ] ) ) {
			return [];
		}

		$traversal   = []; // Traversal result.
		$queue       = []; // Create a queue.
		$queue[]     = $root; // Push root.
		$queue_count = count( $queue );

		while ( 0 !== $queue_count ) {
			// If this node has children.
			while ( $queue_count > 0 ) {
				// Dequeue an item from queue and save it to the traversal result.
				$index       = $queue[0];
				$traversal[] = $index;

				array_shift( $queue );

				if ( isset( $tree[ $index ] ) ) {
					// Push all children of the dequeued item.
					foreach ( $tree[ $index ] as $child ) {
						$queue[] = $child;
					}
				}

				$queue_count --;
			}

			$queue_count = count( $queue );
		}

		return $traversal;
	}

	/**
	 * Find all parent scripts/styles, which need $dependencies as dependencies.
	 * Return $scripts with parents.
	 *
	 * @param array    $tree         Flat tree.
	 * @param string[] $dependencies Scripts/styles.
	 *
	 * @return string[]
	 */
	private function find_parent_dependencies( $tree, $dependencies ) {
		$parent_dependencies = [];

		foreach ( $dependencies as $script ) {
			$parent_dependencies[] = $this->traverse_tree( $tree, $script );
		}

		return array_unique( array_merge( $dependencies, ...$parent_dependencies ) );
	}
}
