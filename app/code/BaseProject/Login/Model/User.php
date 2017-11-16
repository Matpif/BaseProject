<?php

namespace BaseProject\Login\Model;

use App\libs\App\ModelDb;

/**
 *
 * @method int getId()
 * @method string getUsername()
 * @method string getPassword()
 * @method int getGroupId()
 * @method Boolean getUseLdap()
 * @method string getTotpKey()
 * @method string getFirstName()
 * @method string getLastName()
 * @method string getEmail()
 *
 * @method int setId()
 * @method setUsername(string $username)
 * @method setGroupId(int $groupId)
 * @method setUseLdap(Boolean $useLdap)
 * @method setTotpKey(string $totpKey)
 * @method setFirstName(string $firstName)
 * @method setLastName(string $lastName)
 * @method setEmail(string $email)
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