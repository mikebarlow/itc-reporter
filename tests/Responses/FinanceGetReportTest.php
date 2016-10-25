<?php
namespace Snscripts\ITCReporter\Tests\Responses;

use Snscripts\ITCReporter\Responses\FinanceGetReport;

class FinanceGetReportTest extends \PHPUnit_Framework_TestCase
{
    public function testProcessReturnsReportInArrayFormat()
    {
        $Response = $this->getMock('Psr\Http\Message\ResponseInterface');
        $Response->method('getBody')
            ->willReturn(
                new TestFinanceReportContent
            );

        $Processor = new FinanceGetReport(
            $Response
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
            $Processor->process()
        );
    }

    public function testProcessReturnsEmptyArrayWhenContentsEmpty()
    {
        $Response = $this->getMock('Psr\Http\Message\ResponseInterface');
        $Response->method('getBody')
            ->willReturn(
                new TestFinanceReportNoContent
            );

        $Processor = new FinanceGetReport(
            $Response
        );

        $this->assertSame(
            [],
            $Processor->process()
        );
    }

    public function testProcessReturnsEmptyArrayIfFileIsNotGZipped()
    {
        $Response = $this->getMock('Psr\Http\Message\ResponseInterface');
        $Response->method('getBody')
            ->willReturn(
                new TestFinanceReportNoEncoding
            );

        $Processor = new FinanceGetReport(
            $Response
        );

        $this->assertSame(
            [],
            $Processor->process()
        );
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

class TestFinanceReportNoEncoding
{
    public function getContents()
    {
        return "Header 1\tHeader 2\tHeader 3\nFoo\tBar\tFoobar\nFizz\t\tFizzbuzz\n\tTest\tTester\nTotal\t100\nGrand Total\t500";
    }
}

class TestFinanceReportNoContent
{
    public function getContents()
    {
        return '';
    }
}
