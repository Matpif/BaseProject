<?php

namespace BaseProject\Cms\Controller;

use App\App;
use App\libs\App\Controller;
use App\libs\App\Helper;
use App\libs\App\Message;
use App\libs\App\Router;
use BaseProject\Login\Helper\Login;

class Media extends Controller
{

    const ROOT_MEDIA = '/skin/media';
    const MAX_SIZE = 500000;

    /**
     * Cms_MediaController constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->setTemplateHeader('/admin/header/menu.phtml');
        $this->setTemplateFooter(null);
        $this->setTitle('Cms Media');
        $this->addCss('/skin/css/cms/media.css');
    }

    public function indexAction()
    {
        $this->setTemplate('/cms/admin/media/index.phtml');
        $this->addJS('/skin/libs/dropzonejs/dropzone.js');
        $this->addJS('/skin/js/cms/media.js');
    }

    public function uploadAction()
    {
        $post = App::getRequestParams('post');

        $target_dir = App::PathRoot() . self::ROOT_MEDIA . '/' . $post['path'];
        if (!file_exists($target_dir)) {
            if (!mkdir($target_dir)) {
                App::getInstance()->getSession()->addMessage([
                    'level' => Message::LEVEL_ERROR,
                    'message' => $this->__('Sorry, there was an error when create folder..')
                ]);
                $this->redirect(Router::getUrlAction('Cms', 'Media'));
            }
        }
        $target_file = $target_dir . basename($_FILES["fileToUpload"]["name"]);

        if (file_exists($target_file)) {
            App::getInstance()->getSession()->addMessage([
                'level' => Message::LEVEL_ERROR,
                'message' => $this->__('Sorry, file already exists.')
            ]);
            $this->redirect(Router::getUrlAction('Cms', 'Media'));
        }

        if ($_FILES["fileToUpload"]["size"] > self::MAX_SIZE) {
            App::getInstance()->getSession()->addMessage([
                'level' => Message::LEVEL_ERROR,
                'message' => $this->__('Sorry, your file is too large.')
            ]);
            $this->redirect(Router::getUrlAction('Cms', 'Media'));
        }

        if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file)) {
            App::getInstance()->getSession()->addMessage([
                'level' => Message::LEVEL_INFO,
                'message' => $this->__('The file has been uploaded')
            ]);
            $this->redirect(Router::getUrlAction('Cms', 'Media'));
        } else {
            App::getInstance()->getSession()->addMessage([
                'level' => Message::LEVEL_ERROR,
                'message' => $this->__('Sorry, there was an error uploading your file.')
            ]);
            $this->redirect(Router::getUrlAction('Cms', 'Media'));
        }
    }

    public function removeAction()
    {

    }

    public function createRepositoryAction()
    {

    }

    public function isAllowed($action = null)
    {
        $session = App::getInstance()->getSession();
        $user = $session->getUser();
        /** @var Login $helperLogin */
        $helperLogin = Helper::getInstance('Login_Login');
        if ($user) {
            return $helperLogin->hasRole($user, 'cms_media');
        }

        return false;
    }
}