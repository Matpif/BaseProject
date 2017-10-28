<?php

namespace App\libs\App;

abstract class ModelWs extends VarientObject implements ModelInterface
{

    protected $_webservice;

    public function save()
    {
        return $this;
    }

    public function remove()
    {
        return $this;
    }

    public function __sleep()
    {
        return $this;
    }

    public function __wakeup()
    {
        return $this;
    }
}