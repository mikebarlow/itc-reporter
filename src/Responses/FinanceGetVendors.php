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

			if (empty($XML->VendorsAndRegions)) {
				throw new \Exception('No account data');
			}
		} catch (\Exception $e) {
			return [];
		}

		$vendors = [

		];
		// todo: reinstate this when unit tests are written so we can check how it reacts
		// foreach ($XML->Vendors as $VendorXML) {
		// 	$id = (int) $VendorXML->Vendor;

		// 	$vendors[$id] = $id;
		// }

		return $vendors;
	}
}
