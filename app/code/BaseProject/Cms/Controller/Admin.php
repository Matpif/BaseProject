<?php

namespace BaseProject\Cms\Controller;

use App\App;
use App\libs\App\CollectionDb;
use App\libs\App\Controller;
use App\libs\App\Helper;
use App\libs\App\QueryFactory;
use BaseProject\Admin\Block\ListAdmin;
use BaseProject\Cms\Model\Block;
use BaseProject\Login\Helper\Login;

class Admin extends Controller
{

    /**
     * @var \BaseProject\Cms\Model\Block
     */
    private $_currentBlock;
    /**
     * @var ListAdmin
     */
    private $_listBlock;

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
        /** @var ListAdmin $listBlock */
        $this->_listBlock = \App\libs\App\Block::getBlock('Admin_ListAdmin');

        /** @var \BaseProject\Login\Collection\User $users */
        $blockCollection = CollectionDb::getInstanceOf('Cms_Block');

        $select = (new QueryFactory())->newSelect();
        $select->cols(['id', 'name', 'language_code', 'title', "CASE WHEN is_enabled = 1 THEN 'Yes' ELSE 'No' END as is_enabled"])
            ->from($blockCollection->getTable())
            ->orderBy(['id']);

        $blockCollection->loadByQuery($select->getStatement());

        $this->_listBlock->setHeaderLabel(['Id', 'Name', 'Language', 'Title', 'Enabled']);
        $this->_listBlock->setLines($blockCollection->getRows());
        $this->_listBlock->setColsWidth(['20px', '10%', '30%', '', '10px']);
        $this->_listBlock->setUrlToClick($this->getUrlAction('block') . '/id/{id}');
        $this->_listBlock->setUrlParams(['id']);
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
        $this->addJs('/assets/components/tinymce/tinymce.min.js', false, false);
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
        $blocks = CollectionDb::getInstanceOf('Cms_Block')->loadAll(['name ASC']);

        return $blocks;
    }

    /**
     * @return mixed
     */
    public function getCurrentBlock()
    {
        return $this->_currentBlock;
    }

    /**
     * @return ListAdmin
     */
    public function getListBlock()
    {
        return $this->_listBlock;
    }

    public function isAllowed($action = null)
    {
        $session = App::getInstance()->getSession();
        $user = $session->getUser();
        /** @var Login $helperLogin */
        $helperLogin = Helper::getInstance('Login_Login');
        if ($user) {
            return $helperLogin->hasRole($user, 'cms_admin');
        }

        return false;
    }
}