<?php

namespace BaseProject\Admin\Model;

use App\libs\App\ModelDb;


/**
 * @method int getId()
 * @method Boolean getEnable()
 * @method string getProject()
 * @method setId(int $id)
 * @method setEnable(Boolean $enable)
 * @method setProject(string $project)
 *
 * @dbField id INT AUTO_INCREMENT PRIMARY KEY
 * @dbField project nvarchar(50) DEFAULT 'BaseProject' NOT NULL
 * @dbField enable tinyint(1) DEFAULT '1' NULL
 * @dbField module_name nvarchar(50) NOT NULL
 *
 * class Module
 */
class Module extends ModelDb
{

}