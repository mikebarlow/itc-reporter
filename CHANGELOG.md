# Change log

All notable changes to this project will be documented in this file.
This project adheres to [Semantic Versioning](http://semver.org/).

## [2.0.0] - 2017-06-29

* [Update] Amended login for the ITC Reporter to use access tokens rather than email / password

## [1.1.0] - 2016-12-08

* [Feature] Added ability to retrieve the last result object after a request using `$Reporter->getLastResult();`

## [1.0.1] - 2016-12-08

* [Bugfix] Increasing version number and setting user agent of request to Java version number to requests work

## [1.0.0] - 2016-10-25

* First release with full support of all current endpoints (as of 25th Oct 2016)
* Sales
    * get accounts
    * get vendors
    * get reports
* Financials
    * get accounts
    * get vendors & regions
    * get reports
* Full code coverage and Unit Tests