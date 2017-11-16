<?php

namespace BaseProject\Cms\Block\Media;

use App\App;
use App\libs\App\Model;
use App\libs\App\Router;
use BaseProject\Cms\Controller\Media;
use BaseProject\Cms\Model\Dir;
use BaseProject\Install\Model\File;

class ListBlock extends \App\libs\App\Block
{


    /**
     * Default_Cms_Media_ListBlock constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('/cms/admin/media/list.phtml');
    }

    /**
     * @param $path string
     * @param $level int
     * @return Dir | array
     */
    public function getElementsDir($path, $level = 0)
    {
        $elements = [];
        if ($level == 0) {
            $path = App::PathRoot() . Media::ROOT_MEDIA . $path;
        }
        if (file_exists($path)) {
            $elements = array_diff(scandir($path), ['..', '.']);
        }
        foreach ($elements as $key => $element) {
            if (is_dir(App::PathRoot() . Media::ROOT_MEDIA . '/' . $element)) {
                unset($elements[$key]);
                /** @var Dir $dir */
                $dir = Model::getModel('Cms_Dir');
                $dir->setPath($path . $element);
                $dir->addChild($this->getElementsDir($path . $element, ($level + 1)));
                $elements[$element] = $dir;
            } else {
                /** @var File $file */
                $file = Model::getModel('Cms_File');
                $file->setFileName($element);
                $file->setPath($path);
                $elements[$key] = $file;
            }
        }

        if ($level == 0) {
            $dir = Model::getModel('Cms_Dir');
            $dir->setPath($path);
            $dir->addChild($elements);

            return $dir;
        }

        return $elements;
    }

    public function getUl($elements)
    {
        $ul = '<ul>';

        if ($elements instanceof Dir) {
            $ul .= '<li class="dir" data-path="' . $elements->getPath(false) . '"><span class="fa fa-folder-open"></span>';
            $ul .= (($elements->getFolderName()) ? $elements->getFolderName() : 'root') . $this->getUl($elements->getChild());
            $ul .= '</li>';
        } else {
            if ($elements instanceof File) {
                $ul .= '<li class="file"><span class="fa fa-file"></span>';
                $ul .= '<a href="' . Router::getUrlAction('Cms',
                        'Media') . '?id=' . $elements->getFileName() . '">' . $elements->getFileName() . '</a>';
                $ul .= '</li>';
            } else {
                if (is_array($elements)) {
                    foreach ($elements as $element) {
                        if ($element instanceof Dir) {
                            $ul .= '<li class="dir" data-path="' . $element->getPath(false) . '"><span class="fa fa-folder-open"></span>';
                            $ul .= $element->getFolderName() . $this->getUl($element->getChild());
                        } else {
                            if ($element instanceof File) {
                                $ul .= '<li class="file"><span class="fa fa-file"></span>';
                                $ul .= '<a href="' . Router::getUrlAction('Cms',
                                        'Media') . '?id=' . $element->getFileName() . '">' . $element->getFileName() . '</a>';
                            }
                        }
                        $ul .= '</li>';
                    }
                }
            }
        }

        return $ul . '</ul>';
    }
}