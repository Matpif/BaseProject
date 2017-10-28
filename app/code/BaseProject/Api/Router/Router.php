<?php

namespace BaseProject\Api\Router;

class Router extends \App\libs\App\Router
{

    public function getRoute()
    {
        $route = parent::getRoute();

        $splitUri = explode('/', $this->_currentUri);

        $name = '';
        foreach ($splitUri as $key => $item) {
            if ($key == 3) {
                $_GET['model'] = $item;
            }

            if ($key > 3) {
                if ($key % 2 == 0) {
                    $name = $item;
                } else {
                    $_GET[$name] = $item;
                }
            }
        }

        return $route;
    }
}