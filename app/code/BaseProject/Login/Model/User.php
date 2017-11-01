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
 * @method setUsername(String)
 * @method setGroupId(int)
 * @method setUseFlocon(Boolean)
 * @method setTotpKey(String)
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