<?php

namespace BaseProject\Admin\Helper;

use App\ConfigModule;
use App\libs\App\CollectionDb;
use App\libs\App\Helper;
use BaseProject\Admin\Model\Module;
use Exception;

/**
 * class Default_Admin_AdminHelper
 */
class Admin extends Helper
{

    private $_modules;

    /**
     * Enable Module and dependencies
     * @param int|Module $module
     * @throws Exception
     */
    public function enableModule($module)
    {
        if (!$module instanceof Module) {
            $module = CollectionDb::getInstanceOf('Admin_Module')->loadById($module);
        }
        $moduleName = $module->getAttribute('module_name');
        $this->_modules[] = $moduleName;
        $moduleConfig = ConfigModule::getInstance()->getConfig($moduleName);
        if (isset($moduleConfig['dependencies'])) {
            foreach ($moduleConfig['dependencies'] as $dependency) {
                if (!in_array($dependency, $this->_modules)) {
                    /** @var Module $m */
                    $m = CollectionDb::getInstanceOf('Admin_Module')->load(['module_name' => $dependency])->getFirstRow();
                    if ($m) {
                        $this->enableModule($m);
                    }
                }
            }
        }
        if ($module->getEnable() == false) {
            $module->setEnable(true);
            if (!$module->save()) {
                throw new Exception($this->__('There was a problem.'));
            }
        }
    }

    /**
     * @param int|Module $module
     * @throws Exception
     */
    public function disableModule($module)
    {
        // TODO: Revoir la fonction, elle ne prend pas en compte les modules désactivés ou réactivés
        if (!$module instanceof Module) {
            $module = CollectionDb::getInstanceOf('Admin_Module')->loadById($module);
        }
        $this->_modules[] = $module->getAttribute('module_name');

        $modulesDependencies = ConfigModule::getInstance()->getConfigAllModules('dependencies');
        foreach ($modulesDependencies as $moduleName => $dependencies) {
            foreach ($dependencies as $dependency) {
                if ($module->getAttribute('module_name') == $dependency) {
                    throw new Exception($this->__('Modules depend on this one.'));
                }
            }
        }

        $module->setEnable(0);
        if (!$module->save()) {
            throw new Exception($this->__('There was a problem.'));
        }
    }
}