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
 * @dbField id INT AUTO_INCREMENT PRIMARY KEY
 * @dbField username VARCHAR(50) NOT NULL
 * @dbField password VARCHAR(50) NULL
 * @dbField first_name VARCHAR(50) NULL
 * @dbField last_name VARCHAR(50) NULL
 * @dbField email VARCHAR(150) NULL
 * @dbField group_id INT NOT NULL
 * @dbField use_ldap TINYINT(1) DEFAULT '0' NULL
 * @dbField totp_key VARCHAR(255) NULL
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