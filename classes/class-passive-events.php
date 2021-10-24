<?php
/**
 * Passive_Events class file.
 *
 * @package kagg_pagespeed_optimization
 */

namespace KAGG\PageSpeed\Optimization;

/**
 * Class Passive_Events
 */
class Passive_Events {

	/**
	 * Passive_Events constructor.
	 */
	public function __construct() {
		$this->init();
	}

	/**
	 * Init class.
	 */
	public function init() {
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
	}

	/**
	 * Enqueue passive event script.
	 */
	public function enqueue_scripts() {
		wp_enqueue_script(
			'pagespeed-optimization-passive-events',
			KAGG_PAGESPEED_OPTIMIZATION_URL . '/assets/js/passive-events.js',
			[],
			KAGG_PAGESPEED_OPTIMIZATION_VERSION,
			false
		);
	}
}
