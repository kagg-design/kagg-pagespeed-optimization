<?php
/**
 * Delayed_Script class file.
 *
 * @package kagg_pagespeed_optimization
 */

namespace KAGG\PageSpeed\Optimization;

/**
 * Class Delayed_Script
 */
class Delayed_Script {

	/**
	 * Create delayed script.
	 *
	 * @param string $js    js code to wrap in setTimeout().
	 * @param int    $delay Delay in ms. Negative means no delay, just wait for user interaction.
	 *
	 * @return string
	 */
	public static function create( $js, $delay = 3000 ) {
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

		return ob_get_clean();
	}

	/**
	 * Launch script specified by source url.
	 *
	 * @param array $args  Arguments.
	 * @param int   $delay Delay in ms.
	 */
	public static function launch( $args, $delay = 3000 ) {
		ob_start();

		?>
		const t = document.getElementsByTagName( 'script' )[0];
		const s = document.createElement('script');
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
		s.async = true;
		t.parentNode.insertBefore( s, t );
		<?php

		$js = ob_get_clean();

		echo self::create( $js, $delay );
		// phpcs:enable WordPress.Security.EscapeOutput.OutputNotEscaped
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
	public static function launch_html( $html, $delay = 3000 ) {
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
}
