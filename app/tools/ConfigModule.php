<?php

namespace App;

use App\libs\App\CollectionDb;
use App\libs\App\Dispatcher;

class ConfigModule
{

    const PATH_FILE_MODULE = '/etc/modules.json';
    const PATH_CODE_MODULE = '/code';
    const PATH_CACHE = '/var/cache';
    const PATH_CACHE_CONFIG = '/var/cache/config.json';
    /** @var  ConfigModule */
    private static $_instance;
    /** @var  array */
    private $_config;
    /** @var  array */
    private $_modules;

    /**
     * ConfigModule constructor.
     */
    public function __construct()
    {
        $this->_config = [];
        if (file_exists(App::PathRoot() . self::PATH_CACHE_CONFIG)) {
            $this->_config = json_decode(file_get_contents(App::PathRoot() . self::PATH_CACHE_CONFIG), true);
        } else {
            $this->refreshCache();
            $this->removeModuleDisabled();
        }
    }

    private function refreshCache()
    {
        Dispatcher::getInstance()->dispatch('before_refresh_cache', $this);
        if (!file_exists(App::PathRoot() . self::PATH_CACHE)) {
            mkdir(App::PathRoot() . self::PATH_CACHE, 0777, true);
        }
        $modules = $this->getModules();
        $config = [];

        foreach ($modules as $module) {
            foreach ($GLOBALS['override'] as $override) {
                $pathConfigModule = App::PathRoot() . self::PATH_CODE_MODULE . "/{$override}/" . $module . '/etc/config.json';
                if (file_exists($pathConfigModule)) {
                    if (isset($config[$module])) {
                        $config[$module] = array_merge_recursive($config[$module],
                            json_decode(file_get_contents($pathConfigModule), true));
                    } else {
                        $config[$module] = json_decode(file_get_contents($pathConfigModule), true);
                    }
                }
            }
            $this->_config = array_merge($this->_config, $config);
        }
        file_put_contents(App::PathRoot() . self::PATH_CACHE_CONFIG, json_encode($this->_config));
        Dispatcher::getInstance()->dispatch('after_refresh_cache', $this);
    }

    /**
     * @return array
     */
    public function getModules()
    {
        if (!$this->_modules) {
            $this->_modules = json_decode(file_get_contents(App::PathRoot() . self::PATH_FILE_MODULE), true);
        }

        return $this->_modules;
    }

    private function removeModuleDisabled()
    {
        $config = json_decode(file_get_contents(App::PathRoot() . self::PATH_CACHE_CONFIG), true);
        $modules = CollectionDb::getInstanceOf('Admin_Module')->load(['enable' => 1]);
        $_config = [];
        foreach ($modules as $module) {
            $_config[$module->getAttribute('module_name')] = $config[$module->getAttribute('module_name')];
        }
        $this->_config = $_config;
        file_put_contents(App::PathRoot() . self::PATH_CACHE_CONFIG, json_encode($this->_config));
    }

    /**
     * @return ConfigModule
     */
    public static function getInstance()
    {
        if (self::$_instance == null) {
            self::$_instance = new ConfigModule();
        }

        return self::$_instance;
    }

    /**
     * @param null|string $configPath model=> router/rewrite_uri
     * @return mixed
     */
    public function getConfigAllModules($configPath = null)
    {
        if ($configPath) {
            $configPath = explode('/', $configPath);
            $_config = [];
            foreach ($this->_config as $module => $config) {
                $_config = array_merge($_config, $this->getValueArray($config, $configPath, 0, $module));
            }

            return $_config;
        } else {
            return $this->getConfig();
        }
    }

    /**
     * @param $array array
     * @param $arrayKey array
     * @param $key int
     * @param $module string
     * @return mixed
     */
    private function getValueArray($array, $arrayKey, $key, $module)
    {
        if (count($arrayKey) != $key + 1) {
            if (isset($array[$arrayKey[$key]])) {
                return $this->getValueArray($array[$arrayKey[$key]], $arrayKey, $key + 1, $module);
            } else {
                return [];
            }
        } else {
            $value = [];
            if (isset($array[$arrayKey[$key]])) {
                $value[$module] = $array[$arrayKey[$key]];
            }

            return $value;
        }
    }

    /**
     * @param $module
     * @return array
     */
    public function getConfig($module = null)
    {
        if ($module) {
            if (isset ($this->_config[$module])) {
                return $this->_config[$module];
            } else {
                $config = [];
                foreach ($GLOBALS['override'] as $override) {
                    $pathConfigModule = App::PathRoot() . self::PATH_CODE_MODULE . "/{$override}/" . $module . '/etc/config.json';
                    if (file_exists($pathConfigModule)) {
                        $config = array_merge_recursive($config,
                            json_decode(file_get_contents($pathConfigModule), true));
                    }
                }

                return $config;
            }
        } else {
            return $this->_config;
        }
    }
}