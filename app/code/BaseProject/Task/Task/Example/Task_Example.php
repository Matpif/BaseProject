<?php

namespace BaseProject\Task\Task\Example;

use BaseProject\Task\Task\Task;

class Task_Example extends Task
{

    public function __run()
    {
        parent::__run();

        for ($i = 0; $i < 2; $i++) {
            $this->showMessage('Test: ' . $i);
            sleep(1);
        }
    }
}