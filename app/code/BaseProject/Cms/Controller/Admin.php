<?php

namespace BaseProject\Cms\Controller;

use App\App;
use App\libs\App\CollectionDb;
use App\libs\App\Controller;
use App\libs\App\Helper;
use BaseProject\Cms\Model\Block;
use BaseProject\Login\Helper\Login;

class Admin extends Controller
{

    private $_currentBlock;

    /**
     * Default_Admin_IndexController constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->setTemplateHeader('/admin/header/menu.phtml');
        $this->setTemplateFooter(null);
        $this->setTitle('Cms Admin');
    }

    public function indexAction()
    {
        $this->setTemplate('/cms/admin/index.phtml');
    }

    public function blockAction()
    {
        $request = App::getRequestParams();
        if (isset($request['id'])) {
            $this->_currentBlock = CollectionDb::getInstanceOf('Cms_Block')->loadById($request['id']);
        } else {
            $this->_currentBlock = new Block();
        }
        $this->setTemplate('/cms/admin/block.phtml');
        $this->addJs('/skin/libs/tinymce/js/tinymce/tinymce.min.js');
    }

    public function saveBlockAction()
    {
        $request = App::getRequestParams();

        if (isset($request['id'], $request['name'], $request['language_code'])) {

            $block = new Block();
            $block->setAttribute('id', $request['id']);
            $block->setAttribute('name', $request['name']);
            $block->setAttribute('language_code', $request['language_code']);
            $block->setAttribute('title', $request['title']);
            $block->setAttribute('content', $request['content']);
            $block->setAttribute('active_page_format', $request['active_page_format']);
            $block->setAttribute('is_enabled', $request['is_enabled']);

            $block->save();
        }
        $this->redirect($this->getUrlAction('index'));
    }

    public function deleteBlockAction()
    {
        $request = App::getRequestParams();

        if (isset($request['id'])) {
            /** @var Block $block */
            $block = CollectionDb::getInstanceOf('Cms_Block')->loadById($request['id']);
            $block->remove();
        }
        $this->redirect($this->getUrlAction('index'));
    }

    public function getBlocks()
    {
        $blocks = CollectionDb::getInstanceOf('Cms_Block')->loadAll(['name' => 'ASC']);

        return $blocks;
    }

    /**
     * @return mixed
     */
    public function getCurrentBlock()
    {
        return $this->_currentBlock;
    }

    public function isAllowed($action = null)
    {
        $session = App::getInstance()->getSession();
        $user = $session->getUser();
        /** @var Login $helperLogin */
        $helperLogin = Helper::getInstance('Login_Login');
        if ($user) {
            return $helperLogin->hasRole($user, 'cms_admin');
        } else {
            $this->redirect($helperLogin->getUrlLogin());
        }

        return false;
    }
}