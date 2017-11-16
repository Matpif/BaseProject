<?php

namespace BaseProject\Cms\Model;

use App\App;
use App\libs\App\Model;
use Exception;

class Dir extends Model
{

    /** @var  string */
    private $_path;
    /** @var  array */
    private $_child;
    /** @var  string */
    private $_folderName;

    /**
     * @param $all boolean
     * @return string
     */
    public function getPath($all = true)
    {
        if ($all) {
            return $this->_path;
        } else {
            return str_replace(App::PathRoot(), '', $this->_path);
        }
    }

    /**
     * @param string $path
     * @throws Exception
     */
    public function setPath($path)
    {
        if (!file_exists($path)) {
            throw new Exception('Directory doesn\'t exist');
        }
        $folderName = explode('/', $path);
        $this->_folderName = $folderName[count($folderName) - 1];
        $this->_path = $path;
    }

    /**
     * @param array | Dir | File $child
     */
    public function addChild($child)
    {
        if (is_array($child)) {
            $this->_child = $child;
        } else {
            $this->_child[] = $child;
        }
    }

    /**
     * @return array
     */
    public function getChild()
    {
        return $this->_child;
    }

    /**
     * @return string
     */
    public function getFolderName()
    {
        return $this->_folderName;
    }
}