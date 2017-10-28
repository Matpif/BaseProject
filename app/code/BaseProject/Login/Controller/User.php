<?php

namespace BaseProject\Login\Controller;

use App\App;
use App\libs\App\CollectionDb;
use App\libs\App\Controller;
use App\libs\App\Helper;
use BaseProject\Admin\Block\Message;
use BaseProject\Login\Helper\Login;

class User extends Controller
{

    /**
     * @var \BaseProject\Login\Model\User
     */
    private $_currentUser;

    /**
     * Login_UserController constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->setTitle('Show users');
        $this->setTemplateHeader('/admin/header/menu.phtml');
    }

    public function indexAction()
    {
        $this->setTemplate('/login/user/index.phtml');
    }

    public function userAction()
    {
        $this->setTemplate('/login/user/user.phtml');
        $request = App::getRequestParams();

        if (isset($request['id'])) {
            $this->_currentUser = CollectionDb::getInstanceOf('Login_User')->loadById($request['id']);
        } else {
            $this->_currentUser = new \BaseProject\Login\Model\User();
        }
    }

    public function saveUserAction()
    {
        $request = App::getRequestParams();

        if (isset($request['id'], $request['username'], $request['group'])) {
            $user = CollectionDb::getInstanceOf('Login_User')->loadById($request['id']);
            if (!$user) {
                $user = new \BaseProject\Login\Model\User();
            }
            $user->setAttribute('id', $request['id']);
            $user->setAttribute('username', $request['username']);
            $user->setAttribute('group_id', $request['group']);

            if (isset($request['use_flocon'], $request['password'])) {
                $user->setAttribute('password', null);
            } else {
                if (!empty($request['password'])) {
                    $user->setPassword($request['password']);
                }
            }
            $user->setAttribute('use_flocon', isset($request['use_flocon']) ? 1 : 0);

            if (!isset($request['use_otp'])) {
                $user->setTotpKey(null);
            }

            if ($user->save()) {
                App::getInstance()->getSession()->addMessage([
                    'level' => Message::LEVEL_MESSAGE_SUCCESS,
                    'message' => 'Saved with success !'
                ]);
                if (isset($request['use_otp']) && !$user->getTotpKey()) {
                    $this->redirect($this->getUrlAction('otp') . '/id/' . $user->getId());
                }
            } else {
                App::getInstance()->getSession()->addMessage([
                    'level' => Message::LEVEL_MESSAGE_ERROR,
                    'message' => 'Saved with success !'
                ]);
            }
        }
        $this->redirect($this->getUrlAction('index'));
    }

    public function deleteUserAction()
    {
        $request = App::getRequestParams();

        if (isset($request['id'])) {
            /** @var \BaseProject\Login\Model\User $group */
            $user = CollectionDb::getInstanceOf('Login_User')->loadById($request['id']);
            if ($user->remove()) {
                App::getInstance()->getSession()->addMessage([
                    'level' => Message::LEVEL_MESSAGE_SUCCESS,
                    'message' => 'Removed with success !'
                ]);
            } else {
                App::getInstance()->getSession()->addMessage([
                    'level' => Message::LEVEL_MESSAGE_ERROR,
                    'message' => 'Removed without success !'
                ]);
            }
        }
        $this->redirect($this->getUrlAction('index'));
    }

    public function otpAction()
    {
        $requestGet = App::getRequestParams('get');
        $requestPost = App::getRequestParams('post');

        if (isset($requestPost['code'], $requestPost['id'])) {
            $this->_currentUser = CollectionDb::getInstanceOf('Login_User')->loadById($requestPost['id']);
            $otp = new \Otp\Otp();
            if ($otp->checkTotp(\Base32\Base32::decode($this->_currentUser->getTotpKey()), $requestPost['code'])) {
                App::getInstance()->getSession()->addMessage([
                    'level' => Message::LEVEL_MESSAGE_SUCCESS,
                    'message' => "Code match !"
                ]);
                $this->redirect($this);
            } else {
                App::getInstance()->getSession()->addMessage([
                    'level' => Message::LEVEL_MESSAGE_ERROR,
                    'message' => "Code doesn't match - please try again."
                ]);
                $this->redirect($this->getUrlAction('otp') . '/id/' . $this->_currentUser->getId());
            }
        } else {
            $this->setTemplate('/login/user/otp.phtml');
            if (isset($requestGet['id'])) {
                $this->_currentUser = CollectionDb::getInstanceOf('Login_User')->loadById($requestGet['id']);
                if ($this->_currentUser) {
                    if (!$this->_currentUser->getTotpKey()) {
                        $secret = \Otp\GoogleAuthenticator::generateRandom();
                        $this->_currentUser->setTotpKey($secret);
                        $this->_currentUser->save();
                    }
                }
            } else {
                App::getInstance()->getSession()->addMessage([
                    'level' => Message::LEVEL_MESSAGE_ERROR,
                    'message' => "Don't find user !"
                ]);
                $this->redirect($this);
            }
        }
    }

    public function otpCancelAction()
    {
        $request = App::getRequestParams('get');
        if (isset($request['id'])) {
            /** @var \BaseProject\Login\Model\User $user */
            $user = CollectionDb::getInstanceOf('Login_User')->loadById($request['id']);
            if ($user) {
                $user->setTotpKey(null);
                $user->save();
                App::getInstance()->getSession()->addMessage([
                    'level' => Message::LEVEL_MESSAGE_INFO,
                    'message' => "Otp canceled"
                ]);
            }
        }
        $this->redirect($this);
    }

    /**
     * @return \BaseProject\Login\Model\User
     */
    public function getCurrentUser()
    {
        return $this->_currentUser;
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
                    return $helperLogin->hasRole($user, 'Login_show_users');
                    break;
                case 'user':
                case 'saveUser':
                case 'otp':
                case 'otpCancel':
                    return $helperLogin->hasRole($user, 'Login_add_user');
                    break;
                case 'deleteUser':
                    return $helperLogin->hasRole($user, 'Login_delete_user');
                    break;
                default:
                    return $helperLogin->hasRole($user, 'Login_show_users');
            }
        } else {
            $this->redirect($helperLogin->getUrlLogin());
        }

        return false;
    }
}