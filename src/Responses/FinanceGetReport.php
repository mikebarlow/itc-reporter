<?php
namespace Snscripts\ITCReporter\Responses;

use Psr\Http\Message\ResponseInterface;
use Snscripts\ITCReporter\Interfaces\ResponseProcessor;

class FinanceGetReport implements ResponseProcessor
{
    protected $Response;
    
    public function __construct(ResponseInterface $Response)
    {
        $this->Response = $Response;
    }

    public function process()
    {
        $contents = $this->Response->getBody()->getContents();
        if (empty($contents)) {
            return [];
        }

        $reportCSV = @gzdecode($contents);
        if (! isset($reportCSV) || ! $reportCSV) {
            return [];
        }

        $rows = explode("\n", $reportCSV);
        $headers = explode("\t", array_shift($rows));
        $headerCount = count($headers);

        $reportArray = [];

        foreach ($rows as $values) {
            if (empty($values)) {
                continue;
            }

            $data = explode("\t", $values);
            $dataCount = count($data);

            if ($headerCount !== $dataCount && $dataCount === 2) {
                $reportArray[$data[0]] = $data[1];
            } else {
                $reportArray[] = array_combine(
                    $headers,
                    $data
                );
            }
        }

        return $reportArray;
    }
}
