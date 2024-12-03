<?php
/**
 * MonsterInsights class file.
 *
 * @package kagg_pagespeed_optimization
 */

namespace KAGG\PageSpeed\Optimization;

/**
 * Class MonsterInsights.
 */
class MonsterInsights {

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'plugins_loaded', [ $this, 'init_hooks' ] );
	}

	/**
	 * Init hooks.
	 */
	public function init_hooks(): void {
		if ( ! defined( 'MONSTERINSIGHTS_VERSION' ) ) {
			return;
		}

		add_action( 'monsterinsights_tracking_before_gtag', [ $this, 'before_gtag' ] );
		add_action( 'monsterinsights_tracking_after_gtag', [ $this, 'after_gtag' ] );
	}

	/**
	 * Before gtag.
	 *
	 * @return void
	 */
	public function before_gtag(): void {
		ob_start();
	}

	/**
	 * After gtag.
	 *
	 * @return void
	 */
	public function after_gtag(): void {
		$g_tag = ob_get_clean();

		if ( preg_match( '#<script.+googletagmanager.+?</script>#', $g_tag, $matches ) ) {
			$script_tag = $matches[0];

			preg_match( '#<script (.+)></script>#', $script_tag, $matches );

			$script_args = $matches[1] ?? '';

			preg_match_all( '#([\w|-]+)=["\']([^"\']+)["\']|([\w|-]+)#', $script_args, $matches );

			$keys   = array_filter( $matches[1] ) + array_filter( $matches[3] );
			$values = $matches[2];
			$args   = array_combine( $keys, $values );
			$data   = [];

			foreach ( $args as $key => $value ) {
				if ( 0 === strpos( $key, 'data-' ) ) {
					$data[ str_replace( 'data-', '', $key ) ] = $value;
					unset( $args[ $key ] );
				}
			}

			$args['data'] = $data;

			$g_tag  = str_replace( $script_tag, '', $g_tag );

			DelayedScript::launch( $args );
		}

        DelayedScript::launch_html( $g_tag );
	}
}
