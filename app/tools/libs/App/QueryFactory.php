<?php

namespace App\libs\App;


class QueryFactory extends \Aura\SqlQuery\QueryFactory
{
    /**
     * QueryFactory constructor.
     */
    public function __construct()
    {
        parent::__construct('mysql');
    }
}