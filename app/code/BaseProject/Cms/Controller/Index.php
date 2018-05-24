<?php

namespace BaseProject\Cms\Controller;

use App\App;
use App\libs\App\CollectionDb;
use App\libs\App\Controller;
use BaseProject\Cms\Block\Block;

class Index extends Controller
{

    /**
     * @var Block
     */
    private $_currentBlock;

    /**
     * Default_Admin_IndexController constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->setTemplateHeader(null);
        $this->setTemplateFooter(null);
        $this->setTitle($this->__('Cms'));
    }

    public function indexAction()
    {
        $this->setUseCache(true);
        $this->setTemplate('/cms/index.phtml');
        $request = App::getRequestParams('get');
        if (isset($request['name'])) {
            $this->setKey(['/cms/' . $request['name'], App::getInstance()->getLanguageCode()]);
            if (!$this->cacheExist()) {
                $this->_currentBlock = CollectionDb::getInstanceOf('Cms_Block')->load([
                    'name' => $request['name'],
                    'active_page_format' => 1,
                    'is_enabled' => 1
                ])->getFirstRow();
                if ($this->_currentBlock) {
                    $this->setTitle($this->_currentBlock->getTitle());
                }
            }
        }
    }

    /**
     * @return Block
     */
    public function getCurrentBlock()
    {
        return $this->_currentBlock;
    }
}