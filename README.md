# iTunes Connect Reporter (ITC Reporter)

[![Author](http://img.shields.io/badge/author-@mikebarlow-red.svg?style=flat-square)](https://twitter.com/mikebarlow)
[![Source Code](http://img.shields.io/badge/source-mikebarlow/itc--reporter-brightgreen.svg?style=flat-square)](https://github.com/mikebarlow/itc-reporter)
[![Latest Version](https://img.shields.io/github/release/mikebarlow/itc-reporter.svg?style=flat-square)](https://github.com/mikebarlow/itc-reporter/releases)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](https://github.com/mikebarlow/itc-reporter/blob/master/LICENSE)
[![Build Status](https://img.shields.io/travis/mikebarlow/itc-reporter/master.svg?style=flat-square)](https://travis-ci.org/mikebarlow/itc-reporter)

## Introduction

ITC Reporter is a PSR-2 compliant package used for getting data from iTunes Connect. It acts as a PHP port of the Java based reporter.jar that apple provide [here](https://help.apple.com/itc/appsreporterguide/#/itc0f2481229).

The current Autoingestion Tool will cease to work from the end of November 2016 and they recommended everyone switch to use the new Reporter tool in this [post](https://itunespartner.apple.com/en/apps/news/47110289).

This is the first PHP Composer based port of the Reporter tool.

## Requirements

### Composer

ITC-Reporter requires the following:

* "php": ">=5.5.0"
* "guzzlehttp/guzzle": "6.2.*"
* "snscripts/result": "1.0.*"

And the following if you wish to run in dev mode and run tests.

* "phpunit/phpunit": "~4.0"
* "squizlabs/php_codesniffer": "~2.0"
