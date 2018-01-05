<?php

namespace App;

use ReflectionClass;

/**
 * Class Db
 */
class Db
{
    /**
     * @throws \Exception
     */
    public function generateSqlDbModification()
    {
        $config = ConfigModule::getInstance()->getConfigAllModules('tables');
        $pathPhp = App::PathRoot() . '/../install/php/';
        $pathSql = App::PathRoot() . '/../install/sql/';

        $dbFields = [];
        foreach ($config as $tables) {

            foreach ($tables as $table) {
                $modelName = $table['model'];
                $tableName = $table['table_name'];

                $rc = new ReflectionClass($modelName);
                $dbFields[$modelName] = ['table_name' => $tableName];

                foreach ($rc->getProperties() as $key => $property) {
                    $comment = $property->getDocComment();

                    preg_match_all('/@dbType (.*)/', $comment, $matches, PREG_SET_ORDER, 0);

                    if (count($matches) === 1) {
                        $dbFields[$modelName][$property->getName()] = $matches[0][1];
                    }
                }

                $commentClass = $rc->getDocComment();
                preg_match_all('/@dbField (\S*) (.*)/', $commentClass, $matches, PREG_SET_ORDER, 0);

                foreach ($matches as $match) {
                    $dbFields[$modelName][$match[1]] = $match[2];
                }
            }
        }

        $db = [];
        $files = scandir($pathPhp);
        if (($count = count($files)) > 2) {
            $export = [];
            include $pathPhp . $files[$count - 1];
            $db = $export;
        }

        $sql = '';
        foreach ($dbFields as $model => $tableFields) {
            if (!isset($db[$model])) {
                $s = "CREATE TABLE {$tableFields['table_name']} (:fields); \n";
                $fields = '';
                foreach ($tableFields as $name => $value) {
                    if ($name === 'table_name') {
                        continue;
                    }
                    $fields .= $name . ' ' . $value . ', ';
                }
                if ($fields != '') {
                    $fields = substr($fields, 0, strlen($fields) - 2);
                    $sql .= str_replace(':fields', $fields, $s);
                }
            } else {
                foreach ($tableFields as $name => $value) {
                    if ($name === 'table_name' || (isset($db[$model][$name]) && $db[$model][$name] === $value)) {
                        if ($name === 'table_name' && $value !== $db[$model]['table_name']) {
                            $sql .= "RENAME TABLE {$db[$model]['table_name']} TO {$tableFields['table_name']}; \n";
                        }
                        continue;
                    }

                    if (isset($db[$model][$name]) && $db[$model][$name] !== $value) {
                        $sql .= "ALTER TABLE {$tableFields['table_name']} ALTER COLUMN {$name} {$value}; \n";
                    } else {
                        $sql .= "ALTER TABLE {$tableFields['table_name']} ADD {$name} {$value}; \n";
                    }
                }
            }
        }

        $fileName = time();

        if ($sql !== '') {
            $content = '<?php $export = ' . var_export($dbFields, true) . ';';
            file_put_contents($pathPhp . $fileName . '.php', $content);
            file_put_contents($pathSql . $fileName . '.sql', $sql);
        } else {
            throw new \Exception("No modification");
        }
    }

    public function upgradeDb() {
        $config = Config::getInstance()->getConfig();

        if (!isset($config['app']['db_version'])) {
            $config['app']['db_version'] = 0;
        }

        $pathSql = App::PathRoot() . '/../install/sql/';

        $files = scandir($pathSql);
        $re = '/^([0-9]*).(sql)$/';
        $myPdo = MyPdo::getInstance(MyPdo::TYPE_MYSQL);
        $lastDbVersion = $config['app']['db_version'];

        foreach ($files as $file) {
            if ($file === '.' || $file === '..') continue;

            preg_match_all($re, $file, $matches, PREG_SET_ORDER, 0);
            if (isset($matches[0][1])) {
                if ($matches[0][1] > $config['app']['db_version']) {
                    $query = file_get_contents($pathSql . $file);
                    $myStatement = $myPdo->query($query);
                    $myPdo->exec($myStatement);
                    $lastDbVersion = $matches[0][1];
                }
            }
        }
        $config['app']['db_version'] =  $lastDbVersion;
        Config::getInstance()->setConfig($config);
    }
}