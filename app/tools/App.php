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
     * @var bool
     */
    private $_developerModeIsEnabled;

    /**
     * App constructor.
     */
    public function __construct()
    {
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

    public function getBuildVersion()
    {
        exec('git rev-list HEAD | wc -l', $buildNumber);
        exec('git rev-parse --abbrev-ref HEAD', $branch);
        return isset($buildNumber[0], $branch[0]) ? $branch[0] . '-' . $buildNumber[0] : null;
    }

    /**
     * @return bool
     */
    public function cacheIsEnabled()
    {
        return ($this->_cacheIsEnabled && !$this->developerModeIsEnabled());
    }

    /**
     * @return bool
     */
    public function developerModeIsEnabled()
    {
        if (is_null($this->_developerModeIsEnabled)) {
            $parameterHelper = Helper::getInstance('Admin_Parameter');
            $this->_developerModeIsEnabled = $parameterHelper->getParameter('developer/developer/enable')->getValue() == 1;
        }
        return $this->_developerModeIsEnabled;
    }

    public function run()
    {
        $this->_pathRoot = preg_replace('/\/public$/', '/app', $_SERVER['DOCUMENT_ROOT']);
        $this->install();
        $this->maintenance();
        $this->setRequest(ServerRequest::fromGlobals());

        $this->init();

        Dispatcher::getInstance()->initListener();
        $this->initTranslate();
        $router = $this->getRouter();

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
            $ipsAllowed = explode(',', Helper::getInstance('Admin_Parameter')->getParameter('maintenance/general/ipAllowed')->getValue());
            $isAllowed = false;
            $myIp = $_SERVER['REMOTE_ADDR'];
            foreach ($ipsAllowed as $ip) {
                if ($myIp == $ip) {
                    $isAllowed = true;
                    break;
                }
            }

            if (!$isAllowed) {
                $this->setLocale();
                $params['maintenance'] = 1;
                include $this->getPathRoot() . '/../app.php';
                exit(0);
            }
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
        if (php_sapi_name() != 'cli') {
            Session::getInstance()->startSession();
        }
        $this->setLocale();
        ConfigModule::getInstance();
        $this->debug();
    }

    private function setLocale()
    {
        $acceptLanguage = explode(',',
            (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) ? $_SERVER['HTTP_ACCEPT_LANGUAGE'] : '');
        $language = ((isset($acceptLanguage[0])) ? $acceptLanguage[0] : 'fr_FR');
        if (strlen($language) == 2) {
            $language = $language . '_' . strtoupper($language);
        }
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
        $parameterHelper = Helper::getInstance('Admin_Parameter');
        $pathFileLog = $parameterHelper->getParameter('developer/developer/pathFileLog')->getValue();
        $logErrors = $parameterHelper->getParameter('developer/developer/logErrors')->getValue();

        /** Debug mode (depuis le Config) */
        if (Config::getInstance()->getAttribute('app', 'debug') == true || $this->developerModeIsEnabled()) {
            ini_set('display_errors', 'on');
            ini_set('log_errors', $logErrors);
            if ($pathFileLog && $logErrors) {
                ini_set('log_errors', $pathFileLog);
            }
            error_reporting(E_ALL);
        } else {
            ini_set('display_errors', 'off');
            ini_set('log_errors', $logErrors);
            if ($pathFileLog) {
                ini_set('log_errors', $pathFileLog);
            }
            error_reporting(E_ALL & ~E_DEPRECATED);
        }
    }

    private function initTranslate()
    {
        $_modules = CollectionDb::getInstanceOf('Admin_Module')->load(['enable' => 1]);
        if (!$this->developerModeIsEnabled()) {
            $cache = Zend_Cache::factory(
                'Core'
                , 'File'
                , array(
                    'caching' => true
                ,
                    'lifetime' => 900
                ,
                    'automatic_serialization' => true
                ,
                    'automatic_cleaning_factor' => 20
                ,
                    'cache_id_prefix' => 'Translate'
                )
                , array(
                    'hashed_directory_level' => 0
                ,
                    'cache_dir' => $this->getPathRoot() . '/var/cache'
                )
            );
            Zend_Translate::setCache($cache);
        }
        /** @var Module $module */
        foreach ($_modules as $module) {
            $moduleName = $module->getAttribute("module_name");
            $fileName = $this->getPathRoot() . '/locale/' . $this->getLanguageCode() . '/' . $moduleName . '.csv';
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
                    $this->_router = Router::getInstance($classRouter);
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
        if (!$moduleName) {
            $moduleName = $this->getRouter()->getModule();
        }

        if (isset($this->_translates[$moduleName])) {
            return $this->_translates[$moduleName];
        } else {
            return null;
        }
    }

    /**
     * @return array
     */
    public function getTranslates()
    {
        return $this->_translates;
    }
}