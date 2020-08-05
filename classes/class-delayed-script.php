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
	 * Script delay timeout.
	 */
	const TIMEOUT = 3000;

	/**
	 * Create delayed script.
	 *
	 * @param string $js js code to wrap in setTimeout().
	 *
	 * @return false|string
	 */
	public static function create( $js ) {
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
						<?php echo intval( self::TIMEOUT ); ?>
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
	 * @param string $src Script source url.
	 */
	public static function launch( $src ) {
		ob_start();

		?>
		() => {
		const t = document.getElementsByTagName( 'script' )[0];
		const s = document.createElement('script');
		s.type  = 'text/javascript';
		s.src = '<?php echo esc_url( $src ); ?>';
		s.async = true;
		t.parentNode.insertBefore( s, t );
		}
		<?php

		$js = ob_get_clean();

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo self::create( $js );
	}
}
