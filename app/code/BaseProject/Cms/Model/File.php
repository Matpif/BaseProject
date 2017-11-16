<?php

namespace BaseProject\Cms\Model;

use App\libs\App\Model;

class File extends Model
{

    /** @var  string */
    private $_path;
    /** @var  string */
    private $_fileName;

    /**
     * @return string
     */
    public function getPath()
    {
        return $this->_path;
    }

    /**
     * @param string $path
     */
    public function setPath($path)
    {
        $this->_path = $path;
    }

    /**
     * @return string
     */
    public function getFileName()
    {
        return $this->_fileName;
    }

    /**
     * @param string $fileName
     */
    public function setFileName($fileName)
    {
        $this->_fileName = $fileName;
    }
}