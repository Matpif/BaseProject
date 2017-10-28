<?php

namespace BaseProject\Login\Observer;

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
        if ($user instanceof \BaseProject\Login\Model\User) {
            Logs::log("User {$user->getUsername()} is saved", 'Login.log', Logs::LEVEL_INFO);
        }
    }
}