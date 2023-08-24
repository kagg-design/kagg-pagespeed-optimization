<?php
/**
 * PassiveEvents class file.
 *
 * @package kagg_pagespeed_optimization
 */

namespace KAGG\PageSpeed\Optimization;

/**
 * Class PassiveEvents
 */
class PassiveEvents {
	const HANDLE = 'pagespeed-optimization-passive-events';

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
		add_filter( 'script_loader_tag', [ $this, 'make_script_async' ], 10, 3 );
	}

	/**
	 * Enqueue passive event script.
	 */
	public function enqueue_scripts() {
		wp_enqueue_script(
			self::HANDLE,
			KAGG_PAGESPEED_OPTIMIZATION_URL . '/assets/js/passive-events.js',
			[],
			KAGG_PAGESPEED_OPTIMIZATION_VERSION,
			false
		);
	}

	/**
	 * Filter script tag and add async.
	 *
	 * @param string|mixed $tag    The script tag for the enqueued script.
	 * @param string       $handle The script's registered handle.
	 * @param string       $src    The script's source URL.
	 *
	 * @return string|mixed
	 * @noinspection PhpUnusedParameterInspection
	 */
	public function make_script_async( $tag, string $handle, string $src ) {
		if ( self::HANDLE !== $handle ) {
			return $tag;
		}

		return str_replace( '></script>', ' async></script>', $tag );
	}
}
