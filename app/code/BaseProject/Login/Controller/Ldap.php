<?php
/**
 * Created by PhpStorm.
 * User: matpif
 * Date: 01/11/17
 * Time: 13:21
 */

namespace BaseProject\Login\Controller;

use App\App;
use App\libs\App\Collection;
use App\libs\App\Controller;
use App\libs\App\Helper;
use App\libs\App\Model;
use BaseProject\Admin\Block\Message;
use BaseProject\Login\Helper\Login;
use BaseProject\Login\Model\LdapConfig;

class Ldap extends Controller
{
    /** @var LdapConfig */
    private $_currentConfig;

    /**
     * Ldap constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->setTemplateHeader('/admin/header/menu.phtml');
        $this->setTemplateFooter(null);
    }

    public function indexAction() {
        $this->setTemplate('/login/ldap/index.phtml');
        $this->setTitle('Configure LDAP');
        $this->_currentConfig = Collection::getInstanceOf('Login_LdapConfig')->loadById(1);
        if (!$this->_currentConfig) {
            $this->_currentConfig = Model::getModel('Login_LdapConfig');
        }
    }

    public function saveAction() {
        $params = App::getInstance()->getRequest()->getParsedBody();
        if (isset($params['domain_controllers'], $params['base_dn'], $params['admin_username'], $params['admin_password'])) {

            $this->_currentConfig = Collection::getInstanceOf('Login_LdapConfig')->loadById(1);
            if (!$this->_currentConfig) {
                $this->_currentConfig = Model::getModel('Login_LdapConfig');
            }

            $this->_currentConfig->setIsActive(isset($params['is_active']));
            $this->_currentConfig->setDomainControllers($params['domain_controllers']);
            $this->_currentConfig->setBaseDn($params['base_dn']);
            $this->_currentConfig->setAdminUsername($params['admin_username']);
            $this->_currentConfig->setAdminPassword($params['admin_password']);
            $this->_currentConfig->setDomain($params['domain']);

            if ($this->_currentConfig->save()) {
                App::getInstance()->getSession()->addMessage([
                    'level' => Message::LEVEL_MESSAGE_SUCCESS,
                    'message' => "Save with success"
                ]);
            } else {
                App::getInstance()->getSession()->addMessage([
                    'level' => Message::LEVEL_MESSAGE_ERROR,
                    'message' => "Impossible to save"
                ]);
            }
        } else {
            App::getInstance()->getSession()->addMessage([
                'level' => Message::LEVEL_MESSAGE_ERROR,
                'message' => "All fields are mandatory"
            ]);
        }
        $this->redirect($this);
    }

    /**
     * @return LdapConfig
     */
    public function getCurrentConfig()
    {
        return $this->_currentConfig;
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
                case 'save':
                    return $helperLogin->hasRole($user, 'Login_save_ldap');
                    break;
                default:
                    return true;
            }
        }

        return false;
    }
}