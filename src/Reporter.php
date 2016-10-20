<?php
namespace Snscripts\ITCReporter;

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

    /**
     * get list of accounts this user id has access to
     *
     * @return array $accounts
     */
    public function getAccounts()
    {
        $json = $this->buildJsonRequest('Sales.getAccounts');

        return $this->performRequest($json);
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
