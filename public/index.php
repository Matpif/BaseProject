<?php

use App\App;

$GLOBALS['override'] = json_decode(file_get_contents(__DIR__ . '/../app/etc/override.json'), true);

include_once "../vendor/autoload.php";

App::getInstance()->run();