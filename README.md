# WordPress Plugin PageSpeed Optimization

PageSpeed Optimization plugin optimizes external scripts by storing them locally: Google Analytics, Google map, Yandex metrika. 

After activation of the plugin, scripts for above-mentioned services will be cached on local server. They are updated twice daily.

Whenever you run an analysis of your website on Google Pagespeed Insights, Pingdom or GTMetrix, it’ll tell you to leverage browser cache when you’re using Google Analytics. Because Google has set the cache expiry time to 2 hours. This plugin will get you a higher score on Pagespeed and Pingdom and make your website load faster, because the user’s browser doesn’t have to make a roundtrip to download the file from Google’s external server.  

## Features

* The PageSpeed Optimization is based on the [Plugin API](http://codex.wordpress.org/Plugin_API), [Coding Standards](http://codex.wordpress.org/WordPress_Coding_Standards), and [Documentation Standards](https://make.wordpress.org/core/handbook/best-practices/inline-documentation-standards/php/).
* All classes, functions, and variables are documented so that you know what you need to be changed.
* The PageSpeed Optimization uses a strict file organization scheme that correspond both to the WordPress Plugin Repository structure, and that make it easy to organize the files that compose the plugin.
* The project includes a `.pot` file as a starting point for internationalization.

## Installation

```
git@github.com:kagg-design/kagg-pagespeed-optimization.git
cd kagg-pagespeed-optimization
composer install --no-dev
```

## Development

```
git@github.com:kagg-design/kagg-pagespeed-optimization.git
cd kagg-pagespeed-optimization
composer install
```

## License

The WordPress Plugin PageSpeed Optimization is licensed under the GPL v2 or later.

> This program is free software; you can redistribute it and/or modify it under the terms of the GNU General Public License, version 2, as published by the Free Software Foundation.

> This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.

> You should have received a copy of the GNU General Public License along with this program; if not, write to the Free Software Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA

A copy of the license is included in the root of the plugin’s directory. The file is named `LICENSE`.

### Includes

## Credits

The current version of the PageSpeed Optimization was developed by [KAGG Design](https://kagg.eu/en/).
