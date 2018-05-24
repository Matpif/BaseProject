<?php

namespace BaseProject\Login\Observer;

use App\App;
use App\libs\App\Logs;
use App\libs\App\Observer;

class User implements Observer
{

    /**
     * @param $eventName
     * @param $user \BaseProject\Login\Model\User
     */
    public static function notify($eventName, $user)
    {
        switch($eventName) {
            case 'after_save_model':
                if ($user instanceof \BaseProject\Login\Model\User) {
                    Logs::log("User {$user->getUsername()} is saved", 'Login.log', Logs::LEVEL_INFO);
                }
                break;
            case 'authenticated_user':
                if ($user instanceof \BaseProject\Login\Model\User) {
                    Logs::log("User {$user->getUsername()} has authenticated with ip address : ".App::getInstance()->getRequest()->getServerParams()['HTTP_X_REAL_IP'], 'Login.log', Logs::LEVEL_INFO);
                }
                break;
        }
    }
}