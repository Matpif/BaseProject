<?php

namespace BaseProject\Login\Controller;

use App\App;
use App\libs\App\Block;
use App\libs\App\CollectionDb;
use App\libs\App\Controller;
use App\libs\App\Helper;
use App\libs\App\Model;
use BaseProject\Admin\Block\Message;
use BaseProject\Login\Helper\Login;
use FloconApi;

class Index extends Controller
{

    /**
     * Login_IndexController constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->setTemplateHeader(null);
        $this->setTemplateFooter(null);
        $this->setTitle('Login');
    }

    public function indexAction()
    {
        if (App::getInstance()->getSession()->getUser()) {
            $this->redirect();
        }
        $this->setTemplate('/login/index.phtml');

        $this->setUseCache(true);
        $this->setKey([get_class($this) . '_index', App::getInstance()->getLanguageCode()]);
    }

    /**
     * @return Block
     */
    public function getBlockForm()
    {
        return Block::getBlock('Login_Form');
    }

    public function signInAction()
    {
        $request = App::getRequestParams();

        if (isset($request['username'], $request['password'])) {
            /** @var \BaseProject\Login\Model\User $user */
            $user = CollectionDb::getInstanceOf('Login_User')->load(['username' => $request['username']])->getFirstRow();
            if ($user) {
                $auth = false;
                if ($user->getUseLdap()) {
                    $ldap = new \BaseProject\Login\Ldap\Ldap();
                    $provider = $ldap->connect(\BaseProject\Login\Ldap\Ldap::PROVIDER_NAME);
                    $auth = $provider->auth()->attempt($ldap->getDomain().'\\'.$user->getUsername(), $request['password']);
                }
                if (($user->getUseLdap() && $auth)
                    || (!$user->getUseLdap() && $user->checkPassword($request['password']))) {

                    $session = App::getInstance()->getSession();
                    if ($user->getTotpKey()) {
                        $session->setUserTemp($user);
                        $this->redirect($this->getUrlAction('otp'));
                    }
                    $session->setUser($user);
                    if ($request['url_referer']) {
                        $this->redirect($request['url_referer']);
                    }
                    $this->redirect();
                } else {
                    App::getInstance()->getSession()->addMessage(['level' => Message::LEVEL_MESSAGE_ERROR, 'message' => 'Error connection']);
                }
            } else {
                App::getInstance()->getSession()->addMessage(['level' => Message::LEVEL_MESSAGE_ERROR, 'message' => 'Error connection']);
            }
        } else {
            App::getInstance()->getSession()->addMessage(['level' => Message::LEVEL_MESSAGE_ERROR, 'message' => 'All fields are mandatory']);
        }
        /** @var Login $helperLogin */
        $helperLogin = Helper::getInstance('Login_Login');
        $this->redirect($helperLogin->getUrlLogin());
    }

    public function otpAction()
    {
        $this->setTemplate('/login/otp.phtml');
        $session = App::getInstance()->getSession();
        $userTemp = $session->getUserTemp();
        if (!$userTemp) {
            $this->redirect($this);
        }
    }

    public function otpCheckAction()
    {
        $session = App::getInstance()->getSession();
        /** @var \BaseProject\Login\Model\User $userTemp */
        $userTemp = $session->getUserTemp();
        $requestPost = App::getRequestParams('post');
        if ($userTemp) {
            $otp = new \Otp\Otp();
            if ($otp->checkTotp(\Base32\Base32::decode($userTemp->getTotpKey()), $requestPost['code'])) {
                $session->setUser($userTemp);
                $session->unsetUserTemp();
                $this->redirect();
            } else {
                App::getInstance()->getSession()->addMessage([
                    'level' => Message::LEVEL_MESSAGE_ERROR,
                    'message' => "Code doesn't match"
                ]);
                $session->unsetUserTemp();
            }
        }
        $this->redirect($this);
    }

    public function registerAction() {
        $params = App::getInstance()->getRequest()->getParsedBody();

        if (isset($params['username'], $params['password'], $params['password-confirm'])
            && !empty($params['username']) && !empty($params['password']) && !empty($params['password-confirm']) && !empty($params['first_name']) && !empty($params['last_name'])) {

            if ($params['password'] == $params['password-confirm']) {
                /** @var \BaseProject\Login\Model\User $user */
                $user = Model::getModel('Login_User');
                $user->setUsername($params['username']);
                $user->setPassword($params['password']);
                $user->setFirstName($params['first_name']);
                $user->setLastName($params['last_name']);
                $user->setEmail($params['email']);
                $user->setGroupId(2);
                $user->save();
                App::getInstance()->getSession()->addMessage([
                    'level' => Message::LEVEL_MESSAGE_SUCCESS,
                    'message' => "You are registered"
                ]);
                $this->redirect($this);
            } else {
                App::getInstance()->getSession()->addMessage([
                    'level' => Message::LEVEL_MESSAGE_ERROR,
                    'message' => "Is not a same password"
                ]);
                $this->redirect($this);
            }

        } else {
            App::getInstance()->getSession()->addMessage([
                'level' => Message::LEVEL_MESSAGE_ERROR,
                'message' => "All params is mandatory"
            ]);
            $this->redirect($this);
        }
    }

    public function disconnectAction()
    {
        App::getInstance()->getSession()->unsetUser();
        $this->redirect($this);
    }
}