<?php

namespace BaseProject\Ajaxifier\Block;

use App\libs\App\Block;

class Script extends Block
{

    /**
     * Ajaxifier_ScriptBlock constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('/ajaxifier/script.phtml');
    }
}