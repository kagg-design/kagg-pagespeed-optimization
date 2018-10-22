<?php
/**
 * Plugin Name: PageSpeed Optimization
 * Plugin URI:
 * Description: Optimize external scripts by storing them locally
 * Author: KAGG Design
 * Version: 1.0.0
 * Author URI: https://kagg.eu/en/
 * Requires at least: 4.4
 * Tested up to: 5.0
 *
 * Text Domain: kagg-pagespeed-optimization
 * Domain Path: /languages/
 *
 * @package PageSpeed Optimization
 * @author KAGG Design
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// Define WC_PLUGIN_FILE.
if ( ! defined( 'PAGESPEED_OPTIMIZATION_PLUGIN_FILE' ) ) {
	define( 'PAGESPEED_OPTIMIZATION_PLUGIN_FILE', __FILE__ );
}

/**
 * Init PageSpeed Optimization class on plugin load.
 */

function init_pagespeed_optimization_class() {
	static $plugin;

	if ( ! isset( $plugin ) ) {
		// Require main class of the plugin.
		require_once dirname( __FILE__ ) . '/includes/class-pagespeed-optimization.php';

		$plugin = new PageSpeed_Optimization();
	}
}

init_pagespeed_optimization_class();

