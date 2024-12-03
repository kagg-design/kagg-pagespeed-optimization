<?php
/**
 * DelayedScript class file.
 *
 * @package kagg_pagespeed_optimization
 */

// phpcs:disable Generic.Commenting.DocComment.MissingShort
/** @noinspection PhpUndefinedNamespaceInspection */
/** @noinspection PhpUndefinedClassInspection */
// phpcs:enable Generic.Commenting.DocComment.MissingShort

namespace KAGG\PageSpeed\Optimization;

use Exception;
use JShrink\Minifier;

/**
 * Class DelayedScript
 */
class DelayedScript {

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
	 * @noinspection JSUnusedAssignment
	 */
	public static function create( string $js, int $delay = -1 ): string {
		$js = <<<JS
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
			document.body.removeEventListener( 'mouseenter', load );
			document.body.removeEventListener( 'click', load );
			window.removeEventListener( 'scroll', scrollHandler );

$js
		}

		function scrollHandler() {
			if ( ! scrolled ) {
				// Ignore first scroll event, which can be on page load.
				scrolled = true;
				return;
			}

			load();
		}

		window.addEventListener( 'load', function() {
			// noinspection JSAnnotator
			const delay = $delay;

			if ( delay >= 0 ) {
				setTimeout( load, delay );

				return;
			}

			window.addEventListener( 'touchstart', load );
			document.body.addEventListener( 'mouseenter', load );
			document.body.addEventListener( 'click', load );
			window.addEventListener( 'scroll', scrollHandler );
		} );
	} )();
JS;

		$js = "<script>\n" . $js . "\n</script>\n";

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
	 * @param int   $delay Delay in ms. Negative means no delay, just wait for user interaction.
	 */
	public static function launch( array $args, int $delay = -1 ): void {
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
	public static function launch_html( string $html, int $delay = - 1 ): void {
		$found = preg_match_all( '#<script.*?>(.*?)</script>#s', $html, $matches );

		if ( $found ) {
			$placeholder = '<!-- KAGG script placeholder -->';

			foreach ( $matches[0] as $index => $match ) {
				$html = str_replace( $match, 0 === $index ? $placeholder : '', $html );
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
	public static function store( array $args, int $delay = - 1 ): void {
		self::$delayed_scripts[ $delay ][] = $args;
	}

	/**
	 * Launch stored scripts.
	 */
	public static function launch_stored_scripts(): void {
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
	 * @noinspection JSUnusedLocalSymbols
	 */
	private static function get_js( array $args, bool $async = true ): string {
		unset( $args['async'] );
		$async_string = $async ? 'true' : 'false';

		$js = <<<JS
			const t = document.getElementsByTagName( 'script' )[0];
			const s = document.createElement( 'script' );
			s.type  = 'text/javascript';
JS;

		$js = "$js\n";

		// phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped
		foreach ( $args as $key => $arg ) {
			if ( 'data' === $key ) {
				foreach ( $arg as $data_key => $data_arg ) {
					$js .= "\t\t\ts.setAttribute( 'data-' + '$data_key', '$data_arg' );\n";
				}
				continue;
			}

			$js .= "\t\t\ts['$key'] = '$arg';\n";
		}

		$js .= <<<JS
			s.async = $async_string;
			t.parentNode.insertBefore( s, t );
JS;

		return $js;
		// phpcs:enable WordPress.Security.EscapeOutput.OutputNotEscaped
	}
}
