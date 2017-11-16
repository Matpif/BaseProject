<?php

namespace BaseProject\Task\Helper;

use App\App;
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

    /**
     * @param string $code
     * @param boolean $background
     * @param int $schedulerId
     */
    public function runTask($code, $background = false, $schedulerId = 0)
    {
        $script = App::PathRoot() . '/script/Task/task.php';
        if (!$background) {
            $params['c'] = $code;
            $params['s'] = $schedulerId;
            $params['v'] = '';
            include $script;
        } else {
            shell_exec("php {$script} -c={$code} -s={$schedulerId} > /dev/null &");
        }
    }
}