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

		// Show loader.
		add_action( 'wp_head', [ $this, 'loader' ], - PHP_INT_MAX + 1 );
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

			/* Grayscale. */
			img {
				/* IE */
				filter: progid:DXImageTransform.Microsoft.BasicImage(grayscale=1);

				/* Chrome, Safari */
				-webkit-filter: grayscale(1);

				/* Firefox */
				filter: grayscale(1);
			}

			#mourning {
				position: sticky;
				top: 0;
				width: 100%;
				z-index: 1;
				display: flex;
				align-items: center;
				justify-content: center;
				background: #000;
				color: white;
				text-align: center;
				font-family: "Exo 2", Sans-serif;
				font-size: 1.2em;
				padding: 0.5em;
			}

			#mourning img {
				height: 2em;
			}

			#mourning div {
				margin-left: 1em;
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
			<img alt="KAGG PageSpeed Loader" src="<?php echo esc_url( $this->loader_image_url . '?v=13072020' ); ?>" data-skip-lazy>
		</div>
		<div id="mourning">
			<img src="data:image/png;base64,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" alt="covid-19" data-tip="true" class=" image  " title="covid-19">
			<div>13 июля 2020 года - день общенационального траура в республике Казахстан</div>
		</div>
		<?php
	}
}
