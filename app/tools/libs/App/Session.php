<?php

namespace App\libs\App;

use BaseProject\Login\Model\User;

class Session extends VarientObject
{
    /**
     * Singleton
     * @var Session
     */
    private static $_instance;
    /** @var  User */
    private $_user;
    /** @var  array */
    private $_messages;

    /**
     * Session constructor.
     */
    public function __construct()
    {
        if (!is_array($this->_data)) {
            $this->_data = [];
        }
    }

    public static function getInstance()
    {
        if (!self::$_instance) {
            self::$_instance = new Session();
        }

        return self::$_instance;
    }

    /**
     * @param $user User
     */
    public function setUserTemp($user)
    {
        $this->_user = $user;
        $this->setData(array_merge($this->_data, ['user_id_temp' => $user->getId()]));
    }

    public function setData($data)
    {
        parent::setData($data);
        $_SESSION = $data;
    }

    public function addMessage($message)
    {
        $this->_messages[] = $message;
        $this->setData(array_merge($this->_data, ['messages' => $this->_messages]));
    }

    /**
     * @return array
     */
    public function getMessages()
    {
        if (!$this->_messages) {
            if (isset($this->_data['messages'])) {
                $this->_messages = $this->_data['messages'];
            }
        }

        return $this->_messages;
    }

    public function unsetMessages()
    {
        unset($this->_data['messages']);
        $this->setData($this->_data);
        $this->_messages = null;
    }

    /**
     * @return User
     */
    public function getUser()
    {
        if (!$this->_user) {
            if (isset($this->_data['user_id'])) {
                $this->_user = CollectionDb::getInstanceOf('Login_User')->loadById($this->_data['user_id']);
            }
        }

        return $this->_user;
    }

    /**
     * @param $user User
     */
    public function setUser($user)
    {
        $this->_user = $user;
        if (!is_array($this->_data)) {
            $this->_data = [];
        }
        $this->setData(array_merge($this->_data, ['user_id' => $user->getId()]));
    }

    /**
     * @return User
     */
    public function getUserTemp()
    {
        if (!$this->_user) {
            if (isset($this->_data['user_id_temp'])) {
                $this->_user = CollectionDb::getInstanceOf('Login_User')->loadById($this->_data['user_id_temp']);
            }
        }

        return $this->_user;
    }

    public function unsetUser()
    {
        unset($this->_data['user_id']);
        $this->setData($this->_data);
        $this->_user = null;
    }

    public function unsetUserTemp()
    {
        unset($this->_data['user_id_temp']);
        $this->setData($this->_data);
        $this->_user = null;
    }
}