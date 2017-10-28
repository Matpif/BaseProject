<?php

namespace BaseProject\Admin\Block;

use App\App;
use App\libs\App\Block;

class Message extends Block
{

    const LEVEL_MESSAGE_SUCCESS = 0;
    const LEVEL_MESSAGE_INFO = 1;
    const LEVEL_MESSAGE_WARNING = 2;
    const LEVEL_MESSAGE_ERROR = 3;

    /** @var array */
    private $_messages;

    /**
     * Default_Admin_MessageBlock constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('/admin/message.phtml');
        $this->_messages = [];
    }

    /**
     * @return array
     */
    public function getMessages()
    {
        if (App::getInstance()->getSession()->getMessages() && count($this->_messages) == 0) {
            $this->_messages = App::getInstance()->getSession()->getMessages();
            App::getInstance()->getSession()->unsetMessages();
        }

        return $this->_messages;
    }

    /**
     * @param array $messages
     */
    public function setMessages($messages)
    {
        $this->_messages = $messages;
    }

    public function getClass($level)
    {
        $class = 'alert alert-dismissible ';
        switch ($level) {
            case self::LEVEL_MESSAGE_SUCCESS:
                $class .= 'alert-success';
                break;
            case self::LEVEL_MESSAGE_INFO:
                $class .= 'alert-info';
                break;
            case self::LEVEL_MESSAGE_WARNING:
                $class .= 'alert-warning';
                break;
            case self::LEVEL_MESSAGE_ERROR:

                $class .= 'alert-danger';
                break;
        }

        return $class;
    }
}