<?php

namespace BaseProject\Ajaxifier\Controller;

use App\App;
use App\ConfigModule;
use App\ContentTypes;
use App\libs\App\Block;
use App\libs\App\Controller;

class Index extends Controller
{

    /**
     * Ajaxifier_IndexController constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate(null);
        $this->setTemplateHeader(null);
        $this->setTemplateFooter(null);
    }

    public function indexAction()
    {
        $request = App::getRequestParams('post');
        $retour = [];

        $config = ConfigModule::getInstance()->getConfigAllModules('ajaxifier');

        $blockIsAjaxifier = [];
        foreach ($config as $key => $value) {
            foreach ($value as $k => $blockAjaxifier) {
                $blockIsAjaxifier[$k] = $blockAjaxifier['block'];
            }
        }

        foreach ($request['blocks'] as $blockId) {
            $blockName = $blockIsAjaxifier[$blockId];
            $block = Block::getBlock($blockName);
            $block->setAjaxifier(true);
            $retour[$blockId] = $block->getHtml();
        }

        $this->sendJson(json_encode($retour));
    }
}