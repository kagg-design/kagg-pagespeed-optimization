# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/)
and this project adheres to [Semantic Versioning](http://semver.org/).

## [1.5.0]
* Optimize reordering of scripts and styles via tree traversal.
* Add Clutch widget support.

## [1.4.0]
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

## [1.3.0]
* Tested with WordPress 5.8
* Make all optimization for not logged-in users by default
* Added "Optimize when logged-in" option
* Fix minor issues with cache and performance

## [1.2]
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

## [1.1.0]
* Create cache dir during local files updating
* Disable emoji
* Defer scripts
* Add display:swap for Google fonts

## [1.0.2]
* Tested with WordPress 5.4

## [1.0.0]
* Initial Release.
