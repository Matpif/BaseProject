<?php

namespace BaseProject\Task\Task;

use App\App;
use App\libs\App\VarientObject;

class Task extends VarientObject
{

    private $_verbose;
    private $_taskName;
    private $_force;

    /**
     * Task constructor.
     * @param $_verbose
     * @param $_force
     */
    public function __construct($_verbose = false, $_force = false)
    {
        $this->_verbose = $_verbose;
        $this->_force = $_force;
        $this->_taskName = strtolower(str_replace('\\', '_', get_class($this)));
    }

    /**
     * @throws \BaseProject\Task\Exception\Exception
     */
    public function _run()
    {
        if (file_exists(App::PathRoot().'/var/task/locks/'.$this->_taskName.'.lock') && !$this->_force){
            throw new \BaseProject\Task\Exception\Exception('Task locked', \BaseProject\Task\Exception\Exception::TASK_LOCKED);
        }

        $this->__init();
        $this->__beforeExecute();
        $this->__run();
        $this->__afterExecute();
    }

    protected function __init()
    {
        $this->showMessage("Start init");
        if (!file_exists(App::PathRoot().'/var/task/locks')) {
            mkdir(App::PathRoot().'/var/task/locks', 0777, true);
        }
        touch(App::PathRoot().'/var/task/locks/'.$this->_taskName.'.lock');
    }

    public function showMessage($message)
    {
        if ($this->_verbose) {
            if (php_sapi_name() == "cli") {
                echo $message . "\n";
            } else {
                echo "<div class=\"message\">{$message}</div>";
                flush();
                ob_flush();
            }
        }
    }

    protected function __beforeExecute()
    {
        $this->showMessage("Start before execute");
    }

    protected function __run()
    {
        $this->showMessage("Start run");
    }

    protected function __afterExecute()
    {
        $this->showMessage("Start after execute");
        if (file_exists(App::PathRoot().'/var/task/locks/'.$this->_taskName.'.lock')) {
            unlink(App::PathRoot().'/var/task/locks/'.$this->_taskName.'.lock');
        }
    }
}