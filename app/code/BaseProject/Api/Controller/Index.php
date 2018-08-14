<?php

namespace BaseProject\Api\Controller;

use App\App;
use App\libs\App\CollectionDb;
use App\libs\App\Controller;
use App\libs\App\Helper;
use App\StatusCodes;
use BaseProject\Login\Helper\Login;

class Index extends Controller
{

    /**
     * Admin_IndexController constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('/admin/index.phtml');
        $this->setTemplateHeader(null);
        $this->setTemplateFooter(null);
        $this->setTitle($this->__('Api'));
    }

    public function indexAction()
    {
        $this->setTemplate('/admin/index.phtml');
        $this->setTitle($this->__('Admin'));
    }

    public function readerAction()
    {
        $request = App::getRequestParams('get');
        $collection = CollectionDb::getInstanceOf($request['model']);
        unset($request['model']);
        if (count($request) > 0) {
            $collection->load($request);
        } else {
            $collection->loadAll();
        }
        $this->sendJson(json_encode($collection));
    }

    public function writerAction()
    {
        $this->sendJson(json_encode($_GET), StatusCodes::HTTP_OK);
    }

    public function removerAction()
    {
        $this->sendJson(json_encode($_GET), StatusCodes::HTTP_OK);
    }

    public function isAllowed($action = null)
    {
        $session = App::getInstance()->getSession();
        $user = $session->getUser();
        /** @var Login $helperLogin */
        $helperLogin = Helper::getInstance('Login_Login');
        if ($user) {
            switch ($action) {
                case 'reader':
                    return $helperLogin->hasRole($user, 'Api_reader');
                case 'writer':
                    return $helperLogin->hasRole($user, 'Api_writer');
                case 'remover':
                    return $helperLogin->hasRole($user, 'Api_remover');
            }
        }

        return false;
    }
}