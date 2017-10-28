<?php

namespace BaseProject\Login\Block;

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
}