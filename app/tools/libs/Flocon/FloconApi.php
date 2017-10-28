<?php

class FloconApi
{
    /** Access key API */
    const VERSION = "1.0.1";

    /** @var  string */
    private $_url;
    /**
     * @var string
     */
    private $_key;

    /**
     * PivIdentificationApi constructor.
     * @param $secure bool
     */
    public function __construct($secure = true)
    {
        $pathUrl = Config::getInstance()->getAttribute('api', 'url');
        $this->_key = Config::getInstance()->getAttribute('api', 'key');
        $this->_url = (($secure) ? 'https:' : 'http:') . $pathUrl;
    }

    /**
     * Call signIn function of API
     *
     * @param $username string
     * @param $password string
     * @return array
     * @throw HttpException
     */
    public function signIn($username, $password)
    {
        $json = json_decode($this->httpPost($this->getUrlAction('signIn'),
            ['username' => $username, 'password' => $password]), true);

        return ($json) ? $json : ['error' => 'Error !'];
    }

    /**
     * @param $url
     * @param $data
     * @return mixed
     */
    private function httpPost($url, $data)
    {
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER,
            ['version: ' . self::VERSION, 'key: ' . $this->_key, 'Content-Type: application/json']);
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($curl);
        curl_close($curl);

        return $response;
    }

    /**
     * @param $action string
     * @return string
     */
    private function getUrlAction($action)
    {
        return $this->_url . '/' . $action;
    }

    public function signInByToken($token)
    {
        $json = json_decode($this->httpPost($this->getUrlAction('signInByToken'), ['token' => $token]), true);

        return ($json) ? $json : ['error' => 'Error !'];
    }
}