<?php

namespace BaseProject\Admin\Model;

use App\libs\App\ModelDb;
use \Exception;

/**
 *
 * @method setName(string $name)
 * @method string getName()
 * @method setType(string $type)
 * @method string getType()
 *
 * @dbField name nvarchar(150) PRIMARY KEY
 * @dbField type enum('string', 'int', 'datetime', 'date', 'text') NOT NULL default 'string'
 * @dbField value_string nvarchar(250) NULL
 * @dbField value_int int NULL
 * @dbField value_datetime datetime NULL
 * @dbField value_text text NULL
 *
 * Class Parameter
 * @package BaseProject\Admin\Model
 */
class Parameter extends ModelDb
{
    const TYPE_STRING = 'string';
    const TYPE_INT = 'int';
    const TYPE_DATETIME = 'datetime';
    const TYPE_DATE = 'date';
    const TYPE_TEXT = 'text';

    /** @var  bool */
    private $_insert;

    /**
     * Task_TaskModel constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->_insert = false;
    }

    /**
     * @return mixed|null|string
     * @throws Exception
     */
    public function getValue()
    {
        if (!$this->getType()) {
            throw new Exception("Type is not define");
        }
        switch ($this->getType()) {
            case self::TYPE_STRING:
                return $this->getAttribute('value_string');
                break;
            case self::TYPE_INT:
                return $this->getAttribute('value_int');
                break;
            case self::TYPE_DATETIME:
            case self::TYPE_DATE:
                return $this->getAttribute('value_datetime');
                break;
            case self::TYPE_TEXT:
                return $this->getAttribute('value_text');
                break;
            default:
                return null;
        }
    }

    /**
     * @param $value
     * @throws Exception
     */
    public function setValue($value)
    {
        if (!$this->getType()) {
            throw new Exception("Type is not define");
        }
        switch ($this->getType()) {
            case self::TYPE_STRING:
                $this->setAttribute('value_string', $value);
                break;
            case self::TYPE_INT:
                $this->setAttribute('value_int', $value);
                break;
            case self::TYPE_DATETIME:
            case self::TYPE_DATE:
                $this->setAttribute('value_datetime', $value);
                break;
            case self::TYPE_TEXT:
                $this->setAttribute('value_text', $value);
                break;
        }
    }

    /**
     * @inheritdoc
     */
    public function save()
    {
        if (!$this->_insert) {
            return $this->update();
        } else {
            return $this->insert();
        }
    }

    /**
     * @param bool $insert
     */
    public function setInsert($insert)
    {
        $this->_insert = $insert;
    }
}