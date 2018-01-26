<?php

namespace BaseProject\Admin\Controller;

use App\libs\App\Controller;
use App\libs\App\Helper;
use App\App;
use BaseProject\Login\Helper\Login;

class Parameter extends Controller
{
    /**
     * Parameter constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('/admin/parameter/index.phtml');
        $this->setTemplateHeader('/admin/header/menu.phtml');
        $this->setTemplateFooter(null);
        $this->setTitle('Admin Parameter');
        $this->addJS('/assets/js/admin/parameter/parameter.js');
    }

    /**
     * @throws \Exception
     */
    public function saveAction() {
        $request = App::getRequestParams('post');
        /** @var \BaseProject\Admin\Helper\Parameter $helper */
        $helper = Helper::getInstance("Admin_Parameter");

        foreach ($request as $name => $value) {
            $parameter = $helper->getParameter($name);
            $parameter->setValue($value);
            $parameter->save();
        }

        $this->redirect($this->getUrlAction('index'));
    }

    public function isAllowed($action = null)
    {
        $session = App::getInstance()->getSession();
        $user = $session->getUser();
        /** @var Login $helperLogin */
        $helperLogin = Helper::getInstance('Login_Login');
        if ($user) {
            return $helperLogin->hasRole($user, 'Admin_admin');
        }

        return false;
    }
}