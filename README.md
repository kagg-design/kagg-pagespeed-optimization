# PageSpeed Optimization

PageSpeed Optimization plugin performs optimization of external scripts and styles by moving them to the footer, blocking, or delaying. Loading of delayed scripts or styles begins after the first user interaction - scroll, clock, or mouse enter. Links and fonts can be preloaded to minimize loading time.

During moving, blocking, or delaying, the plugin performs exact calculations of all scripts and styles dependencies across the whole dependency tree. So, any script or style whose position has to be modified will be moved with all dependencies in the proper order. This functionality is unique and does not exist in most top-level caching plugins.

Some scripts can be stored locally: Google AdSense, Google Analytics, Google Maps, Google Tag Manager, and Yandex Metrika. Scripts for the above-mentioned services will be cached on the local server and updated twice daily.

Google Pagespeed Insights (PSI) requires leveraging browser cache when using analytics scripts because they only have a 2-hour cache expiry time. When scripts are stored locally, their cache expiry time is increased, raising the PSI score.

PageSpeed Optimization plugin will get you a higher score on Google Pagespeed Insights and make your website load faster. The most impact can usually be achieved by delaying scripts.

![](./.wordpress-org/banner-772x250.png)

## Features

- Any script or style can be moved from the header to the footer.
- Any script or style can be blocked entirely, preventing its loading.
- Any script or style can be delayed until the first user interaction: scroll, click, or mouse enter.
- Dependencies of moved, blocked, or delayed scripts and styles are correctly calculated across the dependency tree. Therefore, all dependent scripts and styles will be moved in a proper order. This unique feature does not exist in most top-level caching plugins.
- Analytics scripts can be stored locally to improve browser cache time.
- Links and fonts can be preloaded to improve overall page loading time.

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

## Credits

The current version of the PageSpeed Optimization was developed by [KAGG Design](https://kagg.eu/en/).
