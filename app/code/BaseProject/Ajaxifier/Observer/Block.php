<?php

namespace BaseProject\Ajaxifier\Observer;

use App\App;
use App\ConfigModule;
use App\libs\App\Observer;

class Block implements Observer
{

    /**
     * @param $eventName
     * @param \App\libs\App\Block $block
     */
    public static function notify($eventName, $block)
    {
        if ($block->getAjaxifier() !== true) {
            $name = get_class($block);
            $ex = explode("\\", $name);
            $name2 = $ex[1] . '_' . $ex[count($ex) - 1];
            $idBlock = '';
            $config = ConfigModule::getInstance()->getConfigAllModules('ajaxifier');

            $controller = App::getInstance()->getRouter()->getController();
            $action = App::getInstance()->getRouter()->getAction();
            $module = App::getInstance()->getRouter()->getModule();

            $blockIsAjaxifier = false;
            $autoload = true;
            $loading = false;
            foreach ($config as $key => $value) {
                foreach ($value as $k => $blockAjaxifier) {
                    if (($blockAjaxifier['module'] == "" || $blockAjaxifier['module'] == $module)
                        && ($blockAjaxifier['controller'] == "" || $blockAjaxifier['controller'] == $controller)
                        && ($blockAjaxifier['action'] == "" || $blockAjaxifier['action'] == $action)
                        && ($blockAjaxifier['block'] == $name || $blockAjaxifier['block'] == $name2)) {
                        $blockIsAjaxifier = true;
                        $idBlock = $k;

                        if (isset($blockAjaxifier['autoload'])) {
                            $autoload = $blockAjaxifier['autoload'];
                        }
                        if (isset($blockAjaxifier['withLoader'])) {
                            $loading = $blockAjaxifier['withLoader'];
                        }

                        break;
                    }
                }
            }

            if ($blockIsAjaxifier) {
                $options = $block->getData();

                $html = "<div class=\"ajaxifier\" data-block-id=\"{$idBlock}\" data-block-autoload=\"" . (($autoload) ? 1 : 0) . "\" data-block-loading=\"" . (($loading) ? 1 : 0) . "\"";
                if ($options) {
                    foreach ($options as $option => $value) {
                        $html .= " data-" . $option . "=\"" . $value . "\"";
                    }
                }
                $html .= "></div>";

                $block->setHtml($html);
            }
        }
    }
}