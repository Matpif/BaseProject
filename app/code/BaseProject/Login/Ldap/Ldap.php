<?php
/**
 * Created by PhpStorm.
 * User: matpif
 * Date: 02/11/17
 * Time: 13:28
 */

namespace BaseProject\Login\Ldap;

use Adldap\Adldap;
use App\libs\App\Collection;
use BaseProject\Login\Model\LdapConfig;

class Ldap extends Adldap
{
    const PROVIDER_NAME = 'default';
    /** @var  string */
    private $_domain;

    /**
     * Ldap constructor.
     */
    public function __construct()
    {
        /** @var LdapConfig $ldapConfig */
        $ldapConfig = Collection::getInstanceOf('Login_LdapConfig')->loadById(1);

        if ($ldapConfig) {
            if (!$ldapConfig->getIsActive()) throw new \Exception('Ldap is not active');
            $domaineControllers = explode(',', $ldapConfig->getDomainControllers());
            $config = [self::PROVIDER_NAME => [
                'domain_controllers' => $domaineControllers,
                'base_dn' => $ldapConfig->getBaseDn(),
                'admin_username' => $ldapConfig->getAdminUsername(),
                'admin_password' => $ldapConfig->decryptAdminPassword()
                ]
            ];
            $this->_domain = $ldapConfig->getDomain();
            parent::__construct($config);
        } else {
            throw new \Exception("Ldap is not configure");
        }
    }

    /**
     * @return string
     */
    public function getDomain() {
        return $this->_domain;
    }
}