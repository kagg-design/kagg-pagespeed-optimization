<?php
/**
 * PageSpeed_Filesystem class file.
 *
 * @package kagg_pagespeed_optimization
 */

/**
 * Class PageSpeed_Filesystem
 */
class PageSpeed_Filesystem {

	/**
	 * Filesystem.
	 *
	 * @var WP_Filesystem_Direct
	 */
	private $wp_filesystem = null;

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
	private function init() {
		global $wp_filesystem;

		add_filter( 'filesystem_method', [ $this, 'set_direct_fs_method' ], PHP_INT_MAX );

		if ( ! function_exists( 'WP_Filesystem' ) ) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
		}

		if ( ! WP_Filesystem() ) {
			throw new RuntimeException( __( 'Unable to get filesystem access', 'kagg-pagespeed-optimization' ) );
		}

		$this->wp_filesystem = $wp_filesystem;
	}

	/**
	 * Set direct FS method.
	 *
	 * @return string
	 */
	public function set_direct_fs_method() {
		return 'direct';
	}

	/**
	 * Read file.
	 *
	 * @param string $filename Name of the file to read.
	 *
	 * @return bool|mixed
	 */
	public function read( $filename ) {
		if ( ! $this->wp_filesystem ) {
			return false;
		}

		return $this->wp_filesystem->get_contents( $filename );
	}

	/**
	 * Write file.
	 *
	 * @param string $filename Name of the file to write.
	 * @param string $content File content.
	 *
	 * @return bool
	 */
	public function write( $filename, $content ) {
		if ( ! $this->wp_filesystem ) {
			return false;
		}

		return $this->wp_filesystem->put_contents( $filename, $content, FS_CHMOD_FILE );
	}
}
