<?php

namespace BaseProject\Login\Helper;

use App\ConfigModule;
use App\libs\App\CollectionDb;
use App\libs\App\Helper;
use App\libs\App\Logs;
use App\libs\App\Router;
use BaseProject\Login\Model\Group;
use BaseProject\Login\Model\User;

class Login extends Helper
{

    private $_groups;

    /**
     * @return array
     */
    public function getAllRoles()
    {
        $config = ConfigModule::getInstance()->getConfigAllModules('roles');
        $roles = [];

        foreach ($config as $role) {
            $roles = array_merge($roles, $role);
        }

        return $roles;
    }

    /**
     * @param $user User
     * @return bool|Group
     */
    public function getGroup($user)
    {
        if (!$this->_groups) {
            $this->_groups = CollectionDb::getInstanceOf('Login_Group')->loadAll();
        }
        /** @var Group $group */
        foreach ($this->_groups as $group) {
            if ($group->getId() == $user->getGroupId()) {
                return $group;
            }
        }

        return false;
    }

    /**
     * @param $user User
     * @param $role string
     *
     * @return bool
     */
    public function hasRole($user, $role)
    {
        $group = CollectionDb::getInstanceOf('Login_Group')->loadById($user->getGroupId());
        $roles = explode(',', $group->getRoles());

        return in_array($role, $roles);
    }

    public function getUrlLogin()
    {
        return Router::getUrlAction('Login');
    }

    /**
     * @param $user Default_Login_UserModel
     */
    public function beforeSaveUser($user)
    {
        Logs::log('test');
    }
}