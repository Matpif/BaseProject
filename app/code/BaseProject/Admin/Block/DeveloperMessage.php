<?php

namespace BaseProject\Admin\Block;

use App\App;
use App\libs\App\Block;

class DeveloperMessage extends Block
{

    /**
     * DeveloperMessage constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('/admin/developerMessage.phtml');
        $this->setUseCache(true);
        $this->setKey([
            'block' => get_class($this),
            App::getInstance()->getLanguageCode()
        ]);
    }
}