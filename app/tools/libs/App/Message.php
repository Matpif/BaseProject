<?php

namespace App\libs\App;

class Message extends VarientObject
{

    CONST LEVEL_SUCCESS = 0;
    CONST LEVEL_ERROR = 1;
    CONST LEVEL_INFO = 2;
    CONST LEVEL_WARNING = 3;

    /** @var  string */
    private $_message;
    /** @var  int */
    private $_level;
    /** @var string */
    private $_code;

    /**
     * Message constructor.
     * @param $_message
     * @param $_level
     */
    public function __construct($_message, $_level)
    {
        $this->_message = $_message;
        $this->_level = $_level;
    }

    /**
     * @return string
     */
    public function getMessage()
    {
        return $this->_message;
    }

    /**
     * @param string $message
     */
    public function setMessage($message)
    {
        $this->_message = $message;
    }

    /**
     * @return int
     */
    public function getLevel()
    {
        return $this->_level;
    }

    /**
     * @param int $level
     */
    public function setLevel($level)
    {
        $this->_level = $level;
    }

    /**
     * @return string
     */
    public function getCode()
    {
        return $this->_code;
    }

    /**
     * @param string $code
     */
    public function setCode($code)
    {
        $this->_code = $code;
    }
}