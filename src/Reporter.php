<?php
namespace Snscripts\ITCReporter;

use GuzzleHttp\ClientInterface;
use Snscripts\Result\Result;
use Psr\Http\Message\ResponseInterface;

class Reporter
{
    const
        SALESURL   = 'https://reportingitc-reporter.apple.com/reportservice/sales/v1',
        FINANCEURL = 'https://reportingitc-reporter.apple.com/reportservice/finance/v1',
        VERSION    = '2.0',
        MODE       = 'Robot.XML';

    protected $userid;
    protected $password;
    protected $account = 'None';
    protected $Guzzle;
    protected $responses = [
        'Sales.getAccounts'            => '\Snscripts\ITCReporter\Responses\SalesGetAccounts',
        'Sales.getVendors'             => '\Snscripts\ITCReporter\Responses\SalesGetVendors',
        'Sales.getReport'              => '\Snscripts\ITCReporter\Responses\SalesGetReport',
        'Finance.getAccounts'          => '\Snscripts\ITCReporter\Responses\FinanceGetAccounts',
        'Finance.getVendorsAndRegions' => '\Snscripts\ITCReporter\Responses\FinanceGetVendors',
        'Finance.getReport'            => '\Snscripts\ITCReporter\Responses\FinanceGetReport'
    ];
    protected $lastResult = null;

    /**
     * constructor - setup guzzle dependency
     *
     * @param ClientInterface $Guzzle
     */
    public function __construct(ClientInterface $Guzzle)
    {
        $this->Guzzle = $Guzzle;
    }

    /**
     * get list of sales accounts this user id has access to
     *
     * @return array $accounts
     */
    public function getSalesAccounts()
    {
        $json = $this->buildJsonRequest('Sales.getAccounts');

        $this->lastResult = $Result = $this->performRequest(self::SALESURL, $json);

        if ($Result->isSuccess()) {
            return $this->processResponse(
                'Sales.getAccounts',
                $Result->getExtra('Response')
            );
        }

        return [];
    }

    /**
     * get list of vendors for the given account number
     *
     * @return array $vendors
     */
    public function getSalesVendors()
    {
        $json = $this->buildJsonRequest('Sales.getVendors');

        $this->lastResult = $Result = $this->performRequest(self::SALESURL, $json);

        if ($Result->isSuccess()) {
            return $this->processResponse(
                'Sales.getVendors',
                $Result->getExtra('Response')
            );
        }

        return [];
    }

    /**
     * get Sales Report given the attribute
     *
     * @see https://help.apple.com/itc/appsreporterguide/#/itcbd9ed14ac
     *
     * @param string $vendor
     * @param string $reportType
     * @param string $reportSubType
     * @param string $dateType
     * @param string $date
     *
     * @return array $report
     */
    public function getSalesReport(
        $vendor,
        $reportType,
        $reportSubType,
        $dateType,
        $date
    ) {
        $json = $this->buildJsonRequest(
            'Sales.getReport',
            $vendor,
            $reportType,
            $reportSubType,
            $dateType,
            $date
        );

        $this->lastResult = $Result = $this->performRequest(self::SALESURL, $json);

        if ($Result->isSuccess()) {
            return $this->processResponse(
                'Sales.getReport',
                $Result->getExtra('Response')
            );
        }

        return [];
    }

    /**
     * get list of finance accounts this user id has access to
     *
     * @return array $accounts
     */
    public function getFinanceAccounts()
    {
        $json = $this->buildJsonRequest('Finance.getAccounts');

        $this->lastResult = $Result = $this->performRequest(self::FINANCEURL, $json);

        if ($Result->isSuccess()) {
            return $this->processResponse(
                'Finance.getAccounts',
                $Result->getExtra('Response')
            );
        }

        return [];
    }

    /**
     * get list of finance vendors and regions for the given account number
     *
     * @return array $vendors
     */
    public function getFinanceVendors()
    {
        $json = $this->buildJsonRequest('Finance.getVendorsAndRegions');

        $this->lastResult = $Result = $this->performRequest(self::FINANCEURL, $json);

        if ($Result->isSuccess()) {
            return $this->processResponse(
                'Finance.getVendorsAndRegions',
                $Result->getExtra('Response')
            );
        }

        return [];
    }

    /**
     * get Finance Report given the attribute
     *
     * @see https://help.apple.com/itc/appsreporterguide/#/itc21263284f
     *
     * @param string $vendor
     * @param string $regionCode
     * @param string $reportType
     * @param string $year
     * @param string $period
     *
     * @return array $report
     */
    public function getFinanceReport(
        $vendor,
        $regionCode,
        $reportType,
        $year,
        $period
    ) {
        $json = $this->buildJsonRequest(
            'Finance.getReport',
            $vendor,
            $regionCode,
            $reportType,
            $year,
            $period
        );

        $this->lastResult = $Result = $this->performRequest(self::FINANCEURL, $json);

        if ($Result->isSuccess()) {
            return $this->processResponse(
                'Finance.getReport',
                $Result->getExtra('Response')
            );
        }

        return [];
    }

    /**
     * given the action as first param + any data
     * build json request
     *
     * @param string $action
     * @param mixed
     * @return string $json JSON string for the request
     * @throws \BadFunctionCallException
     */
    public function buildJsonRequest()
    {
        $args = func_get_args();

        if (empty($args[0])) {
            throw new \BadFunctionCallException('A valid action must be passed to Reporter::buildJsonRequest()');
        }

        $json = [
            'userid'   => $this->userid,
            'password' => $this->password,
            'version'  => self::VERSION,
            'mode'     => self::MODE,
            'account'  => (string)$this->account
        ];

        // build up the action and parameters we actually want to perform
        $queryInput = [
            'p=Reporter.properties',
            array_shift($args)
        ];
        if (! empty($args)) {
            $queryInput[] = implode(',', $args);
        }
        $json['queryInput'] = '[' . implode(', ', $queryInput) . ']';

        return json_encode($json);
    }

    /**
     * given the endpoint & json request, perform the request
     *
     * @param string $endpoint
     * @param string $jsonRequest
     * @return Result
     */
    public function performRequest($endpoint, $jsonRequest)
    {
        try {
            $Response = $this->Guzzle->request(
                'POST',
                $endpoint,
                [
                    'form_params' => [
                        'jsonRequest' => $jsonRequest
                    ],
                    'headers' => [
                        'User-Agent' => 'Java/1.8.0_92',
                        'Accept' => 'text/xml, text/plain'
                    ]
                ]
            );

            if ($Response->getStatusCode() !== 200) {
                throw new \UnexpectedValueException('The request did not return a 200 OK response');
            }

            return Result::success('OK')
                ->setExtra('Response', $Response);
        } catch (\Exception $e) {
            return Result::fail(
                Result::ERROR,
                $e->getMessage()
            );
        }
    }

    /**
     * process the XML returned from API
     *
     * @param string $action
     * @param Psr\Http\Message\ResponseInterface $Response
     * @return array
     */
    public function processResponse($action, ResponseInterface $Response)
    {
        if (empty($this->responses[$action])) {
            throw new \InvalidArgumentException(
                $action . ' was passed to processResponse, no Response class exists for this action.'
            );
        }

        $responseClass = $this->responses[$action];

        $ResponseProcesser = new $responseClass(
            $Response
        );

        return $ResponseProcesser->process();
    }

    /**
     * get the last result set
     *
     * @return Result|null
     */
    public function getLastResult()
    {
        return $this->lastResult;
    }

    /**
     * set the user id
     *
     * @param string $userid
     * @return Reporter $this
     * @throws \InvalidArgumentException
     */
    public function setUserId($userid)
    {
        if (empty($userid) || ! is_string($userid)) {
            throw new \InvalidArgumentException('Argument passed to Reporter::setUser was not a string');
        }

        $this->userid = $userid;
        return $this;
    }

    /**
     * return the user id currently set
     *
     * @return string $userid
     */
    public function getUserId()
    {
        return $this->userid;
    }

    /**
     * set the password
     *
     * @param string $password
     * @return Reporter $this
     * @throws \InvalidArgumentException
     */
    public function setPassword($password)
    {
        if (empty($password) || ! is_string($password)) {
            throw new \InvalidArgumentException('Argument passed to Reporter::setPassword was not a string');
        }

        $this->password = $password;
        return $this;
    }

    /**
     * return the password currently set
     *
     * @return string $password
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * set the account num to use
     *
     * @param string $account
     * @return Reporter $this
     * @throws \InvalidArgumentException
     */
    public function setAccountNum($account)
    {
        if (! is_int($account)) {
            throw new \InvalidArgumentException(
                'Argument passed to Reporter::setAccount must be an integer account number'
            );
        }

        $this->account = $account;
        return $this;
    }

    /**
     * return the account num currently set
     *
     * @return string $account
     */
    public function getAccountNum()
    {
        return $this->account;
    }
}
