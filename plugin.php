<?php
/**
 * Plugin KAGG Pagespeed Optimization
 *
 * @package              kagg_pagespeed_optimization
 * @author               KAGG Design
 * @license              GPL-2.0-or-later
 * @wordpress-plugin
 *
 * Plugin Name:          KAGG PageSpeed Optimization
 * Plugin URI:           https://wordpress.org/plugins/kagg-pagespeed-optimization/
 * Description:          Optimize scripts and styles to get higher scores of Google Pagespeed Insights
 * Author:               KAGG Design
 * Version:              1.7.0
 * Author URI:           https://kagg.eu/en/
 * Requires at least:    5.0
 * Tested up to:         6.7
 *
 * Text Domain:          kagg-pagespeed-optimization
 * Domain Path:          /languages/
 */

use KAGG\PageSpeed\Optimization\Main;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Plugin version
 */
const KAGG_PAGESPEED_OPTIMIZATION_VERSION = '1.7.0';

/**
 * Path to the plugin dir.
 */
const KAGG_PAGESPEED_OPTIMIZATION_PATH = __DIR__;

/**
 * Plugin dir url.
 */
define( 'KAGG_PAGESPEED_OPTIMIZATION_URL', untrailingslashit( plugin_dir_url( __FILE__ ) ) );

/**
 * Plugin main file.
 */
const KAGG_PAGESPEED_OPTIMIZATION_FILE = __FILE__;

/**
 * Init plugin on plugin load.
 */
require_once constant( 'KAGG_PAGESPEED_OPTIMIZATION_PATH' ) . '/vendor/autoload.php';

/**
 * Get KAGG Pagespeed Optimization Main class instance.
 *
 * @return Main
 */
function kagg_pagespeed_optimization(): Main {
	static $kagg_pagespeed_optimization;

	if ( ! $kagg_pagespeed_optimization ) {
		$kagg_pagespeed_optimization = new Main();
	}

	return $kagg_pagespeed_optimization;
}

kagg_pagespeed_optimization()->init();
