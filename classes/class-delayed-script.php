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
	 * @param int    $delay Delay in ms.
	 *
	 * @return string
	 */
	public static function create( string $js, int $delay = 3000 ): string {
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
					setTimeout( load, <?php echo (int) $delay; ?> );
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
	 * Strip javascript removing <script type="text/javascript">...</script> tags
	 * and converting html comments to js comments.
	 * Create delayed script then.
	 *
	 * @param string $js Javascript.
	 * @param int    $delay Delay in ms.
	 *
	 * @return string
	 */
	public static function strip_and_create( string $js, int $delay = 3000 ): string {
		$js = str_replace(
			[ '<script type="text/javascript">', '</script>' ],
			[ '', '' ],
			$js
		);

		return self::create( $js, $delay );
	}

	/**
	 * Launch script specified by source url.
	 *
	 * @param array $args  Arguments.
	 * @param int   $delay Delay in ms.
	 */
	public static function launch( array $args, int $delay = 3000 ): void {
		ob_start();

		// phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped
		?>
		const t = document.getElementsByTagName( 'script' )[0];
		const s = document.createElement('script');
		s.type  = 'text/javascript';
		<?php
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
}
