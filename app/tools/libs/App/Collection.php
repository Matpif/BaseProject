<?php

namespace App\libs\App;

use App\ConfigModule;
use Iterator;
use JsonSerializable;

abstract class Collection implements CollectionInterface, Iterator, JsonSerializable
{

    /**
     * @var string
     */
    protected $_model;
    /**
     * @var array(Model)
     */
    protected $_rows;
    /**
     * @var int
     */
    private $_position = 0;

    /**
     * @param $model string Name of Model
     * @return Collection | CollectionDb | CollectionWs | CollectionInterface
     */
    static public function getInstanceOf($model)
    {
        $name = explode('_', $model);
        $module = $name[0];
        $configModule = ConfigModule::getInstance()->getConfig($module);

        if (isset($configModule['override']['collections'][$model])) {
            $className = $configModule['override']['collections'][$model];
        } else {
            $override = $GLOBALS['override'];
            $className = '';
            foreach ($override as $o) {
                $className = "{$o}\\{$module}\\Collection";
                foreach ($name as $key => $n) {
                    if ($key == 0) {
                        continue;
                    }
                    $className .= "\\{$n}";
                }
                if (class_exists($className)) {
                    break;
                }
            }
        }

        return new $className;
    }

    /**
     * @return mixed|null|ModelInterface
     */
    public function getFirstRow()
    {
        return (isset($this->_rows[0])) ? $this->_rows[0] : null;
    }

    public function count()
    {
        return count($this->_rows);
    }

    public function current()
    {
        return $this->_rows[$this->_position];
    }

    public function next()
    {
        ++$this->_position;
    }

    public function key()
    {
        return $this->_position;
    }

    public function valid()
    {
        return isset($this->_rows[$this->_position]);
    }

    public function rewind()
    {
        $this->_position = 0;
    }

    /**
     * @param $field string
     * @return Collection
     */
    public function sort($field)
    {
        global $_field;
        $_field = $field;
        usort($this->_rows, array($this, 'compare'));

        return $this;
    }

    public function getRows()
    {
        return $this->_rows;
    }

    /**
     * @return array
     */
    function jsonSerialize()
    {
        return $this->_rows;
    }

    public function getModuleName()
    {
        $className = get_class($this);
        $className = explode('\\', $className);
        $module = (isset($className[1])) ? $className[1] : '';

        return $module;
    }

    /**
     * @param $a ModelDb[ModelWs
     * @param $b ModelDb[ModelWs
     * @return int
     */
    private function compare($a, $b)
    {
        global $_field;
        if ($a->getAttribute($_field) == $b->getAttribute($_field)) {
            return 0;
        }

        return ($a->getAttribute($_field) < $b->getAttribute($_field)) ? 1 : -1;
    }
}