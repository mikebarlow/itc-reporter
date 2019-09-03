# iTunes Connect Reporter (ITC Reporter)

[![Author](http://img.shields.io/badge/author-@mikebarlow-red.svg?style=flat-square)](https://twitter.com/mikebarlow)
[![Source Code](http://img.shields.io/badge/source-mikebarlow/itc--reporter-brightgreen.svg?style=flat-square)](https://github.com/mikebarlow/itc-reporter)
[![Latest Version](https://img.shields.io/github/release/mikebarlow/itc-reporter.svg?style=flat-square)](https://github.com/mikebarlow/itc-reporter/releases)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](https://github.com/mikebarlow/itc-reporter/blob/master/LICENSE)
[![Build Status](https://img.shields.io/travis/mikebarlow/itc-reporter/master.svg?style=flat-square)](https://travis-ci.org/mikebarlow/itc-reporter)

## Introduction

ITC Reporter is a PSR-2 compliant package used for getting data from iTunes Connect. It acts as a PHP port of the Java based reporter.jar that apple provide [here](https://help.apple.com/itc/appsreporterguide/#/itc0f2481229).

The current Autoingestion Tool will cease to work from the end of November 2016 and they recommended everyone switch to use the new Reporter tool in this [post](https://itunespartner.apple.com/en/apps/news/47110289).

This is the first PHP Composer based port of the Reporter tool and attempts to make using the tool and reports easier by processing the XML that is returned by the API and returning it as an Array to make usage easier.

## Requirements

### Composer

ITC-Reporter requires the following:

* "php": ">=5.5.0"
* "guzzlehttp/guzzle": "6.*"
* "snscripts/result": "1.0.*"

And the following if you wish to run in dev mode and run tests.

* "phpunit/phpunit": "~4.0"
* "squizlabs/php_codesniffer": "~2.0"

## Installation

### Composer

Simplest installation is via composer.

	composer require snscripts/itc-reporter 2.*

or adding to your projects `composer.json` file.

	{
	    "require": {
	        "snscripts/itc-reporter": "2.*"
	    }
	}

### Setup

Instantiate the class as follows.

	$Reporter = new \Snscripts\ITCReporter\Reporter(
		new \GuzzleHttp\Client
	);

## Usage

### Basics

Aside from the main methods that return the data, all methods support chaining.

To use the Reporter, you will need the an access token for your iTunes Connect account. This is obtained from the [iTunes Connect Sales and Trends screen](https://reportingitc2.apple.com/reports.html), as described in the [Reporter documentation](https://help.apple.com/itc/appsreporterguide/#/apd2f1f1cfa3). Set the access token as follows.

	$Reporter->setAccessToken('12345678-1234-abcd-abcd-12345678abcd');

There are also "getter" methods to retrieve the currently set data.

	$Reporter->getAccessToken();

Before progressing any further with this package, it will be worth while reviewing Apples Documentation at [https://help.apple.com/itc/appsreporterguide/](https://help.apple.com/itc/appsreporterguide/).

### Account Numbers

Most actions require an account number also setting. You can retrieve the accounts your user has available for both "Sales & Trends" and "Payments & Financial Reports" by using the following functions.

	$Reporter->getSalesAccounts();
	$Reporter->getFinanceAccounts();

Both of these return data in the same array format. If you have no access to any accounts a blank array will be returned. If you do have access they will return an array in the following format.

	[
	    1234567 => [
	        'Name' => 'John Smith',
	        'Number' => 1234567
	    ],
	    9876543 => [
	        'Name' => 'Jane Doe',
	        'Number' => 9876543
	    ]
	]

The 'Number' element is the account number that is needed for the rest of the endpoints.

You can set the account number you wish to use by calling;

	$Reporter->setAccountNum(1234567);

You can then also retrieve the currently set account number with:

	$Reporter->getAccountNum();

### Vendor Number

One last ID needed before a report can be retrieved is the Vendor number. Before you can get the list of vendors an Account Number does need to be set.

Once an Account Number is set you can get the Vendors for both "Sales & Trends" and "Payments & Financial Reports", the data returned for Sales & Trends Vendors differs from Payments & Financial Vendors.

In both instances, if no vendors exists, a blank array will be returned.

#### Sales Vendors

Use the following function to get the Vendors for the Sales & Trends

	$Reporter->getSalesVendors();

This will return a simple array of Vendor numbers.

	[
        1234567,
        9876543
    ]

#### Finance Vendors

Use the following function get the Vendors for the Payments & Financial Reports.

	$Reporter->getFinanceVendors();

This returns a slightly more complicated array, detailing the Vendor, the Region Codes available and the Reports available for each Region Code.

    [
        1234567 => [
            'Number' => 1234567,
            'Regions' => [
                'AE' => [
                    'Code' => 'AE',
                    'Reports' => [
                        'Financial'
                    ]
                ],
                'AU' => [
                    'Code' => 'AU',
                    'Reports' => [
                        'Financial'
                    ]
                ]
            ]
        ],
        9876543 => [
            'Number' => 9876543,
            'Regions' => [
                'EU' => [
                    'Code' => 'EU',
                    'Reports' => [
                        'Financial'
                    ]
                ]
            ]
        ]
    ]

### Reports

Both Sales Reports and the Financial Reports require specific data regarding the type of report you want. Consult the documentation for each section so you know what parameters are available.

#### Sales Report

For Sales reports, consult the documentation [here](https://help.apple.com/itc/appsreporterguide/#/itcbd9ed14ac) to view descriptions of the parameters.

To get reports call the method in the following way:

	$Reporter->getSalesReport(
		$vendorNum,
		$reportType,
		$reportSubType,
		$dateType,
		$date
	);

An actual call may look something like this:

	$Reporter->getSalesReport(
		1234567,
		'Sales',
		'Summary',
		'Daily',
		'20161025' //YYYYMMDD
	);

If nothing was found or a problem occurred, a blank array will be returned. If the report does have data an array similar to below will be returned.

	[
        [
            [Provider] => APPLE
            [Provider Country] => US
            [SKU] => 12345678901
            [Developer] => John Smith
            [Title] => My App
            [Version] => 2.0.1
            [Product Type Identifier] => 1F
            [Units] => 1
            [Developer Proceeds] => 0
            [Begin Date] => 10/23/2016
            [End Date] => 10/23/2016
            [Customer Currency] => AUD
            [Country Code] => AU
            [Currency of Proceeds] => AUD
            [Apple Identifier] => 123456789
            [Customer Price] => 0
            [Promo Code] =>
            [Parent Identifier] =>
            [Subscription] =>
            [Period] =>
            [Category] => Travel
            [CMB] =>
            [Device] => iPad
            [Supported Platforms] => iOS
            [Proceeds Reason] =>
            [Preserved Pricing] =>
            [Client] =>
        ],
        [
            [Provider] => APPLE
            [Provider Country] => US
            [SKU] => 12345678901
            [Developer] => John Smith
            [Title] => My App
            [Version] => 2.0.1
            [Product Type Identifier] => 3F
            [Units] => 1
            [Developer Proceeds] => 0
            [Begin Date] => 10/23/2016
            [End Date] => 10/23/2016
            [Customer Currency] => USD
            [Country Code] => BR
            [Currency of Proceeds] => USD
            [Apple Identifier] => 123456789
            [Customer Price] => 0
            [Promo Code] =>
            [Parent Identifier] =>
            [Subscription] =>
            [Period] =>
            [Category] => Travel
            [CMB] =>
            [Device] => iPhone
            [Supported Platforms] => iOS
            [Proceeds Reason] =>
            [Preserved Pricing] =>
            [Client] =>
        ]
	]

#### Financials- Report

For Financial reports, consult the documentation [here](https://help.apple.com/itc/appsreporterguide/#/itc21263284f) to view descriptions of the parameters.

To get reports call the method in the following way:

	$Reporter->getFinanceReport(
		$vendorNum,
		$regionCode,
		$reportType,
		$fiscalYear,
		$fiscalPeriod
	);

An actual call may look something like this:

	$Reporter->getSalesReport(
		1234567,
		'GB',
		'Financial',
		'2016',
		'1' //YYYYMMDD
	);

If nothing was found or a problem occurred, a blank array will be returned. If the report does have data an array similar to below will be returned.

	[
	    [
	        [Start Date] => 09/27/2015
	        [End Date] => 10/31/2015
	        [UPC] =>
	        [ISRC/ISBN] =>
	        [Vendor Identifier] => 123456789012
	        [Quantity] => 22
	        [Partner Share] => 0.46
	        [Extended Partner Share] => 10.12
	        [Partner Share Currency] => GBP
	        [Sales or Return] => S
	        [Apple Identifier] => 123456789
	        [Artist/Show/Developer/Author] => John Smith
	        [Title] => My App
	        [Label/Studio/Network/Developer/Publisher] =>
	        [Grid] =>
	        [Product Type Identifier] => 1
	        [ISAN/Other Identifier] =>
	        [Country Of Sale] => GB
	        [Pre-order Flag] =>
	        [Promo Code] =>
	        [Customer Price] => 0.79
	        [Customer Currency] => GBP
	    ],
	    [
	        [Start Date] => 09/27/2015
	        [End Date] => 10/31/2015
	        [UPC] =>
	        [ISRC/ISBN] =>
	        [Vendor Identifier] => 123789456
	        [Quantity] => 1
	        [Partner Share] => 0.46
	        [Extended Partner Share] => 0.46
	        [Partner Share Currency] => GBP
	        [Sales or Return] => S
	        [Apple Identifier] => 987654321
	        [Artist/Show/Developer/Author] => John Smith
	        [Title] => My App 2
	        [Label/Studio/Network/Developer/Publisher] =>
	        [Grid] =>
	        [Product Type Identifier] => 1F
	        [ISAN/Other Identifier] =>
	        [Country Of Sale] => GB
	        [Pre-order Flag] =>
	        [Promo Code] =>
	        [Customer Price] => 0.79
	        [Customer Currency] => GBP
	    ],
	    [Total_Rows] => 6
	    [Total_Amount] => 28.32
	    [Total_Units] => 58
	]

## Debugging

If you've run a method to get a report or account / vendor numbers to find you are not getting the results expected, you can retrieve the last Result object returned, this method should contain any information relating to why a request may have failed.

    $Reporter = new \Snscripts\ITCReporter\Reporter(
        new \GuzzleHttp\Client
    );

    $Reporter->setAccessToken('12345678-1234-abcd-abcd-12345678abcd');

    $failedAccounts = $Reporter->getSalesAccount();

    $Result = $Reporter->getLastResult();

If no action has been run, the result will return a `null` value.

## Changelog

You can view the changelog [HERE](https://github.com/mikebarlow/itc-reporter/blob/master/CHANGELOG.md)

## Contributing

Please see [CONTRIBUTING](https://github.com/mikebarlow/itc-reporter/blob/master/CONTRIBUTING.md) for details.

## License

The MIT License (MIT). Please see [License File](https://github.com/mikebarlow/itc-reporter/blob/master/LICENSE) for more information.