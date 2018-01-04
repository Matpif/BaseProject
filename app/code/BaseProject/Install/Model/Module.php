<?php

namespace BaseProject\Install\Model;

use App\libs\App\ModelDb;
use DateTime;

/**
 *
 * @method int getId()
 * @method String getModuleName()
 * @method setId(int $id)
 * @method setModuleName(String $moduleName)
 *
 * @dbField id INT AUTO_INCREMENT PRIMARY KEY
 * @dbField module_name VARCHAR(50) NOT NULL
 *
 * class DefaultInstall_ModuleModel
 */
class Module extends ModelDb
{

}