<?php

namespace BaseProject\Install\Model;

use App\libs\App\ModelDb;
use DateTime;

/**
 * @method int getId()
 * @method int getModuleId()
 * @method String getFileName()
 * @method DateTime getLastExec()
 *
 * @method setId(int $id)
 * @method setModuleId(int $moduleId)
 * @method setFileName(String $fileName)
 * @method setLastExec(DateTime $lastExec)
 *
 * @dbField id INT AUTO_INCREMENT PRIMARY KEY
 * @dbField module_id INT NOT NULL
 * @dbField file_name VARCHAR(50) NOT NULL
 * @dbField last_exec DATETIME NULL
 *
 * class DefaultInstall_FileModel
 */
class File extends ModelDb
{

}