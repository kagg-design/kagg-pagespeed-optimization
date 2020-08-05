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
	 * @return false|string
	 */
	public static function create( $js, $delay = 3000 ) {
		ob_start();

		?>
		<script type="text/javascript" async>
			window.addEventListener(
				'load',
				function() {
					setTimeout(
						<?php
						// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						echo $js;
						?>
						,
						<?php echo intval( $delay ); ?>
					);
				}
			);
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

		// phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped
		?>
		() => {
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
		}
		<?php

		$js = ob_get_clean();

		echo self::create( $js, $delay );
		// phpcs:enable WordPress.Security.EscapeOutput.OutputNotEscaped
	}
}
