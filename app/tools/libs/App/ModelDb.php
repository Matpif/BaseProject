<?php

namespace App\libs\App;

use App\ConfigModule;
use App\MyPdo;

abstract class ModelDb extends VarientObject implements ModelInterface
{
    /**
     * Table model
     * @var string
     */
    protected $_table;

    /**
     * ID of table
     * @var string
     */
    protected $_key;

    /**
     * Instance of PDO
     * @var MyPdo
     */
    protected $_db;

    function __construct()
    {
        $this->_db = MyPdo::getInstance(MyPdo::TYPE_MYSQL);
        $this->getConfig();
    }

    protected function getConfig()
    {
        $config = ConfigModule::getInstance()->getConfig($this->getModuleName());
        if (isset($config['tables'])) {
            foreach ($config['tables'] as $table) {
                if (get_class($this) == $table['model']) {
                    $this->_table = $table['table_name'];
                    $this->_key = $table['key'];
                }
            }
        }
    }

    public function getModuleName()
    {
        $className = get_class($this);
        $className = explode('\\', $className);
        $module = (isset($className[1])) ? $className[1] : '';

        return $module;
    }

    /**
     * Save Model into DB
     * @return bool
     */
    public function save()
    {
        Dispatcher::getInstance()->dispatch('before_save_model', $this);
        if (isset($this->_data[$this->_key]) && $this->_data[$this->_key]) {
            $returned = $this->update();
        } else {
            $returned = $this->insert();
        }
        Dispatcher::getInstance()->dispatch('after_save_model', $this);

        return $returned;
    }

    /**
     * Update dans la table les données de $this->data
     *
     * @return bool
     */
    protected function update()
    {
        $dataParamList = $this->_db->dataParamList($this->_data, $this->_key);
        $query = "UPDATE {$this->_table} SET $dataParamList WHERE {$this->_key}=:{$this->_key}";
        $stmt = $this->_db->prepareQuery($query, $this->_data);

        return ($stmt->execute() !== false);
    }

    /**
     * Insert dans la table les données dans $this->data
     *
     * @return bool
     */
    protected function insert()
    {
        $columns = array_keys($this->_data);
        $fieldList = implode(',', $columns);
        $paramList = ':' . implode(", :", $columns);

        $query = "INSERT INTO {$this->_table} ($fieldList) VALUES ($paramList)";

        $stmt = $this->_db->prepareQuery($query, $this->_data);
        if ($stmt->execute() !== false) {
            $this->_data[$this->_key] = $this->_db->lastInsertId();

            return true;
        } else {
            return false;
        }
    }

    /**
     * Supprime le model de sa table
     *
     * @return bool
     */
    public function remove()
    {
        Dispatcher::getInstance()->dispatch('before_remove_model', $this);
        $query = "DELETE FROM {$this->_table} WHERE {$this->_key}=:{$this->_key}";
        $stmt = $this->_db->prepareQuery($query, [$this->_key => $this->getAttribute($this->_key)]);
        $returned = $stmt->execute() !== false;
        Dispatcher::getInstance()->dispatch('after_remove_model', $this);

        return $returned;
    }

    public function __sleep()
    {
        $this->_db = null;

        return get_object_vars($this);
    }

    public function __wakeup()
    {
        $this->_db = MyPdo::getInstance(MyPdo::TYPE_MYSQL);
    }
}