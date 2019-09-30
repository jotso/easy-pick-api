<?php
namespace Controller;

use Lib\Utils;
use Slim\Container;

class AbstractController
{
    protected $_container;

    public function __construct(Container $container)
    {
        $this->_container = $container;
    }
}
