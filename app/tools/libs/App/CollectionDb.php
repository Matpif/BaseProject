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
    /**
     * @var array
     */
    protected $_selectedFields;
    /**
     * @var bool
     */
    protected $_createChild;

    public function __construct()
    {
        $this->_db = MyPdo::getInstance(MyPdo::TYPE_MYSQL);
        $this->_nbByPage = self::DEFAULT_NB_BY_PAGE;
        $this->_createChild = false;
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
        $dataParamList = $this->_db->dataParamList($attributes, ' AND ');

        $select = (new QueryFactory())->newSelect()
            ->cols($this->_selectedFields)
            ->from($this->_table)
            ->where($dataParamList)
            ->bindValues($attributes);

        if (is_array($sort)) {
            $select->orderBy($sort);
        }

        if ($page) {
            $select->page($page);
        }

        // TODO: Get child with join fk
//        if ($this->_createChild) {
//            $fk = ConfigModule::getInstance()->getConfig()['fk'];
//            if (isset($fk[$this->_table])) {
//                foreach ($fk[$this->_table] as $foreignKey) {
//                    $select->join('left', $foreignKey['fk_table'], "{$foreignKey['column']} = {$foreignKey['fk_column']}");
//                }
//            }
//        }

        $stmt = $this->_db->prepare($select->getStatement());
        $stmt->execute($select->getBindValues());

        unset($this->_rows);
        $this->_rows = [];

        while ($result = $stmt->fetch(MyPdo::FETCH_ASSOC)) {
            /**
             * @var $model ModelDb
             */
            $model = Model::getModelByClass($this->_model);
            $model->setInserted(true);
            $model->setData($result);
            $this->_rows[] = $model;
        }

        return $this;
    }

    /**
     * Select all attributes
     * @param $id
     * @return null | ModelDb
     */
    public function loadById($id)
    {
        $select = (new QueryFactory())->newSelect()
            ->cols($this->_selectedFields)
            ->from($this->_table)
            ->where("{$this->_key}=:{$this->_key}")
            ->bindValues([$this->_key => $id]);

        $stmt = $this->_db->prepare($select->getStatement());
        $stmt->execute($select->getBindValues());
        $result = $stmt->fetch(MyPdo::FETCH_ASSOC);

        $model = null;
        if ($result && is_array($result)) {
            /**
             * @var $model ModelDb
             */
            $model = Model::getModelByClass($this->_model);
            $model->setInserted(true);
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
        $select = (new QueryFactory())->newSelect()
            ->cols($this->_selectedFields)
            ->from($this->_table);

        if (is_array($sort)) {
            $select->orderBy($sort);
        }

        if ($page) {
            $select->page($page);
        }

        $stmt = $this->_db->prepare($select->getStatement());
        $stmt->execute();

        unset($this->_rows);
        $this->_rows = [];

        while ($result = $stmt->fetch(MyPdo::FETCH_ASSOC)) {
            /**
             * @var $model ModelDb
             */
            $model = Model::getModelByClass($this->_model);
            $model->setInserted(true);
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
            $model = Model::getModelByClass($this->_model);
            $model->setInserted(true);
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
        $select = (new QueryFactory())->newSelect()
            ->cols(['count(*) as nb'])
            ->from($this->_table);

        if (is_array($attributes)) {
            $dataParamList = $this->_db->dataParamList($attributes, ' AND ');
            $select->where($dataParamList)
                ->bindValues($attributes);
        }

        $stmt = $this->_db->prepare($select->getStatement());
        $stmt->execute($select->getBindValues());

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

    /**
     * @return string
     */
    public function getTable()
    {
        return $this->_table;
    }

    /**
     * @return bool
     */
    public function isCreateChild()
    {
        return $this->_createChild;
    }

    /**
     * @param bool $createChild
     */
    public function setCreateChild($createChild)
    {
        $this->_createChild = $createChild;
    }
}