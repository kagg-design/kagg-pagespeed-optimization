<?php
/**
 * Zopim class file.
 *
 * @package kagg_pagespeed_optimization
 */

namespace KAGG\PageSpeed\Optimization;

use Zopim_Options;

/**
 * Class Zopim.
 */
class Zopim {

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
		if ( ! class_exists( 'Zopim', false ) ) {
			return;
		}

		remove_action( 'wp_footer', [ 'Zopim_Widget', 'zopimme' ] );
		add_action( 'wp_footer', [ $this, 'delayed_zopim_scripts' ] );
	}


	/**
	 * Print delayed zopim scripts.
	 */
	public function delayed_zopim_scripts(): void {
		ob_start();
		$this->zopim_me();
		Delayed_Script::launch_html( ob_get_clean() );
	}

	/**
	 * Run zopim widget
	 *
	 * We need some CSS to position the paragraph.
	 */
	private function zopim_me(): void {
		$subdomain = get_option( Zopim_Options::ZENDESK_OPTION_SUBDOMAIN );

		if ( $subdomain ) {
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo $this->get_widget_code_using_subdomain( $subdomain );

			return;
		}

		$code = get_option( Zopim_Options::ZOPIM_OPTION_CODE );

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$page = isset( $_GET['page'] ) ?
			filter_input( INPUT_GET, 'page', FILTER_SANITIZE_STRING ) :
			'';

		$server_name = isset( $_SERVER['SERVER_NAME'] ) ?
			filter_var( wp_unslash( $_SERVER['SERVER_NAME'] ), FILTER_SANITIZE_STRING ) :
			'';

		if (
			( '' === $code || 'zopim' === $code ) &&
			! ( false !== strpos( $page, 'zopim' ) ) &&
			( false === strpos( $server_name, 'zopim' ) )
		) {
			return;
		}

		?>
		<!--Embed from Zendesk Chat WordPress Plugin v<?php echo esc_html( VERSION_NUMBER ); ?>-->
		<!--Start of Zopim Live Chat Script-->
		<script type="text/javascript">
			window.$zopim || ( function( d, s ) {
				const z = window.$zopim = function( c ) {
						z._.push( c );
					},
					$ = z.s = d.createElement( s ),
					e = d.getElementsByTagName( s )[ 0 ];
				z.set = function( o ) {
					z.set._.push( o );
				};
				z._ = [];
				z.set._ = [];
				$.async = ! 0;
				$.setAttribute( 'charset', 'utf-8' );
				$.src = '//v2.zopim.com/?<?php echo esc_attr( $code ); ?>';
				z.t = +new Date;
				$.type = 'text/javascript';
				e.parentNode.insertBefore( $, e );
			} )( document, 'script' );
		</script>
		<?php

		$current_user = wp_get_current_user();

		$firstname  = '';
		$user_email = '';

		if ( isset( $current_user ) ) {
			$firstname  = $current_user->display_name;
			$user_email = $current_user->user_email;
		}

		if ( $firstname && $user_email ) {
			?>
			<script type="text/javascript">
				$zopim( function() {
					$zopim.livechat.set( {
						name: '<?php echo esc_html( $firstname ); ?>',
						email: '<?php echo esc_html( $user_email ); ?>'
					} );
				} );
			</script>
			<?php
		}

		echo "\n<script type=\"text/javascript\">\n";
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo Zopim_Options::get_widget_options();
		echo "\n</script>\n";

		echo "\n<!--End of Zendesk Chat Script-->\n";
	}

	/**
	 * Get widget code.
	 *
	 * @param string $subdomain Subdomain.
	 *
	 * @return string
	 */
	private function get_widget_code_using_subdomain( string $subdomain ): string {
		$url      = 'https://ekr.zdassets.com/snippets/web_widget/' . $subdomain . '.zendesk.com?dynamic_snippet=true';
		$response = wp_remote_get( $url, [] );

		if ( is_wp_error( $response ) ) {
			$error = [ 'wp_error' => $response->get_error_message() ];

			return (string) wp_json_encode( $error );
		}

		return $response['body'];
	}
}
