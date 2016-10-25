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

    public function testCanSetAndGetUserId()
    {
        $Reporter = new Reporter(
            new Client
        );

        $this->assertInstanceOf(
            'Snscripts\ITCReporter\Reporter',
            $Reporter->setUserId('me@example.com')
        );

        $this->assertSame(
            'me@example.com',
            $Reporter->getUserId()
        );
    }

    public function testSetUserIdThrowsExceptionWithInvalidData()
    {
        $this->setExpectedException('InvalidArgumentException');

        $Reporter = new Reporter(
            new Client
        );

        $Reporter->setUserId(123);
        $Reporter->setUserId([]);
        $Reporter->setUserId(new \StdClass);
        $Reporter->setUserId('');
    }

    public function testCanSetAndGetPassword()
    {
        $Reporter = new Reporter(
            new Client
        );

        $this->assertInstanceOf(
            'Snscripts\ITCReporter\Reporter',
            $Reporter->setPassword('?S3cureP455Word!')
        );

        $this->assertSame(
            '?S3cureP455Word!',
            $Reporter->getPassword()
        );
    }

    public function testSetPasswordThrowsExceptionWithInvalidData()
    {
        $this->setExpectedException('InvalidArgumentException');

        $Reporter = new Reporter(
            new Client
        );

        $Reporter->setPassword(123);
        $Reporter->setPassword([]);
        $Reporter->setPassword(new \StdClass);
        $Reporter->setPassword('');
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

        $Reporter->setUserId('me@example.com')
            ->setPassword('mypassword!');

        $this->assertSame(
            '{"userid":"me@example.com","password":"mypassword!","version":"1.0","mode":"Robot.XML","account":"None","queryInput":"[p=Reporter.properties, Sales.getAccounts]"}',
            $Reporter->buildJsonRequest('Sales.getAccounts')
        );
    }

    public function testBuildJsonRequestBuildsCorrectForSalesGetVendors()
    {
        $Reporter = new Reporter(
            new Client
        );

        $Reporter->setUserId('me@example.com')
            ->setPassword('mypassword!')
            ->setAccountNum(1234567);

        $this->assertSame(
            '{"userid":"me@example.com","password":"mypassword!","version":"1.0","mode":"Robot.XML","account":"1234567","queryInput":"[p=Reporter.properties, Sales.getVendors]"}',
            $Reporter->buildJsonRequest('Sales.getVendors')
        );
    }

    public function testBuildJsonRequestBuildsCorrectForSalesGetReporter()
    {
        $Reporter = new Reporter(
            new Client
        );

        $Reporter->setUserId('me@example.com')
            ->setPassword('mypassword!');

        $this->assertSame(
            '{"userid":"me@example.com","password":"mypassword!","version":"1.0","mode":"Robot.XML","account":"None","queryInput":"[p=Reporter.properties, Sales.getReport, 12345678,Sales,Summary,Daily,20161020]"}',
            $Reporter->buildJsonRequest('Sales.getReport', '12345678', 'Sales', 'Summary', 'Daily', '20161020')
        );
    }

    public function testBuildJsonRequestThrowsExceptionWhenNoDataPassed()
    {
        $this->setExpectedException('BadFunctionCallException');

        $Reporter = new Reporter(
            new Client
        );

        $Reporter->setUserId('me@example.com')
            ->setPassword('mypassword!')
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
        $Reporter->setUserId('me@me.com')->setPassword('123qaz');

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
        $Reporter->setUserId('me@me.com')->setPassword('123qaz');

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
}
