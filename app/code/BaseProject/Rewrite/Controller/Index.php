<?php

namespace BaseProject\Rewrite\Controller;

use App\App;
use App\libs\App\Block;
use App\libs\App\CollectionDb;
use App\libs\App\Controller;
use App\libs\App\Helper;
use App\libs\App\Model;
use App\libs\App\QueryFactory;
use BaseProject\Admin\Block\ListAdmin;
use BaseProject\Admin\Block\Message;
use BaseProject\Login\Helper\Login;
use BaseProject\Rewrite\Collection\Rewrite;

class Index extends Controller
{
    /**
     * @var ListAdmin
     */
    private $_listBlock;

    /**
     * @var \BaseProject\Rewrite\Model\Rewrite
     */
    private $_currentRewrite;

    /**
     * Index constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->setTemplateHeader('/admin/header/menu.phtml');
        $this->setTemplateFooter(null);
        $this->setTitle($this->__('Rewrite'));
    }

    /**
     * @return ListAdmin
     */
    public function getListBlock()
    {
        return $this->_listBlock;
    }

    public function indexAction()
    {
        /** @var ListAdmin $listBlock */
        $this->_listBlock = Block::getBlock('Admin_ListAdmin');

        /** @var Rewrite $rewriteCollection */
        $rewriteCollection = CollectionDb::getInstanceOf('Rewrite_Rewrite');
        $select = (new QueryFactory())->newSelect()
            ->cols(['id', 'name', 'basic_url', 'rewrite_url', "CASE WHEN redirect_visible = 0 THEN 'Not Visible' WHEN redirect_visible = 1 THEN 'Permanently' ELSE 'Temporary' END as Redirect"])
            ->from($rewriteCollection->getTable());
        $rewriteCollection->loadByQuery($select->getStatement());

        $this->_listBlock->setHeaderLabel(['Id', 'Name', 'Basic Url', 'New Url', 'Redirect']);
        $this->_listBlock->setLines($rewriteCollection->getRows());
        $this->_listBlock->setColsWidth(['20px', '', '', '', '150px']);
        $this->_listBlock->setUrlToClick($this->getUrlAction('rewrite') . '/id/{id}');
        $this->_listBlock->setUrlParams(['id']);

        $this->setTemplate('/rewrite/admin/index.phtml');
    }

    public function rewriteAction()
    {
        $this->setTemplate('/rewrite/admin/rewrite.phtml');
        $request = App::getRequestParams('get');
        if (isset($request['id'])) {
            $this->_currentRewrite = CollectionDb::getInstanceOf('Rewrite_Rewrite')->loadById($request['id']);
        } else {
            $this->_currentRewrite = Model::getModel('Rewrite_Rewrite');
        }
    }

    public function saveAction()
    {
        $request = App::getRequestParams('post');

        if (isset($request['id'], $request['name'], $request['basic_url'], $request['rewrite_url'], $request['redirect_visible'])) {
            /** @var \BaseProject\Rewrite\Model\Rewrite $rewrite */
            $rewrite = CollectionDb::getInstanceOf('Rewrite_Rewrite')->loadById($request['id']);
            if (!$rewrite) {
                $rewrite = Model::getModel('Rewrite_Rewrite');
            }

            $rewrite->setName($request['name']);
            $rewrite->setBasicUrl($request['basic_url']);
            $rewrite->setRewriteUrl($request['rewrite_url']);
            $rewrite->setRedirectVisible($request['redirect_visible']);

            if ($rewrite->save()) {
                App::getInstance()->getSession()->addMessage([
                    'level' => Message::LEVEL_MESSAGE_SUCCESS,
                    'message' => 'Saved with success !'
                ]);
            } else {
                App::getInstance()->getSession()->addMessage([
                    'level' => Message::LEVEL_MESSAGE_ERROR,
                    'message' => 'Saved without success !'
                ]);
            }
        } else {
            App::getInstance()->getSession()->addMessage([
                'level' => Message::LEVEL_MESSAGE_ERROR,
                'message' => 'All fields are mandatory !'
            ]);
        }

        $this->redirect($this->getUrlAction('index'));
    }

    public function deleteAction() {
        $request = App::getRequestParams('get');

        if (isset($request['id'])) {
            /** @var \BaseProject\Rewrite\Model\Rewrite $rewrite */
            $rewrite = CollectionDb::getInstanceOf('Rewrite_Rewrite')->loadById($request['id']);
            if ($rewrite) {
                if ($rewrite->remove()) {
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
        }
        $this->redirect($this->getUrlAction('index'));
    }

    /**
     * @return \BaseProject\Rewrite\Model\Rewrite
     */
    public function getCurrentRewrite()
    {
        return $this->_currentRewrite;
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
                    return $helperLogin->hasRole($user, 'Rewrite_read');
                    break;
                case 'rewrite':
                case 'save':
                    return $helperLogin->hasRole($user, 'Rewrite_write');
                    break;
                case 'delete':
                    return $helperLogin->hasRole($user, 'Rewrite_delete');
                    break;
                default:
                    return true;
            }
        }

        return false;
    }
}