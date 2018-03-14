<?php

namespace BaseProject\Login\Block;

use App\App;
use App\libs\App\Block;
use App\libs\App\Helper;

class Form extends Block
{

    /**
     * Login_FormBlock constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('/login/form.phtml');
        $this->setUseCache(true);
    }

    /**
     * @return string
     */
    public function getUrlReferer() {
        if (isset(App::getRequestParams('get')['url_referer'])) {
            return urldecode(App::getRequestParams('get')['url_referer']);
        }
        return '';
    }

    /**
     * @return bool
     * @throws \Exception
     */
    public function registerIsEnabled() {
        /** @var \BaseProject\Admin\Helper\Parameter $helper */
        $helper = Helper::getInstance('Admin_Parameter');
        return ($helper->getParameter('login/general/register')->getValue() === '1');
    }
}