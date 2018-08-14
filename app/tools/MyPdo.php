<?php

namespace App;

use PDO;
use PDOStatement;

class MyPdo extends PDO
{

    const TYPE_MYSQL = 1;
    const TYPE_SQLITE = 2;
    const TYPE_AS400 = 3;
    private static $_instance_mysql;
    private static $_instance_sqlite;
    private static $_instance_as400;

    /**
     * MyPdo constructor.
     * @param int $type
     */
    public function __construct($type)
    {

        if ($type == self::TYPE_MYSQL) {
            $bdd = 'mysql';
            $dbname = Config::getInstance()->getAttribute($bdd, 'dbname');
            $host = Config::getInstance()->getAttribute($bdd, 'host');
            $dsn = 'mysql:dbname=' . $dbname . ';host=' . $host;
            $user = Config::getInstance()->getAttribute($bdd, 'user');
            $pass = Config::getInstance()->getAttribute($bdd, 'pass');
            parent::__construct($dsn, $user, $pass, array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'));
        } else {
            if ($type == self::TYPE_SQLITE) {
                parent::__construct("");
            } else {
                if ($type == self::TYPE_AS400) {
                    $bdd = 'as400';
                    $driver = Config::getInstance()->getAttribute($bdd, 'driver');
                    $dbname = Config::getInstance()->getAttribute($bdd, 'dbname');
                    $host = Config::getInstance()->getAttribute($bdd, 'host');
                    $user = Config::getInstance()->getAttribute($bdd, 'user');
                    $password = Config::getInstance()->getAttribute($bdd, 'pass');
                    $dsn = 'odbc:DRIVER=' . $driver . ';DBQ=' . $dbname . ';SYSTEM=' . $host;
                    parent::__construct($dsn, $user, $password, array(
                            PDO::ATTR_PERSISTENT => true,
                            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
                        )
                    );
                }
            }
        }
    }

    public static function getInstance($type)
    {
        switch ($type) {
            case self::TYPE_MYSQL:
                if (self::$_instance_mysql == null) {
                    self::$_instance_mysql = new MyPdo(self::TYPE_MYSQL);
                }

                return self::$_instance_mysql;
                break;
            case self::TYPE_SQLITE:
                if (self::$_instance_sqlite == null) {
                    self::$_instance_sqlite = new MyPdo(self::TYPE_SQLITE);
                }

                return self::$_instance_sqlite;
                break;
            case self::TYPE_AS400:
                if (self::$_instance_as400 == null) {
                    self::$_instance_as400 = new MyPdo(self::TYPE_AS400);
                }

                return self::$_instance_as400;
                break;
        }

        return null;
    }

    /**
     * Créer le string "attribut1 = :attribut1, attribut2 = :attribut2, ..." pour l'update
     * @param $data
     * @param string $delimiter
     * @return string
     */
    public function dataParamList($data, $delimiter = ', ')
    {
        $string = "";
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $signe = $value[0];

                if ($signe == 'between') {
                    $string .= "{$key} {$signe} :{$key}0 AND :{$key}1{$delimiter}";
                } else {
                    $string .= "{$key}{$signe}:{$key}{$delimiter}";
                }
            } else {
                $string .= "{$key}=:{$key}{$delimiter}";
            }
        }
        $string = substr($string, 0, -strlen($delimiter));

        return $string;
    }

    /**
     * Add param value in query
     * @param $query
     * @param null|array $params
     * @return PDOStatement
     */
    public function prepareQuery($query, $params = null)
    {
        /**
         * @var $stmt PDOStatement
         */
        $stmt = $this->prepare($query);
        if ($stmt !== false && is_array($params)) {
            foreach ($params as $key => $param) {

                if (is_array($param)) {
                    if ($param[1] !== null) {
                        if (is_array($param[1])) {
                            $stmt->bindValue(":{$key}0", $param[1][0]);
                            $stmt->bindValue(":{$key}1", $param[1][1]);
                        } else {
                            $stmt->bindValue(":{$key}", $param[1]);
                        }
                    } else {
                        $stmt->bindValue(":{$key}", $param[1], PDO::PARAM_NULL);
                    }
                } else {
                    if ($param !== null) {
                        $stmt->bindValue(":{$key}", $param);
                    } else {
                        $stmt->bindValue(":{$key}", $param, PDO::PARAM_NULL);
                    }
                }
            }
        }

        return $stmt;
    }

    /**
     * @param $module
     * @param $model
     * @return string
     */
    public function getTableName($module, $model)
    {
        $config = ConfigModule::getInstance()->getConfig($module);
        if (isset($config['tables'])) {
            foreach ($config['tables'] as $table) {
                $mn = explode('\\', $table['collection']);
                $mn = $mn[1] . '_' . $mn[3];

                if ($mn == $module . '_' . $model) {
                    return $table['table_name'];
                }
            }
        }
    }
}
