<?php
namespace Snscripts\ITCReporter\Tests\Responses;

use Snscripts\ITCReporter\Responses\SalesGetVendors;

class SalesGetVendorsTest extends \PHPUnit_Framework_TestCase
{
    public function testProcessReturnsCorrectValueForSingleSalesVendor()
    {
        $Response = $this->getMock('Psr\Http\Message\ResponseInterface');
        $Response->method('getBody')
            ->willReturn('<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Vendors><Vendor>1234567</Vendor></Vendors>');

        $Processor = new SalesGetVendors(
            $Response
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
        $Response = $this->getMock('Psr\Http\Message\ResponseInterface');
        $Response->method('getBody')
            ->willReturn('<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Vendors><Vendor>1234567</Vendor><Vendor>9876543</Vendor></Vendors>');

        $Processor = new SalesGetVendors(
            $Response
        );

        $this->assertSame(
            [
                1234567,
                9876543
            ],
            $Processor->process()
        );
    }

    public function testProcessReturnsEmptyArrayForInvalidXML()
    {
        $Response = $this->getMock('Psr\Http\Message\ResponseInterface');
        $Response->method('getBody')
            ->willReturn('<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Vendors>1234567</Vendor><dor>9876543</Vendor></Vendors>');

        $Processor = new SalesGetVendors(
            $Response
        );

        $this->assertSame(
            [],
            $Processor->process()
        );
    }

    public function testProcessReturnsEmptyArrayForEmptyContents()
    {
        $Response = $this->getMock('Psr\Http\Message\ResponseInterface');
        $Response->method('getBody')
            ->willReturn('');

        $Processor = new SalesGetVendors(
            $Response
        );

        $this->assertSame(
            [],
            $Processor->process()
        );
    }
}
