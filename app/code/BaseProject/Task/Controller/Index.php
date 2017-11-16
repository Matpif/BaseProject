<?php

namespace BaseProject\Task\Controller;

use App\App;
use App\libs\App\Controller;
use App\libs\App\Helper;
use BaseProject\Login\Helper\Login;
use BaseProject\Task\Helper\Task;

class Index extends Controller
{

    /** @var array */
    private $_tasks;

    /**
     * Login_IndexController constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('/task/index.phtml');
        $this->setTemplateHeader('/admin/header/menu.phtml');
        $this->setTemplateFooter(null);
        $this->setTitle('Task');
    }

    public function listAction()
    {
        /** @var Task $helperTask */
        $helperTask = Helper::getInstance('Task_Task');
        $this->_tasks = $helperTask->getAllTask();
    }

    public function startAction()
    {
        $request = App::getRequestParams();
        if (isset($request['code'])) {
            $this->setTemplateHeader(null);
            $taskHelper = Helper::getInstance('Task_Task')->runTask($request['code']);
        } else {
            $this->redirect($this);
        }
        exit;
    }

    /**
     * @return array
     */
    public function getTasks()
    {
        return $this->_tasks;
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
            switch ($action) {
                case 'index':
                case 'list':
                    return $helperLogin->hasRole($user, 'Task_show_tasks');
                    break;
                case 'start':
                    return $helperLogin->hasRole($user, 'Task_exec_task');
                    break;
            }
        } else {
            $this->redirect($helperLogin->getUrlLogin());
        }

        return false;
    }
}