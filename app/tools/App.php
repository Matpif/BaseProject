<?php

namespace App;

use App\libs\App\CollectionDb;
use App\libs\App\Controller;
use App\libs\App\Dispatcher;
use App\libs\App\Helper;
use App\libs\App\Router;
use App\libs\App\Session;
use BaseProject\Admin\Helper\Parameter;
use BaseProject\Admin\Model\Module;
use BaseProject\Login\Helper\Login;
use GuzzleHttp\Psr7\ServerRequest;
use Psr\Http\Message\ServerRequestInterface;
use Zend_Cache;
use Zend_Translate;

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
     * @var  string
     */
    private $_previousUri;
    /**
     * @var string
     */
    private $_appName;
    /**
     * @var array
     */
    private $_translates;

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

    /**
     * @return string
     */
    public function getPreviousUri()
    {
        return $this->_previousUri;
    }

    public function httpAccepted($contentType)
    {
        return (strpos($this->getRequest()->getServerParams()['HTTP_ACCEPT'], $contentType) !== false);
    }

    /**
     * @return ServerRequestInterface
     */
    public function getRequest()
    {
        return $this->_request;
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

    public function getAppName()
    {
        if (!$this->_appName) {
            /** @var Parameter $parameterHelper */
            $parameterHelper = Helper::getInstance('Admin_Parameter');
            $this->_appName = $parameterHelper->getParameter('general/general/appName')->getValue();
        }
        return $this->_appName;
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

    public function run()
    {
        $this->_pathRoot = preg_replace('/\/public$/', '/app', $_SERVER['DOCUMENT_ROOT']);
        $this->install();
        $this->maintenance();
        $this->setRequest(ServerRequest::fromGlobals());

        $this->init();

        Dispatcher::getInstance()->initListener();
        $router = $this->getRouter();
        $this->initTranslate();

        if (!$router->routeExist()) {
            /** @var \BaseProject\Error\Controller\Error $_controller */
            $_controller = Controller::getController('Error_Error');
            $_controller->error404Action();
        } else {
            if (!$router->rulesAccepted()) {
                /** @var \BaseProject\Error\Controller\Error $_controller */
                $_controller = Controller::getController('Error_Error');
                $_controller->error415Action();
            } else {
                if ($this->isAllowed()) {
                    $controllerName = $router->getControllerClassName();
                    /**
                     * @var $_controller Controller
                     */
                    $_controller = Controller::getController($controllerName);
                    if ($_controller) {
                        $_action = $router->getActionMethodName();
                        if (method_exists($_controller, $_action)) {
                            call_user_func(array($_controller, $_action));
                        }
                    }
                } else {
                    if ($this->getSession()->getUser()) {
                        /** @var \BaseProject\Error\Controller\Error $_controller */
                        $_controller = Controller::getController('Error_Error');
                        $_controller->error403Action();
                    } else {
                        /** @var Login $loginHelper */
                        $_controller = Controller::getController('Login_Index');
                        $_controller->setUrlReferer($this->getRequest()->getServerParams()['SCRIPT_URL']);
                        $loginHelper = Helper::getInstance('Login_Login');
                        $_controller->redirect($loginHelper->getUrlLogin());
                    }
                }
            }
        }

        $page = new Page($_controller);
        \Http\Response\send($page->renderer());
    }

    private function install()
    {
        if (Config::getInstance()->getAttribute('app', 'installed') != 1) {
            $params['install'] = 1;
            include $this->getPathRoot() . '/../app.php';
            exit(0);
        }
    }

    private function maintenance()
    {
        if (Config::getInstance()->getAttribute('app', 'maintenance') != 0) {
            $params['maintenance'] = 1;
            include $this->getPathRoot() . '/../app.php';
            exit(0);
        }
    }

    /**
     * @param ServerRequestInterface $request
     */
    private function setRequest($request)
    {
        $this->_request = $request;
    }

    public function init()
    {
        $this->setLocale();
        ConfigModule::getInstance();
        $this->debug();
    }

    private function setLocale()
    {
        $acceptLanguage = explode(',',
            (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) ? $_SERVER['HTTP_ACCEPT_LANGUAGE'] : '');
        $language = ((isset($acceptLanguage[0])) ? $acceptLanguage[0] : 'fr_FR');
        $l = str_replace('-', '_', $language) . '.UTF-8';
        putenv("LANG=" . $l);
        setlocale(LC_MESSAGES, $l);
        $this->setLanguageCode(str_replace('-', '_', $language));
    }

    private function setLanguageCode($language_code)
    {
        $this->_languageCode = $language_code;
    }

    private function debug()
    {
        /** Debug mode (depuis le Config) */
        if (Config::getInstance()->getAttribute('app', 'debug') == true) {
            // DEBUG MODE ON
            error_reporting(E_ALL);
            ini_set('display_errors', 'on');
            // DEBUG MODE ON
        } else {
            // DEBUG MODE OFF
            ini_set('display_errors', 'off');
            // DEBUG MODE OFF
        }
    }

    /**
     * @return Router
     */
    public function getRouter()
    {
        if (!$this->_router) {
            $router = new Router();
            $router->getRoute();
            $module = $router->getModule();
            /** @var Module $_module */
            $_module = CollectionDb::getInstanceOf('Admin_Module')->load(['module_name' => $module])->getFirstRow();
            if ($_module && $_module->getEnable()) {
                $configModule = ConfigModule::getInstance()->getConfig($module);
                if (isset($configModule['override']['router'][$module])) {
                    $classRouter = $configModule['override']['router'][$module];
                } else {
                    $classRouter = $_module->getProject() . '\\' . $module . '\\Router\\Router';
                }
                if (class_exists($classRouter)) {
                    $this->_router = new $classRouter;
                } else {
                    $this->_router = new \BaseProject\Error\Router\Router(StatusCodes::HTTP_NOT_FOUND);
                }
            } else {
                $this->_router = new \BaseProject\Error\Router\Router(StatusCodes::HTTP_NOT_FOUND);
            }
            $this->_router->setRouter($router);
        }

        return $this->_router;
    }

    private function initTranslate()
    {
        $_modules = CollectionDb::getInstanceOf('Admin_Module')->load(['enable' => 1]);
        /** @var Module $module */
        foreach ($_modules as $module) {
//            $moduleName = $this->getRouter()->getModule();
            $moduleName = $module->getAttribute("module_name");
            $fileName = $this->getPathRoot() . '/locale/' . $this->getLanguageCode() . '/' . $moduleName . '.csv';
//            $cache = Zend_Cache::factory('Core', 'File');
//            Zend_Translate::setCache($cache);
            if (file_exists($fileName)) {
                $this->_translates[$moduleName] = new Zend_Translate(
                    'Zend_Translate_Adapter_Csv',
                    $fileName,
                    $this->getLanguageCode()
                    , array('delimiter' => ',', 'enclosure' => '"'));
            }
        }
    }

    public function getLanguageCode()
    {
        return $this->_languageCode;
    }

    /**
     * @return bool
     */
    private function isAllowed()
    {
        $controller = Controller::getController($this->_router->getControllerClassName());

        return $controller->isAllowed($this->_router->getAction());
    }

    /**
     * @return Session
     */
    public function getSession()
    {
        return Session::getInstance();
    }

    /**
     * @param $moduleName
     * @return Zend_Translate|null
     */
    public function getTranslate($moduleName = null)
    {
        if (!$moduleName)
            $moduleName = $this->getRouter()->getModule();

        if (isset($this->_translates[$moduleName]))
            return $this->_translates[$moduleName];
        else
            return null;
    }
}