<?php
namespace Snscripts\ITCReporter\Responses;

use Psr\Http\Message\ResponseInterface;
use Snscripts\ITCReporter\Interfaces\ResponseProcessor;

class SalesGetAccounts implements ResponseProcessor
{
    protected $Response;

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

            if (empty($XML->Account)) {
                throw new \Exception('No account data');
            }
        } catch (\Exception $e) {
            return [];
        }

        $accounts = [];
        foreach ($XML->Account as $AccountXML) {
            $id = (int) $AccountXML->Number;
            $name = (string) $AccountXML->Name;

            $accounts[$id] = [
                'Name' => $name,
                'Number' => $id
            ];
        }

        return $accounts;
    }
}
