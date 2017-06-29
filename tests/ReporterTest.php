<?php
namespace Snscripts\ITCReporter\Tests;

use Snscripts\ITCReporter\Reporter;
use Snscripts\Result\Result;
use GuzzleHttp\Client;

class ReporterTest extends \PHPUnit_Framework_TestCase
{
    public function testCanCreateInstance()
    {
        $this->assertInstanceOf(
            'Snscripts\ITCReporter\Reporter',
            new Reporter(
                new Client
            )
        );
    }

    public function testCanSetAndGetAccessToken()
    {
        $Reporter = new Reporter(
            new Client
        );

        $this->assertInstanceOf(
            'Snscripts\ITCReporter\Reporter',
            $Reporter->setAccessToken('12345678-1234-abcd-abcd-12345678abcd')
        );

        $this->assertSame(
            '12345678-1234-abcd-abcd-12345678abcd',
            $Reporter->getAccessToken()
        );
    }

    public function testSetAccessTokenThrowsExceptionWithInvalidData()
    {
        $this->setExpectedException('InvalidArgumentException');

        $Reporter = new Reporter(
            new Client
        );

        $Reporter->setAccessToken(123);
        $Reporter->setAccessToken([]);
        $Reporter->setAccessToken(new \StdClass);
        $Reporter->setAccessToken('');
    }

    public function testCanSetAndGetAccount()
    {
        $Reporter = new Reporter(
            new Client
        );

        $this->assertInstanceOf(
            'Snscripts\ITCReporter\Reporter',
            $Reporter->setAccountNum(1234567)
        );

        $this->assertSame(
            1234567,
            $Reporter->getAccountNum()
        );
    }

    public function testSetAccountNumThrowsExceptionWithInvalidData()
    {
        $this->setExpectedException('InvalidArgumentException');

        $Reporter = new Reporter(
            new Client
        );

        $Reporter->setAccountNum([]);
        $Reporter->setAccountNum(new \StdClass);
        $Reporter->setAccountNum('1234567');
    }

    public function testBuildJsonRequestBuildsCorrectForSalesGetAccounts()
    {
        $Reporter = new Reporter(
            new Client
        );

        $Reporter->setAccessToken('12345678-1234-abcd-abcd-12345678abcd');

        $this->assertSame(
            '{"accesstoken":"12345678-1234-abcd-abcd-12345678abcd","version":"2.1","mode":"Robot.XML","account":"None","queryInput":"[p=Reporter.properties, Sales.getAccounts]"}',
            $Reporter->buildJsonRequest('Sales.getAccounts')
        );
    }

    public function testBuildJsonRequestBuildsCorrectForSalesGetVendors()
    {
        $Reporter = new Reporter(
            new Client
        );

        $Reporter->setAccessToken('12345678-1234-abcd-abcd-12345678abcd')
            ->setAccountNum(1234567);

        $this->assertSame(
            '{"accesstoken":"12345678-1234-abcd-abcd-12345678abcd","version":"2.1","mode":"Robot.XML","account":"1234567","queryInput":"[p=Reporter.properties, Sales.getVendors]"}',
            $Reporter->buildJsonRequest('Sales.getVendors')
        );
    }

    public function testBuildJsonRequestBuildsCorrectForSalesGetReporter()
    {
        $Reporter = new Reporter(
            new Client
        );

        $Reporter->setAccessToken('12345678-1234-abcd-abcd-12345678abcd');

        $this->assertSame(
            '{"accesstoken":"12345678-1234-abcd-abcd-12345678abcd","version":"2.1","mode":"Robot.XML","account":"None","queryInput":"[p=Reporter.properties, Sales.getReport, 12345678,Sales,Summary,Daily,20161020]"}',
            $Reporter->buildJsonRequest('Sales.getReport', '12345678', 'Sales', 'Summary', 'Daily', '20161020')
        );
    }

    public function testBuildJsonRequestThrowsExceptionWhenNoDataPassed()
    {
        $this->setExpectedException('BadFunctionCallException');

        $Reporter = new Reporter(
            new Client
        );

        $Reporter->setAccessToken('12345678-1234-abcd-abcd-12345678abcd')
            ->setAccountNum(1234567)
            ->buildJsonRequest();
    }

    public function testProcessResponseReturnsCorrectArray()
    {
        $action = 'Sales.getVendors';

        $WorkingResponse = $this->getMock('Psr\Http\Message\ResponseInterface');
        $WorkingResponse->method('getBody')
            ->willReturn('<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Vendors><Vendor>1234567</Vendor><Vendor>9876543</Vendor></Vendors>');

        $Reporter = new Reporter(
            new Client
        );

        $this->assertSame(
            [
                1234567,
                9876543
            ],
            $Reporter->processResponse(
                $action,
                $WorkingResponse
            )
        );

        $BlankResponse = $this->getMock('Psr\Http\Message\ResponseInterface');
        $BlankResponse->method('getBody')
            ->willReturn('');

        $this->assertSame(
            [],
            $Reporter->processResponse(
                $action,
                $BlankResponse
            )
        );
    }

    public function testProcessResponseThrowsExceptionIfInvalidAction()
    {
        $this->setExpectedException('InvalidArgumentException');

        $BlankResponse = $this->getMock('Psr\Http\Message\ResponseInterface');
        $BlankResponse->method('getBody')
            ->willReturn('');

        $Reporter = new Reporter(
            new Client
        );

        $Reporter->processResponse('foobar', $BlankResponse);
    }

    public function testPerformRequestCanCheckStatusAndReturnCorrectly()
    {
        $Response = $this->getMock('Psr\Http\Message\ResponseInterface');
        $Response->method('getBody')
            ->willReturn('<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Vendors><Vendor>1234567</Vendor><Vendor>9876543</Vendor></Vendors>');
        $Response->method('getStatusCode')
            ->willReturn(200);

        $GuzzleMock = $this->getMock('GuzzleHttp\ClientInterface');
        $GuzzleMock->method('request')
            ->willReturn(
                $Response
            );

        $Reporter = new Reporter(
            $GuzzleMock
        );
        $Reporter->setAccessToken('12345678-1234-abcd-abcd-12345678abcd');

        $Result = $Reporter->performRequest(
            Reporter::SALESURL,
            $Reporter->buildJsonRequest('Sales.getVendors')
        );

        $this->assertInstanceOf(
            'Snscripts\Result\Result',
            $Result
        );

        $this->assertTrue(
            $Result->isSuccess()
        );

        $this->assertInstanceOf(
            'Psr\Http\Message\ResponseInterface',
            $Result->getExtra('Response')
        );

        $this->assertSame(
            '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Vendors><Vendor>1234567</Vendor><Vendor>9876543</Vendor></Vendors>',
            $Result->getExtra('Response')->getBody()
        );
    }

    public function testPerformRequestWhenResponseHasFailed()
    {
        $Response = $this->getMock('Psr\Http\Message\ResponseInterface');
        $Response->method('getBody')
            ->willReturn('');
        $Response->method('getStatusCode')
            ->willReturn(404);

        $GuzzleMock = $this->getMock('GuzzleHttp\ClientInterface');
        $GuzzleMock->method('request')
            ->willReturn(
                $Response
            );

        $Reporter = new Reporter(
            $GuzzleMock
        );
        $Reporter->setAccessToken('12345678-1234-abcd-abcd-12345678abcd');

        $Result = $Reporter->performRequest(
            Reporter::SALESURL,
            $Reporter->buildJsonRequest('Sales.getVendors')
        );

        $this->assertInstanceOf(
            'Snscripts\Result\Result',
            $Result
        );

        $this->assertTrue(
            $Result->isFail()
        );

        $this->assertSame(
            'The request did not return a 200 OK response',
            $Result->getMessage()
        );
    }

    public function testGetSalesAccountsReturnsCorrectArray()
    {
        $Response = $this->getMock('Psr\Http\Message\ResponseInterface');
        $Response->method('getBody')
            ->willReturn('<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Accounts><Account><Name>John Smith</Name><Number>1234567</Number></Account></Accounts>');
        $Response->method('getStatusCode')
            ->willReturn(200);

        $GuzzleMock = $this->getMock('GuzzleHttp\ClientInterface');
        $GuzzleMock->method('request')
            ->willReturn(
                $Response
            );

        $Reporter = new Reporter(
            $GuzzleMock
        );

        $this->assertSame(
            [
                1234567 => [
                    'Name' => 'John Smith',
                    'Number' => 1234567
                ]
            ],
            $Reporter->getSalesAccounts()
        );
    }

    public function testGetSalesAccountsReturnsBlankArrayOnFail()
    {
        $Response = $this->getMock('Psr\Http\Message\ResponseInterface');
        $Response->method('getBody')
            ->willReturn('');
        $Response->method('getStatusCode')
            ->willReturn(404);

        $GuzzleMock = $this->getMock('GuzzleHttp\ClientInterface');
        $GuzzleMock->method('request')
            ->willReturn(
                $Response
            );

        $Reporter = new Reporter(
            $GuzzleMock
        );

        $this->assertSame(
            [],
            $Reporter->getSalesAccounts()
        );
    }

    public function testGetSalesVendorsReturnsCorrectArray()
    {
        $Response = $this->getMock('Psr\Http\Message\ResponseInterface');
        $Response->method('getBody')
            ->willReturn('<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Vendors><Vendor>1234567</Vendor><Vendor>9876543</Vendor></Vendors>');
        $Response->method('getStatusCode')
            ->willReturn(200);

        $GuzzleMock = $this->getMock('GuzzleHttp\ClientInterface');
        $GuzzleMock->method('request')
            ->willReturn(
                $Response
            );

        $Reporter = new Reporter(
            $GuzzleMock
        );

        $this->assertSame(
            [
                1234567,
                9876543
            ],
            $Reporter->getSalesVendors()
        );
    }

    public function testGetSalesVendorsReturnsBlankArrayOnFail()
    {
        $Response = $this->getMock('Psr\Http\Message\ResponseInterface');
        $Response->method('getBody')
            ->willReturn('');
        $Response->method('getStatusCode')
            ->willReturn(404);

        $GuzzleMock = $this->getMock('GuzzleHttp\ClientInterface');
        $GuzzleMock->method('request')
            ->willReturn(
                $Response
            );

        $Reporter = new Reporter(
            $GuzzleMock
        );

        $this->assertSame(
            [],
            $Reporter->getSalesVendors()
        );
    }

    public function testGetFinanceAccountsReturnsCorrectArray()
    {
        $Response = $this->getMock('Psr\Http\Message\ResponseInterface');
        $Response->method('getBody')
            ->willReturn('<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Accounts><Account><Name>John Smith</Name><Number>1234567</Number></Account></Accounts>');
        $Response->method('getStatusCode')
            ->willReturn(200);

        $GuzzleMock = $this->getMock('GuzzleHttp\ClientInterface');
        $GuzzleMock->method('request')
            ->willReturn(
                $Response
            );

        $Reporter = new Reporter(
            $GuzzleMock
        );

        $this->assertSame(
            [
                1234567 => [
                    'Name' => 'John Smith',
                    'Number' => 1234567
                ]
            ],
            $Reporter->getFinanceAccounts()
        );
    }

    public function testGetFinanceAccountsReturnsBlankArrayOnFail()
    {
        $Response = $this->getMock('Psr\Http\Message\ResponseInterface');
        $Response->method('getBody')
            ->willReturn('');
        $Response->method('getStatusCode')
            ->willReturn(404);

        $GuzzleMock = $this->getMock('GuzzleHttp\ClientInterface');
        $GuzzleMock->method('request')
            ->willReturn(
                $Response
            );

        $Reporter = new Reporter(
            $GuzzleMock
        );

        $this->assertSame(
            [],
            $Reporter->getFinanceAccounts()
        );
    }

    public function testGetFinanceVendorsReturnsCorrectArray()
    {
        $Response = $this->getMock('Psr\Http\Message\ResponseInterface');
        $Response->method('getBody')
            ->willReturn('<?xml version="1.0" encoding="UTF-8" standalone="yes"?><VendorsAndRegions><Vendor><Number>1234567</Number><Region><Code>AE</Code><Reports><Report>Financial</Report></Reports></Region><Region><Code>AU</Code><Reports><Report>Financial</Report><Report>Sale</Report></Reports></Region></Vendor></VendorsAndRegions>');
        $Response->method('getStatusCode')
            ->willReturn(200);

        $GuzzleMock = $this->getMock('GuzzleHttp\ClientInterface');
        $GuzzleMock->method('request')
            ->willReturn(
                $Response
            );

        $Reporter = new Reporter(
            $GuzzleMock
        );

        $this->assertSame(
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
                                'Financial',
                                'Sale'
                            ]
                        ]
                    ]
                ]
            ],
            $Reporter->getFinanceVendors()
        );
    }

    public function testGetFinanceVendorsReturnsBlankArrayOnFail()
    {
        $Response = $this->getMock('Psr\Http\Message\ResponseInterface');
        $Response->method('getBody')
            ->willReturn('');
        $Response->method('getStatusCode')
            ->willReturn(404);

        $GuzzleMock = $this->getMock('GuzzleHttp\ClientInterface');
        $GuzzleMock->method('request')
            ->willReturn(
                $Response
            );

        $Reporter = new Reporter(
            $GuzzleMock
        );

        $this->assertSame(
            [],
            $Reporter->getFinanceVendors()
        );
    }

    public function testGetSalesReportReturnsCorrectReport()
    {
        $Response = $this->getMock('Psr\Http\Message\ResponseInterface');
        $Response->method('getBody')
            ->willReturn(
                new TestSalesReportContent
            );
        $Response->method('getStatusCode')
            ->willReturn(200);

        $GuzzleMock = $this->getMock('GuzzleHttp\ClientInterface');
        $GuzzleMock->method('request')
            ->willReturn(
                $Response
            );

        $Reporter = new Reporter(
            $GuzzleMock
        );

        $this->assertSame(
            [
                [
                    'Header 1' => 'Foo',
                    'Header 2' => 'Bar',
                    'Header 3' => 'Foobar'
                ],
                [
                    'Header 1' => 'Fizz',
                    'Header 2' => '',
                    'Header 3' => 'Fizzbuzz'
                ],
                [
                    'Header 1' => '',
                    'Header 2' => 'Test',
                    'Header 3' => 'Tester'
                ]
            ],
            $Reporter->getSalesReport(1234567, 'Sales', 'Summary', 'Daily', '20161025')
        );
    }

    public function testGetSalesReportReturnsBlankArrayWhenNoReport()
    {
        $Response = $this->getMock('Psr\Http\Message\ResponseInterface');
        $Response->method('getBody')
            ->willReturn(
                new TestSalesReportNoContent
            );
        $Response->method('getStatusCode')
            ->willReturn(404);

        $GuzzleMock = $this->getMock('GuzzleHttp\ClientInterface');
        $GuzzleMock->method('request')
            ->willReturn(
                $Response
            );

        $Reporter = new Reporter(
            $GuzzleMock
        );

        $this->assertSame(
            [],
            $Reporter->getSalesReport(1234567, 'Sales', 'Summary', 'Daily', '20161025')
        );
    }

    public function testGetFinanceReportReturnsCorrectReport()
    {
        $Response = $this->getMock('Psr\Http\Message\ResponseInterface');
        $Response->method('getBody')
            ->willReturn(
                new TestFinanceReportContent
            );
        $Response->method('getStatusCode')
            ->willReturn(200);

        $GuzzleMock = $this->getMock('GuzzleHttp\ClientInterface');
        $GuzzleMock->method('request')
            ->willReturn(
                $Response
            );

        $Reporter = new Reporter(
            $GuzzleMock
        );

        $this->assertSame(
            [
                [
                    'Header 1' => 'Foo',
                    'Header 2' => 'Bar',
                    'Header 3' => 'Foobar'
                ],
                [
                    'Header 1' => 'Fizz',
                    'Header 2' => '',
                    'Header 3' => 'Fizzbuzz'
                ],
                [
                    'Header 1' => '',
                    'Header 2' => 'Test',
                    'Header 3' => 'Tester'
                ],
                'Total' => '100',
                'Grand Total' => '500'
            ],
            $Reporter->getFinanceReport(1234567, 'GB', 'Financial', '2016', '1')
        );
    }

    public function testGetFinanceReportReturnsBlankArrayWhenNoReport()
    {
        $Response = $this->getMock('Psr\Http\Message\ResponseInterface');
        $Response->method('getBody')
            ->willReturn(
                new TestFinanceReportNoContent
            );
        $Response->method('getStatusCode')
            ->willReturn(404);

        $GuzzleMock = $this->getMock('GuzzleHttp\ClientInterface');
        $GuzzleMock->method('request')
            ->willReturn(
                $Response
            );

        $Reporter = new Reporter(
            $GuzzleMock
        );

        $this->assertSame(
            [],
            $Reporter->getFinanceReport(1234567, 'GB', 'Financial', '2016', '1')
        );
    }

    public function testGetLastResultReturnsCorrectValue()
    {
        $Response = $this->getMock('Psr\Http\Message\ResponseInterface');
        $Response->method('getBody')
            ->willReturn('<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Vendors><Vendor>1234567</Vendor><Vendor>9876543</Vendor></Vendors>');
        $Response->method('getStatusCode')
            ->willReturn(200);

        $GuzzleMock = $this->getMock('GuzzleHttp\ClientInterface');
        $GuzzleMock->method('request')
            ->willReturn(
                $Response
            );

        $Reporter = new Reporter(
            $GuzzleMock
        );
        $Reporter->setAccessToken('12345678-1234-abcd-abcd-12345678abcd');

        $this->assertNull(
            $Reporter->getLastResult()
        );

        $accounts = $Reporter->getSalesAccounts();

        $this->assertInstanceOf(
            'Snscripts\Result\Result',
            $Reporter->getLastResult()
        );
    }
}

class TestSalesReportContent
{
    public function getContents()
    {
        $report = "Header 1\tHeader 2\tHeader 3\n\nFoo\tBar\tFoobar\nFizz\t\tFizzbuzz\n\tTest\tTester";
        return gzencode($report);
    }
}

class TestSalesReportNoContent
{
    public function getContents()
    {
        return '';
    }
}

class TestFinanceReportContent
{
    public function getContents()
    {
        $report = "Header 1\tHeader 2\tHeader 3\nFoo\tBar\tFoobar\n\nFizz\t\tFizzbuzz\n\tTest\tTester\nTotal\t100\nGrand Total\t500";
        return gzencode($report);
    }
}

class TestFinanceReportNoContent
{
    public function getContents()
    {
        return '';
    }
}