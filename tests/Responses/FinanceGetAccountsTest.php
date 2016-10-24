<?php
namespace Snscripts\ITCReporter\Tests\Responses;

use Snscripts\ITCReporter\Responses\FinanceGetAccounts;

class FinanceGetAccountsTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->singleResponse = $this->getMock('Psr\Http\Message\ResponseInterface');
        $this->singleResponse->method('getBody')
            ->willReturn('<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Accounts><Account><Name>John Smith</Name><Number>1234567</Number></Account></Accounts>');

        $this->multiResponse = $this->getMock('Psr\Http\Message\ResponseInterface');
        $this->multiResponse->method('getBody')
            ->willReturn('<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Accounts><Account><Name>John Smith</Name><Number>1234567</Number></Account><Account><Name>Jane Doe</Name><Number>9876543</Number></Account></Accounts>');
    }

    public function testProcessReturnsCorrectValueForSingleFinanceAccount()
    {
        $Processor = new FinanceGetAccounts(
            $this->singleResponse
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

    public function testProcessReturnsCorrectValueForMultipleFinanceAccount()
    {
        $Processor = new FinanceGetAccounts(
            $this->multiResponse
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
