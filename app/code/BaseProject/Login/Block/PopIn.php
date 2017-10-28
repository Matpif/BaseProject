<?php

namespace BaseProject\Login\Block;

use App\libs\App\Block;

class PopIn extends Block
{

    CONST TYPE_DEFAULT = 'btn-default';
    CONST TYPE_PRIMARY = 'btn-primary';
    CONST TYPE_SUCCESS = 'btn-success';
    CONST TYPE_INFO = 'btn-info';
    CONST TYPE_WARNING = 'btn-warning';
    CONST TYPE_DANGER = 'btn-danger';
    CONST TYPE_LINK = 'btn-link';

    /**
     * @var array
     */
    private $_buttons;

    /**
     * Login_PopInBlock constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('/login/popin.phtml');
        $this->_buttons = [];
    }

    /**
     * @param $type
     * @param $callback
     * @param $text
     */
    public function addButton($text, $type, $callback = '')
    {
        $button = "<button type=\"button\" class=\"btn {$type}\" onClick=\"{$callback}\">{$text}</button>";
        $this->_buttons[] = $button;
    }

    /**
     * @return array
     */
    public function getButtons()
    {
        return $this->_buttons;
    }
}