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
class PageSpeed_Loader {

	/**
	 * Loader image url.
	 *
	 * @var string
	 */
	private $loader_image_url = '/wp-content/themes/hello-elementor-child/images/newlogo-vox-black@2x.svg';

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
		// Show loader.
		add_action( 'wp_head', [ $this, 'loader' ], - PHP_INT_MAX );
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
				width: 100%;
				height: 100%;
				left: 0;
				top: 0;
				background: #fff;
				z-index: 99999;
				text-align: center;
				-webkit-transition: all 0.3s ease;
				-moz-transition: all 0.3s ease;
				-o-transition: all 0.3s ease;
				transition: all 0.3s ease;
			}

			#kagg-pagespeed-loader img {
				position: absolute;
				max-width: 80%;
				top: 50%;
				left: 50%;
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
		<div id="kagg-pagespeed-loader">
			<img alt="KAGG PageSpeed Loader" src="<?php echo esc_url( $this->loader_image_url ); ?>" data-skip-lazy>
		</div>
		<?php
	}
}
