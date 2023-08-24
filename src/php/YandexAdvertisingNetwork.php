<?php
/**
 * YandexAdvertisingNetwork class file.
 *
 * @package kagg_pagespeed_optimization
 */

namespace KAGG\PageSpeed\Optimization;

/**
 * Class YandexAdvertisingNetwork
 */
class YandexAdvertisingNetwork {

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
	 * RTB blocks.
	 *
	 * @var array
	 */
	private $rtb_blocks = [];

	/**
	 * Yandex_Advertising_Network constructor.
	 *
	 * @param Main $main Main class instance.
	 */
	public function __construct( Main $main ) {
		$this->main = $main;

		$this->init();
	}

	/**
	 * Init.
	 */
	public function init() {
		add_filter( 'the_content', [ $this, 'remove_rtb_blocks' ], PHP_INT_MAX );
		add_filter( 'do_shortcode_tag', [ $this, 'remove_rtb_blocks' ], PHP_INT_MAX );
		add_action( 'wp_print_footer_scripts', [ $this, 'print_rtb_script' ] );
	}

	/**
	 * Filter content and remove RTB scripts.
	 *
	 * @param string|mixed $content Content of the current post.
	 *
	 * @return string
	 */
	public function remove_rtb_blocks( $content ): string {
		return preg_replace_callback(
			'#<script [\s\S]+?(?:</script>)+?#i',
			[ $this, 'remove_rtb_blocks_callback' ],
			(string) $content
		);
	}

	/**
	 * Callback function for remove_rtb_blocks.
	 *
	 * @param array $matches Matches.
	 *
	 * @return string
	 */
	public function remove_rtb_blocks_callback( array $matches ): string {
		$script = $matches[0];

		if (
			strpos( $script, 'yandex_rtb' ) &&
			( ! in_array( $script, $this->rtb_scripts, true ) )
		) {
			$this->rtb_scripts[] = $script;

			$block_id  = '';
			$render_to = '';

			if ( preg_match( '/blockId:\s+"(.+)"/', $script, $m ) ) {
				$block_id = $m[1];
			}

			if ( preg_match( '/renderTo:\s+"(.+)"/', $script, $m ) ) {
				$render_to = $m[1];
			}

			if ( $block_id && $render_to ) {
				$this->rtb_blocks[] = [
					'blockId'  => $block_id,
					'renderTo' => $render_to,
				];
			}

			return '';
		}

		return $script;
	}

	/**
	 * Print RTB script using info extracted from the content.
	 */
	public function print_rtb_script() {
		if ( ! $this->rtb_blocks ) {
			return;
		}

		ob_start();

		?>
		( function( w, d, n, s, t ) {
		w[n] = w[n] || [];
		w[n].push( function() {
		<?php
		foreach ( $this->rtb_blocks as $rtb_block ) {
			?>
			Ya.Context.AdvManager.render( {
			blockId: '<?php echo esc_html( $rtb_block['blockId'] ); ?>',
			renderTo: '<?php echo esc_html( $rtb_block['renderTo'] ); ?>',
			async: true
			} );
			<?php
		}
		?>
		} );
		t       = d.getElementsByTagName( 'script' )[0];
		s       = d.createElement( 'script' );
		s.type  = 'text/javascript';
		s.src   = '//an.yandex.ru/system/context.js';
		s.async = true;
		t.parentNode.insertBefore( s, t );
		} )( this, this.document, 'yandexContextAsyncCallbacks' )
		<?php

		$js = ob_get_clean();

		$js     = $this->main->replace_urls( $js );
		$script = DelayedScript::create( $js );

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo "\n" . $script . "\n";
	}
}
