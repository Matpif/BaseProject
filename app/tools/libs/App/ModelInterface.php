<?php

namespace App\libs\App;

interface ModelInterface
{

    public function save();

    public function remove();

    public function __sleep();

    public function __wakeup();

}