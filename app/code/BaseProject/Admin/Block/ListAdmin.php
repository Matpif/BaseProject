<?php

namespace BaseProject\Admin\Block;


use App\libs\App\Block;

class ListAdmin extends Block
{
    /** @var array */
    private $_headerLabel;
    /** @var array */
    private $_lines;
    /** @var array */
    private $_colsWidth;
    /** @var string */
    private $_urlToClick;
    /** @var array */
    private $_urlParams;

    /**
     * Menu constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->_colsWidth = [];
        $this->_lines = [];
        $this->_headerLabel = [];
        $this->setTemplate('/admin/list.phtml');
    }

    /**
     * @return array
     */
    public function getHeaderLabel()
    {
        return $this->_headerLabel;
    }

    /**
     * @param array $headerLabel
     */
    public function setHeaderLabel($headerLabel)
    {
        $this->_headerLabel = $headerLabel;
    }

    /**
     * @return array
     */
    public function getLines()
    {
        return $this->_lines;
    }

    /**
     * @param array $lines
     */
    public function setLines($lines)
    {
        $this->_lines = $lines;
    }

    /**
     * @return array
     */
    public function getColsWidth()
    {
        return $this->_colsWidth;
    }

    /**
     * @param array $colWidth
     */
    public function setColsWidth($colWidth)
    {
        $this->_colsWidth = $colWidth;
    }

    /**
     * @return string
     */
    public function getUrlToClick()
    {
        return $this->_urlToClick;
    }

    /**
     * @param string $urlToClick
     */
    public function setUrlToClick($urlToClick)
    {
        $this->_urlToClick = $urlToClick;
    }

    /**
     * @return array
     */
    public function getUrlParams()
    {
        return $this->_urlParams;
    }

    /**
     * @param array $urlParams
     */
    public function setUrlParams($urlParams)
    {
        $this->_urlParams = $urlParams;
    }

    /**
     * @param $line
     * @return null|string
     */
    public function buildUrl($line) {

        if ($this->_urlToClick) {
            $url = $this->_urlToClick;
            foreach ($this->_urlParams as $param) {
                $url = str_replace('{'.$param.'}', $line[$param], $url);
            }

            return $url;
        }
        return null;
    }
}