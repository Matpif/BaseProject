<?php

namespace BaseProject\Admin\Controller;

use App\App;
use App\Config;
use App\ContentTypes;
use App\libs\App\Block;
use App\libs\App\CollectionDb;
use App\libs\App\Controller;
use App\libs\App\Helper;
use App\libs\App\Router;
use BaseProject\Admin\Block\Message;
use BaseProject\Admin\Helper\Admin;
use BaseProject\Admin\Model\Module;
use BaseProject\Login\Helper\Login;
use Exception;
use File_Gettext;
use Redis;

class Index extends Controller
{
    /**
     * Index constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('/admin/index.phtml');
        $this->setTemplateHeader('/admin/header/menu.phtml');
        $this->setTemplateFooter(null);
        $this->setTitle('IndexController');
    }

    public function indexAction()
    {
        $this->setUseCache(true);
        $this->setKey([
            'user' => App::getInstance()->getSession()->getUser(),
            App::getInstance()->getLanguageCode()
        ]);
        if (!$this->cacheExist()) {
            $this->setTitle('Admin');
            $this->setTemplate('/admin/index.phtml');
        }
    }

    public function phpInfoAction()
    {
        echo phpinfo();
        exit;
    }

    public function showConfigAction()
    {
        $this->setTemplate('/admin/config.phtml');
        $this->setTitle('Admin - Config application');
    }

    public function saveConfigAction()
    {
        $params = App::getInstance()->getRequest()->getParsedBody();

        if ($params) {
            $_currentConfig = Config::getInstance();
            $config = array_replace_recursive($_currentConfig->getConfig(), ['app' => $params['app']],
                ['mysql' => $params['mysql']], ['redis' => $params['redis']]);
            $_currentConfig->setConfig($config);
            $this->redirect(Router::getUrlAction('Admin', 'Index', 'showConfig'));
        }
    }

    public function clearCacheAction()
    {
        $filePath = App::PathRoot() . '/var/cache/config.json';
        if (file_exists($filePath)) {
            unlink($filePath);
        }

        if (App::getInstance()->cacheIsEnabled()) {
            $redis = new Redis();
            $redis->connect(Config::getInstance()->getAttribute('redis', 'host'),
                Config::getInstance()->getAttribute('redis', 'port'));
            $redis->flushAll();
        }

        clearstatcache();
        App::getInstance()->getSession()->addMessage([
            'level' => Message::LEVEL_MESSAGE_INFO,
            'message' => 'Clear cache with success'
        ]);

        $this->redirect($this);
    }

    public function clearCacheTranslateAction()
    {

        $languages = scandir(App::PathRoot() . '/locale/');

        if (file_exists(App::PathRoot() . '/var/translate')) {
            system("rm -rf " . escapeshellarg(App::PathRoot() . '/var/translate'));
        }

        foreach ($languages as $language) {
            if ($language == '.' || $language == '..') {
                continue;
            }

            $strings = [];
            $csvFiles = scandir(App::PathRoot() . '/locale/' . $language);
            foreach ($csvFiles as $csvFile) {
                if ($csvFile == '.' || $csvFile == '..') {
                    continue;
                }
                $tanslateModule = [];

                $handle = fopen(App::PathRoot() . '/locale/' . $language . '/' . $csvFile, "r");
                $module = substr($csvFile, 0, strpos($csvFile, '.csv'));

                while (($data = fgetcsv($handle)) !== false) {
                    $s = [$data[0] => $data[1]];
                    $strings = array_merge($strings, $s);
                    $tanslateModule = array_merge($tanslateModule, $s);
                }

                $getText = new File_Gettext();
                $getText->strings = $tanslateModule;
                $getText->meta = [
                    "Project-Id-Version" => App::getInstance()->getAppName() . ' ' . App::getInstance()->getAppVersion() . '\n',
                    "POT-Creation-Date" => ' ' . date('c') . '\n',
                    "PO-Revision-Date" => ' ' . date('c') . '\n',
                    "MIME-Version" => ' 1.0\n',
                    "Content-Type" => ' text/plain; charset=UTF-8\n',
                    "Content-Transfer-Encoding" => ' 8bit\n',
                    "Plural-Forms" => ' nplurals=2; plural=n>1;\n'
                ];
                $mo = $getText->toMO();
//                $po = $getText->toPO();
                if (!file_exists(App::PathRoot() . "/var/translate")) {
                    mkdir(App::PathRoot() . "/var/translate");
                }
                if (!file_exists(App::PathRoot() . "/var/translate/{$language}")) {
                    mkdir(App::PathRoot() . "/var/translate/{$language}");
                }
                if (!file_exists(App::PathRoot() . "/var/translate/{$language}/LC_MESSAGES")) {
                    mkdir(App::PathRoot() . "/var/translate/{$language}/LC_MESSAGES");
                }
//                $po->file = App::PathRoot() . "/var/translate/{$language}/LC_MESSAGES/{$module}.po";
//                $po->save();
                $mo->file = App::PathRoot() . "/var/translate/{$language}/LC_MESSAGES/{$module}.mo";
                $mo->save();
            }

            $getText = new File_Gettext();
            $getText->strings = $strings;
            $getText->meta = [
                "Project-Id-Version" => App::getInstance()->getAppName() . ' ' . App::getInstance()->getAppVersion() . '\n',
                "POT-Creation-Date" => ' ' . date('c') . '\n',
                "PO-Revision-Date" => ' ' . date('c') . '\n',
                "MIME-Version" => ' 1.0\n',
                "Content-Type" => ' text/plain; charset=UTF-8\n',
                "Content-Transfer-Encoding" => ' 8bit\n',
                "Plural-Forms" => ' nplurals=2; plural=n>1;\n'
            ];
            $mo = $getText->toMO();
            if (!file_exists(App::PathRoot() . "/var/translate")) {
                mkdir(App::PathRoot() . "/var/translate");
            }
            if (!file_exists(App::PathRoot() . "/var/translate/{$language}")) {
                mkdir(App::PathRoot() . "/var/translate/{$language}");
            }
            if (!file_exists(App::PathRoot() . "/var/translate/{$language}/LC_MESSAGES")) {
                mkdir(App::PathRoot() . "/var/translate/{$language}/LC_MESSAGES");
            }
            $mo->file = App::PathRoot() . "/var/translate/{$language}/LC_MESSAGES/app.mo";
            $mo->save();
        }

        App::getInstance()->getSession()->addMessage([
            'level' => Message::LEVEL_MESSAGE_INFO,
            'message' => 'Translated with success'
        ]);

        $this->redirect($this);
    }

    public function moduleAction()
    {

        $this->setTitle('Admin Module');
        $this->setTemplate('/admin/module/index.phtml');

        $moduleFilePath = App::PathRoot() . '/tools/modules.json';

        if (file_exists($moduleFilePath)) {
            $modules = json_decode(file_get_contents($moduleFilePath), true);

            $moduleCollection = CollectionDb::getInstanceOf('Admin_Module')->loadAll();
            /** @var Module $module */
            foreach ($moduleCollection as $module) {
                if (($key = array_search($module->getAttribute('module_name'), $modules)) === true) {
                    $module->remove();
                } else {
                    unset($modules[$key]);
                }
            }

            foreach ($modules as $module) {
                $m = new Module();
                $m->setModuleName($module);
                $m->save();
            }
        }
    }

    public function enableModuleAction()
    {
        $request = App::getRequestParams();
        $module = CollectionDb::getInstanceOf('Admin_Module')->load(['module_name' => $request['module-name']])->getFirstRow();
        /** @var Admin $adminHelper */
        $adminHelper = Helper::getInstance('Admin_Admin');
        if ($module) {
            if (App::getInstance()->httpAccepted(ContentTypes::APPLICATION_JSON)) {
                try {
                    if ($request['isChecked'] == 'true') {
                        $adminHelper->enableModule($module);
                        App::getInstance()->getSession()->addMessage([
                            'level' => Message::LEVEL_MESSAGE_SUCCESS,
                            'message' => $this->__('The module has been activated.')
                        ]);
                    } else {
                        $adminHelper->disableModule($module);
                        App::getInstance()->getSession()->addMessage([
                            'level' => Message::LEVEL_MESSAGE_SUCCESS,
                            'message' => $this->__('The module has been disabled.')
                        ]);
                    }
                } catch (Exception $ex) {
                    App::getInstance()->getSession()->addMessage([
                        'level' => Message::LEVEL_MESSAGE_ERROR,
                        'message' => $ex->getMessage()
                    ]);
                }
                /** @var Message $block */
                $block = Block::getBlock('Admin_Message');
                if (App::getInstance()->httpAccepted(ContentTypes::APPLICATION_JSON)) {
                    $modules = CollectionDb::getInstanceOf('Admin_Module')->setSelectedFields([
                        'module_name',
                        'enable'
                    ])->loadAll();
                    $this->sendJson(json_encode([
                        'returned' => $block->getHtml(),
                        'modules' => $modules->getRows()
                    ]));
                }
            }
        }
    }

    public function isAllowed($action = null)
    {
        $session = App::getInstance()->getSession();
        $user = $session->getUser();
        /** @var Login $helperLogin */
        $helperLogin = Helper::getInstance('Login_Login');
        if ($user) {
            switch ($action) {
                case "phpInfo":
                case "clearCacheTranslate":
                case "showConfig":
                case "saveConfig":
                    return $helperLogin->hasRole($user, 'Admin_developer');
                default:
                    return $helperLogin->hasRole($user, 'Admin_admin');
            }
        } else {
            $this->redirect($helperLogin->getUrlLogin());
        }

        return false;
    }
}