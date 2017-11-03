<?php

use App\App;
use App\Config;
use App\libs\App\Controller;
use App\libs\App\Dispatcher;
use App\Page;

$GLOBALS['override'] = json_decode(file_get_contents(__DIR__ . '/etc/override.json'), true);

include_once "../vendor/autoload.php";
if (Config::getInstance()->getAttribute('app', 'installed') != 1) {
    $params['install'] = 1;
    include_once '../app.php';
    exit(0);
}
if (Config::getInstance()->getAttribute('app', 'maintenance') != 0) {
    $params['maintenance'] = 1;
    include_once '../app.php';
    exit(0);
}
session_start();

$acceptLanguage = explode(',', (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) ? $_SERVER['HTTP_ACCEPT_LANGUAGE'] : '');
$language = ((isset($acceptLanguage[0])) ? $acceptLanguage[0] : 'fr_FR');
$l = str_replace('-', '_', $language) . '.UTF-8';
putenv("LANG=" . $l);
setlocale(LC_MESSAGES, $l);

App::getInstance()->setPathRoot(__DIR__);
App::getInstance()->setLanguageCode(str_replace('-', '_', $language));
App::getInstance()->init();
App::getInstance()->setRequest(\GuzzleHttp\Psr7\ServerRequest::fromGlobals());
Dispatcher::getInstance()->initListener();
/** Debug mode (depuis le Config) */
if (Config::getInstance()->getAttribute('app', 'debug') == true) {
    // DEBUG MODE ON
    error_reporting(E_ALL);
    ini_set('display_errors', 'on');
    // DEBUG MODE ON
} else {
    // DEBUG MODE OFF
    ini_set('display_errors', 'off');
    // DEBUG MODE OFF
}

$router = App::getInstance()->getRouter();
$domain = $router->getModule();
bindtextdomain($domain, __DIR__ . "/var/translate");
bindtextdomain('app', __DIR__ . "/var/translate");
bind_textdomain_codeset($domain, 'UTF-8');
textdomain($domain);

if (!$router->routeExist()) {
    /** @var \BaseProject\Error\Controller\Error $_controller */
    $_controller = Controller::getController('Error_Error');
    $_controller->error404Action();
} else if (!$router->rulesAccepted()) {
    /** @var \BaseProject\Error\Controller\Error $_controller */
    $_controller = Controller::getController('Error_Error');
    $_controller->error415Action();
} else {
    if (App::getInstance()->isAllowed()) {
        $controllerName = $router->getControllerClassName();
        /**
         * @var $_controller Controller
         */
        $_controller = Controller::getController($controllerName);
        if ($_controller) {
            $_action = $router->getActionMethodName();
            if (method_exists($_controller, $_action)) {
                call_user_func(array($_controller, $_action));
            }
        }
    } else {
        if (App::getInstance()->getSession()->getUser()) {
            /** @var \BaseProject\Error\Controller\Error $_controller */
            $_controller = Controller::getController('Error_Error');
            $_controller->error403Action();
        } else {
            $_controller = Controller::getController('Login_Index');
            $_controller->redirect($_controller);
        }
    }
}

$page = new Page($_controller);
\Http\Response\send($page->renderer());