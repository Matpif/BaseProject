<?php

namespace BaseProject\Api\Router;

class Router extends \App\libs\App\Router
{


    /**
     * Router constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->getRoute();
    }

    public function getRoute()
    {
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

        return [
            'module' => $this->_module,
            'controller' => $this->_controller,
            'action' => $this->_action,
        ];
    }
}