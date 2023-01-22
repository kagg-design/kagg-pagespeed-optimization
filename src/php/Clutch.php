<?php
/**
 * Clutch class file.
 *
 * @package kagg_pagespeed_optimization
 */

namespace KAGG\PageSpeed\Optimization;

use WP_Widget;

/**
 * Class Clutch.
 */
class Clutch {

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->init_hooks();
	}

	/**
	 * Init hooks.
	 */
	private function init_hooks() {
		add_action( 'widget_text', [ $this, 'delayed_clutch_html' ], 10, 3 );
	}

	/**
	 * Print clutch html.
	 */

	/**
	 * Delayed clutch HTML.
	 *
	 * @param string    $text     The widget content.
	 * @param array     $instance Array of settings for the current widget.
	 * @param WP_Widget $widget   Current widget instance.
	 *
	 * @return string
	 * @noinspection PhpUnusedParameterInspection
	 */
	public function delayed_clutch_html( $text, $instance, $widget ) {
		$src = 'https://widget.clutch.co/static/js/widget.js';

		if ( false === strpos( $text, $src ) ) {
			return $text;
		}

		ob_start();
		DelayedScript::launch(
			[ 'src' => $src ],
			1000
		);

		$delayed_clutch = ob_get_clean();

		return preg_replace( '#<script.*?>(.*?)</script>#s', $delayed_clutch, $text );
	}
}
