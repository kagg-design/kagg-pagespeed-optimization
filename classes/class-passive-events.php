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
		wp_enqueue_script(
			'pagespeed-optimization-passive-events',
			KAGG_PAGESPEED_OPTIMIZATION_URL . '/js/passive-events.js',
			[],
			KAGG_PAGESPEED_OPTIMIZATION_VERSION,
			false
		);
	}
}
