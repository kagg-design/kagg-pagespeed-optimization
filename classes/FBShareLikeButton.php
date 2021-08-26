<?php
/**
 * FBShareLikeButton class file.
 *
 * @package kagg_pagespeed_optimization
 */

namespace KAGG\PageSpeed\Optimization;

/**
 * Class FBShareLikeButton.
 */
class FBShareLikeButton {
	/**
	 * FB root div.
	 *
	 * @var string
	 */
	private string $fb_root_div = '<div id="fb-root"></div>';

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'plugins_loaded', [ $this, 'init_hooks' ] );
	}

	/**
	 * Init hooks.
	 */
	public function init_hooks(): void {
		if ( ! isset( $GLOBALS['vifslb_like_settings'] ) ) {
			return;
		}

		remove_action( 'wp_footer', 'vifslb_like_func_footer' );
		add_action( 'wp_footer', [ $this, 'delayed_script' ] );
	}

	/**
	 * Print delayed script.
	 */
	public function delayed_script(): void {
		global $vifslb_like_settings;

		ob_start();
		$this->vifslb_like_func_footer();
		$js = ob_get_clean();

		if ( 'html5' === $vifslb_like_settings['btntype'] ) {
			$js = str_replace( $this->fb_root_div, '', $js );

			// phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped
			echo $this->fb_root_div;
			echo Delayed_Script::strip_and_create( $js );
			// phpcs:enable WordPress.Security.EscapeOutput.OutputNotEscaped
		}
	}

	/**
	 * Run vifslb_like_func_footer
	 */
	private function vifslb_like_func_footer(): void {
		global $vifslb_like_settings;

		if ( '' === $vifslb_like_settings['language'] ) {
			$lang1 = 'en_Us';
		} else {
			$lang1 = $vifslb_like_settings['language'];
		}

		$app_ids = trim( $vifslb_like_settings['facebook_app_id'] );
		$app_ids = explode( ',', $app_ids );

		if ( ! count( $app_ids ) ) {
			return;
		}

		$app_id = '';

		foreach ( $app_ids as $app_id ) {
			if ( is_numeric( $app_id ) ) {
				break;
			}
		}

		if ( ! is_numeric( $app_id ) ) {
			return;
		}

		if ( 'html5' === $vifslb_like_settings['btntype'] ) {
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo $this->fb_root_div;
			?>
			<script>( function( d, s, id ) {
					var js, fjs = d.getElementsByTagName( s )[ 0 ];
					if ( d.getElementById( id ) ) return;
					js = d.createElement( s );
					js.id = id;
					js.src = "//connect.facebook.net/<?php echo esc_html( $lang1 ); ?>/sdk.js#xfbml=1&version=v2.7&appId=<?php echo esc_html( $app_id ); ?>";
					fjs.parentNode.insertBefore( js, fjs );
				}( document, 'script', 'facebook-jssdk' ) );
			</script>
			<?php
		}
	}
}
