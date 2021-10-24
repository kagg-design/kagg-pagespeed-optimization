<?php
/**
 * LayerSlider class file.
 *
 * @package kagg_pagespeed_optimization
 */

namespace KAGG\PageSpeed\Optimization;

/**
 * Class LayerSlider
 */
class LayerSlider {

	/**
	 * Layer Slider script.
	 *
	 * @var string
	 */
	private $layer_slider_script = '';

	/**
	 * Layer_Slider constructor.
	 */
	public function __construct() {
		$this->init();
	}

	/**
	 * Init class.
	 */
	private function init() {
		add_filter( 'do_shortcode_tag', [ $this, 'do_shortcode_tag' ], 10, 4 );
		add_action( 'wp_print_footer_scripts', [ $this, 'print_script_in_footer' ] );
	}

	/**
	 * Filters the output created by a shortcode callback.
	 *
	 * @since 4.7.0
	 *
	 * @param string       $output Shortcode output.
	 * @param string       $tag    Shortcode name.
	 * @param array|string $attr   Shortcode attributes array or empty string.
	 * @param array        $m      Regular expression match array.
	 *
	 * @return string
	 */
	public function do_shortcode_tag( $output, $tag, $attr, $m ) {
		if ( 'layerslider' !== $tag ) {
			return $output;
		}

		preg_match( '#<script.+_initLayerSlider.+</script>#', $output, $matches );
		$this->layer_slider_script = $matches[0];

		return str_replace( $this->layer_slider_script, '', $output );
	}

	/**
	 * Print Layer Slider script in footer.
	 */
	public function print_script_in_footer() {
		if ( '' === $this->layer_slider_script ) {
			return;
		}

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo str_replace( '<script ', '<script async ', $this->layer_slider_script );
	}
}
