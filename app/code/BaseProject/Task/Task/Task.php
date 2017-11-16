<?php

namespace BaseProject\Task\Task;

use App\libs\App\VarientObject;

class Task extends VarientObject
{

    private $_verbose;

    /**
     * Task constructor.
     * @param $_verbose
     */
    public function __construct($_verbose = false)
    {
        $this->_verbose = $_verbose;
    }

    public function _run()
    {
        $this->__init();
        $this->__beforeExecute();
        $this->__run();
        $this->__afterExecute();
    }

    protected function __init()
    {
        $this->showMessage("Start init");
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
    }
}