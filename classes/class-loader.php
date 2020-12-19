<?php
/**
 * Loader class file.
 *
 * @package kagg_pagespeed_optimization
 */

namespace KAGG\PageSpeed\Optimization;

/**
 * Class Loader
 */
class Loader {

	/**
	 * Main class instance.
	 *
	 * @var Main
	 */
	private $main;

	/**
	 * Loader constructor.
	 *
	 * @param Main $main Main class instance.
	 */
	public function __construct( $main ) {
		$this->main = $main;

		$this->init();
	}

	/**
	 * Init.
	 */
	public function init() {
		// Show site icon before any inline styles. Otherwise, it does not work.
		remove_action( 'wp_head', 'wp_site_icon' );
		add_action( 'wp_head', 'wp_site_icon', - PHP_INT_MAX );

		// Print loader style and script.
		add_action( 'wp_head', [ $this, 'loader' ], - PHP_INT_MAX + 1 );

		// Show loader div.
		add_action( 'wp_body_open', [ $this, 'loader_div' ], - PHP_INT_MAX );
	}

	/**
	 * Show loader.
	 */
	public function loader() {
		?>
		<style>
			#kagg-pagespeed-loader.hidden-loader {
				opacity: 0;
			}

			#kagg-pagespeed-loader {
				position: fixed;
				width: 100vw;
				height: 100vh;
				left: 0;
				top: 0;
				background: #fff;
				z-index: 99999;
				text-align: center;
			}

			#kagg-pagespeed-loader img {
				position: absolute;
				max-width: 80%;
				top: 50vh;
				left: 50vw;
				transform: translate(-50%, -50%);
				opacity: 0;
				-o-animation: kagg-pagespeed-loader-animation ease 1s forwards;
				-moz-animation: kagg-pagespeed-loader-animation ease 1s forwards;
				-webkit-animation: kagg-pagespeed-loader-animation ease 1s forwards;
				animation: kagg-pagespeed-loader-animation ease 1s forwards;
			}

			@-o-keyframes kagg-pagespeed-loader-animation {
				0% {opacity:0;}
				100% {opacity:1;}
			}

			@-moz-keyframes kagg-pagespeed-loader-animation {
				0% {opacity:0;}
				100% {opacity:1;}
			}

			@-webkit-keyframes kagg-pagespeed-loader-animation {
				0% {opacity:0;}
				100% {opacity:1;}
			}

			@keyframes kagg-pagespeed-loader-animation {
				0%   {opacity: 0;}
				100% {opacity: 1;}
			}
		</style>
		<script type="text/javascript">
			document.addEventListener(
				'DOMContentLoaded',
				function() {
					document.getElementById( 'kagg-pagespeed-loader' ).remove();
				}
			);
		</script>
		<?php
	}

	/**
	 * Show loader div.
	 */
	public function loader_div() {
		// data-skip-lazy works with Optimole plugin.

		?>
		<div id="kagg-pagespeed-loader">
			<img
					alt="KAGG PageSpeed Loader"
					src="<?php echo esc_url( $this->main->get_option( 'loader_image_url' ) ); ?>"
					data-skip-lazy>
		</div>
		<?php
	}
}
