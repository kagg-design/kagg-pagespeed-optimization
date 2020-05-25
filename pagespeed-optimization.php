<?php
/**
 * Plugin Name: PageSpeed Optimization
 * Plugin URI:
 * Description: Optimize external scripts by storing them locally
 * Author: KAGG Design
 * Version: 1.1.0
 * Author URI: https://kagg.eu/en/
 * Requires at least: 4.4
 * Tested up to: 5.4
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

if ( defined( 'PAGESPEED_OPTIMIZATION_VERSION' ) ) {
	return;
}

/**
 * Plugin version
 */
define( 'PAGESPEED_OPTIMIZATION_VERSION', '1.1.0' );

/**
 * Plugin main file.
 */
define( 'PAGESPEED_OPTIMIZATION_PLUGIN_FILE', __FILE__ );

/**
 * Init PageSpeed Optimization classes on plugin load.
 */
require_once dirname( __FILE__ ) . '/classes/class-pagespeed-optimization.php';
require_once dirname( __FILE__ ) . '/classes/class-pagespeed-filesystem.php';
require_once dirname( __FILE__ ) . '/classes/class-pagespeed-resources.php';
require_once dirname( __FILE__ ) . '/classes/class-pagespeed-loader.php';

new PageSpeed_Optimization();
new PageSpeed_Resources();
new PageSpeed_Loader();
