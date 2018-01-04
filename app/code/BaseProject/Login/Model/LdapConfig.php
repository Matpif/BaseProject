<?php
/**
 * Created by PhpStorm.
 * User: matpif
 * Date: 01/11/17
 * Time: 13:43
 */

namespace BaseProject\Login\Model;

use App\libs\App\ModelDb;
use Defuse\Crypto\Crypto;
use Defuse\Crypto\Key;

/**
 * Class LdapConfig
 *
 * @method boolean getIsActive()
 * @method string getDomainControllers()
 * @method string getBaseDn()
 * @method string getAdminUsername()
 * @method string getAdminPassword()
 * @method string getDomain()
 * @method setIsActive(boolean $bool)
 * @method setDomainControllers(string $string)
 * @method setBaseDn(string $string)
 * @method setAdminUsername(string $string)
 * @method setDomain(string $string)
 *
 * @dbField id INT AUTO_INCREMENT PRIMARY KEY
 * @dbField is_active TINYINT DEFAULT 0
 * @dbField domain_controllers NVARCHAR(250) NULL
 * @dbField base_dn NVARCHAR(250) NULL
 * @dbField admin_username NVARCHAR(250) NULL
 * @dbField admin_password NVARCHAR(250) NULL
 * @dbField domain NVARCHAR(50) NULL
 *
 * @package BaseProject\Login\Model
 */
class LdapConfig extends ModelDb
{
    const KEY = 'def000009a39a1f5a9d64392b3ec72e0b3bb4b1009b2c309cbb58a439cd9ee99e34a1f9364505a8f133f16e27e76369582c39527f0ca01366aac23bf088dbdbb6e86067c';

    public function setAdminPassword($pwd) {
        if ($this->getAdminPassword() !== $pwd) {
            $key = Key::loadFromAsciiSafeString(self::KEY);
            $this->setAttribute('admin_password', Crypto::encrypt($pwd, $key));
        }
    }

    public function decryptAdminPassword() {
        $key = Key::loadFromAsciiSafeString(self::KEY);
        return Crypto::decrypt($this->getAdminPassword(), $key);
    }
}