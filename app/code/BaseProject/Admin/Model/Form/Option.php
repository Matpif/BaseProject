<?php
namespace BaseProject\Admin\Model\Form;

use App\libs\App\Model;

class Option extends Model
{

    private $_value;
    private $_label;

    /**
     * Option constructor.
     * @param $_value
     * @param $_label
     */
    public function __construct($_value, $_label)
    {
        $this->_value = $_value;
        $this->_label = $_label;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->_value;
    }

    /**
     * @param mixed $value
     */
    public function setValue($value)
    {
        $this->_value = $value;
    }

    /**
     * @return mixed
     */
    public function getLabel()
    {
        return $this->_label;
    }

    /**
     * @param mixed $label
     */
    public function setLabel($label)
    {
        $this->_label = $label;
    }
}