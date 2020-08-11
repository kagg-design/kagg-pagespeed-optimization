<?php
/**
 * One_Signal class file.
 *
 * @package kagg_pagespeed_optimization
 */

namespace KAGG\PageSpeed\Optimization;

/**
 * Class One_Signal
 */
class One_Signal {

	/**
	 * One_Signal constructor.
	 */
	public function __construct() {
		$this->init();
	}

	/**
	 * Init class.
	 */
	public function init() {
		add_action( 'wp_print_footer_scripts', [ $this, 'delay_one_signal_script' ], - PHP_INT_MAX );
	}

	/**
	 * Delay OneSignal script.
	 */
	public function delay_one_signal_script() {
		global $wp_scripts;

		$handle = 'remote_sdk';

		if ( wp_script_is( $handle, 'enqueued' ) ) {
			$src = $wp_scripts->registered[ $handle ]->src;
			$src = str_replace( '#asyncload', '', $src );

			wp_dequeue_script( $handle );
			Delayed_Script::launch( [ 'src' => $src ] );
		}
	}
}
