<?php

namespace App\libs\App;

use App\App;
use Exception;

abstract class VarientObject implements \JsonSerializable
{

    /**
     * @var array
     */
    protected $_data;

    /**
     * Set data to array
     * @param $data array
     */
    public function setData($data)
    {
        $this->_data = $data;
    }

    public function getData() {
        return $this->_data;
    }

    /**
     * Set several values in data
     * @param $data
     */
    public function addData($data)
    {
        foreach ($data as $key => $value) {
            $this->setAttribute($key, $value);
        }
    }

    /**
     * Set value in data
     * @param $key string
     * @param $value mixed
     */
    public function setAttribute($key, $value)
    {
        $this->_data[$key] = $value;
    }

    /**
     * @param $method
     * @param $args
     * @return bool|mixed|string|void
     * @throws Exception
     */
    public function __call($method, $args)
    {
        $key = $this->_underscore(substr($method, 3));

        switch (substr($method, 0, 3)) {
            case 'get' :
                $data = $this->getAttribute($key);

                return $data;
            case 'set' :
                $this->setAttribute($key, isset($args[0]) ? $args[0] : null);

                return true;
            case 'uns' :
                if (isset($this->_data[$key])) {
                    unset($this->_data[$key]);
                }

                return true;
            case 'has' :
                return isset($this->_data[$key]);
        }
        throw new Exception("Invalid method " . get_class($this) . "::" . $method . "(" . print_r($args, 1) . ")");
    }

    /**
     * @param $name
     * @return string
     */
    protected function _underscore($name)
    {
        $result = strtolower(preg_replace('/(.)([A-Z])/', "$1_$2", $name));

        return $result;
    }

    /**
     * Get data by key
     * @param $key string
     * @return mixed|string
     */
    public function getAttribute($key)
    {
        if (is_array($this->_data) && array_key_exists($key, $this->_data)) {
            return $this->_data[$key];
        } else {
            return '';
        }
    }

    /**
     * @return array
     */
    function jsonSerialize()
    {
        return $this->_data;
    }

    public function __($text, $moduleName = null)
    {
        if (App::getInstance()->getTranslate($moduleName)) {
            return App::getInstance()->getTranslate($moduleName)->translate($text);
        } else {
            return $text;
        }
    }
}