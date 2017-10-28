<?php
/**
 * Autoload class
 * @param $class_name
 */
function floconAutoload($class_name)
{
    if ($class_name == 'FloconApi') {
        include_once __DIR__ . '/FloconApi.php';
    }
}

spl_autoload_register('floconAutoload');