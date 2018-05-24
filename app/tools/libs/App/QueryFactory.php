<?php

namespace App\libs\App;


class QueryFactory extends \Aura\SqlQuery\QueryFactory
{
    /**
     * QueryFactory constructor.
     * @param string $type
     */
    public function __construct($type = 'mysql')
    {
        parent::__construct($type);
    }
}