<?php

namespace BaseProject\Admin\Block;

use App\App;
use App\ConfigModule;
use App\libs\App\Block;
use App\libs\App\Router;

class Menu extends Block
{

    private $_sort;

    /**
     * Default_Admin_MenuBlock constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('/admin/header/menu/menu.phtml');
        $this->_sort = [];
        $this->setUseCache(true);
        $this->setKey([
            'user' => App::getInstance()->getSession()->getUser(),
            'block' => get_class($this),
            App::getInstance()->getLanguageCode()
        ]);
    }

    public function getMenu()
    {
        $config = ConfigModule::getInstance();
        $menuByModule = $config->getConfigAllModules('admin_menu/tree');
        $_menu = [];

        foreach ($menuByModule as $menu) {
            $_menu = array_merge_recursive($_menu, $menu);
        }
        $this->sortMenu($_menu);

        return $_menu;
    }

    /**
     * @param $menu array
     */
    public function sortMenu(&$menu)
    {
        foreach ($menu as $key => &$m) {
            if (is_array($m)) {
                foreach ($m as $k1 => $m1) {
                    if (is_array($m1)) {
                        $this->sortMenu($m1);
                    }
                }
                uasort($m, array($this, '_sortMenu'));
            }
        }
        uksort($menu, array($this, '_sortMenu'));
    }

    public function getLabelsMenu()
    {
        $config = ConfigModule::getInstance();
        $menuLabelByModule = $config->getConfigAllModules('admin_menu/labels');
        $_menu = [];

        foreach ($menuLabelByModule as $labels) {
            foreach ($labels as $key => $label) {
                $label = [$key => $label];
                $_menu = array_merge($_menu, $label);
            }
        }

        return $_menu;
    }

    public function getUrl($code)
    {
        $url = explode('_', $code);

        return Router::getUrlAction((isset($url[0])) ? $url[0] : null, (isset($url[1])) ? $url[1] : null,
            (isset($url[2])) ? $url[2] : null);
    }

    private function _sortMenu($a, $b)
    {
        $sort = $this->getSort();
        if (!is_array($a) && !is_array($b)) {
            if (isset($sort[$a], $sort[$b])) {
                if ($sort[$a] == $sort[$b]) {
                    return 0;
                }

                return ($sort[$a] > $sort[$b]) ? 1 : -1;
            }
            if (isset($sort[$a])) {
                return 1;
            }
            if (isset($sort[$b])) {
                return -1;
            }
        }

        return 0;
    }

    private function getSort()
    {
        if (!$this->_sort) {
            $config = ConfigModule::getInstance();
            $configSort = $config->getConfigAllModules('admin_menu/sort');
            foreach ($configSort as $sort) {
                $this->_sort = array_merge($this->_sort, $sort);
            }
        }

        return $this->_sort;
    }
}