<?php

namespace App\libs\App;

interface CollectionInterface
{

    public function load($attributes, $sort = null);

    public function loadById($id);

    public function loadAll($sort = null);

    public function loadByQuery($query, $attributes = null);

    public function countElements($attributes = null);

}