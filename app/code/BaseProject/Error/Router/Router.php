<?php

namespace BaseProject\Error\Router;

use App\StatusCodes;

class Router extends \App\libs\App\Router
{


    /**
     * Error_Router constructor.
     * @param int $status
     */
    public function __construct($status = null)
    {
        parent::__construct();
        switch ($status) {
            case StatusCodes::HTTP_NOT_FOUND:
                $this->_currentUri = '/Error/Error/error404';
                break;
            case StatusCodes::HTTP_FORBIDDEN:
                $this->_currentUri = '/Error/Error/error403';
                break;
        }
        $this->getRoute();
    }
}