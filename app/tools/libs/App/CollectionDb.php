<?php

namespace App\libs\App;

use App\ConfigModule;
use App\MyPdo;

abstract class CollectionDb extends Collection
{
    const SORT_ASC = 'ASC';
    const SORT_DESC = 'DESC';
    const DEFAULT_NB_BY_PAGE = 20;
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
     * @var int
     */
    protected $_nbByPage;
    protected $_selectedFields;

    public function __construct()
    {
        $this->_db = MyPdo::getInstance(MyPdo::TYPE_MYSQL);
        $this->_nbByPage = self::DEFAULT_NB_BY_PAGE;
        $this->getConfig();
    }

    protected function getConfig()
    {
        $config = ConfigModule::getInstance()->getConfig($this->getModuleName());
        if (isset($config['tables'])) {
            foreach ($config['tables'] as $table) {
                if (get_class($this) == $table['collection']) {
                    $this->_table = $table['table_name'];
                    $this->_key = $table['key'];
                    $this->_model = $table['model'];
                    if (isset($table['selected_fileds'])) {
                        $this->_selectedFields = $table['selected_fileds'];
                    } else {
                        $this->_selectedFields = ['*'];
                    }
                }
            }
        }
    }

    /**
     * Load Models by attributes values
     * @param $attributes
     * @param null|array $sort
     * @param null|int $page
     * @return CollectionDb
     */
    public function load($attributes, $sort = null, $page = null)
    {

        $dataParamList = $this->_db->dataParamList($attributes, $this->_key, ' AND ', true);
        $query = "SELECT {$this->getSelectedFieldsToString()} FROM {$this->_table} WHERE " . $dataParamList;

        if (is_array($sort)) {
            foreach ($sort as $key => $value) {
                $query .= " ORDER BY " . $key . ' ' . $value . ',';
            }
            $query = substr($query, 0, -1);
        }

        if ($page) {
            $firstRow = $this->_nbByPage * $page - $this->_nbByPage;
            $query .= " LIMIT " . $firstRow . ',' . $this->_nbByPage;
        }

        $stmt = $this->_db->prepareQuery($query, $attributes);
        $stmt->execute();

        unset($this->_rows);
        $this->_rows = [];

        while ($result = $stmt->fetch(MyPdo::FETCH_ASSOC)) {
            /**
             * @var $model ModelDb
             */
            $model = new $this->_model;
            $model->setData($result);
            $this->_rows[] = $model;
        }

        return $this;
    }

    protected function getSelectedFieldsToString()
    {
        $selectedFields = '';
        foreach ($this->_selectedFields as $selectedField) {
            $selectedFields .= $selectedField . ', ';
        }
        $selectedFields = substr($selectedFields, 0, strlen($selectedFields) - 2);

        return $selectedFields;
    }

    /**
     * Select all attributes
     * @param $id
     * @return null | ModelDb
     */
    public function loadById($id)
    {

        $query = "SELECT {$this->getSelectedFieldsToString()} FROM {$this->_table} WHERE {$this->_key} = :{$this->_key}";
        $stmt = $this->_db->prepareQuery($query, [$this->_key => $id]);
        $stmt->execute();
        $result = $stmt->fetch(MyPdo::FETCH_ASSOC);

        $model = null;
        if ($result && is_array($result)) {
            /**
             * @var $model ModelDb
             */
            $model = new $this->_model;
            $model->setData($result);
        }

        return $model;
    }

    /**
     * @param null | array $sort
     * @param null | int $page
     * @return $this
     */
    public function loadAll($sort = null, $page = null)
    {
        $query = "SELECT {$this->getSelectedFieldsToString()} FROM {$this->_table}";

        if (is_array($sort)) {
            foreach ($sort as $key => $value) {
                $query .= " ORDER BY " . $key . ' ' . $value . ',';
            }
            $query = substr($query, 0, -1);
        }

        if ($page) {
            $firstRow = $this->_nbByPage * $page - $this->_nbByPage;
            $query .= " LIMIT " . $firstRow . ',' . $this->_nbByPage;
        }

        $stmt = $this->_db->prepareQuery($query);
        $stmt->execute();

        unset($this->_rows);
        $this->_rows = [];

        while ($result = $stmt->fetch(MyPdo::FETCH_ASSOC)) {
            /**
             * @var $model ModelDb
             */
            $model = new $this->_model;
            $model->setData($result);
            $this->_rows[] = $model;
        }

        return $this;
    }

    /**
     * @param $query string
     * @param $attributes array
     * @return CollectionDb
     */
    public function loadByQuery($query, $attributes = null)
    {

        $stmt = $this->_db->prepareQuery($query, $attributes);
        $stmt->execute();

        unset($this->_rows);
        $this->_rows = [];

        while ($result = $stmt->fetch(MyPdo::FETCH_ASSOC)) {
            /**
             * @var $model ModelDb
             */
            $model = new $this->_model;
            $model->setData($result);
            $this->_rows[] = $model;
        }

        return $this;
    }

    /**
     * @param null $attributes
     * @return int
     */
    public function countElements($attributes = null)
    {

        $query = "SELECT count(*) as nb FROM {$this->_table}";

        if (is_array($attributes)) {
            $dataParamList = $this->_db->dataParamList($attributes, $this->_key, ' AND ', true);
            $query .= " WHERE " . $dataParamList;
        }

        $stmt = $this->_db->prepareQuery($query, $attributes);
        $stmt->execute();

        $result = $stmt->fetch(MyPdo::FETCH_ASSOC);
        $nb = $result['nb'];

        return $nb;
    }

    /**
     * @param array $selectedFields
     * @return $this
     */
    public function setSelectedFields($selectedFields)
    {
        $this->_selectedFields = $selectedFields;

        return $this;
    }
}