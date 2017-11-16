<?php

namespace BaseProject\Task\Model;

use App\libs\App\CollectionDb;
use App\libs\App\ModelDb;
use Cron\CronExpression;

/**
 * @method int getId()
 * @method string getDescription()
 * @method string getCron()
 * @method string getTaskCode()
 * @method string getIsEnabled()
 * @method String getLastExecution()
 * @method setId(int $id)
 * @method setDescription(string $description)
 * @method setCron(string $cron)
 * @method setTaskCode(string $taskCode)
 * @method setIsEnabled(boolean $isEnabled)
 * @method setLastExecution(String $lastExecution)
 *
 * Class Scheduler
 * @package BaseProject\Task\Model
 */
class Scheduler extends ModelDb
{
    /** @var  Task */
    private $_task;

    /**
     * @return \DateTime|null
     */
    public function getNextRunDate()
    {
        if ($cron = $this->getCron()) {
            $cronExpression = CronExpression::factory($cron);

            return $cronExpression->getNextRunDate();
        }

        return null;
    }

    /**
     * @return bool
     */
    public function isDue()
    {
        if ($cron = $this->getCron()) {
            $cronExpression = CronExpression::factory($cron);

            return $cronExpression->isDue();
        }

        return false;
    }

    /**
     * @return Task | null
     */
    public function getTask()
    {
        if (!$this->_task) {
            if ($taskCode = $this->getTaskCode()) {
                /** @var Task $task */
                $this->_task = CollectionDb::getInstanceOf('Task_Task')->loadById($taskCode);
                if ($this->_task) {
                    return $this->_task;
                }
            }
        } else {
            return $this->_task;
        }

        return null;
    }
}