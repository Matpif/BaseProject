<?php

namespace App\libs\App;

use App\App;
use App\Cache;
use App\ConfigModule;

class Block extends VarientObject
{
    const PATH_TEMPLATE = '/design/template';
    /**
     * @var bool
     */
    protected $_useCache;
    /**
     * @var string Cache key
     */
    protected $_key;
    /**
     * @var string
     */
    protected $_html;
    /**
     * @var string
     */
    private $_template;
    /**
     * @var Controller
     */
    private $_controller;

    /**
     * Block constructor.
     */
    public function __construct()
    {
        $this->_controller = null;
        $this->setTemplate('');
        $this->setKey([get_class($this), App::getInstance()->getLanguageCode()]);
        $this->_useCache = false;
        $this->_html = null;
    }

    /**
     * @param $template string
     */
    public function setTemplate($template)
    {
        if ($template) {
            foreach ($GLOBALS['override'] as $override) {
                $this->_template = App::PathRoot() . self::PATH_TEMPLATE . '/' . $override . $template;
                if (file_exists($this->_template)) {
                    break;
                } else {
                    $this->_template = '';
                }
            }
        } else {
            $this->_template = '';
        }
    }

    /**
     * @param $key
     */
    public function setKey($key)
    {
        $this->_key = sha1(json_encode($key));
    }

    public static function getBlock($name)
    {
        if (class_exists($name)) {
            $className = $name;
            $name = explode('\\', $name);
            $module = $name[1];
        } else {
            $name = explode('_', $name);
            $module = $name[0];
            $configModule = ConfigModule::getInstance()->getConfig($module);
            if (isset($configModule['override']['blocks'][$name])) {
                $className = $configModule['override']['blocks'][$name];
            } else {
                $override = $GLOBALS['override'];
                $className = '';
                foreach ($override as $o) {
                    $className = "{$o}\\{$module}\\Block";
                    foreach ($name as $key => $n) {
                        if ($key == 0) {
                            continue;
                        }
                        $className .= "\\{$n}";
                    }
                    if (class_exists($className)) {
                        break;
                    }
                }
            }
        }

        if (App::getInstance()->moduleIsEnabled($module)) {
            return new $className;
        }

        return new Block();
    }

    /**
     * @return string
     */
    public function getHtml()
    {
        Dispatcher::getInstance()->dispatch('block_before_get_html', $this);
        if (!$this->_html) {
            if (App::getInstance()->cacheIsEnabled() && $this->_useCache) {
                if (Cache::getInstance()->getCacheRedis()->contains($this->_key)) {
                    $this->_html = Cache::getInstance()->getCacheRedis()->fetch($this->_key);

                    return $this->_html;
                }
            }
            if (file_exists($this->_template)) {
                ob_start();
                include($this->_template);
                $this->_html = ob_get_contents();
                if (App::getInstance()->cacheIsEnabled() && $this->_useCache) {
                    Cache::getInstance()->getCacheRedis()->save($this->_key, $this->_html);
                }
                ob_end_clean();
            }
        }
        Dispatcher::getInstance()->dispatch('block_after_get_html', $this);

        return $this->_html;
    }

    /**
     * @param string $html
     */
    public function setHtml($html)
    {
        $this->_html = $html;
    }

    /**
     * @return Controller
     */
    public function getController()
    {
        return $this->_controller;
    }

    /**
     * @param Controller $controller
     */
    public function setController($controller)
    {
        $this->_controller = $controller;
    }

    /**
     * @return bool
     */
    public function cacheExist()
    {
        return Cache::getInstance()->cacheExist($this->_key);
    }

    /**
     * @param bool $useCache
     */
    public function setUseCache($useCache)
    {
        $this->_useCache = $useCache;
    }
}