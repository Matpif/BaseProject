<?php

namespace App\libs\App;

use DateTime;

class Logs
{

    const LOG_PATH = __DIR__ . '/../../../var/logs/';
    const LEVEL_INFO = 0;
    const LEVEL_WARNING = 1;
    const LEVEL_ERROR = 2;

    public static function Log($message, $fileName = null, $level = self::LEVEL_INFO)
    {
        if (!file_exists(self::LOG_PATH)) {
            mkdir(self::LOG_PATH);
        }
        switch ($level) {
            case self::LEVEL_INFO:
                self::info($fileName, $message);
                break;
            case self::LEVEL_WARNING:
                self::warning($fileName, $message);
                break;
            case self::LEVEL_ERROR:
                self::error($fileName, $message);
                break;
        }
    }

    private static function info($fileName, $message)
    {
        $now = (new DateTime())->format('c');
        $message = $now . ' - info: ' . $message;
        error_log($message . "\n", 3, self::LOG_PATH . $fileName);
    }

    private static function warning($fileName, $message)
    {
        $now = (new DateTime())->format('c');
        $message = $now . ' - warning: ' . $message;
        error_log($message . "\n", 3, self::LOG_PATH . $fileName);
    }

    private static function error($fileName, $message)
    {
        $now = (new DateTime())->format('c');
        $message = $now . ' - error: ' . $message;
        error_log($message . "\n", 3, self::LOG_PATH . $fileName);
    }
}