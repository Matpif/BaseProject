<?php

namespace App\libs\App;

use App\App;
use BaseProject\Admin\Model\Parameter;
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
    /** @var String */
    private $_pathSession;

    /**
     * Session constructor.
     */
    public function __construct()
    {
        $this->_pathSession = App::getInstance()->getPathRoot() . '/var/sessions';
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

    public function startSession()
    {
        if (session_status() == PHP_SESSION_NONE) {
            /** @var Parameter $parameterHelper */
            $parameterHelper = Helper::getInstance('Admin_Parameter');
            if (!file_exists($this->_pathSession)) {
                mkdir($this->_pathSession, 0770, true);
            }
            session_save_path($this->_pathSession);

            ini_set('session.gc_maxlifetime', $parameterHelper->getParameter('general/session/time')->getValue());
            session_start();
            if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > $parameterHelper->getParameter('general/session/time')->getValue())) {
                session_destroy();
            }
            $_SESSION['LAST_ACTIVITY'] = time();
            $this->addData(isset($_SESSION) ? $_SESSION : []);
        }
    }

    public function addData($data)
    {
        parent::addData($data);
        $_SESSION = $this->_data;
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
        $_SESSION = $this->_data;
    }

    public function setAttribute($key, $value)
    {
        parent::setAttribute($key, $value);
        $_SESSION = $this->_data;
    }

    public function unSetAttribute($key)
    {
        unset($this->_data[$key]);
        $_SESSION = $this->_data;
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

    public function revokeAllSession()
    {
        if (file_exists($this->_pathSession)) {
            $this->rmrf($this->_pathSession);
        }
    }

    private function rmrf($dir)
    {
        foreach (glob($dir) as $file) {
            if (is_dir($file)) {
                $this->rmrf("$file/*");
                rmdir($file);
            } else {
                unlink($file);
            }
        }
    }
}