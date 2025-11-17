<?php
namespace Snscripts\ITCReporter\Tests\Responses;

use GuzzleHttp\Psr7\Utils;
use PHPUnit\Framework\TestCase;
use Snscripts\ITCReporter\Responses\FinanceGetReport;

class FinanceGetReportTest extends TestCase
{
    public function testProcessReturnsReportInArrayFormat()
    {
        $Response = $this->getMockBuilder('Psr\Http\Message\ResponseInterface')->getMock();
        $Response->method('getBody')
            ->willReturn(Utils::streamFor(
                (new TestFinanceReportContent)->getContents()
            ));

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
        $Response = $this->getMockBuilder('Psr\Http\Message\ResponseInterface')->getMock();
        $Response->method('getBody')
            ->willReturn(Utils::streamFor(
                (new TestFinanceReportNoContent)->getContents()
            ));

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
        $Response = $this->getMockBuilder('Psr\Http\Message\ResponseInterface')->getMock();
        $Response->method('getBody')
            ->willReturn(Utils::streamFor(
                (new TestFinanceReportNoEncoding)->getContents()
            ));

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
