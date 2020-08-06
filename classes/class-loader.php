<?php
/**
 * PageSpeed_Loader class file.
 *
 * @package kagg_pagespeed_optimization
 */

namespace KAGG\PageSpeed\Optimization;

/**
 * Class PageSpeed_Loader
 */
class Loader {

	/**
	 * Loader image url.
	 *
	 * @var string
	 */
	private $loader_image_url = '/wp-content/themes/hello-elementor-child/images/voxpopuli-logo.svg';

	/**
	 * PageSpeed_Loader constructor.
	 */
	public function __construct() {
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
		// data-skip-lazy works with Optimole.
		?>
		<style>
			#kagg-pagespeed-loader.hide {
				opacity: 0;
				z-index: -1;
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
				-webkit-transition: opacity 0.3s ease;
				-moz-transition: opacity 0.3s ease;
				-o-transition: opacity 0.3s ease;
				transition: opacity 0.3s ease;
			}

			#kagg-pagespeed-loader img {
				position: absolute;
				max-width: 80%;
				top: 50vh;
				left: 50vw;
				transform: translate(-50%, -50%);
			}
		</style>
		<script type="text/javascript">
			document.addEventListener(
				'DOMContentLoaded',
				function() {
					document.getElementById( 'kagg-pagespeed-loader' ).classList.add( 'hide' );
				}
			);
		</script>
		<?php
	}

	/**
	 * Show loader div.
	 */
	public function loader_div() {
		?>
		<div id="kagg-pagespeed-loader">
			<img alt="KAGG PageSpeed Loader" src="<?php echo esc_url( $this->loader_image_url ); ?>" data-skip-lazy>
		</div>
		<?php
	}
}
