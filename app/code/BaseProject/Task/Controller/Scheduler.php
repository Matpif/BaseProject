<?php

namespace BaseProject\Task\Controller;

use App\App;
use App\libs\App\CollectionDb;
use App\libs\App\Controller;
use App\libs\App\Helper;
use App\libs\App\Model;
use BaseProject\Admin\Block\Message;
use BaseProject\Login\Helper\Login;

class Scheduler extends Controller
{
    /** @var  \BaseProject\Task\Model\Scheduler */
    private $_currentScheduler;

    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('/task/scheduler/index.phtml');
        $this->setTemplateHeader('/admin/header/menu.phtml');
        $this->setTemplateFooter(null);

        $this->setTitle('Task Scheduler');
    }

    public function indexAction()
    {
    }

    public function schedulerAction()
    {
        $this->setTemplate('/task/scheduler/scheduler.phtml');
        $params = App::getRequestParams('get');
        if (isset($params['id'])) {
            $this->_currentScheduler = CollectionDb::getInstanceOf('Task_Scheduler')->loadById($params['id']);
            if (!$this->_currentScheduler) {
                $this->_currentScheduler = Model::getModel('Task_Scheduler');
            }
        } else {
            $this->_currentScheduler = Model::getModel('Task_Scheduler');
        }
    }

    public function saveSchedulerAction()
    {
        $params = App::getInstance()->getRequest()->getParsedBody();
        if (isset($params['description'], $params['cron'], $params['task_code'])) {
            /** @var \BaseProject\Task\Model\Scheduler $scheduler */
            if (isset($params['id'])) {
                $scheduler = CollectionDb::getInstanceOf('Task_Scheduler')->loadById($params['id']);
            } else {
                $scheduler = Model::getModel('Task_Scheduler');
            }

            $scheduler->setDescription($params['description']);
            $scheduler->setCron($params['cron']);
            $scheduler->setTaskCode($params['task_code']);
            $scheduler->setIsEnabled($params['is_enabled']);

            if ($scheduler->save()) {
                App::getInstance()->getSession()->addMessage([
                    'level' => Message::LEVEL_MESSAGE_SUCCESS,
                    'message' => 'Saved with success'
                ]);
            } else {
                App::getInstance()->getSession()->addMessage([
                    'level' => Message::LEVEL_MESSAGE_ERROR,
                    'message' => 'Saved without success'
                ]);
            }

        } else {
            App::getInstance()->getSession()->addMessage([
                'level' => Message::LEVEL_MESSAGE_ERROR,
                'message' => 'All fields are mandatory'
            ]);
        }

        $this->redirect($this);
    }

    public function removeSchedulerAction()
    {
        $params = App::getRequestParams('get');

        if (isset($params['id'])) {
            /** @var \BaseProject\Task\Model\Scheduler $scheduler */
            $scheduler = CollectionDb::getInstanceOf('Task_Scheduler')->loadById($params['id']);
            if ($scheduler) {
                if ($scheduler->remove()) {
                    App::getInstance()->getSession()->addMessage([
                        'level' => Message::LEVEL_MESSAGE_SUCCESS,
                        'message' => 'Scheduler is deleted'
                    ]);
                } else {
                    App::getInstance()->getSession()->addMessage([
                        'level' => Message::LEVEL_MESSAGE_ERROR,
                        'message' => 'Impossible to remove scheduler'
                    ]);
                }
            } else {
                App::getInstance()->getSession()->addMessage([
                    'level' => Message::LEVEL_MESSAGE_ERROR,
                    'message' => 'Impossible to find scheduler'
                ]);
            }

        } else {
            App::getInstance()->getSession()->addMessage([
                'level' => Message::LEVEL_MESSAGE_ERROR,
                'message' => 'Id is mandatory'
            ]);
        }

        $this->redirect($this);
    }

    /**
     * @return \BaseProject\Task\Collection\Scheduler
     */
    public function getSchedulers()
    {
        /** @var \BaseProject\Task\Collection\Scheduler $schedulers */
        $schedulers = CollectionDb::getInstanceOf('Task_Scheduler')->loadAll();

        return $schedulers;
    }

    /**
     * @return \BaseProject\Task\Model\Scheduler
     */
    public function getCurrentScheduler()
    {
        return $this->_currentScheduler;
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