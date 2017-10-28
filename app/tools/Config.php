<?php

namespace App;

class Config
{
    private static $_instance;
    /** @var array */
    private $_config;
    /** @var  string */
    private $_path;

    public function __construct($path)
    {
        $this->_path = $path;
        $this->_config = json_decode(file_get_contents($this->_path), true);
    }

    public static function getInstance()
    {
        if (self::$_instance == null) {
            self::$_instance = new Config(__DIR__ . '/../etc/config.json');
        }

        return self::$_instance;
    }

    /**
     * Return all config config.json
     * @return array
     */
    public function getConfig()
    {
        return $this->_config;
    }

    /**
     * @param $config array
     */
    public function setConfig($config)
    {
        $this->_config = $config;
        $this->save();
    }

    private function save()
    {
        file_put_contents($this->_path, json_encode($this->_config));
    }

    /**
     * Get attribute
     * @param $section
     * @param $attribute
     * @return null | mixed
     */
    public function getAttribute($section, $attribute)
    {
        if (isset($this->_config[$section][$attribute])) {
            return $this->_config[$section][$attribute];
        } else {
            return null;
        }
    }
}