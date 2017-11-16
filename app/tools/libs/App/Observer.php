<?php

namespace App\libs\App;

interface Observer
{
    public static function notify($eventName, $object);
}