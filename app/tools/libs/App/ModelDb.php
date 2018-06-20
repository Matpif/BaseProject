<?php

namespace App\libs\App;

use App\App;
use App\Config;
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

    /**
     * @var bool
     */
    protected $_inserted;

    /**
     * @var array
     */
    protected $_foreignInstance;

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
        if ($this->isInserted()) {
            $returned = $this->update();
        } else {
            $returned = $this->insert();
        }
        Dispatcher::getInstance()->dispatch('after_save_model', $this);

        return $returned;
    }

    /**
     * @return bool
     */
    public function isInserted()
    {
        return $this->_inserted;
    }

    /**
     * @param bool $inserted
     */
    public function setInserted($inserted)
    {
        $this->_inserted = $inserted;
    }

    /**
     * Update dans la table les données de $this->data
     *
     * @return bool
     */
    protected function update()
    {
        $colsToUpdate = array_keys($this->_data);

        if (is_array($this->_key)) {
            foreach ($this->_key as $key) {
                if (isset($colsToUpdate[$key])) {
                    unset($colsToUpdate[$key]);
                }
            }
        } else {
            if (isset($colsToUpdate[$this->_key])) {
                unset($colsToUpdate[$this->_key]);
            }
        }

        $update = (new QueryFactory())->newUpdate()
            ->table($this->_table)
            ->cols($colsToUpdate)
            ->bindValues($this->_data);

        if (is_array($this->_key)) {
            foreach ($this->_key as $key) {
                $update->where("{$key}=:{$key}");
            }
        } else {
            $update->where("{$this->_key}=:{$this->_key}");
        }

        $stmt = $this->_db->prepare($update->getStatement());

        if ($stmt->execute($update->getBindValues()) !== false) {
            return true;
        } else {
            Dispatcher::getInstance()->dispatch('error_update_model', $this);
            return false;
        }
    }

    /**
     * Insert dans la table les données dans $this->data
     *
     * @return bool
     */
    protected function insert()
    {
        $columns = array_keys($this->_data);
        $insert = (new QueryFactory())->newInsert()
            ->into($this->_table)
            ->cols($columns)
            ->bindValues($this->_data);

        $stmt = $this->_db->prepare($insert->getStatement());
        if ($stmt->execute($insert->getBindValues()) !== false) {
            $insertId = $this->_db->lastInsertId();
            if (!is_array($this->_key) && $insertId) {
                $this->_data[$this->_key] = $insertId;
            }
            return true;
        } else {
            Dispatcher::getInstance()->dispatch('error_insert_model', $this);
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

        $delete = (new QueryFactory())->newDelete()
            ->from($this->_table);

        $_keys = [];
        if (is_array($this->_key)) {
            foreach ($this->_key as $key) {
                $delete->where("{$key}=:{$key}");
                $_keys[$key] = $this->getAttribute($key);
            }
        } else {
            $delete->where("{$this->_key}=:{$this->_key}");
            $_keys[$this->_key] = $this->getAttribute($this->_key);
        }

        $delete->bindValues($_keys);

        $stmt = $this->_db->prepare($delete->getStatement());
        $returned = $stmt->execute($delete->getBindValues()) !== false;
        if (!$returned) {
            Dispatcher::getInstance()->dispatch('error_remove_model', $this);
        }
        Dispatcher::getInstance()->dispatch('after_remove_model', $this);

        return $returned;
    }

    /**
     * @param string $name
     * @param string | null $attribute
     * @param bool $forceReload
     * @return ModelDb | null
     */
    public function foreignInstance($name, $attribute = null, $forceReload = false)
    {
        if (!$forceReload && $attribute && isset($this->_foreignInstance[$name.$attribute])) {
            return $this->_foreignInstance[$name.$attribute];
        } else if (!$forceReload && isset($this->_foreignInstance[$name])) {
            return $this->_foreignInstance[$name];
        }

        $exName = explode('_', $name);
        $module = $exName[0];

        $configModule = ConfigModule::getInstance()->getConfig($module);
        $fk = ConfigModule::getInstance()->getConfig()['fk'];
        $table = null;

        foreach ($configModule['tables'] as $t) {
            $exModel = explode('\\', $t['model']);
            $modelName = $exModel[1] . '_' . $exModel[count($exModel) - 1];
            if ($modelName == $name) {
                $table = $t;
                break;
            }
        }

        if ($name && is_array($fk) && isset($fk[$this->_table]) && $table) {
            foreach ($fk[$this->_table] as $foreignKey) {
                if ($foreignKey['fk_table'] == $table['table_name'] && ($attribute && $foreignKey['column'] == $attribute || !$attribute)) {
                    $instance = CollectionDb::getInstanceOf($name)->load([$foreignKey['column'] => $this->getAttribute($foreignKey['column'])])->getFirstRow();
                    if ($attribute) {
                        $this->_foreignInstance[$name.$attribute] = $instance;
                    } else {
                        $this->_foreignInstance[$name] = $instance;
                    }
                    return $instance;
                }
            }
        }
        return null;
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