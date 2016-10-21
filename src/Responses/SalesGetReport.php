<?php
namespace Snscripts\ITCReporter\Responses;

use Snscripts\ITCReporter\Interfaces\ResponseProcessor;
use Psr\Http\Message\ResponseInterface;

class SalesGetReport implements ResponseProcessor
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

			var_dump($XML);
			var_dump($this->Response);

			// if (empty($XML->Vendor)) {
			// 	throw new \Exception('No account data');
			// }
		} catch (\Exception $e) {
			return [];
		}

		// $vendors = [
		// 	(string) $XML->Vendor
		// ];
		// todo: reinstate this when unit tests are written so we can check how it reacts
		// foreach ($XML->Vendors as $VendorXML) {
		// 	$id = (int) $VendorXML->Vendor;

		// 	$vendors[$id] = $id;
		// }

		return [];
	}
}
