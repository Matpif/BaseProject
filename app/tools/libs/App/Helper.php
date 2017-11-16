<?php

namespace App\libs\App;

use App\ConfigModule;

abstract class Helper extends VarientObject
{

    private static $_instance;

    public static function getInstance($helper)
    {
        $name = explode('_', $helper);
        $module = $name[0];

        $configModule = ConfigModule::getInstance()->getConfig($module);
        if (isset($configModule['override']['helpers'][$helper])) {
            $className = $configModule['override']['helpers'][$helper];
        } else {
            $override = $GLOBALS['override'];
            $className = '';
            foreach ($override as $o) {
                $className = "{$o}\\{$module}\\Helper";
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

        if (class_exists($className)) {
            if (isset(self::$_instance[$className])) {
                return self::$_instance[$className];
            } else {
                self::$_instance[$className] = new $className;

                return self::$_instance[$className];
            }
        }

        return null;
    }
}