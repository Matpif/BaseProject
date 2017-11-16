<?php

namespace App\libs\App;

use App\App;
use App\Config;
use App\ConfigModule;

class Router
{
    /**
     * @var string
     */
    protected $_currentUri;
    /**
     * @var string
     */
    protected $_module;
    /**
     * @var string
     */
    protected $_controller;
    /**
     * @var string
     */
    protected $_action;
    /**
     * @var string
     */
    protected $_rootUrl;
    /**
     * @var boolean
     */
    protected $_secure;

    /**
     * Router constructor.
     */
    public function __construct()
    {
        $this->_currentUri = App::getInstance()->getRequest()->getServerParams()['REQUEST_URI'];
        $this->_rootUrl = App::getInstance()->getRequest()->getServerParams()['SERVER_NAME'];
        $this->_secure = Config::getInstance()->getAttribute('app', 'secure');
        $this->getRoute();
    }

    /**
     * @return array
     */
    public function getRoute()
    {
        $splitUri = explode('/', $this->_currentUri);
        if ($this->_currentUri == '/') {
            $splitUri = explode('/', Config::getInstance()->getAttribute('app', 'defaultPage'));
        }

        $rewriteUriModule = ConfigModule::getInstance()->getConfigAllModules('router/rewrite_uri');

        foreach ($rewriteUriModule as $module => $rewriteUris) {
            foreach ($rewriteUris as $rewriteUri) {
                if (0 === strpos($this->_currentUri, $rewriteUri['url'])) {
                    $this->_module = $module;
                    $this->_controller = $rewriteUri['controller'];
                    $this->_action = $rewriteUri['action'];

                    return [
                        'module' => $this->_module,
                        'controller' => $this->_controller,
                        'action' => $this->_action,
                    ];
                }
            }
        }

        foreach ($splitUri as $key => $item) {
            if ($key == 1) {
                $this->_module = (isset($splitUri[$key])) ? $splitUri[$key] : null;
            }
            if ($key == 2) {
                $this->_controller = (isset($splitUri[$key])) ? $splitUri[$key] : null;
            }
            if ($key == 3) {
                if (isset($splitUri[$key])) {
                    $pos = strpos($splitUri[$key], '?');
                    if ($pos !== false) {
                        $this->_action = substr($splitUri[$key], 0, $pos);
                    } else {
                        $this->_action = $splitUri[$key];
                    }
                } else {
                    $this->_action = null;
                }
            }
            if ($key > 3) {
                if ($key % 2 == 0) {
                    $name = $item;
                } else {
                    $_GET[$name] = $item;
                }
            }
        }

        if (!$this->_controller) {
            $this->_controller = 'Index';
        }
        if (!$this->_action) {
            $this->_action = 'Index';
        }

        return [
            'module' => $this->_module,
            'controller' => $this->_controller,
            'action' => $this->_action,
        ];
    }

    /**
     * @param $module
     * @param $controller
     * @param $action
     *
     * @return string
     */
    public static function getUrlAction($module, $controller = 'Index', $action = 'index')
    {

        $configModule = ConfigModule::getInstance()->getConfig($module);
        if (isset($configModule['override']['router'][$module])) {
            $routerClass = $configModule['override']['router'][$module];
        } else {
            $routerClass = 'BaseProject\\' . $module . '\\Router\\Router';
        }

        /** @var Router $router */
        $router = new $routerClass;
        $router->setModule($module);
        $router->setController($controller);
        $router->setAction($action);
        $url = $router->getRootUrl();

        $config = ConfigModule::getInstance()->getConfig($module);
        if ($config && isset($config['router']) && isset($config['router']['rewrite_uri'])) {
            foreach ($config['router']['rewrite_uri'] as $rewriteUri) {
                if ($rewriteUri['controller'] == $controller && $rewriteUri['action'] == $action) {
                    return $url . $rewriteUri['url'];
                }
            }
        }

        if ($router->getModule()) {
            $url .= '/' . $router->getModule();
        }

        if ($router->getController()) {
            $url .= '/' . $router->getController();
        }

        if ($router->getAction()) {
            $url .= '/' . $router->getAction();
        }

        return $url;
    }

    /**
     * @return string
     */
    public function getRootUrl()
    {
        return (($this->isSecure()) ? 'https://' : 'http://') . $this->_rootUrl;
    }

    /**
     * @return bool
     */
    public function isSecure()
    {
        return $this->_secure;
    }

    /**
     * @param bool $secure
     */
    public function setSecure($secure)
    {
        $this->_secure = $secure;
    }

    /**
     * @return string
     */
    public function getModule()
    {
        return $this->_module;
    }

    /**
     * @param string $module
     */
    public function setModule($module)
    {
        $this->_module = $module;
    }

    /**
     * @return string
     */
    public function getController()
    {
        return $this->_controller;
    }

    /**
     * @param string $controller
     */
    public function setController($controller)
    {
        $this->_controller = $controller;
    }

    /**
     * @return string
     */
    public function getAction()
    {
        return $this->_action;
    }

    /**
     * @param string $action
     */
    public function setAction($action)
    {
        $this->_action = $action;
    }

    /**
     * Check if route exist
     * @return bool
     */
    public function routeExist()
    {
        if ($controller = Controller::getController($this->getControllerClassName())) {
            if (method_exists($controller, $this->getActionMethodName())) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return string
     */
    public function getControllerClassName()
    {
        if ($this->getModule() && $this->getController()) {
            $module = $this->getModule();
            $controller = $this->getController();

            $configModule = ConfigModule::getInstance()->getConfig($module);
            if (isset($configModule['override']['controllers'][$controller])) {
                $className = $configModule['override']['controllers'][$controller];
            } else {
                $override = $GLOBALS['override'];
                $className = '';
                foreach ($override as $o) {
                    $className = "{$o}\\{$module}\\Controller";
                    $name = explode('_', $this->getController());
                    foreach ($name as $n) {
                        $className .= "\\{$n}";
                    }
                    if (class_exists($className)) {
                        break;
                    }
                }
            }

            return $className;
        } else {
            if ($this->getModule()) {
                $module = $this->getModule();
                $className = "BaseProject\\{$module}\\Controller\\Index";

                return $className;
            }

            return 'App\\libs\\App\\Controller';
        }
    }

    /**
     * @return string
     */
    public function getActionMethodName()
    {
        if ($this->getAction()) {
            return $this->getAction() . 'Action';
        } else {
            return 'indexAction';
        }
    }

    /**
     * @return boolean
     */
    public function rulesAccepted()
    {
        $rules = ConfigModule::getInstance()->getConfigAllModules('router/rules');
        $returned = true;

        if (isset($rules[$this->_module])) {
            $request = App::getInstance()->getRequest();
            $uri = '/' . $this->_module . '/' . $this->_controller . '/' . $this->_action;
            $rules = $rules[$this->_module];

            foreach ($rules as $key => $rule) {
                if (!$returned) {
                    break;
                }

                $compare = (isset($request->getServerParams()[$key])) ? $request->getServerParams()[$key] : '';;

                if ($compare) {
                    foreach ($rule as $u => $accepted) {
                        if ($uri == $u) {
                            $returned = false;
                            foreach ($accepted as $a) {
                                if (strpos($compare, $a) !== false) {
                                    $returned = true;
                                    break;
                                }
                            }
                            if (!$returned) {
                                break;
                            }
                        }
                    }
                }
            }
        }

        return $returned;
    }
}