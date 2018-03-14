<?php

namespace BaseProject\Admin\Helper;

use App\ConfigModule;
use App\libs\App\Block;
use App\libs\App\Collection;
use App\libs\App\CollectionDb;
use App\libs\App\Helper;
use App\libs\App\Model;
use \Exception;

/**
 * Class Parameter
 * @package BaseProject\Admin\Helper
 */
class Parameter extends Helper
{
    private $_parametersConfig;

    /**
     * @param $name 'group/section/paramName'
     * @return \BaseProject\Admin\Model\Parameter
     * @throws Exception
     */
    public function getParameter($name) {

        $parameter = Collection::getInstanceOf('Admin_Parameter')->loadById($name);
        if ($parameter) return $parameter;

        $aName = explode('/', $name);
        if (count($aName) === 3) {
            $parametersConfig = $this->getParametersConfig();

            if (isset($parametersConfig['groups'][$aName[0]]['sections'][$aName[1]]['parameters'][$aName[2]])) {
                /** @var \BaseProject\Admin\Model\Parameter $parameter */
                $parameter = Model::getModel('Admin_Parameter');
                $parameter->setInsert(true);
                $parameter->setName($name);
                $parameter->setType($parametersConfig['groups'][$aName[0]]['sections'][$aName[1]]['parameters'][$aName[2]]['type']);
                if (isset($parametersConfig['groups'][$aName[0]]['sections'][$aName[1]]['parameters'][$aName[2]]['defaultValue'])) {
                    $parameter->setValue($parametersConfig['groups'][$aName[0]]['sections'][$aName[1]]['parameters'][$aName[2]]['defaultValue']);
                } else {
                    $parameter->setValue(null);
                }
                $parameter->save();
                return $parameter;
            }
        }
        return null;
    }

    /**
     * @param $name
     * @return string
     * @throws Exception
     */
    public function getHtmlParameter($name) {
        $renderer = '';
        $aName = explode('/', $name);
        $parametersConfig = $this->getParametersConfig();
        if (count($aName) === 3) {
            if (isset($parametersConfig['groups'][$aName[0]]['sections'][$aName[1]]['parameters'][$aName[2]])) {
                /** @var \BaseProject\Admin\Model\Parameter $parameter */
                $parameter = $this->getParameter($name);
                /** @var \BaseProject\Admin\Block\Parameter $block */
                $block = Block::getBlock('Admin_Parameter');
                $block->setValue($parameter->getValue());
                $block->setType($parameter->getType());
                if ($parameter->getType() === \BaseProject\Admin\Model\Parameter::TYPE_SELECT) {
                    if (isset($parametersConfig['groups'][$aName[0]]['sections'][$aName[1]]['parameters'][$aName[2]]['values'])) {
                        $block->setValues($parametersConfig['groups'][$aName[0]]['sections'][$aName[1]]['parameters'][$aName[2]]['values']);
                    } else if (isset($parametersConfig['groups'][$aName[0]]['sections'][$aName[1]]['parameters'][$aName[2]]['collection'])) {
                        $collection = CollectionDb::getInstanceOf($parametersConfig['groups'][$aName[0]]['sections'][$aName[1]]['parameters'][$aName[2]]['collection']);
                        $values = call_user_func([$collection, $parametersConfig['groups'][$aName[0]]['sections'][$aName[1]]['parameters'][$aName[2]]['action']]);
                        $block->setValues($values);
                    }
                }
                $block->setName($name);
                $block->setLabel($parametersConfig['groups'][$aName[0]]['sections'][$aName[1]]['parameters'][$aName[2]]['label']);
                $renderer = $block->getHtml();
            }
        }
        return $renderer;
    }

    public function getParametersConfig() {
        if (!$this->_parametersConfig) {
            $this->_parametersConfig = [];
            $config = ConfigModule::getInstance()->getConfigAllModules('parameter');
            foreach ($config as $module => $params) {
                $this->_parametersConfig = array_merge_recursive($this->_parametersConfig, $params);
            }
        }
        return $this->_parametersConfig;
    }
}