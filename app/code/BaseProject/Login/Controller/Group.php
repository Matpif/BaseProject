<?php

namespace BaseProject\Login\Controller;

use App\App;
use App\ContentTypes;
use App\libs\App\Block;
use App\libs\App\CollectionDb;
use App\libs\App\Controller;
use App\libs\App\Helper;
use BaseProject\Admin\Block\Message;
use BaseProject\Login\Block\FormGroup;
use BaseProject\Login\Helper\Login;

class Group extends Controller
{

    private $_currentGroup;

    /**
     * Login_GroupController constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->setTitle('Groups Admin');
        $this->setTemplate('/login/group/index.phtml');
        $this->setTemplateHeader('/admin/header/menu.phtml');
    }

    public function indexAction()
    {
    }

    public function groupAction()
    {
        $this->setTemplate('/login/group/group.phtml');
        $request = App::getRequestParams();

        if (isset($request['id'])) {
            $this->_currentGroup = CollectionDb::getInstanceOf('Login_Group')->loadById($request['id']);
        } else {
            $this->_currentGroup = new \BaseProject\Login\Model\Group();
        }
    }

    public function saveGroupAction()
    {
        $request = App::getRequestParams();

        if (isset($request['id'], $request['name'], $request['roles'])) {

            $group = new \BaseProject\Login\Model\Group();
            $group->setAttribute('id', $request['id']);
            $group->setAttribute('name', $request['name']);

            $roles = [];
            foreach ($request['roles'] as $key => $role) {
                $roles[] = $key;
            }

            $group->setAttribute('roles', implode(',', $roles));

            if ($group->save()) {
                App::getInstance()->getSession()->addMessage([
                    'level' => Message::LEVEL_MESSAGE_SUCCESS,
                    'message' => 'Saved with success !'
                ]);
            } else {
                App::getInstance()->getSession()->addMessage([
                    'level' => Message::LEVEL_MESSAGE_ERROR,
                    'message' => 'Saved with success !'
                ]);
            }

            if (App::getInstance()->httpAccepted(ContentTypes::APPLICATION_JSON)) {
                $this->sendJson(json_encode($group));
            }
        }
        if (App::getInstance()->httpAccepted(ContentTypes::APPLICATION_JSON)) {
            $this->sendJson(json_encode(['error' => 'id, name or roles is required']));
        }
        $this->redirect($this->getUrlAction('index'));
    }

    public function deleteGroupAction()
    {
        $request = App::getRequestParams();

        if (isset($request['id'])) {
            /** @var \BaseProject\Login\Model\Group $group */
            $group = CollectionDb::getInstanceOf('Login_Group')->loadById($request['id']);
            if ($group->remove()) {
                App::getInstance()->getSession()->addMessage([
                    'level' => Message::LEVEL_MESSAGE_SUCCESS,
                    'message' => 'Removed with success !'
                ]);
            } else {
                App::getInstance()->getSession()->addMessage([
                    'level' => Message::LEVEL_MESSAGE_ERROR,
                    'message' => 'Removed with success !'
                ]);
            }
        }
        $this->redirect($this->getUrlAction('index'));
    }

    /**
     * @return mixed
     */
    public function getCurrentGroup()
    {
        return $this->_currentGroup;
    }

    /**
     * @return FormGroup
     */
    public function getFormGroupBlock()
    {
        $block = Block::getBlock('Login_FormGroup');
        $block->setCurrentGroup($this->_currentGroup);
        $block->setAllRoles($this->getAllRoles());

        return $block;
    }

    public function getAllRoles()
    {
        /** @var Login $loginHelper */
        $loginHelper = Helper::getInstance('Login_Login');

        return $loginHelper->getAllRoles();
    }

    public function isAllowed($action = null)
    {
        $session = App::getInstance()->getSession();
        $user = $session->getUser();
        /** @var Login $helperLogin */
        $helperLogin = Helper::getInstance('Login_Login');
        if ($user) {
            switch ($action) {
                case 'index':
                    return $helperLogin->hasRole($user, 'Login_show_groups');
                    break;
                case 'group':
                case 'saveGroup':
                    return $helperLogin->hasRole($user, 'Login_add_group');
                    break;
                case 'deleteGroup':
                    return $helperLogin->hasRole($user, 'Login_delete_group');
                    break;
            }
        }

        return false;
    }
}