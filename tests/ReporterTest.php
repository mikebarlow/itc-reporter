<?php
namespace Snscripts\ITCReporter\Tests;

use Snscripts\ITCReporter\Reporter;

class ReporterTest extends \PHPUnit_Framework_TestCase
{
    public function testCanCreateInstance()
    {
        $this->assertInstanceOf(
            'Snscripts\ITCReporter\Reporter',
            new Reporter
        );
    }

    public function testCanSetAndGetUserId()
    {
    	$Reporter = new Reporter;

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

    	$Reporter = new Reporter;

    	$Reporter->setUserId(123);
    	$Reporter->setUserId([]);
    	$Reporter->setUserId(new \StdClass);
    	$Reporter->setUserId('');
    }

    public function testCanSetAndGetPassword()
    {
    	$Reporter = new Reporter;

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

    	$Reporter = new Reporter;

    	$Reporter->setUserId(123);
    	$Reporter->setUserId([]);
    	$Reporter->setUserId(new \StdClass);
    	$Reporter->setUserId('');
    }

    public function testCanSetAndGetAccount()
    {
    	$Reporter = new Reporter;

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

    	$Reporter = new Reporter;

    	$Reporter->setAccountNum([]);
    	$Reporter->setAccountNum(new \StdClass);
    	$Reporter->setAccountNum('1234567');
    }

    public function testBuildJsonRequestBuildsCorrectForSalesGetAccounts()
    {
    	$Reporter = new Reporter;

    	$Reporter->setUserId('me@example.com')
    		->setPassword('mypassword!');

    	$this->assertSame(
    		'{"userid":"me@example.com","password":"mypassword!","version":"1.0","mode":"Robot.XML","account":"None","queryInput":"[p=Reporter.properties, Sales.getAccounts]"}',
    		$Reporter->buildJsonRequest('Sales.getAccounts')
    	);
    }

    public function testBuildJsonRequestBuildsCorrectForSalesGetVendors()
    {
    	$Reporter = new Reporter;

    	$Reporter->setUserId('me@example.com')
    		->setPassword('mypassword!')
    		->setAccountNum(1234567);

    	$this->assertSame(
    		'{"userid":"me@example.com","password":"mypassword!","version":"1.0","mode":"Robot.XML","account":1234567,"queryInput":"[p=Reporter.properties, Sales.getVendors]"}',
    		$Reporter->buildJsonRequest('Sales.getVendors')
    	);
    }

    public function testBuildJsonRequestBuildsCorrectForSalesGetReporter()
    {
    	$Reporter = new Reporter;

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

    	$Reporter = new Reporter;

    	$Reporter->setUserId('me@example.com')
    		->setPassword('mypassword!')
    		->setAccountNum(1234567)
    		->buildJsonRequest();
    }
}
