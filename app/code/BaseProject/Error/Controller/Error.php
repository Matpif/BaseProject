<?php

namespace BaseProject\Error\Controller;

use App\libs\App\Controller;
use App\StatusCodes;

class Error extends Controller
{

    /**
     * Error constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    public function error403Action()
    {
        $this->setTitle('403 - Forbidden');
        $this->setTemplate('/error/403.phtml');
        $this->setHtmlStatusCode(StatusCodes::HTTP_FORBIDDEN);
    }

    public function error404Action()
    {
        $this->setTitle('404 - Not found');
        $this->setTemplate('/error/404.phtml');
        $this->setHtmlStatusCode(StatusCodes::HTTP_NOT_FOUND);
    }

    public function error415Action()
    {
        $this->setTitle('415 - Unsupported Media Type');
        $this->setTemplate('/error/415.phtml');
        $this->setHtmlStatusCode(StatusCodes::HTTP_UNSUPPORTED_MEDIA_TYPE);
    }
}