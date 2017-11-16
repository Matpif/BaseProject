<?php

namespace BaseProject\Task\Model;

use App\libs\App\CollectionDb;
use App\libs\App\ModelDb;
use DateTime;

/**
 * @method int getId()
 * @method int getSchedulerId()
 * @method int getCodeError()
 * @method string getMessage()
 * @method String getDate()
 * @method setId(int $id)
 * @method setSchedulerId(int $schedulerId)
 * @method setCodeError(int $codeError)
 * @method setMessage(string $message)
 * @method setDate(String $date)
 *
 * Class Error
 * @package BaseProject\Task\Model
 */
class Error extends ModelDb
{

    /**
     * @return Scheduler|null
     */
    public function getScheduler()
    {
        if ($schedulerId = $this->getSchedulerId()) {
            /** @var Scheduler $scheduler */
            $scheduler = CollectionDb::getInstanceOf('Task_Task')->loadById($schedulerId);
            if ($scheduler) {
                return $scheduler;
            }
        }

        return null;
    }
}