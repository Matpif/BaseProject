<?php

namespace BaseProject\Login\Model;

use App\libs\App\ModelDb;

/**
 * @method int getId()
 * @method String getName()
 * @method String getRoles()
 * @method setId(int $id)
 * @method setName(String $name)
 * @method setRoles(String $roles)
 *
 * @dbField id INT PRIMARY KEY AUTO_INCREMENT
 * @dbField name NVARCHAR(50)
 * @dbField roles TEXT
 *
 * class DefaultLogin_GroupModel
 */
class Group extends ModelDb
{

}