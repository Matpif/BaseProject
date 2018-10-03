<?php

namespace BaseProject\Admin\Block;

use App\App;
use App\libs\App\Block;

class Message extends Block
{

    CONST LEVEL_MESSAGE_SUCCESS = \App\libs\App\Message::LEVEL_SUCCESS;
    CONST LEVEL_MESSAGE_INFO = \App\libs\App\Message::LEVEL_INFO;
    CONST LEVEL_MESSAGE_WARNING = \App\libs\App\Message::LEVEL_WARNING;
    CONST LEVEL_MESSAGE_ERROR = \App\libs\App\Message::LEVEL_ERROR;

    /** @var array */
    protected $_messages;

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
            $messages = App::getInstance()->getSession()->getMessages();
            App::getInstance()->getSession()->unsetMessages();

            foreach ($messages as $message) {
                if ($message instanceof \App\libs\App\Message) {
                    $this->_messages[] = $message;
                } elseif (is_array($message) && isset($message['level'], $message['message'])) {
                    $this->_messages[] = new \App\libs\App\Message($message['message'], $message['level']);
                }
            }
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