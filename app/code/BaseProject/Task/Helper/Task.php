<?php

namespace BaseProject\Task\Helper;

use App\ConfigModule;
use App\libs\App\Helper;

class Task extends Helper
{

    public function getAllTask()
    {
        $allTasks = [];
        $config = ConfigModule::getInstance()->getConfigAllModules('Task');
        foreach ($config['Task'] as $tasks) {
            foreach ($tasks as $c => $cN) {
                $allTasks[$c] = $cN;
                break;
            }
        }

        return $allTasks;
    }
}