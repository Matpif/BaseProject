<?php

namespace App;

use App\libs\App\Controller;
use App\libs\App\Helper;
use App\libs\App\VarientObject;
use BaseProject\Admin\Helper\Parameter;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use MatthiasMullie\Minify;

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
        $this->addFile();
        $this->minify();
        $html = '<!doctype html><html>';

        $html .= $this->head();
        $html .= $this->body();

        return $html . '</html>';
    }

    protected function addFile() {
        $this->_controller->addCSS('/assets/css/style.css', true);
        $this->_controller->addCSS('/assets/components/bootstrap/dist/css/bootstrap-theme.min.css', true);
        $this->_controller->addCSS('/assets/components/bootstrap/dist/css/bootstrap.min.css', true);
        $this->_controller->addJS('/assets/components/bootstrap/dist/js/bootstrap.min.js', true);
        $this->_controller->addJS('/assets/components/jquery/dist/jquery.min.js', true);
    }

    protected function minify()
    {
        /** @var Parameter $parameter */
        $parameter = Helper::getInstance('Admin_Parameter');
        if ($parameter->getParameter('general/general/minify')->getValue() == '1') {

            $root = App::PathRoot();
            $cssFileName = $root . '/assets/css/min/';
            $jsFileName = $root . '/assets/js/min/';

            $hashCss = '';
            $hashJs = '';

            $cssFiles = $this->_controller->getCssFile();
            $jsFiles = $this->_controller->getJsFile();
            $this->_controller->deleteJsFile();
            $this->_controller->deleteCssFile();


            foreach ($cssFiles as $cssFile) {
                if (in_array($cssFile, $this->_controller->getCssFileUnMinify())) {
                    $this->_controller->addCSS($cssFile);
                } else {
                    $hashCss .= $cssFile.filemtime($root.$cssFile);
                }
            }

            foreach ($jsFiles as $jsFile) {
                if (in_array($jsFile, $this->_controller->getJsFileUnMinify())) {
                    $this->_controller->addJS($jsFile);
                } else {
                    $hashJs .= $jsFile.filemtime($root.$jsFile);
                }
            }

            if (!file_exists($cssFileName . md5($hashCss) . '.min.css')) {
                $minifierCSS = new Minify\CSS();
                foreach ($cssFiles as $cssFile) {
                    if (!in_array($cssFile, $this->_controller->getCssFileUnMinify())) {
                        $minifierCSS->add(file_get_contents($root . $cssFile));
                    }
                }

                if (!empty($hashCss)) {
                    if (!file_exists($cssFileName)) {
                        mkdir($cssFileName);
                    }
                    $minifierCSS->minify($cssFileName . md5($hashCss) . '.min.css');
                }
            }

            if (!file_exists($jsFileName . md5($hashJs) . '.min.js')) {
                $minifierJS = new Minify\JS();
                foreach ($jsFiles as $jsFile) {
                    if (!in_array($jsFile, $this->_controller->getJsFileUnMinify())) {
                        $minifierJS->add(file_get_contents($root . $jsFile));
                    }
                }

                if (!empty($hashJs)) {
                    if (!file_exists($jsFileName)) {
                        mkdir($jsFileName);
                    }
                    $minifierJS->minify($jsFileName . md5($hashJs) . '.min.js');
                }
            }

            if (!empty($hashCss)) {
                $this->_controller->addCSS('/assets/css/min/' . md5($hashCss) . '.min.css');
            }
            if (!empty($hashJs)) {
                $this->_controller->addJS('/assets/js/min/' . md5($hashJs) . '.min.js');
            }
        }
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