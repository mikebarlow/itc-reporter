<?php
namespace Snscripts\ITCReporter\Tests\Responses;

use GuzzleHttp\Psr7\Utils;
use PHPUnit\Framework\TestCase;
use Snscripts\ITCReporter\Responses\SalesGetReport;

class SalesGetReportTest extends TestCase
{
    public function testProcessReturnsReportInArrayFormat()
    {
        $Response = $this->getMockBuilder('Psr\Http\Message\ResponseInterface')->getMock();
        $Response->method('getBody')
            ->willReturn(Utils::streamFor(
                (new TestSalesReportContent)->getContents()
            ));

        $Processor = new SalesGetReport(
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
                ]
            ],
            $Processor->process()
        );
    }

    public function testProcessReturnsEmptyArrayWhenContentsEmpty()
    {
        $Response = $this->getMockBuilder('Psr\Http\Message\ResponseInterface')->getMock();
        $Response->method('getBody')
            ->willReturn(Utils::streamFor(
                (new TestSalesReportNoContent)->getContents()
            ));

        $Processor = new SalesGetReport(
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
                (new TestSalesReportNoEncoding)->getContents()
            ));

        $Processor = new SalesGetReport(
            $Response
        );

        $this->assertSame(
            [],
            $Processor->process()
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

class TestSalesReportNoEncoding
{
    public function getContents()
    {
        return "Header 1\tHeader 2\tHeader 3\nFoo\tBar\tFoobar\nFizz\t\tFizzbuzz\n\tTest\tTester\nTotal\t100\nGrand Total\t500";
    }
}

class TestSalesReportNoContent
{
    public function getContents()
    {
        return '';
    }
}
