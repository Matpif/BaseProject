<?php

namespace App\libs\App;

use App\Cache;

class Webservice
{
    /** @var bool */
    protected $_cacheUsed;
    /** @var \SoapClient */
    protected $_soapClient;
    /** @var string */
    protected $_wsdl;
    /** @var array */
    protected $_options;

    /**
     * Webservice constructor.
     * @param string $wsdl
     * @param array $options
     */
    public function __construct($wsdl, $options = null)
    {
        $this->_wsdl = $wsdl;
        $this->_options = $options;
        $this->_soapClient = new \SoapClient($wsdl, $options);
    }

    /**
     * @return \SoapClient
     */
    public function getSoapClient()
    {
        return $this->_soapClient;
    }

    /**
     * @return string
     */
    public function getWsdl()
    {
        return $this->_wsdl;
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        return $this->_options;
    }

    /**
     * @param string $method
     * @param array $args
     * @return array
     */
    protected function call($method, $args = [])
    {
        $cacheKey = $this->generateCahceKey($method, $args);
        if ($this->isCacheUsed()) {
            if (Cache::getInstance()->cacheExist($cacheKey)) {
                return Cache::getInstance()->getCacheRedis()->fetch($cacheKey);
            }
        }

        $data = $this->_soapClient->__soapCall($method, $args);
        if ($this->isCacheUsed()) {
            Cache::getInstance()->getCacheRedis()->save($cacheKey, $data);
        }

        return $data;
    }

    protected function generateCahceKey($method, $args)
    {
        return md5('webservice_' . $method . '_' . serialize($args));
    }

    /**
     * @return bool
     */
    public function isCacheUsed()
    {
        return $this->_cacheUsed;
    }

    /**
     * @param bool $cacheUsed
     */
    public function setCacheUsed($cacheUsed)
    {
        $this->_cacheUsed = $cacheUsed;
    }
}