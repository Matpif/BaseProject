<?php

use App\App;

$GLOBALS['override'] = json_decode(file_get_contents(__DIR__ . '/etc/override.json'), true);

include_once "../vendor/autoload.php";

session_start();
App::getInstance()->run();