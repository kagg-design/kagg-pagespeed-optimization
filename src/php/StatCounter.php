<?php
/**
 * StatCounter class file.
 *
 * @package kagg_pagespeed_optimization
 */

namespace KAGG\PageSpeed\Optimization;

/**
 * Class StatCounter.
 */
class StatCounter {

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'plugins_loaded', [ $this, 'init_hooks' ] );
	}

	/**
	 * Init hooks.
	 *
	 * @noinspection PhpUndefinedConstantInspection
	 */
	public function init_hooks(): void {
		if ( ! defined( 'key_sc_project' ) ) {
			return;
		}

		$sc_position = get_option( key_sc_position );
		if ( 'header' === $sc_position ) {
			remove_action( 'wp_head', 'add_statcounter' );
		} else {
			remove_action( 'wp_footer', 'add_statcounter' );
		}

		add_action( 'wp_footer', [ $this, 'add_statcounter' ] );
	}

	/**
	 * Launch StatCounter script.
	 *
	 * @noinspection PhpUndefinedConstantInspection
	 */
	public function add_statcounter(): void {
		$sc_project   = get_option( key_sc_project );
		$sc_security  = get_option( key_sc_security );
		$sc_invisible = get_option( 'sc_invisible' );

		if ( ! $sc_project ) {
			return;
		}

		$protocol = is_ssl() ? 'https:' : 'http:';

		ob_start();
		?>
		<!--suppress JSUnresolvedLibraryURL -->
		<!--suppress HttpUrlsUsage -->
		<!--suppress HtmlUnknownTarget -->
		<?php
		ob_get_clean();

		// phpcs:disable WordPress.WP.EnqueuedResources.NonEnqueuedScript
		ob_start();
		?>

		<!-- Start of StatCounter Code -->
		<script>
			<!--
			const sc_project =<?php echo esc_html( $sc_project ); ?>;
			const sc_security = "<?php echo esc_html( $sc_security ); ?>";
			<?php
			if ( '1' === $sc_invisible ) {
				echo "      var sc_invisible=1;\n";
			}
			?>
			const scJsHost = ( ( 'https:' === document.location.protocol ) ? 'https://secure.' : 'http://www.' );
			//-->

			<?php
			if ( '1' !== $sc_invisible ) {
				echo "\ndocument.write(\"<sc\"+\"ript src='\" +scJsHost +\"statcounter.com/counter/counter.js'></\"+\"script>\");";
			}
			?>
		</script>

		<?php
		if ( '1' === $sc_invisible ) {
			if ( 'http:' === $protocol ) {
				?>
				<script type="text/javascript" src="http://www.statcounter.com/counter/counter.js" async></script>
				<?php
			}

			if ( 'https:' === $protocol ) {
				?>
				<script type="text/javascript" src="https://secure.statcounter.com/counter/counter.js" async></script>
				<?php
			}
		}
		?>

		<noscript>
			<div class="statcounter">
				<a title="web analytics" href="<?php echo esc_attr( $protocol ); ?>//statcounter.com/">
					<img
							class="statcounter"
							src="<?php echo esc_attr( $protocol ); ?>//c.statcounter.com/<?php echo esc_attr( $sc_project ); ?>/0/<?php echo esc_attr( $sc_security ); ?>/<?php echo esc_attr( $sc_invisible ); ?>/"
							alt="web analytics"/>
				</a>
			</div>
		</noscript>
		<!-- End of StatCounter Code -->
		<?php

		// phpcs:enable WordPress.WP.EnqueuedResources.NonEnqueuedScript
		DelayedScript::launch_html( ob_get_clean() );
	}
}
