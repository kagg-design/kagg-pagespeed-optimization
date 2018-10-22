=== PageSpeed Optimization ===
Contributors: kaggdesign
Donate link: https://kagg.eu/en/
Tags: PageSpeed, PageSpeed Optimization
Requires at least: 4.4
Tested up to: 5.0
Stable tag: 1.0.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

PageSpeed Optimization plugin optimizes external scripts by storing them locally: Google analytics, Google map, Yandex metrika.

This is the fork of CAOS (Complete Analytics Optimization Suite) by Daan van den Bergh https://ru.wordpress.org/plugins/host-analyticsjs-local/.

== Description ==

After activation of the plugin, scripts for above mentioned services will be cached on local server. They are updated twice daily.

Whenever you run an analysis of your website on Google Pagespeed Insights, Pingdom or GTMetrix, it’ll tell you to leverage browser cache when you’re using Google Analytics. Because Google has set the cache expiry time to 2 hours. This plugin will get you a higher score on Pagespeed and Pingdom and make your website load faster, because the user’s browser doesn’t have to make a roundtrip to download the file from Google’s external server.

== Installation ==

= Minimum Requirements =

* PHP version 5.5 or greater (PHP 5.6 or greater is recommended)
* MySQL version 5.0 or greater (MySQL 5.6 or greater is recommended)

= Automatic installation =

Automatic installation is the easiest option as WordPress handles the file transfers itself and you don’t need to leave your web browser. To do an automatic install of PageSpeed Optimization plugin, log in to your WordPress dashboard, navigate to the Plugins menu and click Add New.

In the search field type “PageSpeed Optimization” and click Search Plugins. Once you’ve found our plugin you can view details about it such as the point release, rating and description. Most importantly of course, you can install it by simply clicking “Install Now”.

= Manual installation =

The manual installation method involves downloading our plugin and uploading it to your webserver via your favourite FTP application. The WordPress codex contains [instructions on how to do this here](https://codex.wordpress.org/Managing_Plugins#Manual_Plugin_Installation).

= Updating =

Automatic updates should work like a charm; as always though, ensure you backup your site just in case.

== Frequently Asked Questions ==

= Where can I get support or talk to other users? =

If you get stuck, you can ask for help in the [PageSpeed Optimization Plugin Forum](https://wordpress.org/support/plugin/tomita-parser).

== Screenshots ==

1. The PageSpeed Optimization settings page.

== Changelog ==

= 1.0.0 =
* Initial release.
