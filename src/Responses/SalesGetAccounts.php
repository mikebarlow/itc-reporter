<?php
namespace Snscripts\ITCReporter\Responses;

use Snscripts\ITCReporter\Interfaces\ResponseProcesser;
use Psr\Http\Message\ResponseInterface;

class SalesGetAccounts implements ResponseProcesser
{
	public function __construct(ResponseInterface $Response)
	{
		$this->Response = $Response;
	}

	public function process()
	{
		return [];
	}
}
