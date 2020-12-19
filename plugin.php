<?php
/**
 * Plugin Name: PageSpeed Optimization
 * Plugin URI:
 * Description: Optimize external scripts by storing them locally
 * Author: KAGG Design
 * Version: 1.2
 * Author URI: https://kagg.eu/en/
 * Requires at least: 4.4
 * Tested up to: 5.6
 *
 * Text Domain: kagg-pagespeed-optimization
 * Domain Path: /languages/
 *
 * @package kagg_pagespeed_optimization
 * @author  KAGG Design
 */

namespace KAGG\PageSpeed\Optimization;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( defined( 'KAGG_PAGESPEED_OPTIMIZATION_VERSION' ) ) {
	return;
}

/**
 * Plugin version
 */
define( 'KAGG_PAGESPEED_OPTIMIZATION_VERSION', '1.2' );

/**
 * Path to the plugin dir.
 */
define( 'KAGG_PAGESPEED_OPTIMIZATION_PATH', dirname( __FILE__ ) );

/**
 * Plugin dir url.
 */
define( 'KAGG_PAGESPEED_OPTIMIZATION_URL', untrailingslashit( plugin_dir_url( __FILE__ ) ) );

/**
 * Plugin main file.
 */
define( 'KAGG_PAGESPEED_OPTIMIZATION_FILE', __FILE__ );

/**
 * Init plugin on plugin load.
 */
require_once constant( 'KAGG_PAGESPEED_OPTIMIZATION_PATH' ) . '/vendor/autoload.php';

new Main();
