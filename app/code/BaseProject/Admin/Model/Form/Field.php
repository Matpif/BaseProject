<?php

namespace BaseProject\Admin\Model\Form;


use App\libs\App\Model;

class Field extends Model
{

    const TYPE_TEXT = 'text';
    const TYPE_PASSWORD = 'password';
    const TYPE_HIDDEN = 'hidden';
    const TYPE_SELECT = 'select';
    const TYPE_CHECKBOX = 'checkbox';
    const TYPE_RADIO = 'radio';
    const TYPE_BUTTON = 'button';

    /**
     * @var string
     */
    private $_type;
    /**
     * @var string
     */
    private $_name;
    /**
     * @var string
     */
    private $_label;
    /**
     * @var array
     */
    private $_attributes;
    /**
     * @var array
     */
    private $_options;
    /**
     * @var array
     */
    private $_class;

    /**
     * @return string
     * @throws \Exception
     */
    public function build()
    {
        $templateField = <<<EOT
<div class="form-group">
    <label>{$this->_label}</label>
    {{field}}
</div>
EOT;
        $field = '';
        switch ($this->_type) {
            case self::TYPE_TEXT:
                $class[] = "form-control";
                $field = <<<EOT
<input type="text" class="{$this->classToString()}" name="{$this->_name}"{$this->attributeToString()} />
EOT;
                $field .= str_replace('{{field}}', $field, $templateField);
                break;
            case self::TYPE_PASSWORD:
                $class[] = "form-control";
                $field = <<<EOT
<input type="password" class="{$this->classToString()}" name="{$this->_name}"{$this->attributeToString()} />
EOT;
                $field .= str_replace('{{field}}', $field, $templateField);
                break;
            case self::TYPE_HIDDEN:
                $field = <<<EOT
<input type="hidden" name="{$this->_name}"{$this->attributeToString()} />
EOT;
                $field .= str_replace('{{field}}', $field, $templateField);
                break;
            case self::TYPE_SELECT:
                $class[] = "form-control";
                $options = '';
                /** @var Option $option */
                foreach ($this->_options as $option) {
                    $options .= <<<EOT
<option value="{$option->getValue()}">{$option->getLabel()}</option>
EOT;
                }
                $field = <<<EOT
<select name="{$this->_name}" class="{$this->classToString()}">
    {$options}
</select>
EOT;
                $field .= str_replace('{{field}}', $field, $templateField);
                break;
            case self::TYPE_CHECKBOX:
                $field = <<<EOT
<div class="form-group">
    <div class="checkbox">
        <input type="checkbox" class="{$this->classToString()}" name="{$this->_name}"{$this->attributeToString()} />
        {$this->_label}
    </div>
</div>
EOT;
                break;
            case self::TYPE_RADIO:
                $field = <<<EOT
<div class="form-group">
    <div class="checkbox">
        <input type="radio" class="{$this->classToString()}" name="{$this->_name}"{$this->attributeToString()} />
        {$this->_label}
    </div>
</div>
EOT;
                break;
            case self::TYPE_BUTTON:
                $class[] = "btn";
                $field = <<<EOT
<button class="{$this->classToString()}" name="{$this->_name}"{$this->attributeToString()}>{$this->_label}</button>
EOT;
                break;
            default:
                throw new \Exception("Unknown type");
        }

        return $field;
    }

    private function classToString()
    {
        $classString = '';

        foreach ($this->_class as $value) {
            $classString .= $value . ' ';
        }

        return $classString;
    }

    private function attributeToString()
    {
        $attributeString = '';

        foreach ($this->_attributes as $name => $value) {
            $attributeString .= $name . '="' . $value . '" ';
        }

        return $attributeString;
    }

    /**
     * @param string $type
     */
    public function setType($type)
    {
        $this->_type = $type;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->_name = $name;
    }

    /**
     * @param array $attributes
     */
    public function setAttributes($attributes)
    {
        $this->_attributes = $attributes;
    }

    /**
     * @param array $options
     */
    public function setOptions($options)
    {
        $this->_options = $options;
    }

    /**
     * @param string $label
     */
    public function setLabel($label)
    {
        $this->_label = $label;
    }
}