<?php

namespace App;

use App\libs\App\Controller;
use App\libs\App\VarientObject;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\ResponseInterface;

class Page extends VarientObject
{

    /** @var  Controller */
    private $_controller;
    /** @var  array */
    private $_afterStartBody;
    /** @var  array */
    private $_beforeEndBody;
    /** @var  array */
    private $_beforeEndHead;

    /**
     * Page constructor.
     * @param $_controller Controller
     */
    public function __construct($_controller)
    {
        $this->_controller = $_controller;
        $this->_beforeEndHead = [];
        $this->_beforeEndBody = [];
        $this->_afterStartBody = [];
    }

    /**
     * @return ResponseInterface
     */
    public function renderer()
    {
        $renderer = '';
        if (App::getInstance()->cacheIsEnabled()) {
            if (Cache::getInstance()->cacheExist($this->_controller->getKey()) && $this->_controller->isUseCache()) {
                $renderer = Cache::getInstance()->getCacheRedis()->fetch($this->_controller->getKey());
            }
        }
        if (empty($renderer)) {
            $renderer = $this->html();
            if (App::getInstance()->cacheIsEnabled() && $this->_controller->isUseCache()) {
                Cache::getInstance()->getCacheRedis()->save($this->_controller->getKey(), $renderer);
            }
        }

        return new Response(
            ($this->_controller->getHtmlStatusCode()) ? $this->_controller->getHtmlStatusCode() : 200,
            [],
            $renderer);
    }

    private function html()
    {
        $html = '<!doctype html><html>';

        $html .= $this->head();
        $html .= $this->body();

        return $html . '</html>';
    }

    private function head()
    {
        $config = ConfigModule::getInstance()->getConfigAllModules('page/before_end_head');
        foreach ($config as $module => $beforeEndHead) {
            foreach ($beforeEndHead as $beh) {
                if (class_exists($beh) && is_subclass_of($beh, 'App\\libs\\App\\Block')) {
                    $this->_beforeEndHead[] = new $beh;
                }
            }
        }

        $pathTemplateHead = '';
        foreach ($GLOBALS['override'] as $override) {
            $pathTemplateHead = App::PathRoot() . "/design/template/{$override}/page/head.phtml";
            if (file_exists($pathTemplateHead)) {
                break;
            } else {
                $pathTemplateHead = '';
            }
        }
        $head = '';
        if ($pathTemplateHead) {
            ob_start();
            include($pathTemplateHead);
            $head = ob_get_contents();
            ob_end_clean();
        }

        return $head;
    }

    private function body()
    {
        $config = ConfigModule::getInstance()->getConfigAllModules('page/after_start_body');
        foreach ($config as $module => $afterStartBody) {
            foreach ($afterStartBody as $asb) {
                if (class_exists($asb)) {
                    $this->_afterStartBody[] = new $asb;
                }
            }
        }
        $config = ConfigModule::getInstance()->getConfigAllModules('page/before_end_body');
        foreach ($config as $module => $beforeEndBody) {
            foreach ($beforeEndBody as $beb) {
                if (class_exists($beb)) {
                    $this->_beforeEndBody[] = new $beb;
                }
            }
        }

        $pathTemplateBody = '';
        foreach ($GLOBALS['override'] as $override) {
            $pathTemplateBody = App::PathRoot() . "/design/template/{$override}/page/body.phtml";
            if (file_exists($pathTemplateBody)) {
                break;
            } else {
                $pathTemplateBody = '';
            }
        }
        $body = '';
        if ($pathTemplateBody) {
            ob_start();
            include($pathTemplateBody);
            $body = ob_get_contents();
            ob_end_clean();
        }

        return $body;
    }

    /**
     * @return Controller
     */
    public function getController()
    {
        return $this->_controller;
    }

    /**
     * @return array
     */
    public function getAfterStartBody()
    {
        return $this->_afterStartBody;
    }

    /**
     * @return array
     */
    public function getBeforeEndBody()
    {
        return $this->_beforeEndBody;
    }

    /**
     * @return array
     */
    public function getBeforeEndHead()
    {
        return $this->_beforeEndHead;
    }
}