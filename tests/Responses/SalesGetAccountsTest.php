<?php
namespace Snscripts\ITCReporter\Tests\Responses;

use Snscripts\ITCReporter\Responses\SalesGetAccounts;

class SalesGetAccountsTest extends \PHPUnit_Framework_TestCase
{
	public function setUp()
	{
		$this->singleAccountResponse = $this->getMock('Psr\Http\Message\ResponseInterface');
		$this->singleAccountResponse->method('getBody')
			->willReturn('<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Accounts><Account><Name>John Smith</Name><Number>1234567</Number></Account></Accounts>');

		$this->multiAccountResponse = $this->getMock('Psr\Http\Message\ResponseInterface');
		$this->multiAccountResponse->method('getBody')
			->willReturn('<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Accounts><Account><Name>John Smith</Name><Number>1234567</Number></Account><Account><Name>Jane Doe</Name><Number>9876543</Number></Account></Accounts>');
	}

	public function testProcessReturnsCorrectValueForSingleSalesAccount()
	{
		$Processor = new SalesGetAccounts(
			$this->singleAccountResponse
		);

		$this->assertSame(
			[
				1234567 => [
					'Name' => 'John Smith',
					'Number' => 1234567
				]
			],
			$Processor->process()
		);
	}

	public function testProcessReturnsCorrectValueForMultipleSalesAccount()
	{
		$Processor = new SalesGetAccounts(
			$this->multiAccountResponse
		);

		$this->assertSame(
			[
				1234567 => [
					'Name' => 'John Smith',
					'Number' => 1234567
				],
				9876543 => [
					'Name' => 'Jane Doe',
					'Number' => 9876543
				]
			],
			$Processor->process()
		);
	}
}
