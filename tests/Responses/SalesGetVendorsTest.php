<?php
namespace Snscripts\ITCReporter\Tests\Responses;

use Snscripts\ITCReporter\Responses\SalesGetVendors;

class SalesGetVendorsTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->singleResponse = $this->getMock('Psr\Http\Message\ResponseInterface');
        $this->singleResponse->method('getBody')
            ->willReturn('<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Vendors><Vendor>1234567</Vendor></Vendors>');

        $this->multiResponse = $this->getMock('Psr\Http\Message\ResponseInterface');
        $this->multiResponse->method('getBody')
            ->willReturn('<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Vendors><Vendor>1234567</Vendor><Vendor>9876543</Vendor></Vendors>');
    }

    public function testProcessReturnsCorrectValueForSingleSalesVendor()
    {
        $Processor = new SalesGetVendors(
            $this->singleResponse
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
            $this->multiResponse
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
