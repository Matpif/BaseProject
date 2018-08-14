<?php

namespace BaseProject\Task\Router;

class Router extends \App\libs\App\Router
{
    public static function getTaskUrl($taskCode)
    {
        return \App\libs\App\Router::getUrlAction('Task', 'Index', 'start') . '/code/' . $taskCode;
    }
}