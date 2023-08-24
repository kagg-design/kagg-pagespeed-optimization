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
		add_action( 'widget_text', [ $this, 'remove_clutch_html' ], 10, 3 );
	}

	/**
	 * Remove clutch HTML from widget.
	 *
	 * @param string    $text     The widget content.
	 * @param array     $instance Array of settings for the current widget.
	 * @param WP_Widget $widget   Current widget instance.
	 *
	 * @return string
	 * @noinspection PhpUnusedParameterInspection
	 */
	public function remove_clutch_html( string $text, array $instance, WP_Widget $widget ): string {
		$src = 'https://widget.clutch.co/static/js/widget.js';

		if ( false === strpos( $text, $src ) ) {
			return $text;
		}

		ob_start();
		DelayedScript::launch( [ 'src' => $src ] );

		$delayed_clutch = ob_get_clean();
		$search         = 's.async=true;';
		$onload         = 's.onload=function(){window.CLUTCHCO.Init()};';
		$delayed_clutch = str_replace( $search, $search . $onload, $delayed_clutch );

		return preg_replace( '#<script.*?>(.*?)</script>#s', $delayed_clutch, $text );
	}
}
