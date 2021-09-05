<?php
/**
 * Delayed_Script class file.
 *
 * @package kagg_pagespeed_optimization
 */

namespace KAGG\PageSpeed\Optimization;

use Exception;
use JShrink\Minifier;

/**
 * Class Delayed_Script
 */
class Delayed_Script {

	/**
	 * List of delayed scripts.
	 *
	 * @var array
	 */
	private static $delayed_scripts = [];

	/**
	 * Create delayed script.
	 *
	 * @param string $js    js code to wrap in setTimeout().
	 * @param int    $delay Delay in ms. Negative means no delay, just wait for user interaction.
	 *
	 * @return string
	 */
	public static function create( $js, $delay = - 1 ) {
		ob_start();
		?>
		<!--suppress JSUnusedAssignment, JSUnusedLocalSymbols -->
		<?php
		ob_get_clean();

		ob_start();
		?>

		<script type="text/javascript" async>
			( () => {
				'use strict';

				let loaded = false,
					scrolled = false,
					timerId;

				function load() {
					if ( loaded ) {
						return;
					}

					loaded = true;
					clearTimeout( timerId );

					window.removeEventListener( 'touchstart', load );
					document.removeEventListener( 'mouseenter', load );
					document.removeEventListener( 'click', load );
					window.removeEventListener( 'load', delayedLoad );

					let s;
					<?php
					// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					echo $js;
					?>
				}

				function scrollHandler() {
					if ( ! scrolled ) {
						// Ignore first scroll event, which can be on page load.
						scrolled = true;
						return;
					}

					window.removeEventListener( 'scroll', scrollHandler );
					load();
				}

				function delayedLoad() {
					window.addEventListener( 'scroll', scrollHandler );
					const delay = <?php echo (int) $delay; ?>;

					if ( delay >= 0 ) {
						setTimeout( load, delay );
					}
				}

				window.addEventListener( 'touchstart', load );
				document.addEventListener( 'mouseenter', load );
				document.addEventListener( 'click', load );
				window.addEventListener( 'load', delayedLoad );
			} )();
		</script>

		<?php
		$js = ob_get_clean();

		if ( ! defined( 'SCRIPT_DEBUG' ) || ! SCRIPT_DEBUG ) {
			try {
				$js = (string) Minifier::minify( $js );
			} catch ( Exception $ex ) {
				$js = '';
			}
		}

		return $js;
	}

	/**
	 * Launch script specified by source url.
	 *
	 * @param array $args  Arguments.
	 * @param int   $delay Delay in ms.
	 */
	public static function launch( $args, $delay = - 1 ) {
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo self::create( self::get_js( $args ), $delay );
	}

	/**
	 * Launch html code containing javascript.
	 *
	 * Combine javascript in <script...>...</script> tags.
	 * Convert it to delayed script code and replace in the initial html.
	 * Output resulted html.
	 *
	 * @param string $html  HTMl code with scripts.
	 * @param int    $delay Delay in ms.
	 */
	public static function launch_html( $html, $delay = - 1 ) {
		$found = preg_match_all( '#<script.*?>(.*?)</script>#s', $html, $matches );
		if ( $found ) {
			$placeholder = '<!-- KAGG script placeholder -->';

			foreach ( $matches as $index => $match ) {
				$html = str_replace( $matches[0][ $index ], 0 === $index ? $placeholder : '', $html );
			}

			$html = str_replace(
				$placeholder,
				self::create( implode( "\n", $matches[1] ), $delay ),
				$html
			);
		}

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $html;
	}

	/**
	 * Prepare to launch script specified by source url.
	 *
	 * @param array $args  Arguments.
	 * @param int   $delay Delay in ms.
	 */
	public static function store( $args, $delay = - 1 ) {
		self::$delayed_scripts[ (int) $delay ][] = $args;
	}

	/**
	 * Launch stored scripts.
	 */
	public static function launch_stored_scripts() {
		foreach ( self::$delayed_scripts as $delay => $delayed_scripts ) {
			$scripts = [];

			foreach ( $delayed_scripts as $delayed_script ) {
				$scripts[] = self::get_js( $delayed_script, false );
			}

			if ( ! empty( $scripts ) ) {
				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				echo self::create( implode( "\n", $scripts ), $delay );
			}
		}
	}

	/**
	 * Get js for delayed launch of the script.
	 *
	 * @param array $args  Script arguments.
	 * @param bool  $async Launch as async.
	 *
	 * @return string
	 */
	private static function get_js( array $args, $async = true ) {
		ob_start();

		?>
		s = document.createElement('script');
		s.type  = 'text/javascript';
		<?php
		// phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped
		foreach ( $args as $key => $arg ) {
			if ( 'data' === $key ) {
				foreach ( $arg as $data_key => $data_arg ) {
					echo "s.dataset.$data_key = '$data_arg';\n";
				}
				continue;
			}

			echo "s['$key'] = '$arg';\n";
		}
		?>
		s.async = <?php echo $async ? 'true' : 'false'; ?>;
		document.body.appendChild( s );
		<?php

		return ob_get_clean();
		// phpcs:enable WordPress.Security.EscapeOutput.OutputNotEscaped
	}
}
