<?php

namespace App\libs\App;

use App\App;
use App\Cache;
use App\Config;
use App\ConfigModule;
use BaseProject\Admin\Helper\Parameter;
use GuzzleHttp\Psr7\Response;

class Controller extends VarientObject
{
    const PATH_TEMPLATE = '/design/template';
    private static $_instance;
    /**
     * @var String
     */
    protected $_appname;
    /**
     * @var string
     */
    protected $_title;
    /**
     * @var string
     */
    protected $_page;
    /**
     * @var string eq: fr_FR
     */
    protected $_language;
    /** @var  string */
    protected $_controllerName;
    /** @var  string */
    protected $_moduleName;
    /**
     * @var string Cache key
     */
    protected $_key;
    /**
     * @var int
     */
    protected $_htmlStatus;
    /**
     * @var string
     */
    private $_header;
    /**
     * @var string
     */
    private $_footer;
    /**
     * @var string
     */
    private $_imageUrl;
    /**
     * Path of template
     * @var string
     */
    private $_template;
    /**
     * @var array
     */
    private $_jsFile;
    /**
     * @var array
     */
    private $_cssFile;

    /**
     * @var array
     */
    private $_jsFileUnMinify;
    /**
     * @var array
     */
    private $_cssFileUnMinify;
    /**
     * @var array
     */
    private $_beforeEndHead;
    /**
     * @var array
     */
    private $_beforeEndBody;
    /**
     * @var array
     */
    private $_afterStartBody;
    /**
     * @var bool
     */
    private $_useCache;
    /**
     * @var string
     */
    private $_urlReferer;

    function __construct()
    {
        /** @var Parameter $parameterHelper */
        $parameterHelper = Helper::getInstance('Admin_Parameter');
        $this->_appname = $parameterHelper->getParameter('general/general/appName')->getValue();
        $this->_imageUrl = $parameterHelper->getParameter('general/general/pathImage')->getValue();
        $this->setTemplate('/default.phtml');
        $this->setTemplateHeader('/header.phtml');
        $this->setTemplateFooter('/footer.phtml');
        $this->_title = 'DefaultController';
        $this->_jsFile = [];
        $this->_cssFile = [];
        $this->_jsFileUnMinify = [];
        $this->_cssFileUnMinify = [];
        $this->_beforeEndHead = [];
        $this->_beforeEndBody = [];
        $this->_afterStartBody = [];
        $this->_controllerName = substr(get_class($this), strrpos(get_class($this), '\\') + 1,
            strlen(get_class($this)));
        if (count($explode = explode('\\', get_class($this))) > 0) {
            $this->_moduleName = $explode[1];
        } else {
            $this->_moduleName = '';
        }
        $this->setKey([
            App::getInstance()->getRequest()->getServerParams()['REQUEST_URI'],
            App::getInstance()->getLanguageCode()
        ]);
        $this->_useCache = false;
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
     * @param $template
     */
    protected function setTemplateHeader($template)
    {
        if ($template) {
            foreach ($GLOBALS['override'] as $override) {
                $this->_header = App::PathRoot() . self::PATH_TEMPLATE . '/' . $override . $template;
                if (file_exists($this->_header)) {
                    break;
                } else {
                    $this->_header = '';
                }
            }
        } else {
            $this->_header = '';
        }
    }

    /**
     * @param $template
     */
    protected function setTemplateFooter($template)
    {
        if ($template) {
            foreach ($GLOBALS['override'] as $override) {
                $this->_footer = App::PathRoot() . self::PATH_TEMPLATE . '/' . $override . $template;
                if (file_exists($this->_footer)) {
                    break;
                } else {
                    $this->_footer = '';
                }
            }
        } else {
            $this->_footer = '';
        }
    }

    /**
     * @param $controller
     * @return Controller
     */
    public static function getController($controller)
    {
        if (class_exists($controller)) {
            if (!isset(self::$_instance[$controller]) || self::$_instance[$controller] == null) {
                self::$_instance[$controller] = new $controller;
            }
        } else {
            if (!isset(self::$_instance[$controller]) || self::$_instance[$controller] == null) {
                $name = explode('_', $controller);
                $module = $name[0];
                $configModule = ConfigModule::getInstance()->getConfig($module);
                if (isset($configModule['override']['controllers'][$controller])) {
                    $className = $configModule['override']['controllers'][$controller];
                } else {
                    $className = "BaseProject\\{$module}\\Controller";
                    foreach ($name as $key => $n) {
                        if ($key == 0) {
                            continue;
                        }
                        $className .= "\\{$n}";
                    }
                }
                if (class_exists($className)) {
                    self::$_instance[$controller] = new $className;
                } else {
                    return null;
                }
            }
        }

        return self::$_instance[$controller];
    }

    public function indexAction()
    {
    }

    /**
     * @return string
     */
    public function getHtml()
    {
        $returned = '';
        if (file_exists($this->_template)) {
            ob_start();
            include($this->_template);
            $returned = ob_get_contents();
            ob_end_clean();
        }

        return $returned;
    }

    /**
     * @param $json (json_encode string)
     * @param bool|int $status
     */
    public function sendJson($json, $status = false)
    {
        $response = (new Response(($status) ? $status : 200,
            ['Content-Type' => 'application/json'],
            $json));

        \Http\Response\send($response);
        exit;
    }

    /**
     * @return string
     */
    public function getHeader()
    {
        $returned = '';
        if ($this->_header && file_exists($this->_header)) {
            ob_start();
            include($this->_header);
            $returned = ob_get_contents();
            ob_end_clean();
        }

        return $returned;
    }

    /**
     * @return string
     */
    public function getFooter()
    {
        $returned = '';
        if ($this->_footer && file_exists($this->_footer)) {
            ob_start();
            include($this->_footer);
            $returned = ob_get_contents();
            ob_end_clean();
        }

        return $returned;
    }

    public function setHtmlStatusCode($status)
    {
        $this->_htmlStatus = $status;
    }

    public function getHtmlStatusCode()
    {
        return $this->_htmlStatus;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->_title;
    }

    /**
     * @param string $title
     */
    public function setTitle($title)
    {
        $this->_title = $title;
    }

    /**
     * @return string
     */
    public function getPage()
    {
        return $this->_page;
    }

    /**
     * @param $action string
     * @return string
     */
    public function getUrlAction($action)
    {
        return Router::getUrlAction($this->_moduleName, $this->_controllerName, $action);
    }

    /**
     * @param $file string
     * @return string
     */
    public function getUrlFile($file)
    {
        return App::getInstance()->getRouter()->getRootUrl() . $file;
    }

    /**
     * @param $image string
     * @return string
     */
    public function getUrlImage($image)
    {
        return App::getInstance()->getRouter()->getRootUrl() . $this->_imageUrl . $image;
    }

    /**
     * @return string
     */
    public function getControllerName()
    {
        return $this->_controllerName;
    }

    /**
     * @param string|Controller|null $url
     */
    public function redirect($url = null)
    {
        if ($url instanceof Controller) {
            $location = $url->getUrl();
        } else {
            if (empty($url)) {
                $location = '/';
            } else {
                $location = $url;
            }
        }

        if ($this->getUrlReferer()) {
            $location .= ((strpos($location,
                        '?') !== false) ? '&' : '?') . 'url_referer=' . urlencode($this->getUrlReferer());
        }

        $response = (new Response())
            ->withStatus(302)
            ->withHeader('Location', $location);

        \Http\Response\send($response);
        exit;
    }

    /**
     * @return String
     */
    public function getUrl()
    {
        return Router::getUrlAction($this->_moduleName, $this->_controllerName, '');
    }

    /**
     * @return string
     */
    public function getUrlReferer()
    {
        return $this->_urlReferer;
    }

    /**
     * @param string $urlReferer
     */
    public function setUrlReferer($urlReferer)
    {
        $this->_urlReferer = $urlReferer;
    }

    /**
     * @param string $jsFile
     * @param bool $beginningArray default false
     * @param bool $minify default true
     */
    public function addJS($jsFile, $beginningArray = false, $minify = true)
    {
        if ($beginningArray) {
            array_unshift($this->_jsFile, $jsFile);
        } else {
            $this->_jsFile[] = $jsFile;
        }

        if (!$minify) {
            $this->_jsFileUnMinify[] = $jsFile;
        }
    }

    /**
     * @param string $cssFile
     * @param bool $beginningArray default false
     * @param bool $minify default true
     */
    public function addCSS($cssFile, $beginningArray = false, $minify = true)
    {
        if ($beginningArray) {
            array_unshift($this->_cssFile, $cssFile);
        } else {
            $this->_cssFile[] = $cssFile;
        }

        if (!$minify) {
            $this->_cssFileUnMinify[] = $cssFile;
        }
    }

    /**
     * @return array
     */
    public function getJsFile()
    {
        return $this->_jsFile;
    }

    /**
     * @return array
     */
    public function getCssFile()
    {
        return $this->_cssFile;
    }

    /**
     * @return array
     */
    public function getBeforeEndHead()
    {
        return $this->_beforeEndHead;
    }

    /**
     * @param $html
     */
    protected function setBeforeEndHead($html)
    {
        $this->_beforeEndHead[] = $html;
    }

    /**
     * @return array
     */
    public function getBeforeEndBody()
    {
        return $this->_beforeEndBody;
    }

    /**
     * @param $html
     */
    protected function setBeforeEndBody($html)
    {
        $this->_beforeEndBody[] = $html;
    }

    /**
     * @return array
     */
    public function getAfterStartBody()
    {
        return $this->_afterStartBody;
    }

    /**
     * @param $html
     */
    protected function setAfterStartBody($html)
    {
        $this->_afterStartBody[] = $html;
    }

    /**
     * @return bool
     */
    public function isUseCache()
    {
        return $this->_useCache;
    }

    /**
     * @param bool $useCache
     */
    public function setUseCache($useCache)
    {
        $this->_useCache = $useCache;
    }

    /**
     * @return string
     */
    public function getKey()
    {
        return $this->_key;
    }

    /**
     * @param $key
     */
    public function setKey($key)
    {
        $this->_key = sha1(json_encode($key));
    }

    /**
     * @return bool
     */
    public function cacheExist()
    {
        return ($this->_useCache) ? Cache::getInstance()->cacheExist($this->_key) : false;
    }

    /**
     * @param string $action Is name of method action
     * @return bool
     */
    public function isAllowed($action = null)
    {
        return true;
    }

    public function deleteJsFile()
    {
        $this->_jsFile = [];
    }

    public function deleteCssFile()
    {
        $this->_cssFile = [];
    }

    /**
     * @return array
     */
    public function getJsFileUnMinify()
    {
        return $this->_jsFileUnMinify;
    }

    /**
     * @return array
     */
    public function getCssFileUnMinify()
    {
        return $this->_cssFileUnMinify;
    }
}
