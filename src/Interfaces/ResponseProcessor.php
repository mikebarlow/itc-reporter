<?php
namespace Snscripts\ITCReporter\Interfaces;

use Psr\Http\Message\ResponseInterface;

interface ResponseProcesser
{
	public $Response;

	public function __construct(ResponseInterface $Response);

	public function process();
}
