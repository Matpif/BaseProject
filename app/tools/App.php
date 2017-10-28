<?php

namespace App;

use App\libs\App\CollectionDb;
use App\libs\App\Controller;
use App\libs\App\Router;
use App\libs\App\Session;
use BaseProject\Admin\Model\Module;
use Psr\Http\Message\ServerRequestInterface;

class App
{
    /**
     * Singleton
     * @var App
     */
    private static $_instance;
    /**
     * @var Router
     */
    private $_router;
    /**
     * @var string
     */
    private $_pathRoot;
    /**
     * @var bool
     */
    private $_cacheIsEnabled;
    /**
     * @var string
     */
    private $_languageCode;
    /**
     * @var array
     */
    private $_moduleEnabled;
    /**
     * @var ServerRequestInterface
     */
    private $_request;

    /**
     * App constructor.
     */
    public function __construct()
    {
        $session = Session::getInstance();
        $session->addData(isset($_SESSION) ? $_SESSION : []);
        $this->_pathRoot = "";
        $this->_cacheIsEnabled = Config::getInstance()->getAttribute('app', 'enabledCache');
    }

    /**
     * @param $var string get|post|cookie|files
     * @return array
     */
    public static function getRequestParams($var = null)
    {
        if ($var) {
            switch ($var) {
                case 'get':
                    return $_GET;
                case 'post':
                    return $_POST;
                case 'cookie':
                    return $_COOKIE;
                case 'files':
                    return $_FILES;
            }
        }

        return array_merge($_POST, $_GET, $_COOKIE, $_FILES);
    }

    /**
     * @return string
     */
    public static function PathRoot()
    {
        return App::getInstance()->getPathRoot();
    }

    /**
     * @return string
     */
    public function getPathRoot()
    {
        return $this->_pathRoot;
    }

    /**
     * @param string $pathRoot
     */
    public function setPathRoot($pathRoot)
    {
        $this->_pathRoot = $pathRoot;
    }

    /**
     * Singleton
     * @return App
     */
    public static function getInstance()
    {
        if (!self::$_instance) {
            self::$_instance = new App();
        }

        return self::$_instance;
    }

    public function init()
    {
        ConfigModule::getInstance();
        // TODO: Last page precedent
    }

    /**
     * @return Router
     */
    public function getRouter()
    {
        if (!$this->_router) {
            $router = new Router();
            $module = $router->getModule();
            $_module = CollectionDb::getInstanceOf('Admin_Module')->load(['module_name' => $module])->getFirstRow();
            if ($_module && $_module->getEnable()) {
                $configModule = ConfigModule::getInstance()->getConfig($module);
                if (isset($configModule['override']['router'][$module])) {
                    $classRouter = $configModule['override']['router'][$module];
                } else {
                    $classRouter = 'BaseProject\\' . $module . '\\Router\\Router';
                }
                if (class_exists($classRouter)) {
                    $this->_router = new $classRouter;
                } else {
                    $this->_router = new \BaseProject\Error\Router\Router(StatusCodes::HTTP_NOT_FOUND);
                }
            } else {
                $this->_router = new \BaseProject\Error\Router\Router(StatusCodes::HTTP_NOT_FOUND);
            }
        }

        return $this->_router;
    }

    public function httpAccepted($contentType)
    {
        return (strpos($_SERVER['HTTP_ACCEPT'], $contentType) !== false);
    }

    /**
     * @return Session
     */
    public function getSession()
    {
        return Session::getInstance();
    }

    /**
     * @return bool
     */
    public function isAllowed()
    {
        $controller = Controller::getController($this->_router->getControllerClassName());

        return $controller->isAllowed($this->_router->getAction());
    }

    public function getLanguageCode()
    {
        return $this->_languageCode;
    }

    public function setLanguageCode($language_code)
    {
        $this->_languageCode = $language_code;
    }

    /**
     * @param string $moduleName
     * @return boolean
     */
    public function moduleIsEnabled($moduleName)
    {
        if (!$this->_moduleEnabled) {
            $modules = CollectionDb::getInstanceOf('Admin_Module')->load(['enable' => 1]);
            /** @var Module $module */
            foreach ($modules as $module) {
                $this->_moduleEnabled[] = $module->getAttribute('module_name');
            }
        }

        foreach ($this->_moduleEnabled as $module) {
            if ($moduleName == $module) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return ServerRequestInterface
     */
    public function getRequest()
    {
        return $this->_request;
    }

    /**
     * @param ServerRequestInterface $request
     */
    public function setRequest($request)
    {
        $this->_request = $request;
    }

    public function getAppName()
    {
        return Config::getInstance()->getAttribute('app', 'name');
    }

    public function getAppVersion()
    {
        return Config::getInstance()->getAttribute('app', 'version');
    }

    /**
     * @return bool
     */
    public function cacheIsEnabled()
    {
        return $this->_cacheIsEnabled;
    }
}