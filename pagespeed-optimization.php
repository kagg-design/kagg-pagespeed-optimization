<?php
/**
 * Plugin Name: PageSpeed Optimization
 * Plugin URI:
 * Description: Optimize external scripts by storing them locally
 * Author: KAGG Design
 * Version: 1.0.2
 * Author URI: https://kagg.eu/en/
 * Requires at least: 4.4
 * Tested up to: 5.4
 *
 * Text Domain: kagg-pagespeed-optimization
 * Domain Path: /languages/
 *
 * @package PageSpeed Optimization
 * @author  KAGG Design
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( defined( 'PAGESPEED_OPTIMIZATION_PLUGIN_FILE' ) ) {
	return;
}

/**
 * Plugin main file.
 */
define( 'PAGESPEED_OPTIMIZATION_PLUGIN_FILE', __FILE__ );

/**
 * Init PageSpeed Optimization classes on plugin load.
 */
require_once dirname( __FILE__ ) . '/includes/class-pagespeed-optimization.php';
require_once dirname( __FILE__ ) . '/includes/class-pagespeed-filesystem.php';
require_once dirname( __FILE__ ) . '/includes/class-pagespeed-resources-to-footer.php';

new PageSpeed_Optimization();
new PageSpeed_Resources_To_Footer();
