<?php

namespace BaseProject\Admin\Block;


use BaseProject\Admin\Model\Form\Field;
use BaseProject\Cms\Block\Block;

class FormAdmin extends Block
{
    /** @var string */
    private $_action;
    /** @var string */
    private $_method;
    /** @var array */
    private $_fields;
    /** @var array */
    private $_buttons;

    /**
     * Menu constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('/admin/Field.phtml');
    }

    /**
     * @param $field Field
     */
    public function addField($field)
    {
        $this->_fields[] = $field;
    }

    public function addButton($button) {
        $this->_buttons[] = $button;
    }

    /**
     * @return string
     * @throws \Exception
     */
    public function build()
    {
        $fieldString = '';
        /** @var Field $field */
        foreach ($this->_fields as $field) {
            $fieldString .= $field->build();
        }
        /** @var Field $button */
        foreach ($this->_buttons as $button) {
            $fieldString .= $button->build();
        }
        $form = <<<EOT
<form action="{$this->_action}" method="{$this->_method}">
    {$fieldString}
</form>
EOT;
        return $form;
    }

    /**
     * @param mixed $action
     */
    public function setAction($action)
    {
        $this->_action = $action;
    }

    /**
     * @param mixed $method
     */
    public function setMethod($method)
    {
        $this->_method = $method;
    }

    /**
     * @param array $fields
     */
    public function setFields($fields)
    {
        $this->_fields = $fields;
    }

    /**
     * @param array $buttons
     */
    public function setButtons($buttons)
    {
        $this->_buttons = $buttons;
    }
}