<?php

namespace BaseProject\Login\Block;

use App\App;
use App\libs\App\Block;

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

    public function getUrlReferer() {
        if (isset(App::getRequestParams('get')['url_referer'])) {
            return urldecode(App::getRequestParams('get')['url_referer']);
        }
        return '';
    }
}