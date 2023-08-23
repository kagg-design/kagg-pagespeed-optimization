=== KAGG PageSpeed Optimization ===
Contributors: kaggdesign
Donate link: https://kagg.eu/en/
Tags: PageSpeed, Google Pagespeed Insights, PageSpeed Optimization
Requires at least: 4.4
Tested up to: 6.3
Requires PHP: 7.0
Stable tag: 1.5.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

PageSpeed Optimization plugin performs optimization of external scripts and styles by moving them to the footer, blocking, or delaying. Links and fonts can be preloaded to minimize loading time. Analytics scripts can be stored locally to leverage browser caching.

The above-mentioned measures provide a significant increase in Google Pagespeed Insights score.

== Description ==

PageSpeed Optimization plugin performs optimization of external scripts and styles by moving them to the footer, blocking, or delaying. Loading of delayed scripts or styles begins after the first user interaction - scroll, clock, or mouse enter. Links and fonts can be preloaded to minimize loading time.

During moving, blocking, or delaying, the plugin performs exact calculations of all scripts and styles dependencies across the whole dependency tree. So, any script or style whose position has to be modified will be moved with all dependencies in the proper order. This functionality is unique and does not exist in most top-level caching plugins.

Analytics scripts can be stored locally: Google AdSense, Google Analytics, Google Maps, Google Tag Manager, and Yandex Metrika. Scripts for the above-mentioned services will be cached on the local server and updated twice daily.

Google Pagespeed Insights (PSI) requires leveraging browser cache when using analytics scripts because they only have a 2-hour cache expiry time. When scripts are stored locally, their cache expiry time is increased, raising the PSI score.

PageSpeed Optimization plugin will get you a higher score on Google Pagespeed Insights and make your website load faster. The most impact can usually be achieved by delaying scripts.

== Installation ==

1. Install Pagespeed Optimization either via the WordPress.org plugin repository (best) or by uploading the files to your server. ([Upload instructions](https://www.wpbeginner.com/beginners-guide/step-by-step-guide-to-install-a-wordpress-plugin-for-beginners/))
2. Activate the Pagespeed Optimization plugin on the 'Plugins' admin page
3. Use the plugin settings page to move, delay, or block scripts and styles

== Frequently Asked Questions ==

= Where can I get support or talk to other users? =

If you get stuck, you can ask for help in the [PageSpeed Optimization Plugin Forum](https://wordpress.org/support/plugin/kagg-pagespeed-optimization).

== Screenshots ==

1. The PageSpeed Optimization settings page.

== Changelog ==

= 2.0.0 =
* Tested with WordPress 6.3.

= 1.5.0 =
* Optimize reordering of scripts and styles via tree traversal.
* Add Clutch widget support.

= 1.4.0 =
* Added "Include all dependencies to delayed scripts" feature.
* Added script delay until user interaction.
* Added minification of delayed scripts.
* Added Zopim support.
* Added FB share and like button.
* Added StatCounter support.
* Fixed delayed scripts loading in Firefox.
* Added sorting of delayed scripts in the proper order of dependencies.
* Updated Yandex Metrika code.
* Make delayed scripts passing W3C validation.

= 1.3.0 =
* Tested with WordPress 5.8
* Make all optimization for not logged-in users by default
* Added "Optimize when logged-in" option
* Fix minor issues with cache and performance

= 1.2 =
* Tested with WordPress 5.6
* Cache an.yandex.ru/system/context.js
* Cache Google AdSense and Google Tag Manager
* Process Yandex advertising network blocks
* Add loader
* Move site icon upper any inline style (fixes bug in Chrome)
* Fix passive event listener problem in Google PageSpeed Insights
* Run Google Tag Manager, Google AdSense, Yandex Metrika, One Signal as delayed scripts
* No optimization in admin
* Namespace and composer

= 1.1.0 =
* Create cache dir during local files updating
* Disable emoji
* Defer scripts
* Add display:swap for Google fonts

= 1.0.2 =
* Tested with WordPress 5.4

= 1.0.0 =
* Initial release.
