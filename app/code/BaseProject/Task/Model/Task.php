<?php

namespace BaseProject\Task\Model;

use App\libs\App\ModelDb;
use DateTime;

/**
 * @method String getCode()
 * @method DateTime getLastExec()
 * @method setCode(String $code)
 * @method setLastExec(DateTime $lastExec)
 *
 * class DefaultTask_TaskModel
 */
class Task extends ModelDb
{

    /** @var  bool */
    private $_insert;

    /**
     * Task_TaskModel constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->_insert = false;
    }

    public function save()
    {
        if (!$this->_insert) {
            return $this->update();
        } else {
            return $this->insert();
        }
    }

    /**
     * @param bool $insert
     */
    public function setInsert($insert)
    {
        $this->_insert = $insert;
    }
}