<?php

namespace App\libs\App;

use App\App;
use App\ConfigModule;

class Model extends VarientObject
{

    public static function getModel($name)
    {
        $name = explode('_', $name);
        $module = $name[0];
        $configModule = ConfigModule::getInstance()->getConfig($module);
        if (isset($configModule['override']['models'][$name])) {
            $className = $configModule['override']['models'][$name];
        } else {
            $override = $GLOBALS['override'];
            $className = '';
            foreach ($override as $o) {
                $className = "{$o}\\{$module}\\Model";
                foreach ($name as $key => $n) {
                    if ($key == 0) {
                        continue;
                    }
                    $className .= "\\{$n}";
                }
                if (class_exists($className)) {
                    break;
                }
            }
        }

        if (App::getInstance()->moduleIsEnabled($module)) {
            return new $className;
        }

        return null;
    }
}