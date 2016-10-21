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
        VERSION    = '1.0',
        MODE       = 'Robot.XML';

    protected $userid;
    protected $password;
    protected $account = 'None';
    protected $Guzzle;
    protected $responses = [
        'Sales.getAccounts' => '\Snscripts\ITCReporter\Responses\SalesGetAccounts'
    ];

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
     * get list of accounts this user id has access to
     *
     * @return array $accounts
     */
    public function getAccounts()
    {
        $json = $this->buildJsonRequest('Sales.getAccounts');

        $Result = $this->performRequest(self::SALESURL, $json);

        if ($Result->isSuccess()) {
            return $this->processResponse(
                'Sales.getAccounts',
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
            'account'  => $this->account
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
                    ]
                ]
            );

            if ($Response->getStatusCode() !== 200) {
                throw new \UnexpectedValueException('The request did not return a 200 OK response');
            }

            return Result::success('OK')
                ->setExtra('Response', $Response);
        } catch(\Exception $e) {
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
            throw new \InvalidArgumentException($action . ' was passed to processResponse, no Response class exists for this action.');
        }

        $responseClass = $this->responses[$action];

        $ResponseProcesser = new $responseClass(
            $Response
        );

        return $ResponseProcesser->process();
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
            throw new \InvalidArgumentException('Argument passed to Reporter::setAccount must be an integer account number');
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
