<?php
namespace Snscripts\ITCReporter\Responses;

use Snscripts\ITCReporter\Interfaces\ResponseProcessor;
use Psr\Http\Message\ResponseInterface;

class SalesGetVendors implements ResponseProcessor
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
        foreach ($XML->Vendor as $Vendor) {
            $vendors[] = (int) $Vendor;
        }

        return $vendors;
    }
}
