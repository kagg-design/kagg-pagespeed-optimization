<?php
/**
 * Yandex_Advertising_Network class file.
 *
 * @package kagg_pagespeed_optimization
 */

namespace KAGG\PageSpeed\Optimization;

/**
 * Class Yandex_Advertising_Network
 */
class Yandex_Advertising_Network {

	/**
	 * Main class instance.
	 *
	 * @var Main
	 */
	private $main;

	/**
	 * RTB scripts.
	 *
	 * @var array
	 */
	private $rtb_scripts = [];

	/**
	 * Yandex_Advertising_Network constructor.
	 *
	 * @param Main $main Main class instance.
	 */
	public function __construct( $main ) {
		$this->main = $main;

		$this->init();
	}

	/**
	 * Init.
	 */
	public function init() {
		add_filter( 'the_content', [ $this, 'remove_rtb_blocks' ], PHP_INT_MAX );
		add_filter( 'do_shortcode_tag', [ $this, 'remove_rtb_blocks' ], PHP_INT_MAX );
		add_action( 'wp_print_footer_scripts', [ $this, 'print_rtb_scripts' ] );
	}

	/**
	 * Filter content and remove RTB scripts.
	 *
	 * @param string $content Content of the current post.
	 *
	 * @return string
	 */
	public function remove_rtb_blocks( $content ) {
		$new_content =
			preg_replace_callback(
				'#<script [\s\S]+?(?:</script>)+?#i',
				[ $this, 'remove_rtb_blocks_callback' ],
				$content
			);

		return $new_content;
	}

	/**
	 * Callback function for remove_rtb_blocks.
	 *
	 * @param array $matches Matches.
	 *
	 * @return string
	 */
	public function remove_rtb_blocks_callback( $matches ) {
		$script = $matches[0];

		if (
			strpos( $script, 'yandex_rtb' ) &&
			( ! in_array( $script, $this->rtb_scripts, true ) )
		) {
			$this->rtb_scripts[] = $script;

			return '';
		}

		return $script;
	}

	/**
	 * Print RTB scripts extracted from the content.
	 */
	public function print_rtb_scripts() {
		if ( ! $this->rtb_scripts ) {
			return;
		}

		foreach ( $this->rtb_scripts as $script ) {
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo "\n" . $this->main->replace_urls( $script );
		}

		echo "\n";
	}
}
