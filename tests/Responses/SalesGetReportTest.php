<?php
namespace Snscripts\ITCReporter\Tests\Responses;

use Snscripts\ITCReporter\Responses\SalesGetReport;

class SalesGetReportTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {

    }

    public function testProcessReturnsReportInArrayFormat()
    {
        $Response = $this->getMock('Psr\Http\Message\ResponseInterface');
        $Response->method('getBody')
            ->willReturn(
                new TestSalesReportContent
            );

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
        $Response = $this->getMock('Psr\Http\Message\ResponseInterface');
        $Response->method('getBody')
            ->willReturn(
                new TestSalesReportNoContent
            );

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
        $Response = $this->getMock('Psr\Http\Message\ResponseInterface');
        $Response->method('getBody')
            ->willReturn(
                new TestSalesReportNoEncoding
            );

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
