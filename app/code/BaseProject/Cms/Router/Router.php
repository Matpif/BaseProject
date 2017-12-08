<?php

namespace BaseProject\Cms\Router;

use App\libs\App\CollectionDb;

class Router extends \App\libs\App\Router
{
    /**
     * Router constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->getRoute();
    }

    public function getRoute()
    {
        if (0 === strpos($this->_currentUri, '/cms')) {
            $splitUri = explode('/', $this->_currentUri);
            foreach ($splitUri as $key => $item) {
                if ($key == 2) {
                    $_GET['name'] = $item;
                }
            }
            if (isset($_GET['name'])) {
                $blockCms = CollectionDb::getInstanceOf('Cms_Block')->load([
                    'name' => $_GET['name'],
                    'active_page_format' => 1,
                    'is_enabled' => 1
                ])->getFirstRow();
                if (!$blockCms) {
                    $this->_module = 'Error';
                    $this->_controller = '404';
                    $this->_action = 'index';
                }
            } else {
                $this->_module = 'Error';
                $this->_controller = '404';
                $this->_action = 'index';
            }
        }

        return [
            'module' => $this->_module,
            'controller' => $this->_controller,
            'action' => $this->_action,
        ];
    }
}