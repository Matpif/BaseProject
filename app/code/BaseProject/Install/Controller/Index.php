<?php

namespace BaseProject\Install\Controller;

use App\App;
use App\ConfigModule;
use App\libs\App\CollectionDb;
use App\libs\App\Controller;
use App\libs\App\Helper;
use App\libs\App\Model;
use App\MyPdo;
use BaseProject\Admin\Model\Module;
use BaseProject\Install\Model\File;
use BaseProject\Login\Helper\Login;
use DateTime;

class Index extends Controller
{

    private $_message;

    /**
     * Login_IndexController constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('/install/index.phtml');
        $this->setTemplateHeader('/admin/header/menu.phtml');
        $this->setTemplateFooter(null);
        $this->setTitle($this->__('Install'));
    }

    public function startScriptAction()
    {
        $this->setTemplate('/install/startScript.phtml');
        $this->addJS('/assets/js/install/install.js');
        $this->setTitle($this->__('Start script'));
        $request = App::getRequestParams();
        if (isset($request['message'])) {
            $this->_message = html_entity_decode($request['message']);
        }
    }

    public function execAction()
    {
        $request = App::getRequestParams();

        if (isset($request['id'])) {
            $config = ConfigModule::getInstance()->getConfigAllModules('Install/path');

            $files = CollectionDb::getInstanceOf('Install_File');
            /** @var File $file */
            $file = $files->loadById($request['id']);
            if ($file) {
                /** @var \BaseProject\Install\Model\Module $module */
                $module = CollectionDb::getInstanceOf('Install_Module')->loadById($file->getModuleId());
                $moduleName = $module->getAttribute('module_name');
                foreach ($GLOBALS['override'] as $override) {
                    $path = App::PathRoot() . '/code/' . $override . '/' . $moduleName . '/' . $config[$moduleName] . '/' . $file->getFileName();
                    if (file_exists($path)) {
                        $contentScript = file_get_contents($path);
                        $myPdo = MyPdo::getInstance(MyPdo::TYPE_MYSQL);
                        $stmt = $myPdo->prepareQuery($contentScript);
                        if ($stmt->execute()) {
                            $file->setAttribute('last_exec', (new DateTime())->format('Y-m-d H:i:s'));
                            $file->save();
                            $this->redirect($this->getUrlAction('startScript') . '/message/Ok script executed with success: ' . $file->getFileName());
                        }
                        $this->redirect($this->getUrlAction('startScript') . '/message/Error when execute script: ' . $file->getFileName());
                    }
                }
            }
        }
        $this->redirect($this->getUrlAction('startScript') . '/message/No request');
    }

    public function clearCacheAction()
    {
        $this->clearCache();
        $this->redirect($this->getUrlAction('startScript'));
    }

    private function clearCache()
    {
        $config = ConfigModule::getInstance()->getConfigAllModules('Install/path');
        foreach ($config as $module => $path) {

            $collectionModule = CollectionDb::getInstanceOf('Install_Module');
            $m = $collectionModule->load(['module_name' => $module])->getFirstRow();

            if (!$m) {
                $m = Model::getModel('Install_Module');
                $m->setAttribute('module_name', $module);
                $m->save();
            }
            foreach ($GLOBALS['override'] as $override) {
                $path = App::PathRoot() . "/code/{$override}/{$module}/{$path}";
                if (file_exists($path)) {
                    $pathFiles = scandir($path);
                    foreach ($pathFiles as $pathFile) {
                        if ($pathFile == '.' || $pathFile == '..') {
                            continue;
                        }

                        $collectionFile = CollectionDb::getInstanceOf('Install_File');
                        $f = $collectionFile->load([
                            'file_name' => $pathFile,
                            'module_id' => $m->getId()
                        ])->getFirstRow();

                        if (!$f) {
                            $f = Model::getModel('Install_File');
                            $f->setAttribute('module_id', $m->getId());
                            $f->setAttribute('file_name', $pathFile);
                            $f->save();
                        }
                    }
                }
            }
        }
    }

    /**
     * @return mixed
     */
    public function getMessage()
    {
        return $this->_message;
    }

    public function isAllowed($action = null)
    {
        $session = App::getInstance()->getSession();
        $user = $session->getUser();
        /** @var Login $helperLogin */
        $helperLogin = Helper::getInstance('Login_Login');
        if ($user) {
            return $helperLogin->hasRole($user, 'Install');
        }

        return false;
    }
}