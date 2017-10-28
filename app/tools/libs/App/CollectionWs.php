<?php

namespace App\libs\App;

class CollectionWs extends Collection
{
    /**
     * Instance du webservice
     */
    protected $_webservice;

    public function load($attributes, $sort = null)
    {
        return $this;
    }

    public function loadById($id)
    {
        return $this;
    }

    public function loadAll($sort = null)
    {
        return $this;
    }

    public function loadByQuery($query, $attributes = null)
    {
        return $this;
    }

    public function countElements($attributes = null)
    {
        return $this;
    }
}