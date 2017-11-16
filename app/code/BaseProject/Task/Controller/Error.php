<?php

namespace BaseProject\Task\Controller;


use App\App;
use App\libs\App\CollectionDb;
use App\libs\App\Controller;
use App\libs\App\Helper;
use BaseProject\Login\Helper\Login;

class Error extends Controller
{
    /** @var  \BaseProject\Task\Collection\Error */
    private $_errors;
    /** @var  int */
    private $_schedulerId;

    /**
     * Error constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('/task/scheduler/error.phtml');
        $this->setTemplateHeader('/admin/header/menu.phtml');
        $this->setTemplateFooter(null);

        $this->setTitle('Task Scheduler error');
    }

    public function indexAction()
    {
        $params = App::getRequestParams('get');

        if (isset($params['schedulerId'])) {
            $this->_schedulerId = $params['schedulerId'];
            $this->_errors = CollectionDb::getInstanceOf('Task_Error')->load(['scheduler_id' => $params['schedulerId']]);
        } else {
            $this->_errors = CollectionDb::getInstanceOf('Task_Error')->loadAll(['date' => 'DESC']);
        }
    }

    /**
     * @return mixed
     */
    public function getErrors()
    {
        return $this->_errors;
    }

    /**
     * @return int
     */
    public function getSchedulerId()
    {
        return $this->_schedulerId;
    }


    /**
     * @param null $action
     * @return bool
     */
    public function isAllowed($action = null)
    {
        $session = App::getInstance()->getSession();
        $user = $session->getUser();
        /** @var Login $helperLogin */
        $helperLogin = Helper::getInstance('Login_Login');
        if ($user) {
            return $helperLogin->hasRole($user, 'Task_schedule');
        } else {
            $this->redirect($helperLogin->getUrlLogin());
        }
    }
}