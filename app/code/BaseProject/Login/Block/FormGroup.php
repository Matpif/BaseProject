<?php

namespace BaseProject\Login\Block;

use App\libs\App\Block;
use BaseProject\Login\Model\Group;

class FormGroup extends Block
{

    /** @var  Group */
    private $_currentGroup;
    /** @var  array */
    private $_allRoles;

    /**
     * Login_FormGroupBlock constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('/login/group/form.phtml');
    }

    /**
     * @param $group Group
     * @param $role string
     * @return bool
     */
    public function hasRole($group, $role)
    {
        foreach (explode(',', $group->getRoles()) as $r) {
            if ($role == $r) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return Group
     */
    public function getCurrentGroup()
    {
        return $this->_currentGroup;
    }

    /**
     * @param Group $currentGroup
     */
    public function setCurrentGroup($currentGroup)
    {
        $this->_currentGroup = $currentGroup;
    }

    /**
     * @return array
     */
    public function getAllRoles()
    {
        return $this->_allRoles;
    }

    /**
     * @param array $allRoles
     */
    public function setAllRoles($allRoles)
    {
        $this->_allRoles = $allRoles;
    }
}