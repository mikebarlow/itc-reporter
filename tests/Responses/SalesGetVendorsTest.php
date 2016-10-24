<?php
namespace Snscripts\ITCReporter\Tests\Responses;

use Snscripts\ITCReporter\Responses\SalesGetVendors;

class SalesGetVendorsTest extends \PHPUnit_Framework_TestCase
{
	public function setUp()
	{
		$this->singleAccountResponse = $this->getMock('Psr\Http\Message\ResponseInterface');
		$this->singleAccountResponse->method('getBody')
			->willReturn('<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Vendors><Vendor>1234567</Vendor></Vendors>');

		$this->multiAccountResponse = $this->getMock('Psr\Http\Message\ResponseInterface');
		$this->multiAccountResponse->method('getBody')
			->willReturn('<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Vendors><Vendor>1234567</Vendor><Vendor>9876543</Vendor></Vendors>');
	}

	public function testProcessReturnsCorrectValueForSingleSalesVendor()
	{
		$Processor = new SalesGetVendors(
			$this->singleAccountResponse
		);

		$this->assertSame(
			[
				1234567
			],
			$Processor->process()
		);
	}

	public function testProcessReturnsCorrectValueForMultipleSalesVendor()
	{
		$Processor = new SalesGetVendors(
			$this->multiAccountResponse
		);

		$this->assertSame(
			[
				1234567,
				9876543
			],
			$Processor->process()
		);
	}
}
