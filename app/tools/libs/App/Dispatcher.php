<?php

namespace App\libs\App;

use App\ConfigModule;

class Dispatcher
{
    /** @var  Dispatcher */
    private static $_instance;
    private $listeners = array();

    public static function getInstance()
    {
        if (!self::$_instance) {
            self::$_instance = new Dispatcher();
        }

        return self::$_instance;
    }

    public function initListener()
    {
        $config = ConfigModule::getInstance()->getConfigAllModules('Observer');
        $override = ConfigModule::getInstance()->getConfigAllModules('override/observer');
        $listOverride = [];

        foreach ($override as $module => $events) {
            foreach ($events as $c => $class) {
                $listOverride[$c] = $class;
            }
        }

        foreach ($config as $module => $events) {
            foreach ($events as $event => $class) {
                if (isset($listOverride[$class])) {
                    $class = $listOverride[$class];
                }
                if (is_subclass_of($class, 'App\\libs\\App\\Observer')) {
                    $this->listen($event, [$class, 'notify']);
                }
            }
        }
    }

    public function listen($event, $callback)
    {
        $this->listeners[$event][] = $callback;
    }

    public function dispatch($event, $param)
    {
        if (isset($this->listeners[$event])) {
            foreach ($this->listeners[$event] as $listener) {
                call_user_func_array($listener, array($event, $param));
            }
        }
    }
}