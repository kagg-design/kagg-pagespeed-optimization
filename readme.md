# WordPress Plugin PageSpeed Optimization

PageSpeed Optimization plugin optimizes external scripts by storing them locally: Google analytics, Google map, Yandex metrika. 

After activation of the plugin, scripts for above mentioned services will be cached on local server. They are updated twice daily.

This is the fork of CAOS (Complete Analytics Optimization Suite) by Daan van den Bergh https://ru.wordpress.org/plugins/host-analyticsjs-local/. 

Whenever you run an analysis of your website on Google Pagespeed Insights, Pingdom or GTMetrix, it’ll tell you to leverage browser cache when you’re using Google Analytics. Because Google has set the cache expiry time to 2 hours. This plugin will get you a higher score on Pagespeed and Pingdom and make your website load faster, because the user’s browser doesn’t have to make a roundtrip to download the file from Google’s external server.  

## Contents

The WordPress Plugin PageSpeed Optimization includes the following files:

* `.gitignore`. Used to exclude certain files from the repository.
* `CHANGELOG.md`. The list of changes to the core project.
* `README.md`. The file that you’re currently reading.
* A `plugin-name` directory that contains the source code - a fully executable WordPress plugin.

## Features

* The PageSpeed Optimization is based on the [Plugin API](http://codex.wordpress.org/Plugin_API), [Coding Standards](http://codex.wordpress.org/WordPress_Coding_Standards), and [Documentation Standards](https://make.wordpress.org/core/handbook/best-practices/inline-documentation-standards/php/).
* All classes, functions, and variables are documented so that you know what you need to be changed.
* The PageSpeed Optimization uses a strict file organization scheme that correspond both to the WordPress Plugin Repository structure, and that make it easy to organize the files that compose the plugin.
* The project includes a `.pot` file as a starting point for internationalization.

## Installation

The PageSpeed Optimization can be installed directly into your plugins folder "as-is".

Plugin requires php proc_open function to be enabled on server.

## WordPress.org Preparation

The original launch of this version of the PageSpeed Optimization included the folder structure needed for using your plugin on the WordPress.org. That folder structure has been moved to its own repo here: https://github.com/kagg/

## Recommended Tools

### i18n Tools

The WordPress Plugin PageSpeed Optimization uses a variable to store the text domain used when internationalizing strings throughout the PageSpeed Optimization. To take advantage of this method, there are tools that are recommended for providing correct, translatable files:

* [Poedit](https://poedit.net/)
* [makepot](http://i18n.svn.wordpress.org/tools/trunk/)
* [i18n](https://github.com/grappler/i18n)

Any of the above tools should provide you with the proper tooling to internationalize the plugin.

## License

The WordPress Plugin PageSpeed Optimization is licensed under the GPL v2 or later.

> This program is free software; you can redistribute it and/or modify it under the terms of the GNU General Public License, version 2, as published by the Free Software Foundation.

> This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.

> You should have received a copy of the GNU General Public License along with this program; if not, write to the Free Software Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA

A copy of the license is included in the root of the plugin’s directory. The file is named `LICENSE`.

## Important Notes

### Licensing

The WordPress Plugin PageSpeed Optimization is licensed under the GPL v2 or later; however, if you opt to use third-party code that is not compatible with v2, then you may need to switch to using code that is GPL v3 compatible.

For reference, [here's a discussion](https://make.wordpress.org/themes/2013/03/04/licensing-note-apache-and-gpl/) that covers the Apache 2.0 License used by [Bootstrap](http://getbootstrap.com/2.3.2/).

### Includes

# Credits

The current version of the PageSpeed Optimization was developed by [KAGG Design](https://kagg.eu/en/).

## Documentation, FAQs, and More

If you’re interested in writing any documentation or creating tutorials please [let me know](https://kagg.eu/en/).
