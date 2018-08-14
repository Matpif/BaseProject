<?php

namespace App\libs\App;

use App\ConfigModule;
use App\MyPdo;

abstract class ModelDb extends VarientObject implements ModelInterface
{
    CONST CHECK_ACTION_INSERT = 'insert';
    CONST CHECK_ACTION_UPDATE = 'update';
    CONST CHECK_ACTION_REMOVE = 'remove';

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

    /**
     * @var array
     */
    protected $_fields;

    /**
     * @var array
     */
    protected $_types;

    /**
     * @var Message
     */
    protected $_errorInfo;
    /**
     * @var array
     */
    protected $_fieldsWithForeignKey;

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

    public function reload($fields = ['*']) {
        $select = (new QueryFactory())->newSelect()
            ->cols($fields)
            ->from($this->_table);
        if (is_array($this->_key)) {
            foreach ($this->_key as $key) {
                $select->where($key . '=:' . $key)->bindValue($key, $this->_data[$key]);
            }
        } else {
            $select->where($this->_key . '=:' . $this->_key)->bindValue($this->_key, $this->_data[$this->_key]);
        }
        $stmt = $this->_db->prepareQuery($select->getStatement());

        if ($stmt->execute($select->getBindValues())) {
            if ($result = $stmt->fetch(MyPdo::FETCH_ASSOC)) {
                foreach ($result as $key => $value) {
                    $this->setAttribute($key, $value);
                }
                $this->_inserted = true;
                return true;
            }
        }
        return false;
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
        if ($this->checkModel(self::CHECK_ACTION_UPDATE)) {
            $colsToUpdate = array_keys($this->_data);

            if (is_array($this->_key)) {
                foreach ($this->_key as $key) {
                    if (isset($this->_data[$key])) {
                        foreach ($colsToUpdate as $k => $v) {
                            if ($v == $key) {
                                unset($colsToUpdate[$k]);
                                break;
                            }
                        }
                    }
                }
            } else {
                if (isset($this->_data[$this->_key])) {
                    foreach ($colsToUpdate as $k => $v) {
                        if ($v == $this->_key) {
                            unset($colsToUpdate[$k]);
                            break;
                        }
                    }
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
                $message = new Message($stmt->errorInfo()[2], Message::LEVEL_ERROR);
                $message->setCode($stmt->errorInfo()[0]);
                $this->_errorInfo = $message;
                Dispatcher::getInstance()->dispatch('error_update_model', $this);
                return false;
            }
        } else {
            Dispatcher::getInstance()->dispatch('error_check_model', $this);
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
        if ($this->checkModel(self::CHECK_ACTION_INSERT)) {
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
                $this->_inserted = true;
                return true;
            } else {
                $message = new Message($stmt->errorInfo()[2], Message::LEVEL_ERROR);
                $message->setCode($stmt->errorInfo()[0]);
                $this->_errorInfo = $message;
                Dispatcher::getInstance()->dispatch('error_insert_model', $this);
                return false;
            }
        } else {
            Dispatcher::getInstance()->dispatch('error_check_model', $this);
            return false;
        }
    }

    /**
     * @param $pColName
     * @return mixed
     */
    public function getColumnType($pColName)
    {
        if (!$this->_fields) {
            $this->getColumnsName();
        }
        return $this->_types[$pColName];
    }

    /**
     * @return array
     */
    public function getColumnsName()
    {
        if (!$this->_fields) {
            $this->_fields = [];

            $stmt = $this->_db->prepareQuery("DESC {$this->_table}");
            $stmt->execute();

            while ($result = $stmt->fetch(\App\MyPdo::FETCH_ASSOC)) {
                $this->_fields[] = $result['Field'];
                $this->_types[$result['Field']] = $result['Type'];
                if ($result['Key']) {
                    $this->_fieldsWithForeignKey[] = $result['Field'];
                }
            }
        }
        return $this->_fields;
    }

    /**
     * @param $pColName
     * @return bool
     */
    public function columHasForeignKey($pColName)
    {
        return in_array($pColName, $this->_fieldsWithForeignKey);
    }

    public function getDefaultLine()
    {
        $stmt = $this->_db->prepareQuery("DESC {$this->_table}");
        $stmt->execute();

        while ($result = $stmt->fetch(\App\MyPdo::FETCH_ASSOC)) {
            $this->_data[$result['Field']] = $result['Default'];
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

        if ($this->checkModel(self::CHECK_ACTION_REMOVE)) {
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
                $message = new Message($stmt->errorInfo()[2], Message::LEVEL_ERROR);
                $message->setCode($stmt->errorInfo()[0]);
                $this->_errorInfo = $message;
                Dispatcher::getInstance()->dispatch('error_remove_model', $this);
            }
            Dispatcher::getInstance()->dispatch('after_remove_model', $this);
        } else {
            Dispatcher::getInstance()->dispatch('error_check_model', $this);
            $returned = false;
        }

        return $returned;
    }

    /**
     * @param string $name
     * @param string | null $attribute
     * @param bool $forceReload
     * @param bool $inverse
     * @return ModelDb | CollectionDb | null
     */
    public function foreignInstance($name, $attribute = null, $forceReload = false, $inverse = false)
    {
        if (!$forceReload && $attribute && isset($this->_foreignInstance[$name . $attribute])) {
            return $this->_foreignInstance[$name . $attribute];
        } else {
            if (!$forceReload && isset($this->_foreignInstance[$name])) {
                return $this->_foreignInstance[$name];
            }
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

        if (!$inverse) {
            if ($name && is_array($fk) && isset($fk[$this->_table]) && $table) {
                foreach ($fk[$this->_table] as $foreignKey) {
                    if ($foreignKey['fk_table'] == $table['table_name']) {
                        foreach ($foreignKey['link'] as $link) {
                            if ($attribute && $link['fk_column'] == $attribute || !$attribute) {
                                $instance = CollectionDb::getInstanceOf($name)->load([$link['column'] => $this->getAttribute($link['fk_column'])])->getFirstRow();
                                if ($attribute) {
                                    $this->_foreignInstance[$name . $attribute] = $instance;
                                } else {
                                    $this->_foreignInstance[$name] = $instance;
                                }
                                return $instance;
                            }
                        }
                    }
                }
            }
        } else {
            if (!$attribute) {
                $attribute = $this->_key;
            }
            if ($name && is_array($fk) && $table && isset($fk[$table['table_name']])) {
                foreach ($fk[$table['table_name']] as $foreignKey) {
                    if ($foreignKey['fk_table'] == $this->_table) {
                        foreach ($foreignKey['link'] as $link) {
                            if ($attribute && $link['fk_column'] == $attribute || !$attribute) {
                                $instance = CollectionDb::getInstanceOf($name)->load([$link['fk_column'] => $this->getAttribute($link['column'])]);
                                if ($attribute) {
                                    $this->_foreignInstance[$name . $attribute] = $instance;
                                } else {
                                    $this->_foreignInstance[$name] = $instance;
                                }
                                return $instance;
                            }
                        }
                    }
                }
            }
        }
        return null;
    }

    /**
     * @param string $action
     * @return bool
     */
    protected function checkModel($action = null)
    {
        return true;
    }

    /**
     * @return Message
     */
    public function getErrorInfo()
    {
        return $this->_errorInfo;
    }

    /**
     * @param Message $errorInfo
     */
    public function setErrorInfo($errorInfo)
    {
        $this->_errorInfo = $errorInfo;
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