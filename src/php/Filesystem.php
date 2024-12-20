<?php
/**
 * Filesystem class file.
 *
 * @package kagg_pagespeed_optimization
 */

namespace KAGG\PageSpeed\Optimization;

use RuntimeException;
use WP_Filesystem_Direct;

/**
 * Class Filesystem
 */
class Filesystem {

	/**
	 * Filesystem.
	 *
	 * @var WP_Filesystem_Direct
	 */
	private $wp_filesystem;

	/**
	 * PageSpeed_Filesystem constructor.
	 */
	public function __construct() {
		$this->init();
	}

	/**
	 * Init filesystem.
	 *
	 * @throws RuntimeException RuntimeException.
	 */
	private function init(): void {
		global $wp_filesystem;

		add_filter( 'filesystem_method', [ $this, 'set_direct_fs_method' ], PHP_INT_MAX );

		if ( ! function_exists( 'WP_Filesystem' ) ) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
		}

		if ( ! WP_Filesystem() ) {
			throw new RuntimeException( esc_html__( 'Unable to get filesystem access', 'kagg-pagespeed-optimization' ) );
		}

		$this->wp_filesystem = $wp_filesystem;
	}

	/**
	 * Set direct FS method.
	 *
	 * @return string
	 */
	public function set_direct_fs_method(): string {
		return 'direct';
	}

	/**
	 * Read file.
	 *
	 * @param string $filename Name of the file to read.
	 *
	 * @return string|false
	 */
	public function read( string $filename ) {
		if ( ! $this->wp_filesystem ) {
			return false;
		}

		return $this->wp_filesystem->get_contents( $filename );
	}

	/**
	 * Write file.
	 *
	 * @param string $filename Name of the file to write.
	 * @param string $content  File content.
	 *
	 * @return bool
	 */
	public function write( string $filename, string $content ): bool {
		if ( ! $this->wp_filesystem ) {
			return false;
		}

		return $this->wp_filesystem->put_contents( $filename, $content, FS_CHMOD_FILE );
	}
}
