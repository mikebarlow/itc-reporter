<?php
namespace Snscripts\ITCReporter\Responses;

use Snscripts\ITCReporter\Interfaces\ResponseProcessor;
use Psr\Http\Message\ResponseInterface;

class FinanceGetVendors implements ResponseProcessor
{
    public function __construct(ResponseInterface $Response)
    {
        $this->Response = $Response;
    }

    public function process()
    {
        try {
            $XML = new \SimpleXMLElement(
                $this->Response->getBody()
            );

            if (empty($XML->Vendor)) {
                throw new \Exception('No account data');
            }
        } catch (\Exception $e) {
            return [];
        }

        $vendors = [];
        foreach ($XML->Vendor as $VendorXML) {
            $id = (int) $VendorXML->Number;

            $regions = [];
            foreach ($VendorXML->Region as $RegionXML) {
                $code = (string) $RegionXML->Code;

                $reports = [];
                foreach ($RegionXML->Reports->Report as $Report) {
                    $reports[] = (string) $Report;
                }

                $regions[$code] = [
                    'Code' => $code,
                    'Reports' => $reports
                ];
            }

            $vendors[$id] = [
                'Number' => $id,
                'Regions' => $regions
            ];
        }

        return $vendors;
    }
}
