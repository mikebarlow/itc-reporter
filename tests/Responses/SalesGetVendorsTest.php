<?php
namespace Snscripts\ITCReporter\Tests\Responses;

use GuzzleHttp\Psr7\Utils;
use PHPUnit\Framework\TestCase;
use Snscripts\ITCReporter\Responses\SalesGetVendors;

class SalesGetVendorsTest extends TestCase
{
    public function testProcessReturnsCorrectValueForSingleSalesVendor()
    {
        $Response = $this->getMockBuilder('Psr\Http\Message\ResponseInterface')->getMock();
        $Response->method('getBody')
            ->willReturn(Utils::streamFor('<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Vendors><Vendor>1234567</Vendor></Vendors>'));

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
        $Response = $this->getMockBuilder('Psr\Http\Message\ResponseInterface')->getMock();
        $Response->method('getBody')
            ->willReturn(Utils::streamFor('<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Vendors><Vendor>1234567</Vendor><Vendor>9876543</Vendor></Vendors>'));

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
        $Response = $this->getMockBuilder('Psr\Http\Message\ResponseInterface')->getMock();
        $Response->method('getBody')
            ->willReturn(Utils::streamFor('<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Vendors>1234567</Vendor><dor>9876543</Vendor></Vendors>'));

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
        $Response = $this->getMockBuilder('Psr\Http\Message\ResponseInterface')->getMock();
        $Response->method('getBody')
            ->willReturn(Utils::streamFor(''));

        $Processor = new SalesGetVendors(
            $Response
        );

        $this->assertSame(
            [],
            $Processor->process()
        );
    }
}
