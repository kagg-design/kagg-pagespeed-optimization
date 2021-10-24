<?php
/**
 * Medusa class file.
 *
 * @package kagg_pagespeed_optimization
 */

namespace KAGG\PageSpeed\Optimization;

/**
 * Class Medusa
 */
class Medusa {

	/**
	 * Medusa constructor.
	 */
	public function __construct() {
		$this->init();
	}

	/**
	 * Init class.
	 */
	private function init() {
		$theme = wp_get_theme();

		if ( 'medusa' !== $theme->get_stylesheet() && 'medusa' !== $theme->get_template() ) {
			return;
		}

		add_action( 'wp_head', [ $this, 'head' ] );
		add_action( 'wp_print_footer_scripts', [ $this, 'print_footer_scripts' ], 100 );
	}

	/**
	 * Remove theme inline styles from header.
	 */
	public function head() {
		remove_action( 'wp_head', 'theme_option_styles', 100 );
	}

	/**
	 * Print theme inline styles in footer.
	 */
	public function print_footer_scripts() {
		theme_option_styles();
	}
}
