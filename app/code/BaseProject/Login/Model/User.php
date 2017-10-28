<?php

namespace BaseProject\Login\Model;

use App\libs\App\ModelDb;

/**
 *
 * @method int getId()
 * @method String getUsername()
 * @method String getPassword()
 * @method int getGroupId()
 * @method Boolean getUseFlocon()
 * @method String getTotpKey()
 * @method int setId()
 * @method String setUsername()
 * @method int setGroupId()
 * @method Boolean setUseFlocon()
 * @method String setTotpKey()
 *
 * class DefaultLogin_UserModel
 */
class User extends ModelDb
{

    public function checkPassword($password)
    {
        return $this->getPassword() == sha1($password);
    }

    public function setPassword($password)
    {
        $this->setAttribute('password', sha1($password));
    }
}