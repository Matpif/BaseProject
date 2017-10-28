<?php
include_once __DIR__ . '/../../vendor/autoload.php';

use App\App;
use App\ConfigModule;
use App\libs\App\CollectionDb;

if (php_sapi_name() == 'cli') {
    App::getInstance()->setPathRoot(__DIR__ . '/..');
    $params = getopt('c::v', ['code::', 'help', 'list']);
}

function showHelp()
{
    echo (php_sapi_name() != 'cli') ? '<pre>' : '';
    echo "task.php [options]\n";
    echo "  --help      show help\n";
    echo "  --list      show list of task\n";
    echo "  --code|-c   task code to execute\n\n";
    echo "  -v          show message\n\n";
    echo (php_sapi_name() != 'cli') ? '</pre>' : '';
    exit;
}

if (isset($params['list'])) {
    echo (php_sapi_name() != 'cli') ? '<pre>' : '';
    $config = ConfigModule::getInstance()->getConfigAllModules('Task');
    echo "List of tasks :\n";
    foreach ($config['Task'] as $tasks) {
        foreach ($tasks as $code => $className) {
            echo "  " . $code . ' - ' . $className . "\n";
        }
    }
    echo "\n";
    echo (php_sapi_name() != 'cli') ? '</pre>' : '';
    exit;
} else {
    if (isset($params['help'])) {
        showHelp();
    }
}

/** @var string|bool $code */
$code = isset($params['c']) ? $params['c'] : (isset($params['code']) ? $params['code'] : false);

if ($code) {
    $className = false;
    $label = '';
    $config = ConfigModule::getInstance()->getConfigAllModules('Task');
    foreach ($config['Task'] as $tasks) {
        foreach ($tasks as $c => $cN) {
            if ($c == $code) {
                $className = $cN['className'];
                $label = $cN['label'];
                break;
            }
        }
    }

    if ($className) {
        $v = isset($params['v']);
        /** @var \BaseProject\Task\Task\Task $task */
        $task = new $className($v);
        $task->showMessage('Start ' . $label);
        $task->_run();
        /** @var \BaseProject\Task\Model\Task $t */
        $t = CollectionDb::getInstanceOf('Task_Task')->loadById($code);
        if (!$t) {
            $t = new \BaseProject\Task\Model\Task();
            $t->setInsert(true);
            $t->setCode($code);
        }
        $t->setLastExec((new DateTime())->format('Y-m-d H:i:s'));
        $t->save();
    } else {
        echo (php_sapi_name() != 'cli') ? '<pre>' : '';
        echo "Don't find this task : {$code}\n";
        echo (php_sapi_name() != 'cli') ? '</pre>' : '';
        exit;
    }
} else {
    showHelp();
}
